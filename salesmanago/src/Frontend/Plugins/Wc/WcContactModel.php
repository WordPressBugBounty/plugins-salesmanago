<?php

namespace bhr\Frontend\Plugins\Wc;

if(!defined('ABSPATH')) exit;

use bhr\Frontend\Model\AbstractContactModel;
use bhr\Frontend\Model\Helper;
use bhr\Includes\GlobalConstant;
use SALESmanago\Entity\Contact\Contact;

class WcContactModel extends AbstractContactModel
{
    public  $user;      //holds userId or user login
    public  $userType;  //login or register

    public function __construct($PlatformSettings)
    {
        //do not continue without settings
        if(empty($PlatformSettings) || empty($PlatformSettings->PluginWc)) {
            return false;
        }
        //create an Abstract Contact
        parent::__construct($PlatformSettings, $PlatformSettings->PluginWc);
        return true;
    }

    /**
     * @param $user
     * @param $userType
     * @param $oldData
     *
     * @return false|Contact
     */
    public function parseContact($user, $userType = 'id', $oldData = null)
    {
        $this->user     = $user;
        $this->userType = $userType;
        if (empty($this->user)) {
            return null;
        }

        $contactData = '';
        if ($this->userType === GlobalConstant::ID) {
            $contactData = Helper::getUserBy('id', $this->user);
        } elseif ($this->userType === GlobalConstant::LOGIN) {
            $contactData = Helper::getUserBy('login', $this->user);
        }

        if (empty($contactData)) {
            return false;
        }

        /* email */
        if (empty($contactData->user_email)) {
            return false;
        }

        if ( ! empty( $oldData->user_email ) && $contactData->user_email !== $oldData->user_email ) {
            $this->Contact->setEmail($oldData->user_email);
            $this->Contact->getOptions()->setNewEmail($contactData->user_email);
        } else {
            $this->Contact->setEmail($contactData->user_email);
        }

        /* name
        Try to get name from Billing Address, Account Details, or Request
         */
        $name = trim(Helper::getPostMetaData($contactData->ID, GlobalConstant::F_NAME, true) . ' '
            . Helper::getPostMetaData($contactData->ID, GlobalConstant::L_NAME, true));
        if(empty($name)) {
            $name = trim(Helper::getPostMetaData($contactData->ID, GlobalConstant::B_F_NAME, true) . ' '
                . Helper::getPostMetaData($contactData->ID, GlobalConstant::B_L_NAME, true));
        }
        if (empty($name) && isset($_REQUEST['action']) && $_REQUEST['action'] == 'save_account_details') {
            $name = trim($_REQUEST['account_first_name'] . ' '
                . $_REQUEST['account_last_name']);
        }
        $this->Contact->setName($name);

        /* phone */
        $phone = Helper::getPostMetaData($contactData->ID, GlobalConstant::B_PHONE, GlobalConstant::SINGLE_VALUE);
        $this->Contact->setPhone($phone);

        /* company */
        $company = Helper::getPostMetaData($contactData->ID, GlobalConstant::B_COMPANY, GlobalConstant::SINGLE_VALUE);
        $this->Contact->setCompany($company);

        /* options */
        $this->setLanguage();
        if (Helper::preventMultipleDoubleOptInMails()) {
            $this->setOptInStatuses();
        }

        return $this->Contact;
    }

