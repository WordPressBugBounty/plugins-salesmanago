<?php

namespace Tests\Model\Collections;

use Faker;
use Generator;
use PHPUnit\Framework\TestCase;
use SALESmanago\Entity\Consent;
use SALESmanago\Exception\Exception;
use SALESmanago\Model\Collections\ConsentsCollection;
use stdClass;

class ConsentsCollectionTest extends TestCase
{
    /**
     * @dataProvider provideTestAddItemSuccess
     * @param $input
     * @param $expectedOutput
     *
     * @return void
     * @throws Exception
     */
    public function testAddItemSuccess($input, $expectedOutput)
    {
        $ConsentCollection = new ConsentsCollection();
        $ConsentCollection->addItem($input);
        $this->assertEquals($ConsentCollection->get()[0], $expectedOutput);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testAddItemFail()
    {
        $this->expectException('SALESmanago\Exception\Exception');
        $this->expectExceptionMessage('Not right entity type');

        $ConsentCollection = new ConsentsCollection();
        $ConsentCollection->addItem(new stdClass());
    }

    /**
     * @dataProvider provideTestToArray
     * @param $ConsentCollection
     * @param $expectedOutput
     *
     * @return void
     */
    public function testToArray($ConsentCollection, $expectedOutput)
    {
        $this->assertEquals($ConsentCollection->toArray(), $expectedOutput);
    }

    /**
     * DataProvider for testAddItemSuccess()
     * @return Generator
     */
    public function provideTestAddItemSuccess()
    {
        $faker = Faker\Factory::create();
        $Consent = new Consent([
            "consentName"          => $faker->sentence(1),
            "consentAccept"        => $faker->boolean,
            "agreementDate"        => $faker->unixTime,
            "ip"                   => $faker->ipv4,
            "optOut"               => $faker->boolean,
            "consentDescriptionId" => $faker->randomNumber(),
        ]);
        yield [$Consent, $Consent];
    }

    /**
     * DataProvider for testToArray()
     * @return Generator
     * @throws Exception
     */
    public function provideTestToArray()
    {
        $faker = Faker\Factory::create();
        $ConsentsCollection = new ConsentsCollection();
        $expectedArray = [];
        for ($i=0;$i<$faker->numberBetween(1,10);$i++) {
            $array = [
                "consentName"          => $faker->sentence(1),
                "consentAccept"        => $faker->boolean,
                "agreementDate"        => $faker->unixTime,
                "ip"                   => $faker->ipv4,
                "optOut"               => $faker->boolean,
                "consentDescriptionId" => $faker->randomNumber(),
            ];
            $ConsentsCollection->addItem(new Consent($array));
            $expectedArray[] = $array;
        }
        yield [$ConsentsCollection, $expectedArray];
    }
}
