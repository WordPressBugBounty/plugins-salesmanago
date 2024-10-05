<?php


namespace SALESmanago\Services;


use SALESmanago\Adapter\ContactStatusSynchronizationManagerAdapter as ContactSyncAdapter;
use SALESmanago\Entity\Configuration;
use SALESmanago\Entity\ConfigurationInterface;
use SALESmanago\Entity\Contact\Contact;
use SALESmanago\Model\ContactModel;
use SALESmanago\Model\ConfModel;
use SALESmanago\Controller\ContactController;
use SALESmanago\Exception\Exception;

class SynchronizationService
{
    const
        REQ_SYNC = 'requiresSynchronization',
        SYNC_TYPE_EMAIL_OPTIN = 'emailSubscriptionStatusSync';

    private $conf;
    private $ConfModel;

    private $requiresSynchronization = false;

    /**
     * @var Contact
     */
    private $Contact;

    /**
     * @var ContactSyncAdapter
     */
    private $ContactSyncManager;

    /**
     * SynchronizationService constructor.
     * @param ConfigurationInterface $conf
     */
    public function __construct(ConfigurationInterface $conf)
    {
        $this->conf = $conf;
    }

    /**
     * @param ContactSyncAdapter $ContactSyncManager
     * @return $this;
     */
    public function setContactSyncManager(ContactSyncAdapter $ContactSyncManager)
    {
        $this->ContactSyncManager = $ContactSyncManager;
        return $this;
    }

    /**
     * Synchronize Contact through Adapter
     * @param Contact $Contact
     * @return bool
     */
    private function syncContactWithManager(Contact $Contact)
    {
        return isset($this->ContactSyncManager)
            ? $this->ContactSyncManager->subscribe($this->Contact)
            : false;
    }

    /**
     * @param Contact $Contact
     * @return bool
     * @throws Exception
     */
    public function isNeedSyncContactEmailStatus(Contact $Contact)
    {
        if (!$this->conf->getActiveSynchronization()) {
            return false;
        }

        $this->Contact = $Contact;
        $contactController = new ContactController($this->conf);
        $ContactBasic = $contactController->getContactBasic($this->Contact);

        $checkSyncContactEmailStatus = $this->checkSyncContactEmailStatus($ContactBasic);

        //Try to synchronize contact email subscription through adapter
        if (isset($this->ContactSyncManager)
            && $checkSyncContactEmailStatus
        ) {
            //if synchronization will be ok, we don't need to return that contact need sync;
            $checkSyncContactEmailStatus = !$this->syncContactWithManager($this->Contact);
        }

        return $checkSyncContactEmailStatus;
    }

    /**
     * Checker for SyncContactEmailStatus
     * @param Contact|null $ContactBasic
     * @return bool
     */
    protected function checkSyncContactEmailStatus($ContactBasic)
    {
        if (!$this->conf->getActiveSynchronization()
            || $ContactBasic == null
            || $ContactBasic->getOptions()->getOptedOut()
            || $this->Contact->getOptions()->getIsSubscribes()
            || $this->Contact->getOptions()->getIsUnSubscribes()
            || $this->Contact->getOptions()->getForceOptIn() == true
        ) {
            return false;
        }

        return true;
    }
}
