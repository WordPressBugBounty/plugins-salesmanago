<?php

namespace bhr\Admin\Controller;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use bhr\Admin\Model\AdminModel;
use bhr\Admin\Model\ExportModel;
use bhr\Admin\Model\Helper;
use bhr\Includes\Helper as IncludesHelper;
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
			$this->ExportModel->setMessage( $e->getMessage() );
			$this->ExportModel->setStatus( self::FAILED );
			$this->ExportModel->buildResponse();
		}
	}

	/**
	 *
	 */
	private function registerActions() {
		Helper::addAction( 'wp_ajax_salesmanago_export_count_contacts', array( $this, 'countContacts' ), 5 );
		Helper::addAction( 'wp_ajax_salesmanago_export_contacts', array( $this, 'exportContacts' ), 5 );

		Helper::addAction( 'wp_ajax_salesmanago_export_count_events', array( $this, 'countEvents' ), 5 );
		Helper::addAction( 'wp_ajax_salesmanago_export_events', array( $this, 'exportEvents' ), 5 );

		Helper::addAction( 'wp_ajax_salesmanago_export_products', array( $this, 'exportProducts' ), 5 );
	}

	/**
	 *
	 */
	public function countContacts() {
		try {
			$this->ExportModel->parseArgs();
			$this->ExportModel->setExportType( self::CONTACTS );

			$query = $this->ExportModel->getExportContactsQuery( true );
			$this->ExportModel->setCount( $this->db->get_var( $query ) );
			$this->ExportModel->setPackageCount( (int) ceil( $this->ExportModel->getCount() / ExportModel::PACKAGE_SIZE ) );
			$this->ExportModel->setStatus( self::PREPARING );
			$this->ExportModel->buildResponse();
		} catch ( Exception $e ) {
			$this->ExportModel->setMessage( $e->getViewMessage() );
			$this->ExportModel->setStatus( self::FAILED );
			$this->ExportModel->buildResponse();
		} catch ( \Exception $e ) {
			$this->ExportModel->setMessage( $e->getMessage() );
			$this->ExportModel->setStatus( self::FAILED );
			$this->ExportModel->buildResponse();
		}
	}

	/**
	 *
	 */
	public function countEvents() {
		try {
			$this->ExportModel->parseArgs();
			$this->ExportModel->setExportType( self::EVENTS );

			$this->ExportModel->setCount( $this->ExportModel->getEventsData( true ) );
			$this->ExportModel->setPackageCount( (int) ceil( $this->ExportModel->getCount() / ExportModel::PACKAGE_SIZE ) );
			$this->ExportModel->setStatus( self::PREPARING );
			$this->ExportModel->buildResponse();
		} catch ( \Exception $e ) {
			$this->ExportModel->setMessage( $e->getMessage() );
			$this->ExportModel->setStatus( self::FAILED );
			$this->ExportModel->buildResponse();
		}
	}

	/**
	 *
	 */
	public function exportContacts() {
		$this->ExportModel->parseArgs();
		if ( $this->ExportModel->getPackageCount() ) {
			try {
				$this->ExportModel->setExportType( self::CONTACTS );

				$query   = $this->ExportModel->getExportContactsQuery( false );
				$results = $this->db->get_results( $query, ARRAY_A );

				if ( ! empty( $results ) ) {
					$Collection = $this->ExportModel->prepareContactsToExport( $results );
					if ( ! $Collection->isEmpty() ) {
						$exportResponse = $this->SMExportController->export( $Collection );

						if ( $exportResponse->getStatus() ) {
							$this->ExportModel->setLastExportedPackage(
								$this->ExportModel->getLastExportedPackage() + 1
							);
							if ( $this->ExportModel->getLastExportedPackage() + 1 == $this->ExportModel->getPackageCount(
							) ) {
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
				$this->ExportModel->setMessage( $e->getViewMessage() );
				$this->ExportModel->setStatus( self::FAILED );
				$this->ExportModel->buildResponse();
			} catch ( \Exception $e ) {
				$this->ExportModel->setMessage( $e->getMessage() );
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
	 *
	 */
	public function exportEvents() {
		$this->ExportModel->parseArgs();
		if ( $this->ExportModel->getPackageCount() ) {
			try {
				$this->ExportModel->setExportType( self::EVENTS );

				$results = $this->ExportModel->getEventsData();

				if ( ! empty( $results ) ) {
					$Collection     = $this->ExportModel->prepareEventsToExport( $results );
					$exportResponse = $this->SMExportController->export( $Collection );

					if ( $exportResponse->getStatus() ) {
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
				$this->ExportModel->setMessage( $e->getViewMessage() );
				$this->ExportModel->setStatus( self::FAILED );
				$this->ExportModel->buildResponse();
			} catch ( \Exception $e ) {
				$this->ExportModel->setMessage( $e->getMessage() );
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
		if ( ! $this->AdminModel->getConfiguration()->getApiV3Key() ) {
			$this->ExportModel->buildProductExportResponseForExpiredApiKey();
		}
		try {
			$this->ExportModel->parseProductExportArgs();
			$products = $this->ExportModel->getProductsFromDB();
			$productIdentifierType = $this->AdminModel->getPlatformSettingsFromDb()->getPlatformSettings()->getPluginWc()->getProductIdentifierType();
			$ProductsCollection = $this->ExportModel->prepareProductsForExport( $products, $productIdentifierType );
			$ProductService = new ProductService( $this->AdminModel->getConfiguration() );
			$Catalog = new CatalogEntity(
				array(
					'catalogId' => $this->AdminModel->getConfiguration()->getActiveCatalog(),
				)
			);
			$ProductService->upsertProducts( $Catalog, $ProductsCollection );
			$this->ExportModel->handlePackageCount();
		} catch ( Exception $e ) {
			$this->ExportModel->setStatus( self::FAILED );
			$this->ExportModel->setMessage( $e->getMessage() );
			if ( $e instanceof ApiV3Exception ) {
				$arr_of_messages = $e->getAllViewMessages();
				if ( IncludesHelper::extract_product_id_from_error_message_array( $arr_of_messages, $ProductsCollection ) ) {
					$this->ExportModel->setMessage(
						IncludesHelper::extract_product_id_from_error_message_array( $arr_of_messages, $ProductsCollection )
					);
				} else {
					$this->ExportModel->setMessage( $e->getViewMessage() );
				}
				if ( in_array( 10, $e->getCodes() ) ) {
					$this->AdminModel->getConfiguration()->setApiV3Key( '' );
					$this->AdminModel->saveConfiguration();
				}
			}
		} finally {
			$this->ExportModel->buildProductExportResponse(); } } }
