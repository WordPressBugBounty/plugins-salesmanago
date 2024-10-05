<?php

namespace bhr\Frontend\Plugins\Wc;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use bhr\Admin\Entity\Plugins\Wc;
use bhr\Frontend\Model\Helper;
use bhr\Frontend\Plugins\Wc\WcContactModel as ContactModel;
use bhr\Frontend\Plugins\Wc\WcEventModel as EventModel;
use bhr\Includes\GlobalConstant;
use bhr\Admin\Entity\Configuration;
use SALESmanago\Exception\Exception;

use bhr\Frontend\Controller\TransferController;

class WcController {

	const PREVENT_EVENT_DUPLICATION_TIME = 30;

	private $TransferController;
	private $ContactModel;
	private $EventModel;
	private $Configuration;

	private $productIdentifierType;
	private $preventEventDuplication = false;
    private $lang;

	public function __construct( $PlatformSettings, Configuration $Configuration, TransferController $TransferController ) {
		$this->TransferController = $TransferController;
		$this->Configuration      = $Configuration;
		if ( ! $this->ContactModel = new ContactModel( $PlatformSettings ) ) {
			return false;
		}
		$this->EventModel = new EventModel( $PlatformSettings );

		$this->productIdentifierType = $PlatformSettings->PluginWc->productIdentifierType;

        $this->lang = Helper::getLanguage(
            isset($PlatformSettings->languageDetection)
                ? $PlatformSettings->languageDetection
                : Wc::DEFAULT_LANGUAGE_DETECTION
        );

		if ( isset( $PlatformSettings->PluginWc->preventEventDuplication ) ) {
			$this->preventEventDuplication = boolval( $PlatformSettings->PluginWc->preventEventDuplication );
		}

		return $this;
	}

    /**
     * @param $userId
     * @param $oldData
     *
     * @return bool
     */
	public function createUser( $userId, $oldData = null ) {
		try {
			// Populate new Contact Model with fields from submitted data
			if ( $this->ContactModel->parseCustomerFromPost() ) {
				Helper::doAction( 'salesmanago_wc_create_contact', array( 'Contact' => $this->ContactModel->get() ) );
				return $this->TransferController->transferContact( $this->ContactModel->get() );
			} elseif ( $this->ContactModel->parseContact( $userId, GlobalConstant::ID, $oldData ) ) {
				Helper::doAction( 'salesmanago_wc_create_contact', array( 'Contact' => $this->ContactModel->get() ) );
				return $this->TransferController->transferContact( $this->ContactModel->get() );
			}
			return false;

		} catch ( \Exception $e ) {
			error_log( print_r( $e->getMessage(), true ) );
		}
		return false;
	}

	/**
	 * @param $userLogin
	 * @return bool
	 */
	public function loginUser( $userLogin ) {
		try {
			$user = ( ! Helper::getUserBy( 'email', $userLogin ) )
				? Helper::getUserBy( 'login', $userLogin )
				: Helper::getUserBy( 'email', $userLogin );

			if ( $user && empty( $user->get_role_caps()['customer'] ) ) {
				return false;
			}

			if ( $this->ContactModel->parseContact( $userLogin, GlobalConstant::LOGIN ) ) {
				$this->ContactModel->setTagsFromConfig( ContactModel::TAGS_LOGIN );
				Helper::doAction( 'salesmanago_wc_login_contact', array( 'Contact' => $this->ContactModel->get() ) );
				return $this->TransferController->transferContact( $this->ContactModel->get() );
			}

			return false;
		} catch ( \Exception $e ) {
			error_log( print_r( $e->getMessage(), true ) );
		}
		return false;
	}

	/**
	 * Parse woocommerce user to SM contact, send contact to SM
	 * Set smclient for contact
	 *
	 * @param $userId - registered user id
	 * @return bool
	 */
	public function registerUser( $userId ) {
		try {
			if ( $this->ContactModel->parseContact( $userId, GlobalConstant::ID ) ) {
				$this->ContactModel->setTagsFromConfig( ContactModel::TAGS_REGISTRATION );
				Helper::doAction( 'salesmanago_wc_register_contact', array( 'Contact' => $this->ContactModel->get() ) );
				return $this->TransferController->transferContact( $this->ContactModel->get() );
			}
			return false;
		} catch ( \Exception $e ) {
			error_log( print_r( $e->getMessage(), true ) );
		}
		return false;
	}

