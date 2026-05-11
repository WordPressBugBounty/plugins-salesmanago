<?php

namespace bhr\Admin\Model;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use bhr\Admin\Builder\ProductBuilder;
use SALESmanago\Entity\Contact\Address;
use SALESmanago\Entity\Contact\Contact;
use SALESmanago\Entity\Contact\Options;
use SALESmanago\Entity\Contact\Properties;
use SALESmanago\Entity\Event\Event;
use SALESmanago\Exception\Exception;
use SALESmanago\Model\Collections\ContactsCollection;
use SALESmanago\Model\Collections\EventsCollection;

class ExportModel {

	const
		PACKAGE_SIZE         = 400,
		PRODUCT_PACKAGE_SIZE = 100,

		PREPARING   = 'preparing',
		FAILED      = 'failed',
		IN_PROGRESS = 'in_progress',
		LAST_CHECK  = 'last_check',
		DONE        = 'done',

		CONTACTS = 'contacts',
		EVENTS   = 'events',
		PURCHASE = 'PURCHASE',

		PRODUCT_AVAILABLE = 'instock',
		PRODUCTS          = 'products',
		NO_PRODUCTS       = 'no_data',

		PRODUCT_IDENTIFIER_TYPE_SKU     = 'sku',
		PRODUCT_IDENTIFIER_TYPE_VARIANT = 'variant Id',
		DEFAULT_PRODUCT_IDENTIFIER_TYPE = 'id',
		ALLOWED_TYPES                   = array( 'PURCHASE', 'CANCELLED', 'OTHER' );

	protected $db;
	protected $Configuration;
	protected $ProductBuilder;

	protected $exportType;
	protected $dateFrom;
	protected $dateTo;
	protected $tags;
	protected $started;
	protected $lastSuccess;
	protected $packageCount          = 0;
	protected $lastExportedPackage   = -1;
	protected $status                = 'unknown';
	protected $message               = '';
	protected $productIdentifierType = self::DEFAULT_PRODUCT_IDENTIFIER_TYPE;
	protected $count                 = 0;
	protected $statuses              = 'wc-completed';
	protected $exportAs              = self::PURCHASE;

	public function __construct( $AdminModel ) {
		$this->db             = $GLOBALS['wpdb'];
		$this->Configuration  = $AdminModel->getConfiguration();
		$this->ProductBuilder = new ProductBuilder( $AdminModel );
	}

	/**
	 * @param int $packageCount
	 */
	public function setPackageCount( $packageCount ) {
		$this->packageCount = $packageCount;
	}

	/**
	 * @return int
	 */
	public function getLastExportedPackage() {
		return $this->lastExportedPackage;
	}

	/**
	 * @param int $lastExportedPackage
	 */
	public function setLastExportedPackage( $lastExportedPackage ) {
		$this->lastExportedPackage = $lastExportedPackage;
	}

	/**
	 * @param mixed $exportType
	 */
	public function setExportType( $exportType ) {
		$this->exportType = $exportType;
	}

	/**
	 * @return int
	 */
	public function getPackageCount() {
		return $this->packageCount;
	}

	/**
	 * @param string $status
	 */
	public function setStatus( $status ) {
		$this->status = $status;
	}

	/**
	 * @param string $message
	 */
	public function setMessage( $message ) {
		$this->message = $this->sanitize_output( $message );
	}

	/**
	 * @return int
	 */
	public function getCount() {
		return $this->count;
	}

	/**
	 * @param  int $count
	 * @return ExportModel
	 */
	public function setCount( $count ) {
		$this->count = $count;
		return $this;
	}

