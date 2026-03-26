<?php

namespace bhr\Admin\Controller;

use bhr\Admin\Entity\PlatformSettings;
use bhr\Admin\Entity\Plugins\Wc;
use bhr\Admin\Model\AdminActionModel;
use bhr\Admin\Model\Helper;

use SALESmanago\Controller\ContactAndEventTransferController;
use SALESmanago\Entity\Configuration;
use SALESmanago\Entity\Response;
use SALESmanago\Exception\Exception;

class AdminActionController {

	const
		EVENT_TYPE_CANCELLATION         = 'CANCELLATION',
		EVENT_TYPE_PURCHASE             = 'PURCHASE',
		EVENT_TYPE_RETURN               = 'RETURN',
		WOOCOMMERCE_STATUS_PROCESSING   = 'processing',
		WOOCOMMERCE_STATUS_REFUNDED     = 'refunded',
		WOOCOMMERCE_STATUS_CANCELLED    = 'cancelled',
		WOOCOMMERCE_STATUS_FAILED       = 'failed';

	/**
	 * @var Configuration
	 */
	private $Configuration;

	/**
	 * @var PlatformSettings
	 */
	private $PlatformSettings;

	/**
	 * @var AdminActionModel
	 */
	private $AdminActionModel;

	/**
	 * @var ContactAndEventTransferController
	 */
	private $ContactAndEventTransferController;

    private $lang;

    /**
	 * @param Configuration    $Configuration
	 * @param PlatformSettings $PlatformSettings
	 */
	public function __construct( $Configuration, $PlatformSettings ) {
		$this->Configuration    = $Configuration;
		$this->PlatformSettings = $PlatformSettings;
		$this->AdminActionModel = new AdminActionModel();

        $this->lang = Helper::getLanguage(
            !empty($PlatformSettings->getLanguageDetection())
                ? $PlatformSettings->getLanguageDetection()
                : Wc::DEFAULT_LANGUAGE_DETECTION
        );

		$this->ContactAndEventTransferController = new ContactAndEventTransferController( $this->Configuration );
	}

	/**
	 * @param $data
	 *
	 * @return false|Response
	 */
	public function orderStatusChanged( $data ) {
		try {
			$WcOrder = Helper::wcGetOrder( $data );

			$Contact = $this->AdminActionModel->parseCustomerFromWcOrder( $WcOrder->get_data() );

			if ( ! $Contact ) {
				return false;
			}

			$eventType = $this->resolveEventType( $WcOrder );

			if ( !empty( $eventType ) && Helper::wasOrderStatusProcessed( $WcOrder, $eventType )) {
				return false;
			}

			$Event = $this->AdminActionModel->bindEvent(
				$this->AdminActionModel->parseEventFromWcOrder( $WcOrder, $this->PlatformSettings ),
				$eventType,
				$Contact,
				$this->Configuration->getLocation(),
				$this->lang
			);
			if ( $Event ) {
				$response = $this->ContactAndEventTransferController->transferBoth( $Contact, $Event );

				if ( $response ) {
					Helper::markOrderAsProcessed( $WcOrder, $eventType );
				}

				return $response;
			} else {
				return $this->ContactAndEventTransferController->transferContact( $Contact );
			}
		} catch ( Exception $e ) {
			error_log( $e->getMessage(), $e->getCode() );
			return false;
		}
	}

	/**
	 * @param $order
	 *
	 * @return string
	 */
	private function resolveEventType ( $order ): string {
		switch ( $order->get_status() ) {
			case self::WOOCOMMERCE_STATUS_PROCESSING:
				return self::EVENT_TYPE_PURCHASE;
			case self::WOOCOMMERCE_STATUS_REFUNDED:
				return self::EVENT_TYPE_RETURN;
			case self::WOOCOMMERCE_STATUS_CANCELLED:
			case self::WOOCOMMERCE_STATUS_FAILED:
				return self::EVENT_TYPE_CANCELLATION;
			default:
				return '';
		}
	}

    /**
     * @param $userId
     * @param $oldData
     *
     * @return false|Response
     */
    public function updateUser($userId, $oldData)
    {
        try {
            $User = Helper::getUserBy('id', $userId);

            $Contact = $this->AdminActionModel->parseCustomer($User, $oldData);

            if ( $Contact ) {
                return $this->ContactAndEventTransferController->transferContact($Contact);
            }
            return true;
        } catch ( Exception $e ) {
            error_log( $e->getMessage(), $e->getCode() );
            return false;
        }
    }
}
