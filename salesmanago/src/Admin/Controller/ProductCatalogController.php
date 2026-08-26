<?php

namespace bhr\Admin\Controller;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use bhr\Admin\Builder\ProductBuilder;
use bhr\Admin\Entity\Configuration;
use bhr\Admin\Entity\MessageEntity;
use bhr\Admin\Model\AdminModel;
use bhr\Admin\Model\Helper;
use bhr\Admin\Model\ProductCatalogModel;
use bhr\Includes\GlobalConstant;
use bhr\Includes\Integrations\Wpml\WpmlCatalogResolver;
use Error;
use Exception;
use SALESmanago\Entity\Api\V3\CatalogEntity;
use SALESmanago\Exception\ApiV3Exception;
use SALESmanago\Exception\Exception as SmException;
use SALESmanago\Services\Api\V3\CatalogService;
use SALESmanago\Services\Api\V3\ProductService;

class ProductCatalogController {

	/**
	 * @var ProductCatalogModel $ProductCatalogModel
	 */
	private $ProductCatalogModel;

	/**
	 * @var AdminModel $AdminModel
	 */
	private $AdminModel;

	/**
	 * @var WpmlCatalogResolver $WpmlCatalogResolver
	 */
	private $WpmlCatalogResolver;

	/**
	 * @var bool
	 */
	private $error = false;

	/**
	 * @var array
	 */
	private $mapping;

    /**
     * @var array
     */
    private array $attributes;

	/**
	 * @var array|string[]
	 */
	private array $systemDetails = array(
		GlobalConstant::MAP_BRAND,
		GlobalConstant::MAP_MANUFACTURER,
		GlobalConstant::MAP_SEASON,
		GlobalConstant::MAP_COLOR
	);

	/**
	 * @var array|string[]
	 */
	private array $customDetails = array(
		GlobalConstant::MAP_DETAIL_1,
		GlobalConstant::MAP_DETAIL_2,
		GlobalConstant::MAP_DETAIL_3,
		GlobalConstant::MAP_DETAIL_4,
		GlobalConstant::MAP_DETAIL_5
	);

    /**
	 * @param  ProductCatalogModel $ProductCatalogModel
	 * @throws SmException
	 */
	public function __construct( $ProductCatalogModel ) {
		$this->ProductCatalogModel = $ProductCatalogModel;
		$this->AdminModel          = new AdminModel();
		if ( ! $this->AdminModel->getConfigurationFromDb() ) {
			throw new SmException( 'Cannot get configuration from DB' );
		}

		$this->WpmlCatalogResolver = new WpmlCatalogResolver();
	}

	/**
	 * Process form request with APIv3 Key
	 *
	 * @param array $request
	 * @return void
	 */
	public function processApiV3Key( $request ) {
		if ( isset( $request['api-v3-key'] ) ) {
			try {
				if ( ! preg_match( '/^[a-zA-Z0-9]{1,64}$/', $request['api-v3-key'] ) ) {
						MessageEntity::getInstance()->addMessage( 'Invalid API Key', 'error', 706 );
						return;
				}
				$this->ProductCatalogModel->saveApiV3Key( $request['api-v3-key'] );
				$this->getCatalogList();
				if ( $this->error ) {
					$this->ProductCatalogModel->saveApiV3Key( '' );
				} else {
					MessageEntity::getInstance()->addMessage( 'Authentication successful', 'success', 704 );
				}
			} catch ( Exception $ex ) {
				Helper::salesmanago_log( $ex->getMessage(), __FILE__ );
				MessageEntity::getInstance()->addMessage( 'Unknown API Error', 'error', 706 );
			}
		}
	}

	/**
	 * Get catalogs from SALESmanago
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getCatalogList() {
		if ( empty( Configuration::getInstance()->getApiV3Key() ) ) {
			return array();
		}
		try {
			$catalogService = new CatalogService( Configuration::getInstance() );
			$Catalogs       = $catalogService->getCatalogs();
			$this->ProductCatalogModel->saveCatalogs( $Catalogs );
			return $Catalogs;
		} catch ( ApiV3Exception $apiEx ) {
			$this->handleApiV3Exception( $apiEx );
		}
	}

	/**
	 * Get catalogs from SALESmanago
	 *
	 * @return int
	 * @throws Exception
	 */
	public function getCatalogsLimit() {
		if ( empty( Configuration::getInstance()->getApiV3Key() ) ) {
			return null;
		}
		try {
			$catalogService = new CatalogService( Configuration::getInstance() );
			return $catalogService->getLimit();
		} catch ( ApiV3Exception $apiEx ) {
			$this->handleApiV3Exception( $apiEx );
		}
	}

