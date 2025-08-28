<?php


namespace SALESmanago\Services;

use SALESmanago\Entity\ConfigurationInterface;
use SALESmanago\Entity\Contact\Contact;
use SALESmanago\Entity\Event\Event;
use SALESmanago\Entity\Response;
use SALESmanago\Exception\Exception;
use SALESmanago\Model\ContactModel;
use SALESmanago\Model\EventModel;
use SALESmanago\Model\ConfModel;


class ContactAndEventTransferService
{
    const
        REQUEST_METHOD_POST = 'POST',
        API_METHOD          = '/api/integration/upsert',
        CONTACT_OBJ_NAME    = 'contactUpsertRequest',
        EVENT_OBJ_NAME      = 'addContactExtEventRequest';

    private $RequestService;
    private $conf;
    private $ConfModel;

    /**
     * ContactAndEventTransferService constructor.
     * @param ConfigurationInterface $conf
     * @throws Exception
     */
    public function __construct(ConfigurationInterface $conf)
    {
        $this->conf = $conf;
        $this->ConfModel = new ConfModel($conf);
        $this->RequestService = new RequestService($conf);
    }

    /**
     * @param Contact $Contact
     * @param Event $Event
     * @return Response
     * @throws Exception
     */
    public function transferBoth(Contact $Contact, Event $Event)
    {
        $ContactModel = new ContactModel($Contact, $this->conf);

        if ($Event->isEmpty()) {
            $Response = $this->transferContact($Contact);
            throw (
                new Exception('Event with external id: ' . $Event->getExternalId() . ' is empty')
            )->setLastApiResponse($Response);//contactId must be returned
        }

        $EventModel = new EventModel($Event, $this->conf);

        $settings = $this->ConfModel->getAuthorizationApiData();
        $contact  = $ContactModel->getContactForUnionTransfer();
        $event    = $EventModel->getEventForUnionTransfer();

        $data = array_merge(
            $settings,
            [self::CONTACT_OBJ_NAME => $contact, self::EVENT_OBJ_NAME => $event]
        );

        $Response = $this->RequestService->request(
            self::REQUEST_METHOD_POST,
            self::API_METHOD,
            $data
        );

        return $this->RequestService->validateCustomResponse(
            $Response,
            array(
                boolval($Response->getField('contactId')),
                boolval($Response->getField('eventId'))
            )
        );
    }

    /**
     * @throws Exception
     * @param Event $Event
     * @return Response
     */
    public function transferEvent(Event $Event)
    {
        if ($Event->isEmpty()) {
            throw new Exception('Event with external id: ' . $Event->getExternalId() . ' is empty');
        }

        $EventModel = new EventModel($Event, $this->conf);

        $settings = $this->ConfModel->getAuthorizationApiData();
        $event    = $EventModel->getEventForUnionTransfer();

        $data = array_merge(
            $settings,
            [self::EVENT_OBJ_NAME => $event]
        );

        $Response = $this->RequestService->request(
            self::REQUEST_METHOD_POST,
            self::API_METHOD,
            $data
        );

        return $this->RequestService->validateCustomResponse(
            $Response,
            array(
                boolval($Response->getField('eventId')
                )
            )
        );
    }

    /**
     * @throws Exception
     * @param Contact $Contact
     * @return Response
     */
    public function transferContact(Contact $Contact)
    {
        $ContactModel = new ContactModel($Contact, $this->conf);

        $settings = $this->ConfModel->getAuthorizationApiData();
        $contact  = $ContactModel->getContactForUnionTransfer();

        $data = array_merge(
            $settings,
            [self::CONTACT_OBJ_NAME => $contact]
        );

        $Response = $this->RequestService->request(
            self::REQUEST_METHOD_POST,
            self::API_METHOD,
            $data
        );

        return $this->RequestService
            ->validateCustomResponse(
                $Response,
                array(
                    boolval($Response->getField('contactId')
                    )
                )
            );
    }

    /**
     * Update Configuration e.g. Endpoint after Request service has been constructed
     */
    public function updateConfiguration()
    {
        $this->RequestService->updateConfiguration();
    }
}
