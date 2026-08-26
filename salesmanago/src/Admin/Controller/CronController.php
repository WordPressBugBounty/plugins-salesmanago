<?php

namespace bhr\Admin\Controller;

if( !defined( 'ABSPATH' ) ) exit;

use bhr\Admin\Builder\ProductBuilder;
use bhr\Admin\Model\AdminModel;
use bhr\Admin\Model\Helper;
use bhr\Includes\Integrations\Wpml\WpmlCatalogResolver;
use SALESmanago\Entity\Api\V3\CatalogEntity;
use SALESmanago\Exception\ApiV3Exception;
use SALESmanago\Exception\Exception;
use SALESmanago\Model\Collections\Api\V3\ProductsCollection;
use SALESmanago\Services\Api\V3\Product\ProductService;

class CronController {

    const CRON_SCHEDULE_KEY_0 = 'salesmanago_custom_cron_schedule_0';
    const CRON_SCHEDULE_KEY_60 = 'salesmanago_custom_cron_schedule_60';
    const CRON_SCHEDULE_KEY_180 = 'salesmanago_custom_cron_schedule_180';
    const CRON_SCHEDULE_KEY_300 = 'salesmanago_custom_cron_schedule_300';
    const CRON_TOKEN_OPTION_KEY = 'salesmanago_cron_security_token';

    public function __construct() {
        add_filter('cron_schedules', array($this, 'custom_cron_schedule'));
        add_action('wp', array($this, 'schedule_salesmanago_cron'));
        add_action('salesmanago_cron_event', array($this, 'execute'));
        add_action('init', array($this, 'handle_cron_request'));
    }

    /**
     * Handle CRON request and validate a security token
     * https://your-domain.xx/?salesmanago_cron=run&token=your_token
     *
     * @return void
     */
    public function handle_cron_request() {
        if ( ! isset( $_GET['salesmanago_cron'] ) || $_GET['salesmanago_cron'] !== 'run' ) {
            return;
        }

        $request_token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
        $stored_token = $this->get_cron_security_token();

        if ( ! hash_equals( $stored_token, $request_token ) ) {
            status_header( 404 );
            exit();
        }

        do_action( 'salesmanago_cron_event' );
        status_header( 200 );
        exit();
    }

    /**
     * Retrieves the cron security token from the database or generates a new one if it doesn't exist.
     *
     * @return string
     */
    private function get_cron_security_token() {
        $token = get_option( self::CRON_TOKEN_OPTION_KEY );

        if ( empty( $token ) ) {
            try {
                $token = bin2hex( random_bytes( 32 ) );
            } catch ( \Exception $e ) {
                $token = wp_generate_password( 64, true, false );
            }
            update_option( self::CRON_TOKEN_OPTION_KEY, $token );
        }

        return $token;
    }
    
    /**
    * Gets the cron security token, optionally masked for display purposes.
    *
    * @param bool $masked
    * @return string
    */
    public function get_cron_token( bool $masked = true ) {
        $token = $this->get_cron_security_token();
        
        if ( $masked ) {
            return str_repeat('*', max(0, strlen($token) - 6)) . substr($token, -6);
        }
        
        return $token;
    }

	/**
	 * Schedule SM cron event
	 *
	 * @param null $custom_cron_schedule
	 *
	 * @return void
	 */
    public function schedule_salesmanago_cron( $custom_cron_schedule = null ) {
        $recurrence = $custom_cron_schedule && is_string( $custom_cron_schedule )
            ? $custom_cron_schedule
            : self::CRON_SCHEDULE_KEY_0;

        if ( $recurrence === self::CRON_SCHEDULE_KEY_0 ) {
            $this->update_cron_schedule( '0' );
            return;
        }

        if ( !wp_next_scheduled( 'salesmanago_cron_event' ) ) {
            wp_schedule_event( time(), $recurrence, 'salesmanago_cron_event' );
        }
    }

    /**
     * Initialize custom cron schedules
     *
     * @param array $schedules
     * @return array
     */
    public function custom_cron_schedule( array $schedules ) {
        $schedules[ self::CRON_SCHEDULE_KEY_60 ] = [
            'interval' => 60,
            'display' => __('Manago AI Custom Cron Schedule - 60 sec')
        ];
        $schedules[ self::CRON_SCHEDULE_KEY_180 ] = [
            'interval' => 180,
            'display' => __('Manago AI Custom Cron Schedule - 180 sec'),
        ];
        $schedules[ self::CRON_SCHEDULE_KEY_300 ] = [
            'interval' => 300,
            'display' => __('Manago AI Custom Cron Schedule - 300 sec'),
        ];

        return $schedules;
    }

    /**
     * Update cron schedule
     *
     * @param string $interval
     * @return void
     */
    public function update_cron_schedule( string $interval ) {
        wp_clear_scheduled_hook('salesmanago_cron_event');
        if ( $interval === '0' ) {
             return;
        }
        $this->schedule_salesmanago_cron( $interval );
    }

