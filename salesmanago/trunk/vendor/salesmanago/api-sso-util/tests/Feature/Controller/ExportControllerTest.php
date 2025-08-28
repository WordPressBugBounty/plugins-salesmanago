<?php


namespace Tests\Feature\Controller;

use Faker;
use Tests\Feature\TestCaseFeature;
use ReflectionException;

use SALESmanago\Controller\ExportController;
use SALESmanago\Entity\Event\Event;
use SALESmanago\Model\Collections\ContactsCollection;
use SALESmanago\Model\Collections\EventsCollection;
use SALESmanago\Entity\Contact\Contact;
use SALESmanago\Exception\Exception;
use SALESmanago\Entity\cUrlClientConfiguration;
use Tests\Feature\User\Contact\ContactTestTrait;


class ExportControllerTest extends TestCaseFeature
{
    use ContactTestTrait;

    /**
     * @throws Exception
     */
    public function testExportEventsSuccess()
    {
        $conf = $this->initConf();
        $conf->setRequestClientConf(
            new cUrlClientConfiguration(
                [
                    cUrlClientConfiguration::HOST => $conf->getEndpoint()
                ]
            )
        );

        $faker = Faker\Factory::create();
        $eventCollection = new EventsCollection();

        for ($i=0; $i<=100; $i++) {
            $event = new Event();
            $eventCollection->addItem(
                $event
                    ->setEmail($faker->email)
                    ->setContactExtEventType(Event::EVENT_TYPE_PURCHASE)
                    ->setProducts($faker->uuid)
                    ->setDescription($faker->text)
                    ->setDate(time())
                    ->setExternalId($faker->uuid)
                    ->setLocation($faker->sha1)
                    ->setValue($faker->randomNumber())
            );
        }

        $exportController = new ExportController($conf);
        $Response = $exportController->export($eventCollection);
        $this->assertEquals(true, $Response->getStatus());
    }

    /**
     * @throws Exception
     */
    public function testExportContactsSuccess()
    {
            $contactsCollection = $this->generateContactsCollection();
            $exportController   = new ExportController($this->initConf());
            $Response           = $exportController->export($contactsCollection);

            $this->assertEquals(true, $Response->getStatus());
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function testCheckAndFilterContactsCollectionSuccess()
    {
        $faker = Faker\Factory::create();
        $conf = $this->initConf();

        $numberOfGeneratedEmailDomains = $faker->numberBetween(1, 50);
        $generatedEmailDomains = $this->generateEmailDomains($numberOfGeneratedEmailDomains);
        $conf->setIgnoredDomains($generatedEmailDomains);

        $numberOfAllContact = $faker->numberBetween(1, 200);
        $numberOfContactsWithIgnoredDomains = $faker->numberBetween(1, $numberOfAllContact);

        $contactsCollection = $this->generateContactsCollection(
            $numberOfAllContact,
            $generatedEmailDomains,
            $numberOfContactsWithIgnoredDomains
        );

        $exportController = new ExportController($conf);

        $method = $this->getMethod('checkAndFilterContactsCollection', $exportController);
        $filteredCollection = $method->invokeArgs($exportController, [$contactsCollection]);

        $expectingCollectionSize = $numberOfAllContact - $numberOfContactsWithIgnoredDomains;

        $this->assertEquals($filteredCollection->count(), $expectingCollectionSize);
    }

    /**
     * Generates email domains
     *
     * @param int $numberOfUniqueDomainNames
     * @return array
     */
    protected function generateEmailDomains($numberOfUniqueDomainNames = 1)
    {
        $faker = Faker\Factory::create();
        $domains = [];

        while ($numberOfUniqueDomainNames) {
            array_push($domains, $faker->word . '.' . $faker->word);
            $numberOfUniqueDomainNames--;
        }

        return $domains;
    }
}
