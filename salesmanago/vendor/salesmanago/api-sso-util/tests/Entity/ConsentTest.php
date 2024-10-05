<?php

namespace Tests\Entity;

use Generator;
use PHPUnit\Framework\TestCase;
use Faker;
use SALESmanago\Entity\Consent;

class ConsentTest extends TestCase
{
    /**
     * @dataProvider provideTestSetConsentName
     * @param $input
     * @param $expectedOutput
     *
     * @return void
     */
    public function testSetConsentName($input, $expectedOutput)
    {
        $Consent = new Consent();
        $Consent->setConsentName($input);

        $this->assertEquals($Consent->getConsentName(), $expectedOutput);
    }

    /**
     * @dataProvider provideTestSetConsentAccept
     *
     * @param $input
     * @param $expectedOutput
     *
     * @return void
     */
    public function testSetConsentAccept($input, $expectedOutput)
    {
        $Consent = new Consent();
        $Consent->setConsentAccept($input);

        $this->assertEquals($Consent->getConsentAccept(), $expectedOutput);
    }

    /**
     * @dataProvider provideTestSetAgreementDate
     * @param $input
     * @param $expectedOutput
     *
     * @return void
     */
    public function testSetAgreementDate($input, $expectedOutput)
    {
        $Consent = new Consent();
        $Consent->setAgreementDate($input);

        $this->assertEquals($Consent->getAgreementDate(), $expectedOutput);
    }

    /**
     * @dataProvider provideTestSetIp
     * @param $input
     * @param $expectedOutput
     *
     * @return void
     */
    public function testSetIp($input, $expectedOutput)
    {
        $Consent = new Consent();
        $Consent->setIp($input);

        $this->assertEquals($Consent->getIp(), $expectedOutput);
    }

    /**
     * @dataProvider provideTestSetOptOut
     * @param $input
     * @param $expectedOutput
     *
     * @return void
     */
    public function testSetOptOut($input, $expectedOutput)
    {
        $Consent = new Consent();
        $Consent->setOptOut($input);

        $this->assertEquals($Consent->isOptOut(), $expectedOutput);
    }

    /**
     * @dataProvider provideTestSetConsentDescriptionId
     * @param $input
     * @param $expectedOutput
     *
     * @return void
     */
    public function testSetConsentDescriptionId($input, $expectedOutput)
    {
        $Consent = new Consent();
        $Consent->setConsentDescriptionId($input);

        $this->assertEquals($Consent->getConsentDescriptionId(), $expectedOutput);
    }

    /**
     * DataProvider for testSetConsentName()
     * @return Generator
     */
    public function provideTestSetConsentName()
    {
        $faker = Faker\Factory::create();
        $name = $faker->sentence(1);
        yield [$name, $name];

        $name = $faker->sentence(2);
        yield [$name, $name];

        $name = $faker->sentence(3);
        yield [$name, $name];
    }

    /**
     * DataProvider for testSetConsentAccept()
     * @return Generator
     */
    public function provideTestSetConsentAccept()
    {
        $faker = Faker\Factory::create();
        $status = $faker->boolean;
        yield [$status, $status];
    }

    /**
     * DataProvider for testSetAgreementDate()
     * @return Generator
     */
    public function provideTestSetAgreementDate()
    {
        $faker = Faker\Factory::create();
        $time = $faker->unixTime;
        yield [$time, $time];
    }

    /**
     * DataProvider for testSetIp()
     * @return Generator
     */
    public function provideTestSetIp()
    {
        $faker = Faker\Factory::create();
        $ip = $faker->ipv4;
        yield [$ip, $ip];

        $ip = $faker->localIpv4;
        yield [$ip, $ip];
    }

    /**
     * DataProvider for testSetOptOut()
     * @return Generator
     */
    public function provideTestSetOptOut()
    {
        $faker = Faker\Factory::create();
        $status = $faker->boolean;
        yield [$status, $status];
    }

    /**
     * DataProvider for testSetConsentDescriptionId()
     * @return Generator
     */
    public function provideTestSetConsentDescriptionId()
    {
        $faker = Faker\Factory::create();
        $id = $faker->randomNumber();
        yield [$id, $id];
    }
}
