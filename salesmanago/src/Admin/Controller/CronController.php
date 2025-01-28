<?php

namespace bhr\Admin\Controller;

if( !defined( 'ABSPATH' ) ) exit;

use bhr\Admin\Builder\ProductBuilder;
use bhr\Admin\Model\AdminModel;
use bhr\Admin\Model\Helper;
use SALESmanago\Entity\Api\V3\CatalogEntity;
use SALESmanago\Exception\ApiV3Exception;
use SALESmanago\Exception\Exception;
use SALESmanago\Model\Collections\Api\V3\ProductsCollection;
use SALESmanago\Services\Api\V3\Product\ProductService;

class CronController {

    const CRON_SCHEDULE_KEY_60 = 'salesmanago_custom_cron_schedule_60';
    const CRON_SCHEDULE_KEY_180 = 'salesmanago_custom_cron_schedule_180';
    const CRON_SCHEDULE_KEY_300 = 'salesmanago_custom_cron_schedule_300';

    public function __construct() {
        add_filter('cron_schedules', array($this, 'custom_cron_schedule'));
        add_action('wp', array($this, 'schedule_salesmanago_cron'));
        add_action('salesmanago_cron_event', array($this, 'execute'));
    }

    /**
     * Schedule SM cron event
     *
     * @param $custom_cron_schedules
     * @return void
     */
    public function schedule_salesmanago_cron( $custom_cron_schedule = null ) {
        if ( !wp_next_scheduled( 'salesmanago_cron_event' ) ) {
            wp_schedule_event( time(), empty( $custom_cron_schedule ) ? self::CRON_SCHEDULE_KEY_60 : $custom_cron_schedule, 'salesmanago_cron_event' );
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
            'display' => __('SALESmanago Custom Cron Schedule - 60 sec')
        ];
        $schedules[ self::CRON_SCHEDULE_KEY_180 ] = [
            'interval' => 180,
            'display' => __('SALESmanago Custom Cron Schedule - 180 sec'),
        ];
        $schedules[ self::CRON_SCHEDULE_KEY_300 ] = [
            'interval' => 300,
            'display' => __('SALESmanago Custom Cron Schedule - 300 sec'),
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

            $products_collection = $this->build_product_collection($products, $product_identifier_type, $admin_model);

            $this->upsert_product_collection($products_collection, $admin_model);

            $this->clear_stored_products();
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
            $products_collection = $product_builder->add_product_to_collection( $product->id, $product_identifier_type );

            if ( $product->get_children() ) {
                foreach ( $product->get_children() as $childId ) {
                    $products_collection = $product_builder->add_product_to_collection(
                        $childId, $product_identifier_type, $products_collection
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
     *
     * @return void
     * @throws ApiV3Exception
     * @throws Exception
     */
    private function upsert_product_collection( ProductsCollection $product_collection, AdminModel $admin_model ) {
        $catalog = new CatalogEntity( [ 'catalogId' => $admin_model->getConfiguration()->getActiveCatalog() ] );
        $product_service = new ProductService( $admin_model->getConfiguration() );
        $product_service->upsertProducts( $catalog, $product_collection );
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
     * @return void
     */
    private function clear_stored_products() {
        update_option( 'salesmanago_cron', [] );
    }

}