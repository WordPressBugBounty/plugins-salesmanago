<?php

namespace Tests\Feature\User\Contact;

use SALESmanago\Controller\ContactAndEventTransferController;
use SALESmanago\Controller\ExportController;
use SALESmanago\Entity\Configuration;
use SALESmanago\Entity\Contact\Contact;
use SALESmanago\Entity\Contact\Contact as ContactEntity;
use Faker;
use SALESmanago\Entity\UnionConfigurationEntity;
use SALESmanago\Exception\Exception;
use SALESmanago\Model\Collections\ContactsCollection;
use SALESmanago\Services\ExportService;

trait ContactTestTrait
{
    /**
     * Create contact in SALESmango application for test purpose:
     *
     * @return ContactEntity
     * @throws Exception
     */
    protected function createDummyContactInSalesmanago(): ContactEntity
    {
        //parent of trait must have initConf()
        $conf = $this->initConf();

        $transferController = new ContactAndEventTransferController(Configuration::getInstance());
        $Contact = $this->generateContact();

        //required to save Contact to application asap:
        $Contact->setOptions($Contact->getOptions());

        $Response = $transferController->transferContact($Contact);
        $Contact->setContactId($Response->getField('contactId'));

        return $Contact;
    }

    /**
     * Generates contacts collection
     *
     * @param int $totalNrOfItems
     * @param array $specialEmailDomains - array of pre defined email domains
     * @param null $nrOfContactWithSpecialEmailDomains - can't be greater than $nrOfItems
     * @return ContactsCollection
     * @throws Exception
     */
    protected function generateContactsCollection(
        $totalNrOfItems = 100,
        $specialEmailDomains = [],
        $nrOfContactWithSpecialEmailDomains = null
    ) {
        $faker = Faker\Factory::create();
        $contactsCollection = new ContactsCollection();

        $totalNrOfItems = ($nrOfContactWithSpecialEmailDomains != null)
            ? $totalNrOfItems - $nrOfContactWithSpecialEmailDomains
            : $totalNrOfItems;

        for ($i = 0; $i < $totalNrOfItems; $i++) {
            $Contact = $this->generateContact();
            $contactsCollection->addItem($Contact);
        }

        if (empty($specialEmailDomains)) {
            return $contactsCollection;
        }

        for ($i = 0; $i < $nrOfContactWithSpecialEmailDomains; $i++) {
            $contactEmail = $faker->word . '@' . $specialEmailDomains[array_rand($specialEmailDomains)];
            $Contact = $this->generateContact(
                null,
                $contactEmail
            );
            $contactsCollection->addItem($Contact);
        }

        return $contactsCollection;
    }

    /**
     * Generate ContactEntity
     *
     * @param string|null $name
     * @param string|null $email
     * @param string|null $faxNumber
     * @param string|null $phoneNumber
     * @param string|null $company
     * @param string|null $externalId
     * @param string|null $state
     * @param array|array $tags
     * @param null|bool $isSubscribed
     * @param null|int $createdOn
     * @return Contact
     */
    protected function generateContact(
        $name         = null,
        $email        = null,
        $faxNumber    = null,
        $phoneNumber  = null,
        $company      = null,
        $externalId   = null,
        $state        = null,
        $tags         = [],
        $isSubscribed = null,
        $createdOn    = null
    ): ContactEntity {
        $faker = Faker\Factory::create();
        $Contact = new Contact();

        $Contact
            ->setName($name ?? $faker->name)
            ->setEmail($email ?? $faker->email)
            ->setFax($faxNumber ?? $faker->phoneNumber)
            ->setPhone($phoneNumber ?? $faker->phoneNumber)
            ->setCompany($company ?? $faker->company)
            ->setExternalId($externalId ?? $faker->uuid)
            ->setState($state ?? $faker->randomElement(['CUSTOMER', 'PROSPECT', 'PARTNER', 'OTHER', 'UNKNOWN']))
            ->setOptions(
                $Contact->getOptions()
                    ->setTags(empty($tags) ? $faker->words($nb = 3, $asText = false) : $tags)
                    ->setIsSubscribed($isSubscribed ?? $faker->boolean)
                    ->setCreatedOn($createdOn ?? $faker->unixTime($max = 'now'))
            )->setAddress(
                ($Contact->getAddress())//return new Address Entity
                ->setCity($faker->city)
                    ->setCountry($faker->country)
                    ->setProvince($faker->word)
                    ->setZipCode($faker->postcode)
                    ->setStreetAddress($faker->streetAddress)
            );

        return $Contact;
    }
}