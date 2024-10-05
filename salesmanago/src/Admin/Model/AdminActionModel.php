<?php

namespace bhr\Admin\Model;

use bhr\Admin\Entity\PlatformSettings;
use bhr\Includes\GlobalConstant;
use SALESmanago\Entity\Contact\Address;
use SALESmanago\Entity\Contact\Contact;
use SALESmanago\Entity\Event\Event;

class AdminActionModel
{
    /**
     * @var Contact
     */
    private $Contact;
    /**
     * @var Address
     */
    private $Address;

    /**
     * @var Event
     */
    private $Event;

    public function __construct() {
        $this->Contact = new Contact();
        $this->Address = new Address();
        $this->Event   = new Event();

        $this->Contact
            ->setAddress( $this->Address );
    }

    /**
     * @param $data
     *
     * @return false|Contact
     */
    public function parseCustomerFromWcOrder( $data ) {
        if ( empty( $data['billing'] ) || empty( $data['billing']['email'] ) ) {
            return false;
        }
        $this->Contact
            ->setEmail( $data['billing']['email'] )
            ->setName(
                (! empty( $data['billing']['first_name'] ) ? $data['billing']['first_name'] : '')
                . ' ' .
                (! empty( $data['billing']['last_name'] ) ? $data['billing']['last_name'] : '')
            )
            ->setPhone( ! empty( $data['billing']['phone'] ) ? $data['billing']['phone'] : '' )
            ->getAddress()
            ->setCountry( ! empty( $data['billing']['country'] ) ? $data['billing']['country'] : '' )
            ->setStreetAddress(
                (! empty( $data['billing']['address_1'] ) ? $data['billing']['address_1'] : '')
                . ' ' .
                (! empty( $data['billing']['address_2'] ) ? $data['billing']['address_2'] : '')
            )
            ->setCity( ! empty( $data['billing']['city'] ) ? $data['billing']['city'] : '' )
            ->setZipCode( ! empty( $data['billing']['postcode'] ) ? $data['billing']['postcode'] : '' );

        return $this->Contact;
    }

    /**
     * @param array   $products
     * @param string  $eventType
     * @param Contact $Contact
     * @param string  $location
     * @param string  $lang
     *
     * @return false|Event
     */
    public function bindEvent( $products, $eventType, $Contact, $location, $lang ) {
        if ( ! $Contact->getEmail() ) {
            return false;
        }

        try {
            $this->Event
                ->setContactExtEventType( $eventType )
                ->setProducts( isset( $products['products'] ) ? $products['products'] : '' )
                ->setDescription( isset( $products['description'] ) ? $products['description'] : '' )
                ->setValue( isset( $products['value'] ) ? $products['value'] : '' )
                ->setLocation( ! empty( $location ) ? $location : Helper::getLocation() )
                ->setDetails(
                    array(
                        '1' => isset( $products['detail1'] ) ? $products['detail1'] : '',
                        '2' => isset( $products['detail2'] ) ? $products['detail2'] : '',
                        '3' => isset( $products['detail3'] ) ? $products['detail3'] : '',
                        '4' => isset( $products['detail4'] ) ? $products['detail4'] : '',
                        '5' => isset( $products['detail5'] ) ? $products['detail5'] : '',
                        '6' => isset( $products['detail6'] ) ? $products['detail6'] : '',
                        '7' => isset( $products['detail7'] ) ? $products['detail7'] : '',
                        '8' => !empty( $lang ) ? $lang : ''
                    )
                )
                ->setDate( time() )
                ->setExternalId( isset( $products['externalId'] ) ? $products['externalId'] : '' )
                ->setEmail( $Contact->getEmail() );

            return $this->Event;
        } catch ( \Exception $e ) {
            return false;
        }
    }

    /**
     * @param \WC_Order        $WcOrder
     * @param PlatformSettings $PlatformSettings
     *
     * @return array|false
     */
    public function parseEventFromWcOrder( $WcOrder, $PlatformSettings ) {
        $products = Helper::getProductsFromOrder( $WcOrder, $PlatformSettings->getPluginWc()->getProductIdentifierType() );

        if ( empty( $products ) || ! is_array( $products ) ) {
            return false;
        }
        return $products;
    }

    /**
     * @param $User
     * @param $oldData
     *
     * @return false|Contact
     */
    public function parseCustomer($User, $oldData)
    {
        /* email */
        if ( empty( $User->user_email ) ) {
            return false;
        }
        if ( !empty( $oldData ) && $User->user_email !== $oldData->user_email ) {
            $this->Contact->setEmail($oldData->user_email);
            $this->Contact->getOptions()->setNewEmail($User->user_email);
        } else {
            $this->Contact->setEmail($User->user_email);
        }

        $this->Contact
            ->setName( ! empty( $User->display_name ) ? $User->display_name : '' );

        return $this->Contact;
    }
}
