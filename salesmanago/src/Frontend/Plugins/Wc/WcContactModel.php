<?php

namespace bhr\Frontend\Plugins\Wc;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use bhr\Frontend\Model\AbstractContactModel;
use bhr\Frontend\Model\Helper;
use bhr\Includes\GlobalConstant;
use SALESmanago\Entity\Contact\Contact;

class WcContactModel extends AbstractContactModel {

	public $user;      // holds userId or user login
	public $userType;  // login or register

	public function __construct( $PlatformSettings ) {
		// do not continue without settings
		if ( empty( $PlatformSettings ) || empty( $PlatformSettings->PluginWc ) ) {
			return false;
		}
		// create an Abstract Contact
		parent::__construct( $PlatformSettings, $PlatformSettings->PluginWc );
		return true;
	}

	/**
	 * @param $user
	 * @param $userType
	 * @param $oldData
	 *
	 * @return false|Contact
	 */
	public function parseContact( $user, $userType = 'id', $oldData = null ) {
		$this->user     = $user;
		$this->userType = $userType;
		if ( empty( $this->user ) ) {
			return null;
		}

		$contactData = '';
		if ( $this->userType === GlobalConstant::ID ) {
			$contactData = Helper::getUserBy( 'id', $this->user );
		} elseif ( $this->userType === GlobalConstant::LOGIN ) {
			$contactData = Helper::getUserBy( 'login', $this->user );
		}

		if ( empty( $contactData ) ) {
			return false;
		}

		/* email */
		if ( empty( $contactData->user_email ) ) {
			return false;
		}

		if ( ! empty( $oldData->user_email ) && $contactData->user_email !== $oldData->user_email ) {
			$this->Contact->setEmail( $oldData->user_email );
			$this->Contact->getOptions()->setNewEmail( $contactData->user_email );
		} else {
			$this->Contact->setEmail( $contactData->user_email );
		}

		/*
		name
		Try to get name from Billing Address, Account Details, or Request
		 */
		$name = trim(
			Helper::getPostMetaData( $contactData->ID, GlobalConstant::F_NAME, true ) . ' '
			. Helper::getPostMetaData( $contactData->ID, GlobalConstant::L_NAME, true )
		);
		if ( empty( $name ) ) {
			$name = trim(
				Helper::getPostMetaData( $contactData->ID, GlobalConstant::B_F_NAME, true ) . ' '
				. Helper::getPostMetaData( $contactData->ID, GlobalConstant::B_L_NAME, true )
			);
		}
		if ( empty( $name ) && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'save_account_details' ) {
			$name = trim(
				$_REQUEST['account_first_name'] . ' '
				. $_REQUEST['account_last_name']
			);
		}
		$this->Contact->setName( $name );

		/* phone */
		$phone = Helper::getPostMetaData( $contactData->ID, GlobalConstant::B_PHONE, GlobalConstant::SINGLE_VALUE );
		$this->Contact->setPhone( $phone );

		/* company */
		$company = Helper::getPostMetaData( $contactData->ID, GlobalConstant::B_COMPANY, GlobalConstant::SINGLE_VALUE );
		$this->Contact->setCompany( $company );

		/* options */
		$this->setLanguage();
		if ( Helper::preventMultipleDoubleOptInMails() ) {
			$this->setOptInStatuses();
		}

		return $this->Contact;
	}