	/**
	 * Creates a Manago AI catalog from the form.
	 *
	 * In multi-catalog mode, validates the selected location and automatically
	 * assigns the created catalog to that location.
	 *
	 * @return false|void
	 */
	public function processCatalogCreateRequest() {
		try {
			$request = wp_unslash( $_POST );
			$data = $this->processCatalogData( $request );
			if ( ! $data ) {
				return false;
			}

			if ( $this->isMultiCatalogMode() ) {
				$rawLocation = $request['sm-catalog-location'] ?? '';

				$location = is_scalar( $rawLocation )
					? sanitize_text_field( (string) $rawLocation )
					: '';

				if ( '' === $location || !$this->isSelectableMultilocation( $location ) ) {
					MessageEntity::getInstance()->addMessage( 'Invalid catalog location', 'error', 709 );
					return false;
				}

				$data['location'] = $location;
			}

			$catalog_id = $this->createCatalog( $data );
			if ( $catalog_id ) {
				if ( isset( $data['location'] ) ) {
					$this->getCatalogList();
					$this->ProductCatalogModel->assignCatalogToLocation( $data['location'], $catalog_id );
				} else {
					$this->ProductCatalogModel->setActiveCatalog( $catalog_id );
				}
				MessageEntity::getInstance()->addMessage( 'Catalog created!', 'success', 707 );
				wp_safe_redirect( admin_url( 'admin.php?page=salesmanago-product-catalog&catalog-created=true' ) );
				exit;
			} else {
				MessageEntity::getInstance()->addMessage( 'Problem on creating catalog', 'apiV3Error', 711 );
			}
		} catch ( Error | Exception $e ) {
			Helper::salesmanago_log( $e->getMessage(), __FILE__ );
			MessageEntity::getInstance()->addMessage( 'Unknown API Error', 'error', 706 );
			return false;
		}
	}

	/**
	 * Create new catalog
	 *
	 * @param $catalog_data
	 * @return string|void
	 */
	public function createCatalog( $catalog_data ) {
		$catalogService = new CatalogService( Configuration::getInstance() );
		try {
			if ( ! empty( Configuration::getInstance()->getApiV3Key() ) ) {
				$this->ProductCatalogModel->buildCatalogEntity( $catalog_data );
				$response = $catalogService->createCatalog(
					$this->ProductCatalogModel->getCatalogEntity()
				);
				return $response['catalogId'];
			}
		} catch ( ApiV3Exception $apiEx ) {
			$this->handleApiV3Exception( $apiEx );
		}
	}

	/**
	 * Helper function that parses APIv3 error response string from SALESmanago
	 * reason code: X - message : MESSAGE
	 * returning array with two elements, (X, MESSAGE)
	 *
	 * @param string $apiV3StringResponse
	 *
	 * @return array
	 */
	private function parseSmStringResponse( $apiV3StringResponse ) {
		$splitResp = explode( ' - ', $apiV3StringResponse );
		$reason    = explode( ': ', $splitResp[0] )[1];
		$message   = explode( ': ', $splitResp[1] )[1];

		return array( trim( $reason ), trim( $message ) );
	}

	/**
	 * Handle API v3 exception based on the reason code
	 *
	 * @param ApiV3Exception $api_ex ApiV3 Exception.
	 *
	 * @return void
	 */
	private function handleApiV3Exception( $api_ex ) {
		try {
			$this->error = true;
			$reason_code = (int) $this->parseSmStringResponse( $api_ex->getMessage() )[0];
			foreach ( $api_ex->getCombined() as $reason_code => $message ) {
				$log_entry = array(
					'reasonCode' => $reason_code,
					'message'    => $message,
				);
				Helper::salesmanago_log( $log_entry, debug_backtrace()[1]['function'], true );
			}
			switch ( $reason_code ) {
				case 10: // API authentication error
					MessageEntity::getInstance()->addMessage( 'Incorrect APIv3 Key', 'apiV3Error', 705 );
					// Reset API key if incorrect
					$this->ProductCatalogModel->saveApiV3Key( '' );
					break;
				case 18: // Wrong Location value
					MessageEntity::getInstance()->addMessage( 'Wrong location', 'apiV3Error', 708 );
					break;
				case 428:
					MessageEntity::getInstance()->addMessage( 'Timeout', 'apiV3Error', 710 );
			}
		} catch ( Error | Exception $e ) {
			Helper::salesmanago_log( $e->getMessage(), debug_backtrace()[1]['function'] );
		}
	}

