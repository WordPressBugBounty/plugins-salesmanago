<?php

namespace Tests\Feature\Event;

use SALESmanago\Entity\Configuration;
use Tests\Feature\ContactHelper;
use Tests\Feature\TestCaseFeature;
use Tests\Feature\EventHelper;
use SALESmanago\Exception\Exception;
use SALESmanago\Controller\ContactAndEventTransferController;

class TestTransferEvent extends TestCaseFeature
{
    /**
     * @var EventHelper
     */
    protected $eventHelper;

    /**
     * @var ContactHelper
     */
    protected $contactHelper;

    /**
     * @var ContactAndEventTransferController
     */
    protected $controller;

    /**
     * @return void
     * @throws Exception
     */
    public function setUp(): void
    {
        //use EventHelper:
        $this->eventHelper = new EventHelper();
        $this->contactHelper = new ContactHelper();

        //initiate configuration:
        $conf = $this->initConf();

        //create transfer controller:
        $this->controller = new ContactAndEventTransferController($conf);
        parent::setUp();
    }

    /**
     * Test send empty event
     *
     * @return void
     * @throws Exception
     */
    public function testPreventToSendEmptyEventToSalesmanagoSuccess()
    {
        $Event = $this->eventHelper->generate();

        //set attributes which defines empties of event
        $Event
            ->setProducts('')
            ->setValue(null);

        $this->expectException(Exception::class);
        $this->controller->transferEvent($Event);
    }

    /**
     * Test send empty event with contact
     *
     * @return void
     * @throws Exception
     */
    public function testPreventToSendEmptyEventThroughTransferBothMethodSuccess()
    {
        $this->expectException(Exception::class);
        $this->generateAndTransferBothContactAndEvent();
    }

    /**
     * Test send empty event with contact
     *
     * @return void
     * @throws Exception
     */
    public function testReturnContactResponseInExceptionWhenSendEmptyEventThroughTransferBothMethodSuccess()
    {
        try {
            $this->generateAndTransferBothContactAndEvent();
        } catch (Exception $e) {
            $this->assertNotEmpty($e->getLastApiResponse());
            $this->assertNotEmpty($e->getLastApiResponse()->getField('contactId'));
        }
    }

    /**
     * @throws Exception
     */
    protected function generateAndTransferBothContactAndEvent()
    {
        $Event = $this->eventHelper->generate();
        $Contact = $this->contactHelper->generateContact();

        //set attributes which defines empties of event and set contact email
        $Event
            ->setProducts('')
            ->setValue(null)
            ->setEmail($Contact->getEmail());

        $this->controller->transferBoth($Contact, $Event);
    }
}