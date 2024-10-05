<?php


namespace Tests\Contact\Entity;

use Generator;
use PHPUnit\Framework\TestCase;

use SALESmanago\Exception\Exception;
use SALESmanago\Entity\Contact\Contact as Contact;
use Faker;

final class ContactTest extends TestCase
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
        $Contact = new Contact();
        $Contact->set($setItems);
        $this->assertEquals($Contact->$getMethod(), $answer);
    }

    /**
     * @dataProvider provideTestSetItemsAsArrayThrowExceptionSuccess
     * @param $setItems
     */
    public function testSetItemsAsArrayThrowExceptionSuccess($setItems){
        $Contact = new Contact();

        $this->expectException(Exception::class);
        $Contact->set($setItems);
    }

    /**
     * @dataProvider provideTestSetName
     * @param $expectedInput - expected input name;
     * @param $expectedOutPut - expected output name;
     */
    public function testSetNameSuccess($expectedInput, $expectedOutPut){

        $Contact = new Contact();

        $Contact->setName($expectedInput);
        $this->assertEquals($Contact->getName(), $expectedOutPut);
    }

    /**
     * DataProvider
     *
     * @return string[][]
     */
    public function provideTestSetItemsAsArray()
    {
        $dummyData = [];

        $faker = Faker\Factory::create();

        $contact = [
            'name'       => $faker->name,
            'email'      => $faker->email,
            'contactId'  => $faker->uuid,
            'fax'        => $faker->phoneNumber,
            'phone'      => $faker->phoneNumber,
            'company'    => $faker->company,
            'externalId' => $faker->uuid,
            'state'      => $faker->randomElement(['CUSTOMER', 'PROSPECT', 'PARTNER', 'OTHER', 'UNKNOWN'])
        ];

        foreach ($contact as $key => $val) {
            array_push($dummyData, ['get'.ucfirst($key), array($key => $val), $val]);
        }

        return $dummyData;
    }

    /**
     * Provider for testSetName()
     */
    public function provideTestSetName(){
        $faker = Faker\Factory::create();

        $name = [$faker->firstName, $faker->lastName];
        yield [$name, implode(' ', $name)];

        $name = $faker->name;
        yield [$name, $name];

        $name = [$faker->firstName, ' '];
        yield [$name, trim(implode('', $name))];

        $name = [' ', $faker->lastName];
        yield [$name, trim(implode('', $name))];
    }

    /**
     * DataProvider for testSetItemsAsArrayThrowExceptionSuccess()
     * @return Generator
     */
    public function provideTestSetItemsAsArrayThrowExceptionSuccess(){
        yield['string'];
        yield[array()];
    }
}