	/**
	 * Handle setting active catalog request
	 *
	 * @param $request $_REQUEST
	 * @return void
	 */
	public function processSetActiveCatalogRequest( $request ) {
		try {
			$catalog = $request['sm-product-catalog-select'];
			$this->ProductCatalogModel->setActiveCatalog( $catalog );
			header( 'Location:/wp-admin/admin.php?page=salesmanago-product-catalog' );
		} catch ( Exception $ex ) {
			Helper::salesmanago_log( $ex->getMessage(), __FILE__ );
			MessageEntity::getInstance()->addMessage( 'Error on setting the active catalog', 'error', 709 );
		}
	}

	/**
	 * Upsert product to SM on WC hook
	 *
	 * @param $wc_product
	 * @param  bool  $deleteAction
	 *
	 * @return void
	 */
	public function upsertProduct( $wc_product, $deleteAction = false ) {
		try {
			$configuration = $this->AdminModel->getConfiguration();
			if ( ! $configuration->getApiV3Key() ) {
				return;
			}
			$ProductBuilder    = new ProductBuilder( $this->AdminModel );
			$productIdentifierType = $this->AdminModel->getPlatformSettings()->getPluginWc()->getProductIdentifierType();
			$platformSettings = $this->AdminModel->getPlatformSettings();

			$ProductCollection = $ProductBuilder->add_product_to_collection( $wc_product->get_id(), $productIdentifierType, null, [], $deleteAction );
			// Variable product case - simple products have no children
			if ( $wc_product->get_children() ) {
				$items = $ProductCollection->getItems();
				$parentImageUrls = reset( $items )->getImageUrls();
				$parentTermsForCategories = [];
				if ( isset( $items['productId'] ) ) {
					$terms = get_the_terms( $items['productId'], 'product_cat' );
					$parentTermsForCategories = is_array( $terms ) ? $terms : [];
				}
				foreach ( $wc_product->get_children() as $product_variation_id ) {
					$ProductCollection = $ProductBuilder->add_product_to_collection(
						$product_variation_id,
						$productIdentifierType,
						$ProductCollection,
						$parentImageUrls,
						$deleteAction,
						$parentTermsForCategories
					);
				}
			}

			$catalogId = $this->WpmlCatalogResolver->getCatalogIdForProduct(
				(int) $wc_product->get_id(),
				$configuration->getLocation(),
				$configuration->getMultilocations(),
				$configuration->getActiveCatalogsByLocation(),
				$platformSettings->isWpmlMultilocationEnabled()
			);

			// in single-catalog mode resolver returns null
			$catalogId = $catalogId ?? $configuration->getActiveCatalog();

			if ( empty( $catalogId ) ) {
				return;
			}

			$Catalog = new CatalogEntity(
				array(
					'catalogId' => $catalogId,
				)
			);
			  $ProductService = new ProductService( $this->AdminModel->getConfiguration() );

              if ( $ProductCollection->count() > 100 ) {
                  $collections = $ProductCollection->chunk();

                  foreach ( $collections as $collection ) {
                      $ProductService->upsertProducts( $Catalog, $collection );
                  }
              } else {
                  $ProductService->upsertProducts( $Catalog, $ProductCollection );
              }
		} catch ( ApiV3Exception $api_ex ) {
			$this->handleApiV3Exception( $api_ex );
			// Highlight product upsert error only for reason code 10
			if ( in_array( 10, $api_ex->getCodes() ) ) {
				$this->AdminModel->getConfiguration()->setIsNewApiError( true );
				$this->AdminModel->saveConfiguration();
			}
		} catch ( Error | Exception $ex ) {
			Helper::salesmanago_log( $ex->getMessage(), __FILE__ );
		}
	}

