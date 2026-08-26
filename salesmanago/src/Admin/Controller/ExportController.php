<?php

namespace bhr\Admin\Controller;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use bhr\Admin\Model\AdminModel;
use bhr\Admin\Model\ExportModel;
use bhr\Admin\Model\Helper;
use bhr\Includes\Helper as IncludesHelper;
use bhr\Includes\Integrations\Wpml\WpmlCatalogResolver;
use bhr\Includes\SecureHelper;
use SALESmanago\Entity\Api\V3\CatalogEntity;
use SALESmanago\Exception\ApiV3Exception;
use SALESmanago\Exception\Exception;
use SALESmanago\Controller\ExportController as SMExportController;
use SALESmanago\Services\Api\V3\ProductService;

class ExportController {

	const
		PREPARING   = 'preparing',
		FAILED      = 'failed',
		IN_PROGRESS = 'in_progress',
		LAST_CHECK  = 'last_check',
		DONE        = 'done',
		NO_DATA     = 'no_data',

		CONTACTS = 'contacts',
		EVENTS   = 'events',
		PURCHASE = 'PURCHASE';

	protected $db;
	protected $AdminModel;
	protected $ExportModel;
	protected $SMExportController;

	/**
	 * ExportController constructor.
	 */
	public function __construct() {
		try {
			$this->db         = $GLOBALS['wpdb'];
			$this->AdminModel = new AdminModel();
			if ( ! $this->AdminModel->getConfigurationFromDb() ) {
				throw new Exception( 'Cannot get configuration from DB' );
			}
			$this->ExportModel        = new ExportModel( $this->AdminModel );
			$this->SMExportController = new SMExportController( $this->AdminModel->getConfiguration() );
			$this->registerActions();
		} catch ( \Exception $e ) {
			$this->ExportModel->setMessage( $this->sanitize_error_message( $e->getMessage() ) );
			$this->ExportModel->setStatus( self::FAILED );
			$this->ExportModel->buildResponse();
		}
	}

	/**
	 * Register AJAX actions
	 */
	private function registerActions() {
		Helper::addAction( 'wp_ajax_salesmanago_export_count_contacts', array( $this, 'countContacts' ), 5 );
		Helper::addAction( 'wp_ajax_salesmanago_export_contacts', array( $this, 'exportContacts' ), 5 );

		Helper::addAction( 'wp_ajax_salesmanago_export_count_events', array( $this, 'countEvents' ), 5 );
		Helper::addAction( 'wp_ajax_salesmanago_export_events', array( $this, 'exportEvents' ), 5 );

		Helper::addAction( 'wp_ajax_salesmanago_export_products', array( $this, 'exportProducts' ), 5 );
	}

	/**
	 * Count contacts for export
	 */
	public function countContacts() {
		try {
			SecureHelper::validate_ajax_nonce( 'salesmanago_export_count_contacts' );
			
			if ( ! current_user_can( 'manage_options' ) ) {
				$this->ExportModel->setMessage( 'Access denied' );
				$this->ExportModel->setStatus( self::FAILED );
				$this->ExportModel->buildResponse();
				return;
			}

			$this->ExportModel->parseArgs();
			$this->ExportModel->setExportType( self::CONTACTS );

			$query = $this->ExportModel->getExportContactsQuery( true );
			if ( ! $query ) {
				throw new Exception( 'Failed to generate contacts query' );
			}
			$this->ExportModel->setCount( $this->db->get_var( $query ) );
			$this->ExportModel->setPackageCount( (int) ceil( $this->ExportModel->getCount() / ExportModel::PACKAGE_SIZE ) );
			$this->ExportModel->setStatus( self::PREPARING );
			$this->ExportModel->buildResponse();
		} catch ( Exception $e ) {
			$this->ExportModel->setMessage( $this->sanitize_error_message( method_exists( $e, 'getViewMessage' ) ? $e->getViewMessage() : $e->getMessage() ) );
			$this->ExportModel->setStatus( self::FAILED );
			$this->ExportModel->buildResponse();
		} catch ( \Exception $e ) {
			$this->ExportModel->setMessage( $this->sanitize_error_message( $e->getMessage() ) );
			$this->ExportModel->setStatus( self::FAILED );
			$this->ExportModel->buildResponse();
		}
	}

