<?php


namespace SALESmanago\Controller\Traits;


use SALESmanago\Adapter\ContactStatusSynchronizationManagerAdapter as ContactSyncAdapter;
use SALESmanago\Entity\Configuration;
use SALESmanago\Entity\Contact\Contact;
use SALESmanago\Services\SynchronizationService;

trait ContactStatusSynchronizationTrait
{
    /**
     * @var SynchronizationService
     */
    protected $syncService;

    /**
     * @param ContactSyncAdapter $ContactSyncManager
     * @return $this
     */
    public function setContactSyncManager(ContactSyncAdapter $ContactSyncManager)
    {
        if (isset($this->syncService)) {
            $this->syncService->setContactSyncManager($ContactSyncManager);
        }

        return $this;
    }
}