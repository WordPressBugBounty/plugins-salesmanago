<?php


namespace SALESmanago\Services;


use SALESmanago\Entity\Configuration;
use SALESmanago\Entity\ConfigurationInterface;
use SALESmanago\Entity\Contact\Contact;
use SALESmanago\Entity\Event\Event;
use SALESmanago\Entity\Response;
use SALESmanago\Exception\Exception;
use SALESmanago\Model\ContactModel;
use SALESmanago\Model\EventModel;
use SALESmanago\Model\ConfModel;

class ContactService
{
    const
        REQUEST_METHOD_POST = 'POST',
        API_METHOD_BASIC    = '/api/contact/basic';

    private $RequestService;
    private $conf;
    private $ConfModel;

    /**
     * ContactService constructor.
     * @param ConfigurationInterface $conf
     */
    public function __construct(ConfigurationInterface $conf)
    {
        $this->conf = $conf;
        $this->ConfModel = new ConfModel($conf);
        $this->RequestService = new RequestService($conf);
    }

    /**
     * @param Contact $Contact
     * @return Response
     * @throws Exception
     */
    public function getContactBasic(Contact $Contact)
    {
        $ContactModel = new ContactModel($Contact, $this->conf);
        $settings = $this->ConfModel->getAuthorizationApiData();

        $contact = $ContactModel->getContactForBasicRequest();

        $data = array_merge($settings, $contact);

        $Response = $this->RequestService->request(
            self::REQUEST_METHOD_POST,
            self::API_METHOD_BASIC,
            $data
        );

        return $this->RequestService->validateResponse(
            $Response
        );
    }
}