    /**
     * @param $orderId
     * @return Contact|null
     */
    public function parseCustomer( $orderId ) {
        /* email */
        $email = Helper::getPostMetaData( $orderId, GlobalConstant::P_NO_ACC_EMAIL, GlobalConstant::SINGLE_VALUE );
        if ( empty( $email ) ) {
            return null;
        }
        $this->Contact->setEmail( $email );

        /* name */
        $name = trim(
            Helper::getPostMetaData( $orderId, GlobalConstant::P_NO_ACC_F_NAME, GlobalConstant::SINGLE_VALUE ) . ' ' .
            Helper::getPostMetaData( $orderId, GlobalConstant::P_NO_ACC_L_NAME, GlobalConstant::SINGLE_VALUE )
        );
        $this->Contact->setName( ! empty( $name ) ? $name : '' );

        /* phone */
        $phone = Helper::getPostMetaData( $orderId, GlobalConstant::P_NO_ACC_PHONE, GlobalConstant::SINGLE_VALUE );
        $this->Contact->setPhone( ! empty( $phone ) ? $phone : '' );

        /* company */
        $company = Helper::getPostMetaData( $orderId, GlobalConstant::P_NO_ACC_COMPANY, GlobalConstant::SINGLE_VALUE );
        $this->Contact->setCompany( ! empty( $company ) ? $company : '' );

        /* streetAddress */
        $streetAddress = trim(
            Helper::getPostMetaData( $orderId, GlobalConstant::P_NO_ACC_ADDRESS_1, GlobalConstant::SINGLE_VALUE ) . ' ' .
            Helper::getPostMetaData( $orderId, GlobalConstant::P_NO_ACC_ADDRESS_2, GlobalConstant::SINGLE_VALUE )
        );
        $this->Address->setStreetAddress( ! empty( $streetAddress ) ? $streetAddress : '' );

        /* zipCode */
        $zipCode = Helper::getPostMetaData( $orderId, GlobalConstant::P_NO_ACC_POSTCODE, GlobalConstant::SINGLE_VALUE );
        $this->Address->setZipCode( ! empty( $zipCode ) ? $zipCode : '' );

        /* city */
        $city = Helper::getPostMetaData( $orderId, GlobalConstant::P_NO_ACC_CITY, GlobalConstant::SINGLE_VALUE );
        $this->Address->setCity( ! empty( $city ) ? $city : '' );

        /* country */
        $country = Helper::getPostMetaData( $orderId, GlobalConstant::P_NO_ACC_COUNTRY, GlobalConstant::SINGLE_VALUE );
        $this->Address->setCountry( ! empty( $country ) ? $country : '' );

        $this->Contact->setAddress( $this->Address );

        /* options */
        $this->setLanguage();
        if ( Helper::preventMultipleDoubleOptInMails() ) {
            $this->setOptInStatuses();
        }

        return $this->Contact;
    }

	/**
     * Parses customer data from an order ID.
     *
	 * @param $orderId
	 * @return Contact|null
	 */
	public function parseCustomerFromOrder( $order ) {
        $user_id = $order->get_user_id();
        if ($user_id) {
            $user = $order->get_user();
            $email = $user->user_email;
            $first_name = $user->first_name;
            $last_name = $user->last_name;
        } else {
            $email = $order->get_billing_email();
            $first_name = $order->get_billing_first_name();
            $last_name = $order->get_billing_last_name();
        }

        $phone    = $order->get_billing_phone();
        $company  = $order->get_billing_company();
        $address  = $order->get_billing_address_1() . ' ' . $order->get_billing_address_2();
        $zipCode  = $order->get_billing_postcode();
        $city     = $order->get_billing_city();
        $country  = $order->get_billing_country();

        $name = trim($first_name . ' ' . $last_name);

        $this->Contact
            ->setEmail( $email )
            ->setName( $name )
            ->setPhone( ! empty( $phone ) ? $phone : '' )
            ->setCompany( ! empty( $company ) ? $company : '' )
            ->setAddress(
                $this->Address
                    ->setStreetAddress(!empty($address) ? $address : '')
                    ->setZipCode(!empty($zipCode) ? $zipCode : '')
                    ->setCity(!empty($city) ? $city : '')
                    ->setCountry(!empty($country) ? $country : '')
            );

		/* options */
		$this->setLanguage();
		if ( Helper::preventMultipleDoubleOptInMails() ) {
			$this->setOptInStatuses();
		}

		return $this->Contact;
	}

	/**
	 * @return array|null
	 */
	public static function getSmClient() {
		$smclient = isset( $_COOKIE['smclient'] ) ? $_COOKIE['smclient'] : null;
		if ( ! $smclient ) {
			$smclient = isset( $_SESSION['smclient'] ) ? $_SESSION['smclient'] : null;
		}
		return $smclient;
	}