	/**
	 * @return void
	 */
	public function parseArgs() {
		try {
			if ( ! isset( $_REQUEST['data'] ) ) {
				throw new Exception( 'Missing request data' );
			}

			$raw_data = sanitize_text_field( $_REQUEST['data'] );
			$decoded  = base64_decode( $raw_data, true );
			
			if ( false === $decoded ) {
				throw new Exception( 'Invalid base64 encoding' );
			}
			
			$data = json_decode( $decoded );
			
			if ( json_last_error() !== JSON_ERROR_NONE || ! is_object( $data ) ) {
				throw new Exception( 'Invalid JSON data' );
			}

			$raw_date_from = isset( $data->dateFrom ) ? $data->dateFrom : '';
			$this->dateFrom = empty( $raw_date_from )
				? '2000-01-01'
				: $this->validate_date_format( sanitize_text_field( $raw_date_from ) );

			$raw_date_to = isset( $data->dateTo ) ? $data->dateTo : '';
			$this->dateTo = empty( $raw_date_to )
				? date( 'Y-m-d', time() + 86400 )
				: date( 'Y-m-d', strtotime( $this->validate_date_format( sanitize_text_field( $raw_date_to ) ) ) + 86400 );

			$this->tags = empty( $data->tags )
				? array()
				: Helper::clearCSVInput( $data->tags, false, true, true );

			$this->lastExportedPackage = isset( $data->lastExportedPackage )
				? (int) $data->lastExportedPackage
				: -1;

			$this->packageCount = empty( $data->packageCount )
				? 0
				: (int) $data->packageCount;

			$this->started = empty( $data->started )
				? time()
				: (int) $data->started;

			$raw_identifier = isset( $data->identifierType ) ? $data->identifierType : '';
			$this->productIdentifierType = empty( $raw_identifier )
				? self::DEFAULT_PRODUCT_IDENTIFIER_TYPE
				: $this->validate_identifier_type( sanitize_text_field( $raw_identifier ) );

			$this->lastSuccess = empty( $data->lastSuccess )
				? 0
				: (int) $data->lastSuccess;

			$raw_statuses = isset( $data->statuses ) ? sanitize_text_field( $data->statuses ) : '';
			$this->statuses = self::checkStatusesFromRequest( $raw_statuses )
				? $raw_statuses
				: 'wc-completed';

			$raw_export_as = isset( $data->exportAs ) ? $data->exportAs : '';
			$this->exportAs = empty( $raw_export_as ) || ! in_array( $raw_export_as, self::ALLOWED_TYPES, true )
				? self::PURCHASE
				: sanitize_text_field( $raw_export_as );

		} catch ( \Exception $e ) {
			$this->message = $this->sanitize_output( $e->getMessage() );
			$this->status  = self::FAILED;
			$this->buildResponse();
		}
	}

	/**
	 * @return void
	 */
	public function buildResponse() {
		$response = array(
			'packageSize'         => self::PACKAGE_SIZE,
			'packageCount'        => $this->packageCount,
			'lastExportedPackage' => $this->lastExportedPackage,
			'started'             => $this->started,
			'lastSuccess'         => time(),
			'type'                => $this->exportType,
			'tags'                => $this->tags,
			'status'              => $this->status,
			'message'             => $this->sanitize_output( $this->message ),
			'identifierType'      => $this->productIdentifierType,
			'dateFrom'            => $this->dateFrom,
			'dateTo'              => date( 'Y-m-d', strtotime( $this->dateTo ) - 86400 ),
			'count'               => $this->count,
			'statuses'            => $this->statuses,
			'exportAs'            => $this->exportAs,
		);
		wp_send_json( $response );
	}

