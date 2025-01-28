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
	 * Handle create new catalog request
	 *
	 * @return false|void
	 */
	public function processCatalogCreateRequest() {
		try {
			$data = $this->processCatalogData( $_POST );
			if ( ! $data ) {
				return false;
			}
			$catalog_id = $this->createCatalog( $data );
			if ( $catalog_id ) {
				$this->ProductCatalogModel->setActiveCatalog( $catalog_id );
				MessageEntity::getInstance()->addMessage( 'Catalog created!', 'success', 707 );
				// Redirect to product-catalog view and show notification
				header( 'Location:/wp-admin/admin.php?page=salesmanago-product-catalog&catalog-created=true' );
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
	 * @return void
	 */
	public function upsertProduct( $wc_product ) {
		try {
			if ( ! $this->AdminModel->getConfiguration()->getActiveCatalog() || ! $this->AdminModel->getConfiguration()->getApiV3Key() ) {
				return;
			}
			$ProductBuilder    = new ProductBuilder( $this->AdminModel );
			$productIdentifierType = $this->AdminModel->getPlatformSettings()->getPluginWc()->getProductIdentifierType();

			$ProductCollection = $ProductBuilder->add_product_to_collection( $wc_product->id, $productIdentifierType );
			// Variable product case - simple products have no children
			if ( $wc_product->get_children() ) {
				$items = $ProductCollection->getItems();
				$parentImageUrls = reset( $items )->getImageUrls();
				foreach ( $wc_product->get_children() as $product_variation_id ) {
					$ProductCollection = $ProductBuilder->add_product_to_collection( $product_variation_id, $productIdentifierType, $ProductCollection, $parentImageUrls );
				}
			}
			$Catalog = new CatalogEntity(
				array(
					'catalogId' => $this->AdminModel->getConfiguration()->getActiveCatalog(),
				)
			);
			  $ProductService = new ProductService( $this->AdminModel->getConfiguration() );
			  $ProductService->upsertProducts( $Catalog, $ProductCollection );
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
		$catalog_data['name']                  = ! empty( $request_data['sm-catalog-name'] ) ? str_replace( ' ', '_', $request_data['sm-catalog-name'] ) : '';
		$catalog_data['currency']              = ! empty( $request_data['sm-catalog-currency'] ) ? trim( $request_data['sm-catalog-currency'] ) : '';
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
	 * @return array
	 */
	public function getAttributesNames() {
        $attributesFromDb = $this->ProductCatalogModel->getAttributesFromDb();
        $customAttributes = $this->ProductCatalogModel->getCustomAttributesFromDb();

        $this->attributes = array_merge( $attributesFromDb, $customAttributes );

        return $this->ProductCatalogModel->getAttributesNamesFromArray( $this->attributes );
	}

    /**
     * @return array
     */
    public function getAttributes() {
        return $this->attributes;
    }
	/**
	 * @param array $mapping
	 * @return ProductCatalogController
	 */
	public function sanitizeMapping( $mapping ) {
		$this->mapping = array_map( 'sanitize_text_field', $mapping );

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
		$data = get_option( 'salesmanago_cron', [] );
		$data = array_filter( $data, fn ( $product ) => $product->id !== $wc_product->id );
		$data[] = $wc_product;

		update_option( 'salesmanago_cron', $data );
	}
}