	/**
	 * Process catalog post data
	 *
	 * @param $request_data
	 * @return array
	 */
	private function processCatalogData( $request_data ) {
		$catalog_data                          = array();
		$catalog_data['name']                  = ! empty( $request_data['sm-catalog-name'] ) ? str_replace( ' ', '_', sanitize_text_field( $request_data['sm-catalog-name'] ) ) : '';
		$catalog_data['currency']              = ! empty( $request_data['sm-catalog-currency'] ) ? sanitize_text_field( $request_data['sm-catalog-currency'] ) : '';
		$catalog_data['recommendation_frames'] = ! empty( $request_data['sm-catalog-allow-in-recommendation-frames'] ) ? (bool) $request_data['sm-catalog-allow-in-recommendation-frames'] : '';
		return $catalog_data;
	}

	/**
	 * @return void
	 */
	public function setDetailsMappingToPlatformSettings() {
		$this->AdminModel->getPlatformSettings()->setDetailsMapping( $this->mapping );
		$this->AdminModel->savePlatformSettings();
	}

	/**
	 * @deprecated since 3.9.0
	 *
	 * @return array
	 */
	public function getAttributesNames() {
        $attributesFromDb = $this->ProductCatalogModel->getAttributesFromDb();
        $customAttributes = $this->ProductCatalogModel->getCustomAttributesFromDb();

        $this->attributes = array_merge( $attributesFromDb, $customAttributes );

        return $this->ProductCatalogModel->getAttributesNamesFromArray( $this->attributes );
	}

	/**
	 * Get all mapping labels to render in product catalog settings
	 *
	 * @return array
	 */
	public function getDetailMappingLabelsToRender() {
		return array_merge(
			$this->getAttributes(),
			$this->getProductCategoriesSettings()
		);
	}

	/**
	 * Get product category settings
	 *
	 * @return array
	 */
	public function getProductCategoriesSettings(): array {
		return [
			[
				'name' => 'Main categories IDs',
				'value' => '',
				'label' => 'Main categories IDs',
				'id' => '0',
				'type' => 'category'
			],
			[
				'name' => 'All categories IDs',
				'value' => '',
				'label' => 'All categories IDs',
				'id' => '1',
				'type' => 'category'
			]
		];
	}

    /**
     * Get product attributes
     *
     * @return array
     */
    public function getAttributes() {
	    $attributesFromDb = $this->ProductCatalogModel->getAttributesFromDb();

	    $customAttributes = [
			[
			    'name' => 'Custom attributes',
			    'value' => '',
			    'label' => 'Custom attributes',
			    'id' => '0',
			    'type' => 'custom attribute'
	        ]
	    ];

	    return array_merge( $attributesFromDb, $customAttributes );
    }
	/**
	 * @param array $mapping
	 * @return ProductCatalogController
	 */
	public function sanitizeMapping( $mapping ) {
		$this->mapping = array_map( 'sanitize_text_field', $mapping );

		return $this;
	}

	public function getMapping() {
		return $this->mapping;
	}

