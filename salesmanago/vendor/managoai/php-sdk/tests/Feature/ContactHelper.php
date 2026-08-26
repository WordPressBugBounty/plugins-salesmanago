<?php

namespace Tests\Feature;

use SALESmanago\Entity\Contact\Contact;
use Faker;
use Tests\Feature\User\Contact\ContactTestTrait;

/**
 * @deprecated since 3.1.10
 * @see ContactTestTrait
 */
class ContactHelper
{
    /**
     * @var Faker\Factory
     */
    protected $faker;

    public function __construct()
    {
        $this->faker = Faker\Factory::create();
    }

    /**
     * Generate Contact Entity with sample data
     *
     * @deprecated since 3.1.10
     * @see ContactTestTrait::generateContact()
     * @return Contact
     */
    public function generateContact(): Contact
    {
        $Contact = new Contact();
        $Contact
            ->setEmail($this->faker->email)
            ->setCompany($this->faker->company)
            ->setFax($this->faker->phoneNumber)
            ->setName($this->faker->name)
            ->setExternalId($this->faker->uuid)
            ->setPhone($this->faker->phoneNumber)
            ->setAddress(
                ($Contact->getAddress())//return new Address Entity
                    ->setCity($this->faker->city)
                    ->setCountry($this->faker->country)
                    ->setProvince($this->faker->word)
                    ->setZipCode($this->faker->postcode)
                    ->setStreetAddress($this->faker->streetAddress)
            )->setOptions(
                ($Contact->getOptions())
                    ->setIsSubscribes(true)//simulating subscribing process
                    ->setTags('NEWSLETTER_' . $this->faker->word)
                    ->setCreatedOn(time())
            );

        return $Contact;
    }
}