	/**
	 * Parse & send order data as SM external event Purchase
	 *
	 * @param mixed $order  WC order or wc_order_id
	 * @return bool         When no orders exist or get order with no processed statuses
	 * @log Exception       log to wp logs an \Exception
	 */
	public function purchase( $order ) {
		try {
			if ( empty( $order ) ) {
				return true;
			}

			try {
				if ( $this->preventEventDuplication ) {
					session_start();
					if ( isset( $_SESSION['salesmanagoLastEvent'] )
						&& $_SESSION['salesmanagoLastEvent'] + self::PREVENT_EVENT_DUPLICATION_TIME > time() ) {
						return false;
					} else {
						$_SESSION['salesmanagoLastEvent'] = time();
					}
				}
			} catch ( \Exception $e ) {
				/* Silence is golden */
			}

			$order = is_int( $order ) ? wc_get_order( $order ) : $order;

			// Populate Contact Model with fields from submitted data
			if ( ! $this->ContactModel->parseCustomerFromPost() ) {
				if ( ! $this->ContactModel->parseCustomer( $order->get_id() ) ) {
                    return false;
				}
			}

			$order->get_user()
				? $this->ContactModel->setTagsFromConfig( ContactModel::TAGS_PURCHASE )
				: $this->ContactModel->setTagsFromConfig( ContactModel::TAGS_GUEST_PURCHASE );

			$client[ ContactModel::EMAIL ]     = $this->ContactModel->getClientEmail();
			$client[ ContactModel::SM_CLIENT ] = ContactModel::getSmClient();
			if ( empty( $client[ ContactModel::SM_CLIENT ] ) && empty( $client[ ContactModel::EMAIL ] ) ) {
				return false;
			}

			$products = Helper::getProductsFromOrder( $order, $this->productIdentifierType );
			if ( ! $products ) {
				return false;
			}

			$this->EventModel->bindEvent(
				$client,
				EventModel::EVENT_TYPE_PURCHASE,
				$products,
				$this->Configuration->getLocation(),
                $this->lang
			);
			Helper::doAction(
				'salesmanago_wc_purchase',
				array(
					'Contact' => $this->ContactModel->get(),
					'Event'   => $this->EventModel->get(),
				)
			);
			return $this->TransferController->transferBoth( $this->ContactModel->get(), $this->EventModel->get() );
		} catch ( \Exception $e ) {
			error_log( print_r( $e->getMessage(), true ) );
		}
		return false;
	}

	/**
	 * @return bool
	 */
	public function addToCart() {
		try {
			$client[ ContactModel::SM_CLIENT ] = ContactModel::getSmClient();
			$client[ ContactModel::EMAIL ]     = $this->ContactModel->getClientEmail();
			if ( empty( $client[ ContactModel::SM_CLIENT ] ) && empty( $client[ ContactModel::EMAIL ] ) ) {
				return false;
			}

			$products = $this->EventModel->getProductsFromCart();
			if ( ! $products ) {
				return false;
			}

			$smEvent = $this->EventModel->getSmEvent();

			$this->EventModel->bindEvent(
				$client,
				EventModel::EVENT_TYPE_CART,
				$products,
				$this->Configuration->getLocation(),
                $this->lang,
				$smEvent
			);
			Helper::doAction( 'salesmanago_wc_cart', array( 'Event' => $this->EventModel->get() ) );
			return $this->TransferController->transferEvent( $this->EventModel->get() );

		} catch ( Exception $e ) {
			error_log( print_r( $e->getMessage(), true ) );
		} catch ( \Exception $e ) {
			error_log( print_r( $e->getMessage(), true ) );
		}
		return false;
	}

	/**
	 * @param $orderId
	 * @return bool
	 */
	public function orderStatusChanged( $orderId ) {
		try {
			$eventHook = Helper::getCurrentAction();
			switch ( $eventHook ) {
				case 'woocommerce_order_status_cancelled':
                case 'woocommerce_order_status_failed':
					$eventType = EventModel::EVENT_TYPE_CANCELLATION;
					break;
				case 'woocommerce_order_status_refunded':
					$eventType = EventModel::EVENT_TYPE_RETURN;
					break;
				default:
					$eventType = EventModel::EVENT_TYPE_DEFAULT;
			}

			if ( ! $this->ContactModel->parseCustomer( $orderId ) ) {
				return false;
			}

			$client['email']    = $this->ContactModel->get()->getEmail();
			$client['smclient'] = ContactModel::getSmClient();
			if ( empty( $client['email'] ) && empty( $client['smclient'] ) ) {
				return false;
			}

			$smEvent = $this->EventModel->getSmEvent();

			$products = Helper::getProductsFromOrder( $orderId, $this->productIdentifierType );
			if ( ! $products ) {
				return false;
			}

			$this->EventModel->bindEvent(
				$client,
				$eventType,
				$products,
                Configuration::getInstance()->getLocation(),
                $this->lang,
				$smEvent
			);
			Helper::doAction( 'salesmanago_wc_action_order_change_status', array( 'Event' => $this->EventModel->get() ) );
			return $this->TransferController->transferEvent( $this->EventModel->get() );

		} catch ( Exception $e ) {
			error_log( print_r( $e->getMessage(), true ) );
		} catch ( \Exception $e ) {
			error_log( print_r( $e->getMessage(), true ) );
		}
		return false;
	}
}