	/**
	 * Rebuild mapping from DB
	 *
	 * @return ProductCatalogController
	 */
	public function rebuildMapping() {
		$rebuilt = array();

		foreach ($this->mapping as $field => $json) {
			if (!$json) {
				$rebuilt[$field] = null;
				continue;
			}

			$decoded = json_decode(stripslashes($json), true);
			$rebuilt[$field] = $decoded;
		}

		$this->mapping = $rebuilt;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getCustomDetails() {
		return $this->customDetails;
	}

	/**
	 * @return array
	 */
	public function getSystemDetails() {
		return $this->systemDetails;
	}

    /**
     * Save product to database in case CRON is enabled
     *
     * @param $wc_product
     *
     * @return void
     */
	public function storeProduct( $wc_product ) {
        try {
            $data = get_option( 'salesmanago_cron', [] );
            $data = is_array( $data ) ? $data : [];

            $data = array_filter( $data, function ( $product ) use ( $wc_product ) {
                return isset( $product->id ) && ($product->id !== $wc_product->id);
            });

            $data[] = $wc_product;
            update_option( 'salesmanago_cron', $data );
        } catch ( Exception $e ) {
            Helper::salesmanago_log( $e->getMessage(), __FILE__ );
        }
	}

	/**
	 * Returns cached catalogs enriched with active WPML language data.
	 *
	 * @return array
	 */
	public function getMultiCatalogRows(): array {
		$configuration = $this->AdminModel->getConfiguration();
		$settings = $this->AdminModel->getPlatformSettings();

		$languagesByLocation = $this->WpmlCatalogResolver->getActiveLocations(
			$configuration->getLocation(),
			$configuration->getMultilocations(),
			$settings->isWpmlMultilocationEnabled()
		);

		return $this->ProductCatalogModel->getMultiCatalogRows( $languagesByLocation );
	}

	/**
	 * Returns one creatable catalog location per active WPML location.
	 *
	 * Languages sharing a location are grouped into one entry.
	 *
	 * @return array
	 */
	public function getMultiCatalogLocations(): array {
		$configuration = $this->AdminModel->getConfiguration();
		$settings = $this->AdminModel->getPlatformSettings();

		return $this->WpmlCatalogResolver->getActiveLocations(
			$configuration->getLocation(),
			$configuration->getMultilocations(),
			$settings->isWpmlMultilocationEnabled()
		);
	}

	/**
	 * Returns true only when the enabled WPML integration can resolve languages.
	 *
	 * @return bool
	 */
	public function isMultiCatalogMode(): bool {
		return $this->WpmlCatalogResolver->isMultiCatalogMode(
			$this->AdminModel->getPlatformSettings()->isWpmlMultilocationEnabled()
		);
	}

	/**
	 * Validates and persists selected catalogs from the multi-catalog form.
	 *
	 * @param  array  $request
	 *
	 * @return void
	 */
	public function processSetActiveCatalogsRequest( array $request ): void {
		if ( !current_user_can( 'manage_options' ) ) {
			return;
		}

		$selectedCatalogIds = isset( $request['sm-selected-catalogs'] )
			? wp_unslash( $request['sm-selected-catalogs'] )
			: [];

		if ( !is_array( $selectedCatalogIds )) {
			MessageEntity::getInstance()->addMessage(
				'Invalid catalog selection request.',
				'error',
				709
			);
			return;
		}

		$catalogsById = $this->ProductCatalogModel->getCachedCatalogsById();
		$mapping = [];

		foreach ( $selectedCatalogIds as $catalogId ) {
			if ( !is_string( $catalogId )) {
				MessageEntity::getInstance()->addMessage(
					'Invalid catalog selection request.',
					'error',
				709
				);
				return;
			}

			$catalogId = sanitize_text_field( $catalogId );

			if ( !isset( $catalogsById[ $catalogId ] )) {
				MessageEntity::getInstance()->addMessage(
					'A selected catalog is no longer available. Refresh the catalog list and try again.',
					'error',
					709
				);
				return;
			}

			$location = $catalogsById[ $catalogId ]['location'];

			if ( !$this->isSelectableMultilocation( $location )) {
				MessageEntity::getInstance()->addMessage(
					'A selected catalog location is not available for WPML synchronization.',
					'error',
					709
				);
				return;
			}

			if ( isset( $mapping[ $location ] )) {
				MessageEntity::getInstance()->addMessage(
					'Only one catalog can be selected for each location.',
					'error',
					709
				);
				return;
			}

			$mapping[ $location ] = $catalogId;
		}

		$this->ProductCatalogModel->saveActiveCatalogsByLocation( $mapping );

		wp_safe_redirect( admin_url( 'admin.php?page=salesmanago-product-catalog&catalogs-saved=1' ) );
		exit;
	}

	/**
	 * Checks whether a location can be selected for multi-catalog synchronization.
	 *
	 * @param  string  $location
	 *
	 * @return bool
	 */
	private function isSelectableMultilocation( string $location ): bool {
		$configuration = $this->AdminModel->getConfiguration();
		$settings = $this->AdminModel->getPlatformSettings();

		return $this->WpmlCatalogResolver->isSelectableLocation(
			$location,
			$configuration->getLocation(),
			$configuration->getMultilocations(),
			$settings->isWpmlMultilocationEnabled()
		);
	}
}