	/**
	 * @param $collection
	 * @return ContactsCollection|null
	 */
	public function prepareContactsToExport( $collection ) {
		try {
			$ContactsCollection = new ContactsCollection();

			foreach ( $collection as $customer ) {
				if ( empty( $customer['email'] ) ) {
					continue;
				}

				$Contact    = new Contact();
				$Options    = new Options();
				$Address    = new Address();
				$Properties = new Properties();
				$Contact->setOptions( $Options );
				$Contact->setAddress( $Address );
				$Contact->setProperties( $Properties );

				/* Contact */
				$customer['name'] = trim(
					( isset( $customer['first_name'] ) ? sanitize_text_field( $customer['first_name'] ) : '' ) .
					' ' .
					( isset( $customer['last_name'] ) ? sanitize_text_field( $customer['last_name'] ) : '' )
				);
				$Contact
					->setEmail( isset( $customer['email'] ) ? sanitize_email( $customer['email'] ) : null )
					->setName( isset( $customer['name'] ) ? sanitize_text_field( $customer['name'] ) : null )
					->setExternalId( isset( $customer['user_id'] ) ? (int) $customer['user_id'] : null )
					->setPhone( isset( $customer['phone'] ) ? sanitize_text_field( $customer['phone'] ) : null );

				/* Address */
				$customer['address'] = trim(
					( isset( $customer['address_1'] ) ? sanitize_text_field( $customer['address_1'] ) : '' ) .
					' ' .
					( isset( $customer['address_2'] ) ? sanitize_text_field( $customer['address_2'] ) : '' )
				);
				$Address
					->setStreetAddress( isset( $customer['address'] ) ? sanitize_text_field( $customer['address'] ) : null )
					->setCity( isset( $customer['city'] ) ? sanitize_text_field( $customer['city'] ) : null )
					->setZipCode( isset( $customer['postcode'] ) ? sanitize_text_field( $customer['postcode'] ) : null );

				/* Options */
				$Options
					->setTags( empty( $this->tags ) ? array() : $this->tags )
					->setCreatedOn( isset( $customer['created_on'] ) ? sanitize_text_field( $customer['created_on'] ) : null );

				$ContactsCollection->addItem( $Contact );
			}
			return $ContactsCollection;
		} catch ( Exception $e ) {
			$this->message = $this->sanitize_output( method_exists( $e, 'getViewMessage' ) ? $e->getViewMessage() : $e->getMessage() );
			$this->status  = self::FAILED;
			$this->buildResponse();

		} catch ( \Exception $e ) {
			$this->message = $this->sanitize_output( $e->getMessage() );
			$this->status  = self::FAILED;
			$this->buildResponse();
		}
		return null;
	}

	/**
	 * @param $collection
	 * @return EventsCollection|null
	 */
	public function prepareEventsToExport( $collection ) {
		try {
			$EventsCollection = new EventsCollection();

			foreach ( $collection as $event ) {
				if ( empty( $event['email'] ) ) {
					continue;
				}

				$Event = new Event();

				if ( isset( $event['date'] ) ) {
					/* 'WooCommerce' return 'date' as timestamp in milliseconds, this code prevent to translate it into microseconds */
					$date = strlen( strval( $event['date'] ) ) == 10 ? $event['date'] : $event['date'] / 1000;
				} else {
					$date = null;
				}

				$Event
					->setEmail( isset( $event['email'] ) ? sanitize_email( $event['email'] ) : null )
					->setDate( $date )
					->setDescription( isset( $event['description'] ) ? sanitize_text_field( $event['description'] ) : null )
					->setProducts( isset( $event['products'] ) ? sanitize_text_field( $event['products'] ) : null )
					->setValue( isset( $event['value'] ) ? floatval( $event['value'] ) : null )
					->setContactExtEventType( isset( $event['contactExtEventType'] ) ? sanitize_text_field( $event['contactExtEventType'] ) : self::PURCHASE )
					->setExternalId( isset( $event['externalId'] ) ? sanitize_text_field( $event['externalId'] ) : null )
					->setShopDomain( isset( $event['shopDomain'] ) ? esc_url_raw( $event['shopDomain'] ) : get_site_url() )
					->setLocation( ! empty( $this->Configuration->getLocation() ) ? sanitize_text_field( $this->Configuration->getLocation() ) : md5( get_site_url() ) )
					->setDetails(
						array(
							'1' => isset( $event['detail1'] ) ? sanitize_text_field( $event['detail1'] ) : null,
							'2' => isset( $event['detail2'] ) ? sanitize_text_field( $event['detail2'] ) : null,
							'3' => isset( $event['detail3'] ) ? sanitize_text_field( $event['detail3'] ) : null,
						)
					);

				$EventsCollection->addItem( $Event );
			}
			return $EventsCollection;
		} catch ( Exception $e ) {
			$this->message = $this->sanitize_output( method_exists( $e, 'getViewMessage' ) ? $e->getViewMessage() : $e->getMessage() );
			$this->status  = self::FAILED;
			$this->buildResponse();

		} catch ( \Exception $e ) {
			$this->message = $this->sanitize_output( $e->getMessage() );
			$this->status  = self::FAILED;
			$this->buildResponse();
		}
		return null;
	}

