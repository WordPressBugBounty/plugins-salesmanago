<?php


namespace SALESmanago\Controller;


use SALESmanago\Entity\Configuration;
use SALESmanago\Entity\ConfigurationInterface;
use SALESmanago\Entity\Contact\Contact;
use SALESmanago\Model\ContactModel;
use SALESmanago\Services\ContactService;
use SALESmanago\Exception\Exception;


class ContactController
{
    protected $conf;
    protected $service;

    public function __construct(ConfigurationInterface $conf)
    {
        Configuration::setInstance($conf);
        $this->conf = $conf;
        $this->service  = new ContactService($conf);
    }

    /**
     * @param Contact $Contact
     * @return Contact|null
     * @throws Exception
     */
    public function getContactBasic(Contact $Contact)
    {
        $Response = $this->service->getContactBasic($Contact);
        $contactModel = new ContactModel($Contact, $this->conf);

        if ($Response->isSuccess() && !empty($Response->getField('contacts')[0])) {
            return $contactModel->getContactFromBasicResponse($Response->getField('contacts')[0]);
        }

        return null;
    }
}
