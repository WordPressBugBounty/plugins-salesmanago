<?php


namespace Tests\Contact\Entity;

use Generator;
use PHPUnit\Framework\TestCase;
use SALESmanago\Entity\Contact\Address;
use Faker;

class AddressTest extends TestCase
{
    /**
     * @dataProvider provideTestSetItemsAsArray
     *
     * @param $getMethod  - get method name;
     * @param $setItems - item data set;
     * @param $answer  - method right return;
     */
    public function testSetItemsAsArraySuccess($getMethod, $setItems, $answer)
    {
        $Address = new Address();
        $Address->set($setItems);
        $this->assertEquals($Address->$getMethod(), $answer);
    }

    /**
     * @dataProvider provideTestSetStreetAddressSuccess
     * @param string $input;
     * @param string $expectedOutput;
     */
    public function testSetStreetAddressSuccess($input, $expectedOutput)
    {
        $Address = new Address();
        $Address->setStreetAddress($input);
        $this->assertEquals($Address->getStreetAddress(), $expectedOutput);
    }

    /**
     * @dataProvider provideTestSetZipCode
     * @param $input
     * @param $expectedOutput
     */
    public function testSetZipCodeSuccess($input, $expectedOutput) {
        $Address = new Address();
        $Address->setZipCode($input);
        $this->assertEquals($Address->getZipCode(), $expectedOutput);
    }

    /**
     * @dataProvider provideTestSetCitySuccess
     * @param $input
     * @param $expectedOutput
     */
    public function testSetCitySuccess($input, $expectedOutput) {
        $Address = new Address();
        $Address->setCity($input);
        $this->assertEquals($Address->getCity(), $expectedOutput);
    }

    /**
     * @dataProvider provideTestSetProvinceSuccess
     * @param $input
     * @param $expectedOutput
     */
    public function testSetProvinceSuccess($input, $expectedOutput)
    {
        $Address = new Address();
        $Address->setCity($input);
        $this->assertEquals($Address->getCity(), $expectedOutput);
    }

    /**
     * DataProvider for testSetStreetAddressSuccess()
     */
    public function provideTestSetStreetAddressSuccess()
    {
        $faker = Faker\Factory::create();

        $streetAddress = [$faker->streetName, $faker->secondaryAddress];
        yield [$streetAddress, implode(' ', $streetAddress)];

        $streetAddress = $faker->streetAddress;
        yield [$streetAddress, $streetAddress];

        $streetAddress = [$faker->streetName, ' '];
        yield [$streetAddress, trim(implode(' ', $streetAddress))];

        $streetAddress = [' ', $faker->secondaryAddress];
        yield [$streetAddress, trim(implode(' ', $streetAddress))];
    }

    /**
     * DataProvider for testSetZipCodeSuccess()
     */
    public function provideTestSetZipCode() {
        $faker = Faker\Factory::create();

        $code = $faker->postcode;
        yield [$code, $code];

        yield['32-234', '32-234'];
    }

    /**
     * DataProvider for testSetCitySuccess
     * @return Generator
     */
    public function provideTestSetCitySuccess() {
        $faker = Faker\Factory::create();

        $city = $faker->city;
        yield [$city, $city];

        $city = $faker->citySuffix.'-'.$faker->city;
        yield [$city, $city];
    }

    /**
     * DataProvider - for testSetItemsAsArraySuccess()
     *
     * @return string[][]
     */
    public function provideTestSetItemsAsArray()
    {
        $dummyData = [];

        $faker = Faker\Factory::create();

        $address = [
            'streetAddress' => $faker->streetAddress,
            'zipCode' => $faker->postcode,
            'city' => $faker->city,
            'country' => $faker->country,
            'province' => $faker->state
        ];

        foreach ($address as $key => $val) {
            array_push($dummyData, ['get'.ucfirst($key), array($key => $val), $val]);
        }

        return $dummyData;
    }

    /**
     * DataProvider - for testSetProvinceSuccess
     */
    public function provideTestSetProvinceSuccess()
    {
        $faker = Faker\Factory::create();

        $province = $faker->state;
        yield [$province, $province];

        $province = $faker->state.'-'.$faker->state;
        yield[$province, $province];
    }
}