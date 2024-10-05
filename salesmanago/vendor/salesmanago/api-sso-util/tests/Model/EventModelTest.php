<?php


namespace Tests\Model;


use PHPUnit\Framework\TestCase;

use Faker;
use SALESmanago\Entity\Event\Event;

class EventModelTest extends TestCase
{
    public static $eventTypes = [
        'PURCHASE',
        'CART',
        'VISIT',
        'PHONE_CALL',
        'OTHER',
        'RESERVATION',
        'CANCELLED',
        'ACTIVATION',
        'MEETING',
        'OFFER',
        'DOWNLOAD',
        'LOGIN',
        'TRANSACTION',
        'CANCELLATION',
        'RETURN',
        'SURVEY',
        'APP_STATUS',
        'APP_TYPE_WEB',
        'APP_TYPE_MANUAL',
        'APP_TYPE_RETENTION',
        'APP_TYPE_UPSALE',
        'LOAN_STATUS',
        'LOAN_ORDER',
        'FIRST_LOAN',
        'REPEATED_LOAN'
    ];

    public static function prepareDummyDataForEventEntity()
    {
        $faker = $faker = Faker\Factory::create();

        $loops = $faker->numberBetween(1, 12);

        $productIds = [];
        $productNames = [];
        $details = [];

        for ($i=0; $i < $loops; $i++) {
            array_push($productIds, $faker->isbn13);
            array_push($productNames, $faker->sentence($faker->numberBetween(1, 10)));
            array_push($details, $faker->sentence($faker->numberBetween(1, 4)));
        }

        return [
            'email'               => $faker->email,
            'date'                => $faker->unixTime,
            'description'         => $faker->sentence(10),
            'products'            => $productIds,
            'location'            => $faker->ean13,
            'value'               => $faker->randomFloat(2),
            'contactExtEventType' => $faker->randomElement(self::$eventTypes),
            'contactId'           => $faker->uuid,
            'details'             => $details,
            'externalId'          => $faker->uuid,
            'shopDomain'          => $faker->url
        ];
    }
}