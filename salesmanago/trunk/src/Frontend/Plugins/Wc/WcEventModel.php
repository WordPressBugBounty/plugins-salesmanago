<?php

namespace bhr\Frontend\Plugins\Wc;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use bhr\Admin\Entity\Plugins\Wc;
use bhr\Frontend\Model\AbstractModel;
use bhr\Frontend\Model\Helper;

use SALESmanago\Entity\Event\Event;
use SALESmanago\Exception\Exception;

class WcEventModel extends AbstractModel {

	const
		COOKIES_EXT_EVENT               = 'smevent',
		EVENT_TYPE_CART                 = 'CART',
		EVENT_TYPE_PURCHASE             = 'PURCHASE',
		EVENT_TYPE_CANCELLATION         = 'CANCELLATION',
		EVENT_TYPE_RETURN               = 'RETURN',
		EVENT_TYPE_DEFAULT              = 'OTHER',
		PRODUCT_IDENTIFIER_TYPE_SKU     = 'sku',
		PRODUCT_IDENTIFIER_TYPE_VARIANT = 'variant Id';

	protected $Event;
	protected $productIdentifierType = Wc::DEFAULT_PRODUCT_IDENTIFIER_TYPE;
	protected $languageDetection     = Wc::DEFAULT_LANGUAGE_DETECTION;

	public function __construct( $PlatformSettings ) {
		parent::__construct();

		if ( isset( $PlatformSettings->PluginWc->productIdentifierType ) ) {
			$this->productIdentifierType = $PlatformSettings->PluginWc->productIdentifierType;
		}
        if ( isset( $PlatformSettings->languageDetection ) ) {
            $this->languageDetection = $PlatformSettings->languageDetection;
        }

		$this->Event = new Event();
	}

	/**
	 * @param $client
	 * @param $eventType
	 * @param $products
	 * @param $location
     * @param $lang
	 * @param $smevent
	 *
	 * @return Event|null
	 * @throws Exception
	 */
	public function bindEvent( $client, $eventType, $products, $location, $lang, $smevent = null ) {
		try {
			$this->Event
				->setContactExtEventType( $eventType )
				->setProducts( isset( $products['products'] ) ? $products['products'] : '' )
				->setDescription( isset( $products['description'] ) ? $products['description'] : '' )
				->setValue( isset( $products['value'] ) ? $products['value'] : '' )
				->setLocation( ! empty( $location ) ? $location : Helper::getLocation() )
				->setDetails(
					array(
						'1' => $products['detail1'] ?? '',
						'2' => $products['detail2'] ?? '',
						'3' => $products['detail3'] ?? '',
						'4' => $products['detail4'] ?? '',
						'5' => $products['detail5'] ?? '',
						'6' => $products['detail6'] ?? '',
						'7' => $products['detail7'] ?? '',
                        '8' => !empty($lang) ? $lang : '',
						'9' => function_exists('get_woocommerce_currency') ? get_woocommerce_currency() : ''
					)
				)
				->setEventId( $smevent )
				->setDate( time() )
				->setExternalId( isset( $products['externalId'] ) ? $products['externalId'] : '' );
			if ( ! empty( $client[ self::EMAIL ] ) ) {
				$this->Event->setEmail( $client[ self::EMAIL ] );
			} elseif ( ! empty( $client ) && ! is_array( $client ) ) {
				$this->Event->setEmail( $client );
			} elseif ( ! empty( $client[ self::SM_CLIENT ] ) ) {
				$this->Event->setContactId( $client[ self::SM_CLIENT ] );
			} else {
				return null;
			}
			return $this->Event;
		} catch ( \Exception $e ) {
			throw new Exception( $e->getMessage() );
		}
	}

	/**
	 * @return Event
	 */
	public function get() {
		return $this->Event;
	}
	/**
	 * @return array
	 */
	public function getProductsFromCart() {
		global $woocommerce;

		$ids            = array();
		$variantIds     = array();
		$names          = array();
		$quantities     = array();
		$skus           = array();
		$cartTotalPrice = 0;
		$smProductArray = array();

		$i = 0;
		foreach ( $woocommerce->cart->get_cart() as $key => $item ) {
			$id        = $item['data']->get_id();
			$WcProduct = Helper::wcGetProduct( $id );

			$smProductArray[] = Helper::getSmEventDetailsFromWcProduct( $WcProduct );
			$quantities[]     = $item['quantity'];
			$cartTotalPrice  += (float) ( $item['quantity'] * $smProductArray[ $i ]->getUnitPrice() );
			$i++;
		}

		foreach ( $smProductArray as $SmProduct ) {
			$ids[]        = $SmProduct->getId();
			$variantIds[] = $SmProduct->getVariantId();
			$skus[]       = $SmProduct->getSku();
			$names[]      = $SmProduct->getName();
		}

		$CartRecoveryModel = new WcCartRecoveryModel();
		try {
			$cartRecoveryUrl = $CartRecoveryModel->getCartRecoveryUrl();
		} catch ( \Exception $e ) {
			$cartRecoveryUrl = '';
		}

		$products = array(
			'description' => $cartRecoveryUrl,
			'value'       => $cartTotalPrice,
			'detail1'     => implode( ',', $names ),
			'detail3'     => implode( '/', $quantities ),
		);

		$products += Helper::generateProductsDetailsByIdentifierType( $this->productIdentifierType, $ids, $variantIds, $skus );

		return $products;
	}

	/**
	 * @return mixed|null
	 */
	public function getSmEvent() {
		$smevent = isset( $_COOKIE['smevent'] ) ? $_COOKIE['smevent'] : null;
		if ( ! $smevent ) {
			$smevent = isset( $_SESSION['smevent'] ) ? $_SESSION['smevent'] : null;
		}
		return empty( $smevent ) ? null : $smevent;
	}
}