	/**
	 * Count events for export
	 */
	public function countEvents() {
		try {
			SecureHelper::validate_ajax_nonce( 'salesmanago_export_count_events' );
			
			if ( ! current_user_can( 'manage_options' ) ) {
				$this->ExportModel->setMessage( 'Access denied' );
				$this->ExportModel->setStatus( self::FAILED );
				$this->ExportModel->buildResponse();
				return;
			}

			$this->ExportModel->parseArgs();
			$this->ExportModel->setExportType( self::EVENTS );

			$this->ExportModel->setCount( $this->ExportModel->getEventsData( true ) );
			$this->ExportModel->setPackageCount( (int) ceil( $this->ExportModel->getCount() / ExportModel::PACKAGE_SIZE ) );
			$this->ExportModel->setStatus( self::PREPARING );
			$this->ExportModel->buildResponse();
		} catch ( \Exception $e ) {
			$this->ExportModel->setMessage( $this->sanitize_error_message( $e->getMessage() ) );
			$this->ExportModel->setStatus( self::FAILED );
			$this->ExportModel->buildResponse();
		}
	}

	/**
	 * Export contacts batch
	 */
	public function exportContacts() {
		SecureHelper::validate_ajax_nonce( 'salesmanago_export_contacts' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			$this->ExportModel->setMessage( 'Access denied' );
			$this->ExportModel->setStatus( self::FAILED );
			$this->ExportModel->buildResponse();
			return;
		}

		$this->ExportModel->parseArgs();
		if ( $this->ExportModel->getPackageCount() ) {
			try {
				$this->ExportModel->setExportType( self::CONTACTS );

				$query   = $this->ExportModel->getExportContactsQuery( false );
				if ( ! $query ) {
					throw new Exception( 'Failed to generate export query' );
				}
				$results = $this->db->get_results( $query, ARRAY_A );

				if ( ! empty( $results ) ) {
					$Collection = $this->ExportModel->prepareContactsToExport( $results );
					if ( $Collection && ! $Collection->isEmpty() ) {
						$exportResponse = $this->SMExportController->export( $Collection );

						if ( $exportResponse && $exportResponse->getStatus() ) {
							$this->ExportModel->setLastExportedPackage(
								$this->ExportModel->getLastExportedPackage() + 1
							);
							if ( $this->ExportModel->getLastExportedPackage() + 1 == $this->ExportModel->getPackageCount() ) {
								$this->ExportModel->setStatus( self::LAST_CHECK );
								$this->ExportModel->buildResponse();
							} else {
								$this->ExportModel->setStatus( self::IN_PROGRESS );
								$this->ExportModel->buildResponse();
							}
						} else {
							$this->ExportModel->setMessage( 'Got false response from ExportController' );
							$this->ExportModel->setStatus( self::FAILED );
							$this->ExportModel->buildResponse();
						}
					} else {
						$this->ExportModel->setStatus( self::DONE );
						$this->ExportModel->buildResponse();
					}
				} else {
					$this->ExportModel->setStatus( self::DONE );
					$this->ExportModel->buildResponse();
				}
			} catch ( Exception $e ) {
				$this->ExportModel->setMessage( $this->sanitize_error_message( method_exists( $e, 'getViewMessage' ) ? $e->getViewMessage() : $e->getMessage() ) );
				$this->ExportModel->setStatus( self::FAILED );
				$this->ExportModel->buildResponse();
			} catch ( \Exception $e ) {
				$this->ExportModel->setMessage( $this->sanitize_error_message( $e->getMessage() ) );
				$this->ExportModel->setStatus( self::FAILED );
				$this->ExportModel->buildResponse();
			}
		} else {
			$this->ExportModel->setMessage( 'No data to export' );
			$this->ExportModel->setStatus( self::NO_DATA );
			$this->ExportModel->buildResponse();
		}
	}

	/**
	 * Export events batch
	 */
	public function exportEvents() {
		SecureHelper::validate_ajax_nonce( 'salesmanago_export_events' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			$this->ExportModel->setMessage( 'Access denied' );
			$this->ExportModel->setStatus( self::FAILED );
			$this->ExportModel->buildResponse();
			return;
		}

		$this->ExportModel->parseArgs();
		if ( $this->ExportModel->getPackageCount() ) {
			try {
				$this->ExportModel->setExportType( self::EVENTS );

				$results = $this->ExportModel->getEventsData();

				if ( ! empty( $results ) ) {
					$Collection     = $this->ExportModel->prepareEventsToExport( $results );
					$exportResponse = $this->SMExportController->export( $Collection );

					if ( $exportResponse && $exportResponse->getStatus() ) {
						$this->ExportModel->setLastExportedPackage( $this->ExportModel->getLastExportedPackage() + 1 );
						if ( $this->ExportModel->getLastExportedPackage() + 1 == $this->ExportModel->getPackageCount() ) {
							$this->ExportModel->setStatus( self::LAST_CHECK );
							$this->ExportModel->buildResponse();
						} else {
							$this->ExportModel->setStatus( self::IN_PROGRESS );
							$this->ExportModel->buildResponse();
						}
					} else {
						$this->ExportModel->setMessage( 'Got false response from ExportController' );
						$this->ExportModel->setStatus( self::FAILED );
						$this->ExportModel->buildResponse();
					}
				} else {
					$this->ExportModel->setStatus( self::DONE );
					$this->ExportModel->buildResponse();
				}
			} catch ( Exception $e ) {
				$this->ExportModel->setMessage( $this->sanitize_error_message( method_exists( $e, 'getViewMessage' ) ? $e->getViewMessage() : $e->getMessage() ) );
				$this->ExportModel->setStatus( self::FAILED );
				$this->ExportModel->buildResponse();
			} catch ( \Exception $e ) {
				$this->ExportModel->setMessage( $this->sanitize_error_message( $e->getMessage() ) );
				$this->ExportModel->setStatus( self::FAILED );
				$this->ExportModel->buildResponse();
			}
		} else {
			$this->ExportModel->setMessage( 'No data to export' );
			$this->ExportModel->setStatus( self::NO_DATA );
			$this->ExportModel->buildResponse();
		}
	}