    /**
     * Execute cron event
     */
    public function execute() {
        try {
            $products = $this->get_stored_products();

            if (!$products) {
                return;
            }

            $admin_model = $this->initialize_admin_model();
            $product_identifier_type = $admin_model->getPlatformSettings()->getPluginWc()->getProductIdentifierType();

            $groupedProducts = $this->group_products_by_catalog( $products, $admin_model );

            foreach ( $groupedProducts['groups'] as $catalogId => $products ) {
                $products_collection = $this->build_product_collection( $products, $product_identifier_type, $admin_model );
                $this->upsert_product_collection( $products_collection, $admin_model, $catalogId );
            }

            $this->clear_stored_products( $groupedProducts['unresolved'] );
        } catch ( ApiV3Exception $e ) {
            $entry = array(
                'reasonCode' => $e->getCode(),
                'message' => $e->getMessage(),
            );
            Helper::salesmanago_log( $entry, __FILE__, true );
        } catch ( Exception $e ) {
            Helper::salesmanago_log( $e->getViewMessage(), __FILE__ );
        }
    }

    /**
     * Initialize AdminModel
     *
     * @return AdminModel
     */
    private function initialize_admin_model() {
        $admin_model = new AdminModel();
        $admin_model->getConfigurationFromDb();
        $admin_model->getPlatformSettingsFromDb();

        return $admin_model;
    }

    /**
     * Build ProductsCollection from stored products
     *
     * @param array $products
     * @param string $product_identifier_type
     * @param AdminModel $admin_model
     *
     * @return ProductsCollection|null
     * @throws Exception
     */
    private function build_product_collection( array $products, string $product_identifier_type, AdminModel $admin_model ) {
        $product_builder = new ProductBuilder( $admin_model );
        $products_collection = new ProductsCollection();

        foreach ( $products as $product ) {
            $products_collection = $product_builder->add_product_to_collection(
                $product->id,
                $product_identifier_type,
                $products_collection
            );

            if ( $product->get_children() ) {
                foreach ( $product->get_children() as $childId ) {
                    $products_collection = $product_builder->add_product_to_collection(
                        $childId,
                        $product_identifier_type,
                        $products_collection
                    );
                }
            }
        }

        return $products_collection;
    }

    /**
     * Send ProductsCollection to SALESmanago Product Catalog
     *
     * @param ProductsCollection $product_collection
     * @param AdminModel $admin_model
     * @param string $catalogId
     *
     * @return void
     * @throws ApiV3Exception
     * @throws Exception
     */
    private function upsert_product_collection(
		ProductsCollection $product_collection,
		AdminModel $admin_model,
		string $catalogId
    ) {
        $catalog = new CatalogEntity( [ 'catalogId' => $catalogId ] );
        $product_service = new ProductService( $admin_model->getConfiguration() );

        if ( $product_collection->count() > 100)  {
            $chunks = $product_collection->chunk();
            foreach ($chunks as $chunk) {
                $product_service->upsertProducts( $catalog, $chunk );
            }
        } else {
            $product_service->upsertProducts( $catalog, $product_collection );
        }
    }

	/**
	 * Groups queued products by their resolved catalog.
	 *
	 * Products without a selected catalog remain unresolved and are retained in the CRON queue for later run.
	 *
	 * @param  array  $products
	 * @param  AdminModel  $admin_model
	 *
	 * @return array
	 */
	private function group_products_by_catalog( array $products, AdminModel $admin_model ) {
		$configuration = $admin_model->getConfiguration();
		$enabled = $admin_model->getPlatformSettings()->isWpmlMultilocationEnabled();
		$resolver = new WpmlCatalogResolver();
		$groups = [];
		$unresolved = [];

		foreach ( $products as $product ) {
			$catalogId = $resolver->getCatalogIdForProduct(
				(int) $product->get_id(),
				$configuration->getLocation(),
				$configuration->getMultilocations(),
				$configuration->getActiveCatalogsByLocation(),
				$enabled
			);

			if ( !$resolver->isMultiCatalogMode( $enabled ) ) {
				$catalogId = $configuration->getActiveCatalog();
			}

			if ( empty( $catalogId ) ) {
				$unresolved[] = $product;
				continue;
			}

			$groups[ $catalogId ][] = $product;
		}

		return [
			'groups' => $groups,
			'unresolved' => $unresolved
		];
	}

    /**
     * Return products that are already stored in DB
     *
     * @return array
     */
    private function get_stored_products() {
        return get_option( 'salesmanago_cron' , [] );
    }

    /**
     * Clear stored products and update DB
     *
     * Unresolved products remain in the DB (WPML only)
     *
     * @return void
     */
    private function clear_stored_products( array $unresolved ) {
        update_option( 'salesmanago_cron', $unresolved );
    }

}