    /**
     * @param $orderId
     * @return Contact|null
     */
    public function parseCustomer($orderId)
    {
        /* email */
        $email = Helper::getPostMetaData($orderId, GlobalConstant::P_NO_ACC_EMAIL, GlobalConstant::SINGLE_VALUE);
        if(empty($email)) {
            return null;
        }
        $this->Contact->setEmail($email);

        /* name */
        $name = trim(Helper::getPostMetaData($orderId, GlobalConstant::P_NO_ACC_F_NAME, GlobalConstant::SINGLE_VALUE) . ' ' .
            Helper::getPostMetaData($orderId, GlobalConstant::P_NO_ACC_L_NAME, GlobalConstant::SINGLE_VALUE));
        $this->Contact->setName(!empty($name) ? $name : '');

        /* phone */
        $phone = Helper::getPostMetaData($orderId, GlobalConstant::P_NO_ACC_PHONE, GlobalConstant::SINGLE_VALUE);
        $this->Contact->setPhone(!empty($phone) ? $phone : '');

        /* company */
        $company = Helper::getPostMetaData($orderId, GlobalConstant::P_NO_ACC_COMPANY, GlobalConstant::SINGLE_VALUE);
        $this->Contact->setCompany(!empty($company) ? $company : '');

        /* streetAddress */
        $streetAddress = trim(Helper::getPostMetaData($orderId, GlobalConstant::P_NO_ACC_ADDRESS_1, GlobalConstant::SINGLE_VALUE) . ' ' .
            Helper::getPostMetaData($orderId, GlobalConstant::P_NO_ACC_ADDRESS_2, GlobalConstant::SINGLE_VALUE));
        $this->Address->setStreetAddress(!empty($streetAddress) ? $streetAddress : '');

        /* zipCode */
        $zipCode = Helper::getPostMetaData($orderId, GlobalConstant::P_NO_ACC_POSTCODE, GlobalConstant::SINGLE_VALUE);
        $this->Address->setZipCode(!empty($zipCode) ? $zipCode : '');

        /* city */
        $city = Helper::getPostMetaData($orderId, GlobalConstant::P_NO_ACC_CITY, GlobalConstant::SINGLE_VALUE);
        $this->Address->setCity(!empty($city) ? $city : '');

        /* country */
        $country = Helper::getPostMetaData($orderId, GlobalConstant::P_NO_ACC_COUNTRY, GlobalConstant::SINGLE_VALUE);
        $this->Address->setCountry(!empty($country) ? $country : '');

        $this->Contact->setAddress($this->Address);

        /* options */
        $this->setLanguage();
        if (Helper::preventMultipleDoubleOptInMails()) {
            $this->setOptInStatuses();
        }

        return $this->Contact;
    }

    /**
     * @return array|null
     */
    public static function getSmClient()
    {
        $smclient = isset($_COOKIE['smclient']) ? $_COOKIE['smclient'] : null;
        if(!$smclient) {
            $smclient = isset($_SESSION['smclient']) ? $_SESSION['smclient'] : null;
        }
        return $smclient;
    }

    /**
     * @return mixed|null
     */
    public function getClientEmail()
    {
        try {
            if (!empty($this->Contact->getEmail())) {
                return $this->Contact->getEmail();
            }
        } catch (\Exception $e) {
            //silence is gold
        }

        if ($user = Helper::getUserBy('id', Helper::getCurrentUserId())) {
            $currentUser = $user->data;
            return !empty($currentUser->user_email) ? $currentUser->user_email : null;
        }
        return null;
    }

    /**
     * @return Contact|null
     */
    public function parseCustomerFromPost()
    {
        /* email */
        $email = empty($_REQUEST[GlobalConstant::B_EMAIL]) ? '' : $_REQUEST[GlobalConstant::B_EMAIL];
        if(empty($email)) {
            return null;
        }
        $this->Contact->setEmail($email);

        /* name */
        $name = trim($_REQUEST[GlobalConstant::B_F_NAME] . ' ' .
            $_REQUEST[GlobalConstant::B_L_NAME]);
        $this->Contact->setName(!empty($name) ? $name : '');

        /* phone */
        $phone = $_REQUEST[GlobalConstant::B_PHONE];
        $this->Contact->setPhone(!empty($phone) ? $phone : '');

        /* company */
        $company = $_REQUEST[GlobalConstant::B_COMPANY];
        $this->Contact->setCompany(!empty($company) ? $company : '');


        /* streetAddress */
        $streetAddress = trim($_REQUEST[GlobalConstant::B_ADDRESS_1] . ' ' .
            $_REQUEST[GlobalConstant::B_ADDRESS_2]);
        $this->Address->setStreetAddress(!empty($streetAddress) ? $streetAddress : '');

        /* zipCode */
        $zipCode = $_REQUEST[GlobalConstant::B_POSTCODE];
        $this->Address->setZipCode(!empty($zipCode) ? $zipCode : '');

        /* city */
        $city = $_REQUEST[GlobalConstant::B_CITY];
        $this->Address->setCity(!empty($city) ? $city : '');

        /* country */
        $country = $_REQUEST[GlobalConstant::B_COUNTRY];
        $this->Address->setCountry(!empty($country) ? $country : '');

        $this->Contact->setAddress($this->Address);

        /* options */
        $this->setLanguage();
        if (Helper::preventMultipleDoubleOptInMails()) {
            $this->setOptInStatuses();
        }

        return $this->Contact;
    }
}
