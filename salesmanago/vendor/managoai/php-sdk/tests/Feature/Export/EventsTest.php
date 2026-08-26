<?php

namespace Tests\Feature\Export;

use SALESmanago\Entity\AbstractConfiguration;
use SALESmanago\Entity\Contact\Contact;
use Tests\Feature\TestCaseFeature;
use SALESmanago\Controller\ExportController;
use SALESmanago\Controller\ContactAndEventTransferController;
use Tests\Feature\EventHelper;
use SALESmanago\Exception\Exception as SalesmanagoException;
use SALESmanago\Entity\cUrlClientConfiguration;

class EventsTest extends TestCaseFeature
{
    /**
     * @return void
     * @throws SalesmanagoException
     */
    public function testExportEventsSuccess()
    {
        //create configuration of api connection and integration:
        $conf = $this->initConf();

        //email for contact creation and events:
        $email = $this->faker->email;

        //number of generates events:
        $nbOfEvents = $this->faker->numberBetween(1, 500);

        //contact need to created before add events to it:
        (new ContactAndEventTransferController($conf))->transferContact((new Contact())->setEmail($email));

        //create collection of events:
        $collection = (new EventHelper())->generateCollection($email, $nbOfEvents);

        //export generated events collection:
        $response = (new ExportController($conf))
            ->export($collection);

        //check success created amount of events:
        //temporary disabled salesmanago api problems:
        //@TODO
        //$this->assertEquals($nbOfEvents, $response->getField('createdAmount'));

        $this->assertTrue(($response->getField('createdAmount') > 0));
        $this->assertTrue(($response->getField('failedAmount') < 1));
    }

    /**
     * @runTestsInSeparateProcesses
     * @return void
     * @throws SalesmanagoException
     */
    public function testExportEventsWithTimeOutConnectionSuccess()
    {
        //create configuration of api connection and integration:
        $conf = $this->initConf();

        //set timeouts to get exception:
        $conf->setRequestClientConf(
            (new cUrlClientConfiguration())
                ->setConnectTimeOutMs(2)
                ->setTimeOutMs(2)
        );

        //email for contact creation and events:
        $email = $this->faker->email;

        //number of generates events:
        $nbOfEvents = $this->faker->numberBetween(1, 500);

        //except exception to timeout:
        $this->expectException(SalesmanagoException::class);

        //contact need to created before add events to it:
        (new ContactAndEventTransferController($conf))->transferContact((new Contact())->setEmail($email));

        //except exception to timeout:
        $this->expectException(SalesmanagoException::class);

        //export generated events collection:
        (new ExportController($conf))
            ->export((new EventHelper())->generateCollection($email, $nbOfEvents));
    }

    public function tearDown(): void
    {
        parent::tearDown();

        //clear configuration singleton
        $reflection = new \ReflectionProperty(AbstractConfiguration::class, 'instances');
        $reflection->setAccessible(true);
        $reflection->setValue(null, []);
    }
}