	/**
	 * @param false $count - return count query instead of data query
	 * @return string|null
	 */
	public function getExportContactsQuery( $count = false ) {
		try {
			$limit  = self::PACKAGE_SIZE;
			$offset = $limit * ( $this->lastExportedPackage + 1 );

			$query = '';
			if ( $count ) {
				$query .= 'SELECT COUNT(*) AS count FROM (';
			}

			// Build base query with placeholders
			$query .= "
            SELECT DISTINCT 
                   B.meta_value as first_name,
                   C.meta_value as last_name,
                   D.meta_value as address_1,
                   E.meta_value as address_2,
                   F.meta_value as country,
                   G.meta_value as state,
                   H.meta_value as city,
                   I.meta_value as postcode,
                   J.meta_value as user_id,
                   K.meta_value as email,
                   L.meta_value as phone
            FROM
                {$this->db->posts} as A
            LEFT JOIN
                {$this->db->postmeta} B
                    ON A.id = B.post_id AND B.meta_key = '_billing_first_name'
            LEFT JOIN
                {$this->db->postmeta} C
                    ON A.id = C.post_id AND C.meta_key = '_billing_last_name'
            LEFT JOIN
                {$this->db->postmeta} D
                    ON A.id = D.post_id AND D.meta_key = '_billing_address_1'
            LEFT JOIN
                {$this->db->postmeta} E
                    ON A.id = E.post_id AND E.meta_key = '_billing_address_2'
            LEFT JOIN
                {$this->db->postmeta} F
                    ON A.id = F.post_id AND F.meta_key = '_billing_country'
            LEFT JOIN
                {$this->db->postmeta} G
                    ON A.id = G.post_id AND G.meta_key = '_billing_state'
            LEFT JOIN
                {$this->db->postmeta} H
                    ON A.id = H.post_id AND H.meta_key = '_billing_city'
            LEFT JOIN
                {$this->db->postmeta} I
                    ON A.id = I.post_id AND I.meta_key = '_billing_postcode'
            LEFT JOIN
                {$this->db->postmeta} J
                    ON A.id = J.post_id AND J.meta_key = '_customer_user'
            LEFT JOIN
                {$this->db->postmeta} K
                    ON A.id = K.post_id AND K.meta_key = '_billing_email'
            LEFT JOIN
                {$this->db->postmeta} L
                    ON A.id = L.post_id AND L.meta_key = '_billing_phone'

            WHERE
                  A.post_type = 'shop_order'
            ";

			$query .= $this->db->prepare( "
            AND
                  A.post_date >= %s
            AND
                  A.post_date <= %s
            ", $this->dateFrom, $this->dateTo );

			if ( ! empty( $this->Configuration->getIgnoredDomains() ) ) {
				$ignored_domains = array_map( 'sanitize_text_field', $this->Configuration->getIgnoredDomains() );
				if ( ! empty( $ignored_domains ) ) {
					$placeholders = implode( ',', array_fill( 0, count( $ignored_domains ), '%s' ) );
					$query .= $this->db->prepare( "
                    AND SUBSTRING_INDEX(K.meta_value, '@', -1) NOT IN($placeholders)
                    ", ...$ignored_domains );
				}
			}

			if ( ! $count ) {
				$query .= $this->db->prepare( "
                LIMIT %d
                OFFSET %d
                ", $limit, $offset );
			}

			if ( $count ) {
				$query .= ') AS qwerty';
			}

			return trim( preg_replace( '/\s\s+/', ' ', $query ) );
		} catch ( \Exception $e ) {
			$this->message = $this->sanitize_output( $e->getMessage() );
			$this->status  = self::FAILED;
			$this->buildResponse();
		}
		return null;
	}

	/**
	 * @param false $count - return count instead of data
	 * @return array|false|int
	 */
	public function getEventsData( $count = false ) {
		try {
			$data = array();
			$page = $this->lastExportedPackage + 2;

			$argGetOrders['date_created']  = strtotime( $this->dateFrom );
			$argGetOrders['date_created'] .= '...';
			$argGetOrders['date_created'] .= strtotime( $this->dateTo );

			if ( $count ) {
				$argGetOrders += array(
					'status' => $this->statuses,
					'limit'  => -1,
					'page'   => '',
				);
				return count( Helper::wcGetOrders( $argGetOrders ) );
			} else {
				$argGetOrders += array(
					'status' => $this->statuses,
					'limit'  => self::PACKAGE_SIZE,
					'page'   => $page,
				);
			}

			if ( $orders = Helper::wcGetOrders( $argGetOrders ) ) {
				if ( empty( $orders ) ) {
					return false;
				}

				foreach ( $orders as $order ) {
					if ( $order->get_items() ) {
						$products = $order->get_items();
						$prodArr  = array(
							'ids'          => array(),
							'names'        => array(),
							'quantity'     => array(),
							'variationIds' => array(),
							'skus'         => array(),
						);

						foreach ( $products as $product ) {

							$arrayProducts = $product->get_data();
							$quantity      = $arrayProducts['quantity'];

							$WcProduct = Helper::wcGetProduct( $arrayProducts['variation_id'] )
								? Helper::wcGetProduct( $arrayProducts['variation_id'] )
								: Helper::wcGetProduct( $product->get_product_id() );

							if ( $quantity > 0 && $WcProduct ) {
								$prodArr['ids'][]          = ( $WcProduct->get_parent_id() !== 0 )
									? $WcProduct->get_parent_id()
									: $WcProduct->get_id();
								$prodArr['names'][]        = ( $WcProduct->get_name() )
									? sanitize_text_field( $WcProduct->get_name() )
									: '';
								$prodArr['quantity'][]     = ( $product->get_quantity() )
									? (int) $product->get_quantity()
									: '';
								$prodArr['variationIds'][] = ( $WcProduct->get_id() )
									? $WcProduct->get_id()
									: '';
								$prodArr['skus'][]         = ( $WcProduct->get_sku() )
									? sanitize_text_field( $WcProduct->get_sku() )
									: '';
							}
						}
						if ( $quantity > 0 ) {
							if ( $order->get_billing_email() ) {
								$data[] = self::generateOrderDetailsByIdentifierType( $order, $this->productIdentifierType, $prodArr, $this->exportAs );
							}
						}
					}
				}
				return $data;
			}
		} catch ( Exception $e ) {
			$this->message = $this->sanitize_output( method_exists( $e, 'getViewMessage' ) ? $e->getViewMessage() : $e->getMessage() );
			$this->status  = self::FAILED;
			$this->buildResponse();

		} catch ( \Exception $e ) {
			$this->message = $this->sanitize_output( $e->getMessage() );
			$this->status  = self::FAILED;
			$this->buildResponse();
		}
		return array();
	}

	private static function generateOrderDetailsByIdentifierType(
		$order,
		$productIdentifierType = self::DEFAULT_PRODUCT_IDENTIFIER_TYPE,
		$prodArr = array(),
		$exportAs = self::PURCHASE
	) {
		$data     = array(
			'email'               => sanitize_email( $order->get_billing_email() ),
			'date'                => ( $order->get_date_created()->getTimestamp() )
				? $order->get_date_created()->getTimestamp() * 1000
				: '',
			'description'         => ( $order->get_payment_method_title() )
				? sanitize_text_field( $order->get_payment_method_title() )
				: '',
			'products'            => is_array( $prodArr['ids'] )
				? implode( ',', array_map( 'intval', $prodArr['ids'] ) )
				: $prodArr['ids'],
			'value'               => ( $order->get_total() )
				? floatval( $order->get_total() )
				: '',
			'contactExtEventType' => sanitize_text_field( $exportAs ),
			'detail1'             => is_array( $prodArr['names'] )
				? implode( ',', array_map( 'sanitize_text_field', $prodArr['names'] ) )
				: '',
			'detail2'             => ( $order->get_order_key() )
				? sanitize_text_field( $order->get_order_key() )
				: '',
			'detail3'             => is_array( $prodArr['quantity'] )
				? implode( '/', array_map( 'intval', $prodArr['quantity'] ) )
				: $prodArr['quantity'],
			'externalId'          => ( $order->get_id() )
				? (int) $order->get_id()
				: '',
			'shopDomain'          => esc_url_raw( get_site_url() ),
		);
			$skus = is_array( $prodArr['skus'] )
				? implode( ',', array_map( 'sanitize_text_field', $prodArr['skus'] ) )
				: $prodArr['skus'];

			$ids = is_array( $prodArr['ids'] )
				? implode( ',', array_map( 'intval', $prodArr['ids'] ) )
				: $prodArr['ids'];

			$variationIds = is_array( $prodArr['variationIds'] )
				? implode( ',', array_map( 'intval', $prodArr['variationIds'] ) )
				: $prodArr['variationIds'];

		switch ( $productIdentifierType ) {
			case self::PRODUCT_IDENTIFIER_TYPE_SKU:
				$data['products'] = $skus;
				$data['detail6']  = $ids;
				$data['detail7']  = $variationIds;
				break;

			case self::PRODUCT_IDENTIFIER_TYPE_VARIANT:
				$data['products'] = $variationIds;
				$data['detail6']  = $ids;
				$data['detail7']  = $skus;
				break;

			default:
				$data['products'] = $ids;
				$data['detail6']  = $skus;
				$data['detail7']  = $variationIds;
				break;
		}
		return $data;
	}

	/**
	 * @return false|string
	 */
	public function getExportDataForReporting() {
		$details = array(
			'exportType'          => sanitize_text_field( $this->exportType ),
			'dateFrom'            => $this->dateFrom,
			'dateTo'              => $this->dateTo,
			'tags'                => $this->tags,
			'lastExportedPackage' => (int) $this->lastExportedPackage,
			'started'             => (int) $this->started,
			'lastSuccess'         => (int) $this->lastSuccess,
			'packageCount'        => (int) $this->packageCount,
			'status'              => sanitize_text_field( $this->status ),
			'message'             => $this->sanitize_output( $this->message ),
		);
		return wp_json_encode( $details );
	}

	/**
	 * @param $statuses
	 *
	 * @return bool
	 */
	private static function checkStatusesFromRequest( $statuses ) {
		if ( empty( $statuses ) ) {
			return false;
		}
		$wcOrderStatuses = Helper::wcGetOrderStatuses();
        $allowedStatuses = array_keys( $wcOrderStatuses );
		$orderStatuses   = explode( ',', sanitize_text_field( $statuses ) );
		foreach ( $orderStatuses as $status ) {
			if ( ! in_array( sanitize_text_field( $status ), $allowedStatuses, true ) ) {
				return false;
			}
		}
		return true;
	}

	// PRODUCT EXPORT

	/**
	 * Count products in db
	 *
	 * @return mixed
	 * @throws Exception
	 */
	protected function countProducts() {
		$query = $this->db->prepare( "
		SELECT COUNT(ID) FROM {$this->db->posts} 
		WHERE (post_type = %s OR post_type = %s)
		AND post_name != ''
		AND post_status != %s;
		", 'product', 'product_variation', 'auto-draft' );
		return $this->db->get_var( trim( preg_replace( '/\s\s+/', ' ', $query ) ) );
	}

	/**
	 * Prepare DB query for basic product info
	 * productId, name, description, availability & prices
	 *
	 * @return string|null
	 * @throws Exception
	 */
	public function getBasicProductDataQuery() {
		try {
			$limit  = self::PRODUCT_PACKAGE_SIZE;
			$offset = $limit * ( $this->lastExportedPackage + 1 );

			$query = $this->db->prepare( "
				SELECT
                   A.ID as productId,
                   A.post_title as name,
                   A.post_content as description,
                   A.post_excerpt as short_description,
                   A.post_status as post_status,
                   B.meta_value as stock_status,
                   C.meta_value as regular_price,
                   D.meta_value as sale_price,
                   E.meta_value as _price,
                   F.meta_value as _sku
            FROM
                {$this->db->posts} as A
            LEFT JOIN
                {$this->db->postmeta} as B
                    ON A.id = B.post_id AND B.meta_key = '_stock_status'
            LEFT JOIN
                {$this->db->postmeta} as C
                    ON A.id = C.post_id AND C.meta_key = '_regular_price'
            LEFT JOIN
                {$this->db->postmeta} as D
                    ON A.id = D.post_id AND D.meta_key = '_sale_price'
            LEFT JOIN
                {$this->db->postmeta} as E
                    ON A.id = E.post_id AND E.meta_key = '_price'
            LEFT JOIN
                {$this->db->postmeta} as F
                    ON A.id = F.post_id AND F.meta_key = '_sku'
            WHERE ( post_type = %s OR post_type = %s )
            AND post_name != ''
            AND post_status != %s
            GROUP BY A.ID
			LIMIT %d
			OFFSET %d;", 'product', 'product_variation', 'auto-draft', $limit, $offset );

			return trim( preg_replace( '/\s\s+/', ' ', $query ) );
		} catch ( \Exception $e ) {
			error_log( $this->sanitize_output( $e->getMessage() ) );
			throw new Exception( $this->sanitize_output( $e->getMessage() ) );
		}
	}

	/**
	 * Get wc product data and transform it to a collection
	 *
	 * @param array $products
	 * @throws Exception
	 */
	public function prepareProductsForExport( $products, $productIdentifierType ) {
		 return $this->ProductBuilder->add_products_to_collection( $products, $this->validate_identifier_type( sanitize_text_field( $productIdentifierType ) ) );
	}

	/**
	 *  Parse and set arguments from product export request
	 *
	 * @throws Exception
	 */
	public function parseProductExportArgs() {
		if ( ! isset( $_REQUEST['data'] ) ) {
			throw new Exception( 'Missing request data' );
		}

		$raw_data = sanitize_text_field( $_REQUEST['data'] );
		$decoded  = base64_decode( $raw_data, true );
		
		if ( false === $decoded ) {
			throw new Exception( 'Invalid base64 encoding' );
		}
		
		$data = json_decode( $decoded );
		
		if ( json_last_error() !== JSON_ERROR_NONE || ! is_object( $data ) ) {
			throw new Exception( 'Invalid JSON data' );
		}

		$this->setExportType( self::PRODUCTS );

		$this->started = empty( $data->started )
			? time()
			: (int) $data->started;

		$this->lastSuccess = empty( $data->lastSuccess )
			? 0
			: (int) $data->lastSuccess;

		if ( isset( $data->packageCount ) ) {
			$this->packageCount = (int) $data->packageCount;
		}

		if ( isset( $data->lastExportedPackage ) ) {
			$this->lastExportedPackage = (int) $data->lastExportedPackage;
		}

		if ( isset( $data->count ) ) {
			$this->count = (int) $data->count;
		}

		if ( isset( $data->message ) ) {
			$this->message = $this->sanitize_output( $data->message );
		}

		if ( isset( $data->status ) ) {
			$this->status = sanitize_text_field( $data->status );
			switch ( $this->status ) {
				case self::FAILED:
					throw new Exception( $this->message ?? 'Export failed' );
				case self::DONE:
					$this->lastExportedPackage++;
					break;
			}
		}
	}

	/**
	 * Build response for product export
	 *
	 * @return void
	 */
	public function buildProductExportResponse() {
		$response = array(
			'packageSize'         => self::PRODUCT_PACKAGE_SIZE,
			'packageCount'        => $this->packageCount,
			'lastExportedPackage' => $this->lastExportedPackage,
			'started'             => $this->started,
			'lastSuccess'         => time(),
			'type'                => $this->exportType,
			'status'              => sanitize_text_field( $this->status ),
			'message'             => $this->sanitize_output( $this->message ),
			'count'               => $this->count,
		);
		wp_send_json( $response );
	}

	/**
	 * Build export response when there's no API Key - it has been reset because of expiration
	 *
	 * @return void
	 */
	public function buildProductExportResponseForExpiredApiKey() {
		$response = array(
			'packageSize'         => self::PRODUCT_PACKAGE_SIZE,
			'packageCount'        => $this->packageCount,
			'lastExportedPackage' => $this->lastExportedPackage,
			'started'             => $this->started,
			'lastSuccess'         => time(),
			'type'                => $this->exportType,
			'status'              => self::FAILED,
			'message'             => 'Expired API Key. Refresh the page and add a new API key',
			'count'               => $this->count,
		);
		wp_send_json( $response );
	}

	/**
	 * Handle package count for Product Export
	 *
	 * @return void
	 */
	public function handlePackageCount() {
		$this->setLastExportedPackage( $this->getLastExportedPackage() + 1 );
		if ( $this->getLastExportedPackage() + 1 === $this->getPackageCount() ) {
			$this->setStatus( self::DONE );
		} else {
			$this->setStatus( self::IN_PROGRESS );
		}
	}

	/**
	 * Get products from DB and set package count
	 *
	 * @return array products
	 * @throws Exception
	 */
	public function getProductsFromDB() {
		$query    = $this->getBasicProductDataQuery();
		$products = $this->db->get_results( $query, ARRAY_A );
		$this->setCount( $this->countProducts() );
		$this->setPackageCount(
			(int) ceil(
				$this->getCount() / self::PRODUCT_PACKAGE_SIZE
			)
		);
		if ( ! $products ) {
			if ( $this->status !== 'done' ) {
				$this->setStatus( self::NO_PRODUCTS );
				$this->setMessage( 'No products to export' );
			}
			$this->buildProductExportResponse();
		}
		return $products;
	}
	
	// ========================================================================
	// SECURITY HELPER METHODS (for sanitization/validation)
	// ========================================================================
	
	/**
	 * Sanitize output to prevent XSS and log injection
	 *
	 * @param string $text Raw text to sanitize
	 * @return string Sanitized text
	 */
	private function sanitize_output( $text ) {
		if ( ! is_string( $text ) ) {
			return '';
		}
		$sanitized = preg_replace( '/[\r\n\t\x00-\x1F\x7F]/', ' ', $text );
		// Trim and limit length to prevent log flooding/DoS
		return substr( trim( $sanitized ), 0, 2048 );
	}
	
	/**
	 * Validate date format (YYYY-MM-DD)
	 *
	 * @param string $date Raw date string
	 * @return string Validated date or default
	 */
	private function validate_date_format( $date ) {
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			return $date;
		}
		// Return safe default for invalid dates
		return '2000-01-01';
	}
	
	/**
	 * Validate product identifier type against allowed values
	 *
	 * @param string $type Raw identifier type
	 * @return string Validated type or default
	 */
	private function validate_identifier_type( $type ) {
		$allowed = array(
			self::DEFAULT_PRODUCT_IDENTIFIER_TYPE,
			self::PRODUCT_IDENTIFIER_TYPE_SKU,
			self::PRODUCT_IDENTIFIER_TYPE_VARIANT,
		);
		return in_array( $type, $allowed, true ) ? $type : self::DEFAULT_PRODUCT_IDENTIFIER_TYPE;
	}
}
