<?php

namespace Tests\Services;


use PHPUnit\Framework\TestCase;
use SALESmanago\Controller\LoginController;
use SALESmanago\Entity\Configuration;
use SALESmanago\Entity\Contact\Contact;
use SALESmanago\Entity\Contact\Address;
use SALESmanago\Entity\Contact\Options;
use SALESmanago\Entity\Event\Event;
use SALESmanago\Entity\User;
use SALESmanago\Exception\Exception;
use SALESmanago\Model\ContactModel;
use SALESmanago\Services\ContactAndEventTransferService;
use SALESmanago\Services\GuzzleClientAdapter;

use Tests\Model\ContactModelTest;
use Tests\Model\EventModelTest;

class ContactAndEventTransferServiceTest extends TestCase
{
    public function testTransferContact()
    {
        $Contact = new Contact();
        $user = new User();
        $user
            ->setEmail('semowet930@boldhut.com')
            ->setPass('#Salesmanago123');

        $loginController = new LoginController(Configuration::getInstance());
        $loginController->login($user);//this one for property configuration create


        $dummyData = ContactModelTest::prepareDummyDataForContactEntity();
        $contactFromPlatform = $dummyData['contactFromPlatform'];
        $addressFromPlatform = $dummyData['addressFromPlatform'];
        $optionsFromPlatform = $dummyData['optionsFromPlatform'];

        $Contact->set($contactFromPlatform);

        $Address = $Contact->getAddress();
        $Contact->setAddress(
            $Address->set($addressFromPlatform)
        );

        $Options = $Contact->getOptions();
        $Contact->setOptions(
            $Options->set($optionsFromPlatform)
        );

        $service = new ContactAndEventTransferService(Configuration::getInstance());

        $Response = $service->transferContact($Contact);

        $this->assertInstanceOf('SALESmanago\Entity\Response', $Response);
        $this->assertNotNull($Response->getField(Contact::C_ID));
    }

    public function testTransferEvent()
    {
        $Event = new Event();
        $user = new User();
        $user
            ->setEmail('semowet930@boldhut.com')
            ->setPass('#Salesmanago123');

        $loginController = new LoginController(Configuration::getInstance());
        $loginController->login($user);//this one for property configuration create

        $dummyData = EventModelTest::prepareDummyDataForEventEntity();

        $Event->set($dummyData);

        $service = new ContactAndEventTransferService(Configuration::getInstance());

        $Response = $service->transferEvent($Event);

        $this->assertInstanceOf('SALESmanago\Entity\Response', $Response);
        $this->assertNotNull($Response->getField(Event::EVENT_ID));
    }

    public function testTransferBoth()
    {
        $Contact  = new Contact();
        $Event    = new Event(EventModelTest::prepareDummyDataForEventEntity());
        $user     = new User();

        $user
            ->setEmail('semowet930@boldhut.com')
            ->setPass('#Salesmanago123');

        $loginController = new LoginController(Configuration::getInstance());
        $loginController->login($user);//this one for property configuration create

        $dummyData           = ContactModelTest::prepareDummyDataForContactEntity();
        $contactFromPlatform = $dummyData['contactFromPlatform'];
        $addressFromPlatform = $dummyData['addressFromPlatform'];
        $optionsFromPlatform = $dummyData['optionsFromPlatform'];

        $Contact->set($contactFromPlatform);

        $Address = $Contact->getAddress();
        $Contact->setAddress(
            $Address->set($addressFromPlatform)
        );

        $Options = $Contact->getOptions();
        $Contact->setOptions(
            $Options->set($optionsFromPlatform)
        );

        $service = new ContactAndEventTransferService(Configuration::getInstance());

        $Response = $service->transferBoth($Contact, $Event);

        $this->assertInstanceOf('SALESmanago\Entity\Response', $Response);
        $this->assertNotNull($Response->getField(Contact::C_ID));
        $this->assertNotNull($Response->getField(Event::EVENT_ID));
    }
}