	/**
	 * Handle export products request
	 */
	public function exportProducts() {
		SecureHelper::validate_ajax_nonce( 'salesmanago_export_products' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			$this->ExportModel->setMessage( 'Access denied' );
			$this->ExportModel->setStatus( self::FAILED );
			$this->ExportModel->buildProductExportResponse();
			return;
		}

		if ( ! $this->AdminModel->getConfiguration()->getApiV3Key() ) {
			$this->ExportModel->buildProductExportResponseForExpiredApiKey();
			return;
		}
		try {
			$this->ExportModel->parseProductExportArgs();
			$configuration = $this->AdminModel->getConfiguration();
			$multi_catalog_resolver = new WpmlCatalogResolver();
			$multi_catalog_mode = $multi_catalog_resolver->isMultiCatalogMode(
				$this->AdminModel->getPlatformSettings()->isWpmlMultilocationEnabled()
			);
			$catalog_id = $configuration->getActiveCatalog();
			$language_codes = array();

			if ( $multi_catalog_mode ) {
				$language_codes = $this->ExportModel->getProductExportLanguageCodes();
				$catalog_id = $multi_catalog_resolver->getCatalogIdForLocation(
					$this->ExportModel->getProductExportLocation(),
					$configuration->getActiveCatalogsByLocation(),
					true
				);

				if ( empty( $language_codes ) || empty( $catalog_id ) ) {
					throw new Exception( 'The selected location is not configured for product export' );
				}
			}

			if ( empty( $catalog_id ) ) {
				throw new Exception( 'No product catalog selected for export' );
			}

			$products = $this->ExportModel->getProductsFromDB( $language_codes );
			$productIdentifierType = $this->AdminModel->getPlatformSettingsFromDb()->getPlatformSettings()->getPluginWc()->getProductIdentifierType();
			$ProductsCollection = $this->ExportModel->prepareProductsForExport( $products, $productIdentifierType );
			$ProductService = new ProductService( $this->AdminModel->getConfiguration() );
			$Catalog = new CatalogEntity(
				array(
					'catalogId' => $catalog_id,
				)
			);
			$ProductService->upsertProducts( $Catalog, $ProductsCollection );
			$this->ExportModel->handlePackageCount();
		} catch ( Exception $e ) {
			$this->ExportModel->setStatus( self::FAILED );
			$this->ExportModel->setMessage( $this->sanitize_error_message( $e->getMessage() ) );
			if ( $e instanceof ApiV3Exception ) {
				$arr_of_messages = $e->getAllViewMessages();
				$extracted_msg = IncludesHelper::extract_product_id_from_error_message_array( $arr_of_messages, $ProductsCollection ?? null );
				if ( $extracted_msg ) {
					$this->ExportModel->setMessage( $this->sanitize_error_message( $extracted_msg ) );
				} else {
					$this->ExportModel->setMessage( $this->sanitize_error_message( method_exists( $e, 'getViewMessage' ) ? $e->getViewMessage() : $e->getMessage() ) );
				}
				if ( in_array( 10, $e->getCodes() ) ) {
					$this->AdminModel->getConfiguration()->setApiV3Key( '' );
					$this->AdminModel->saveConfiguration();
				}
			}
		} finally {
			$this->ExportModel->buildProductExportResponse();
        }
    }
    
    /**
     * Sanitize error messages to prevent XSS and log injection
     * 
     * @param string $message Raw error message
     * @return string Sanitized message safe for output/logging
     */
    private function sanitize_error_message( $message ) {
        if ( ! is_string( $message ) ) {
            return '';
        }
        
        $sanitized = preg_replace( '/[\r\n\t\x00-\x1F\x7F]/', ' ', $message );
        
        // Trim and limit length to prevent log flooding/DoS
        $sanitized = substr( trim( $sanitized ), 0, 1024 );
        
        if ( function_exists( 'esc_html' ) ) {
            $sanitized = esc_html( $sanitized );
        }
        
        return $sanitized;
    }
}
