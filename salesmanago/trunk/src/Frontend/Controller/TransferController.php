<?php

namespace bhr\Frontend\Controller;

if(!defined('ABSPATH')) exit;

use bhr\Frontend\Model\CookieManager;
use bhr\Frontend\Model\Helper;

use SALESmanago\Entity\ApiDoubleOptIn;
use SALESmanago\Entity\Configuration;
use SALESmanago\Entity\Contact\Contact;
use SALESmanago\Entity\Event\Event;

use SALESmanago\Controller\ContactAndEventTransferController;
use SALESmanago\Exception\Exception;

class TransferController
{
    protected $Configuration;
    protected $TransferController;

    /**
     * TransferController constructor.
     * @param Configuration $Configuration
     * @param \stdClass|null $PlatformSettings
     */
    public function __construct(Configuration $Configuration, \stdClass $PlatformSettings = null) {
        $this->Configuration = $Configuration;
        if(!empty($PlatformSettings)) {
            $this->setAdditionalConfigurationFields($PlatformSettings);
        }
        try {
            $this->TransferController = new ContactAndEventTransferController($this->Configuration);
            $this->TransferController->setCookieManager(new CookieManager());
        } catch (\Exception $e) {
            error_log(print_r($e->getMessage(), true));
        }
    }

    /**
     * @param string $owner
     * @return $this
     */
    public function setOwner($owner = '') {
        if(!empty($owner))
        {
            $this->Configuration->setOwner($owner);
        }
        return $this;
    }

    /**
     * @param Contact $Contact
     * @param Event $Event
     * @return bool
     */
    public function transferBoth(Contact $Contact, Event $Event)
    {
        try {
            Helper::doAction('salesmanago_transfer_both_before_send', array('Contact' => $Contact, 'Event' => $Event));
            $response = $this->TransferController->transferBoth($Contact, $Event);
            if(!$response->getStatus()) {
                throw new Exception($response->getMessage());
            }
        } catch (Exception $e) {
            error_log($e->getLogMessage());
            return false;
        }
        return true;
    }

    /**
     * Transfer contact to SALESmanago
     * @param Contact $Contact
     * @return string|false $contactId
     */
    public function transferContact(Contact $Contact)
    {
        try {
	        Helper::doAction('salesmanago_transfer_contact_before_send', array('Contact' => $Contact));
            $response = $this->TransferController->transferContact($Contact);
            if (!$response->getStatus()) {
                throw new Exception($response->getMessage());
            }
        } catch (Exception $e) {
            error_log($e->getLogMessage());
            return false;
        }
        return $response->getField( 'contactId' );
    }

    /**
     * @param Event $Event
     * @return bool
     */
    public function transferEvent(Event $Event)
    {
        try {
	        Helper::doAction('salesmanago_transfer_event_before_send', array('Event' => $Event));
            $response = $this->TransferController->transferEvent($Event);
            if (!$response->getStatus()) {
                throw new Exception($response->getMessage());
            }
        } catch (Exception $e) {
            error_log($e->getLogMessage());
            return false;
        }
        return true;
    }

    /**
     * @param \stdClass $PlatformSettings
     */
    public function setAdditionalConfigurationFields(\stdClass $PlatformSettings)
    {
        if(empty($PlatformSettings)) {
            return;
        }
        if(!empty($PlatformSettings->owner)) {
            $this->Configuration->setOwner($PlatformSettings->owner);
        }
        if(!empty($PlatformSettings->DoubleOptIn->active)) {
            $doi = $PlatformSettings->DoubleOptIn;
            $ApiDoubleOptIn = new ApiDoubleOptIn();
            $ApiDoubleOptIn
                ->setEnabled(isset($doi->active) ? boolval($doi->active) : false)
	            ->setEmailId( $doi->emailId ?? null )
	            ->setTemplateId(isset($doi->templateId) ? $doi->templateId : null)
                ->setAccountId(isset($doi->accountId) ? $doi->accountId : null)
                ->setSubject(isset($doi->subject) ? $doi->subject : null);
            $this->Configuration->setApiDoubleOptIn($ApiDoubleOptIn);
        }
    }
}