	/**
	 * @return mixed|null
	 */
	public function getClientEmail() {
		try {
			if ( ! empty( $this->Contact->getEmail() ) ) {
				return $this->Contact->getEmail();
			}
		} catch ( \Exception $e ) {
			// silence is gold
		}

		if ( $user = Helper::getUserBy( 'id', Helper::getCurrentUserId() ) ) {
			$currentUser = $user->data;
			return ! empty( $currentUser->user_email ) ? $currentUser->user_email : null;
		}
		return null;
	}

	/**
	 * @return Contact|null
	 */
	public function parseCustomerFromPost() {
		$data = $_REQUEST;

		/* email */
		$email = $data[ GlobalConstant::EMAIL ]
			?? $data[ GlobalConstant::B_EMAIL ]
			?? $data[ GlobalConstant::P_NO_ACC_EMAIL ]
			?? null;

		if ( is_null( $email ) ) {
			return null;
		}

		$this->Contact->setEmail( $email );

		/* name */
		$name = trim(
			implode(
				' ',
				array(
					$data[ GlobalConstant::B_F_NAME ]
					?? $data[ GlobalConstant::F_NAME ]
					?? $data[ GlobalConstant::P_NO_ACC_F_NAME ]
					?? '',
					$data[ GlobalConstant::B_L_NAME ]
					?? $data[ GlobalConstant::L_NAME ]
					?? $data[ GlobalConstant::P_NO_ACC_L_NAME ]
					?? '',
				)
			)
		);

		$this->Contact->setName( $name );

		/* phone */
		$phone = $data[ GlobalConstant::B_PHONE ]
			?? $data[ GlobalConstant::PHONE ]
			?? $data[ GlobalConstant::P_NO_ACC_PHONE ]
			?? '';
		$this->Contact->setPhone( $phone );

		/* company */
		$company = $data[ GlobalConstant::B_COMPANY ] ?? $data[ GlobalConstant::P_NO_ACC_COMPANY ] ?? '';
		$this->Contact->setCompany( $company );

		/* streetAddress */
		$streetAddress = trim(
			implode(
				' ',
				array(
					$data[ GlobalConstant::B_ADDRESS_1 ]
					?? $data[ GlobalConstant::P_NO_ACC_ADDRESS_1 ]
					?? '',
					$data[ GlobalConstant::B_ADDRESS_2 ]
					?? $data[ GlobalConstant::P_NO_ACC_ADDRESS_2 ]
					?? '',
				)
			)
		);
		$this->Address->setStreetAddress( $streetAddress );

		/* zipCode */
		$zipCode = $data[ GlobalConstant::B_POSTCODE ] ?? $data[ GlobalConstant::P_NO_ACC_POSTCODE ] ?? '';
		$this->Address->setZipCode( $zipCode );

		/* city */
		$city = $data[ GlobalConstant::B_CITY ] ?? $data[ GlobalConstant::P_NO_ACC_CITY ] ?? '';
		$this->Address->setCity( $city );

		/* country */
		$country = $data[ GlobalConstant::B_COUNTRY ] ?? $data[ GlobalConstant::P_NO_ACC_COUNTRY ] ?? '';
		$this->Address->setCountry( $country );

		$this->Contact->setAddress( $this->Address );

		/* birthday */
		$birthday = $data[ GlobalConstant::B_BIRTHDAY ]
			?? $data[ GlobalConstant::BIRTHDAY ]
			?? $data[ GlobalConstant::P_NO_ACC_BIRTHDAY ]
			?? '';
		try {
			$this->Contact->setBirthday( $birthday );
		} catch ( \Exception $e ) {
			error_log( print_r( $e->getMessage(), true ) );
		}

		/* options */
		$this->setLanguage();
		if ( Helper::preventMultipleDoubleOptInMails() ) {
			$this->setOptInStatuses();
		}

		return $this->Contact;
	}
}
