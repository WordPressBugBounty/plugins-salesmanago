<?php


namespace SALESmanago\Controller;


use SALESmanago\Controller\Traits\ContactStatusSynchronizationTrait;
use SALESmanago\Controller\Traits\TemporaryStorageControllerTrait;
use SALESmanago\Entity\Configuration;
use SALESmanago\Entity\ConfigurationInterface;
use SALESmanago\Entity\Response;
use SALESmanago\Exception\Exception;
use SALESmanago\Services\ContactAndEventTransferService;

use SALESmanago\Entity\Contact\Contact;
use SALESmanago\Entity\Event\Event;
use SALESmanago\Services\SynchronizationService as SyncService;
use SALESmanago\Services\CheckIfIgnoredService as IgnoreService;

use SALESmanago\Adapter\CookieManagerAdapter;

class ContactAndEventTransferController
{
    //this one is to set cookies and sessions:
    use TemporaryStorageControllerTrait;
    //this one allow to synchronize contact status:
    use ContactStatusSynchronizationTrait;

    protected $conf;
    protected $service;

    /**
     * @var SyncService
     */
    protected $syncService;

    /**
     * @var IgnoreService
     */
    protected $ignoreService;

    /**
     * ContactAndEventTransferController constructor.
     * @param ConfigurationInterface $conf
     */
    public function __construct(ConfigurationInterface $conf)
    {
        Configuration::setInstance($conf);
        $this->conf          = $conf;
        $this->service       = new ContactAndEventTransferService($this->conf);
        $this->syncService   = new SyncService($this->conf);
        $this->ignoreService = new IgnoreService($this->conf);
    }

    /**
     * @param Contact $Contact
     * @param Event $Event
     * @return Response
     * @throws Exception
     */
    public function transferBoth(Contact $Contact, Event $Event)
    {
        if ($this->ignoreService->isContactIgnored($Contact)) {
            $Response = $this->ignoreService->getDeclineResponse();
            return $Response->setField('conf', $this->conf);
        }

        Configuration::getInstance()->setRequireSynchronization(
            $this->syncService->isNeedSyncContactEmailStatus(clone $Contact)
        );

        $Response = $this->service->transferBoth($Contact, $Event);

        //set cookies if adapter exist:
        $this->setSmClient($Response->getField(Response::CONTACT_ID));

        //set cookies if adapter exist:
        if ($Event->getContactExtEventType() == Event::EVENT_TYPE_CART) {
            $this->setSmEvent($Response->getField(Response::EVENT_ID));
        } elseif($Event->getContactExtEventType() == Event::EVENT_TYPE_PURCHASE) {
            $this->unsetSmEvent();
        }

        return $Response->setField('conf', Configuration::getInstance());
    }

    /**
     * @param Event $Event
     * @return Response
     * @throws Exception
     */
    public function transferEvent(Event $Event)
    {
        $Response = $this->service->transferEvent($Event);

        //set cookies if adapter exist:
        if ($Event->getContactExtEventType() == Event::EVENT_TYPE_CART) {
            $this->setSmEvent($Response->getField(Response::EVENT_ID));
        } elseif($Event->getContactExtEventType() == Event::EVENT_TYPE_PURCHASE) {
            $this->unsetSmEvent();
        }
        return $Response;
    }

    /**
     * @param Contact $Contact
     * @return Response
     * @throws Exception
     */
    public function transferContact(Contact $Contact)
    {
        if ($this->ignoreService->isContactIgnored($Contact)) {
            return $this->ignoreService->getDeclineResponse();
        }

        Configuration::getInstance()
            ->setRequireSynchronization(
            $this->syncService->isNeedSyncContactEmailStatus(clone $Contact)
        );

        $Response = $this->service->transferContact($Contact);

        //set cookies if adapter exist:
        $this->setSmClient($Response->getField(Response::CONTACT_ID));

        return $Response->setField('conf', Configuration::getInstance());
    }

    /**
     * Update Configuration e.g. Endpoint after Request service has been constructed
     */
    public function updateConfiguration()
    {
        $this->service->updateConfiguration();
    }
}
