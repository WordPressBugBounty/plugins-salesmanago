<?php

namespace SALESmanago\Controller;

use SALESmanago\Entity\Configuration;
use SALESmanago\Entity\ConfigurationInterface;
use SALESmanago\Model\Collections\Collection;
use SALESmanago\Model\Collections\ContactsCollection;
use SALESmanago\Services\CheckIfIgnoredService as IgnoreService;
use SALESmanago\Services\ContactAndEventTransferService;
use SALESmanago\Services\ExportService;
use SALESmanago\Entity\Response;
use SALESmanago\Exception\Exception;

class ExportController
{
    /**
     * @var Configuration
     */
    protected $conf;

    /**
     * @var ContactAndEventTransferService
     */
    protected $service;

    /**
     * @var IgnoreService
     */
    protected $ignoreService;

    /**
     * ExportController constructor.
     *
     * @param ConfigurationInterface $conf
     */
    public function __construct(ConfigurationInterface $conf)
    {
        Configuration::setInstance($conf);
        $this->conf = $conf;
        $this->service = new ExportService($conf);
        $this->ignoreService = new IgnoreService($this->conf);
    }

    /**
     * Export collection
     *
     * @param Collection $collection
     * @return Response
     * @throws Exception
     */
    public function export(Collection $collection)
    {
        $collection = $this->checkAndFilterContactsCollection($collection);
        return $this->service->export($collection);
    }

    /**
     * Check and filter contacts
     *
     * @param Collection $collection
     * @return Collection
     */
    protected function checkAndFilterContactsCollection(Collection $collection)
    {
        if (!($collection instanceof ContactsCollection)) {
            return $collection;
        }

        $collectionToArr = $collection->getItems();

        array_walk($collectionToArr, function ($item, $key) use (&$collection) {
            if ($this->ignoreService->isContactIgnored($item)) {
                $collection->removeItem($key);
            }

        });

        return $collection;
    }
}