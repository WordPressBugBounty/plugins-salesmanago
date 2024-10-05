<?php

namespace Tests\Contact\Entity;

use DateTime;
use Generator;
use PHPUnit\Framework\TestCase;
use SALESmanago\Exception\Exception;
use SALESmanago\Entity\Contact\Options;
use Faker;
use SALESmanago\Helper\EntityDataHelper;

final class OptionsTest extends TestCase
{
    /**
     * @dataProvider provideTestSetTagsSuccess
     *
     * @param string $input
     * @param string $expectedOutput
     */
    public function testSetTagsSuccess($input, $expectedOutput)
    {
        $Options = new Options();
        $Options->setTags($input);

        $this->assertEquals($Options->getTags(), $expectedOutput);
    }

    /**
     * @dataProvider provideTestSetTagsSuccess
     *
     * @param string $input
     * @param string $expectedOutput
     */
    public function testSetRemoveTagsSuccess($input, $expectedOutput)
    {
        $Options = new Options();
        $Options->setTags($input);

        $this->assertEquals($Options->getTags(), $expectedOutput);
    }

    /**
     * @dataProvider provideTestSetItemsAsArraySuccess
     *
     * @param array $setItems
     * @param string $getMethod
     * @param string $expectedOutput
     * @throws Exception
     */
    public function testSetItemsAsArraySuccess($getMethod, $setItems, $expectedOutput)
    {
        $Options = new Options();
        $Options->set($setItems);
        $this->assertEquals($Options->$getMethod(), $expectedOutput);
    }

    /**
     * DataProvider for testSetTagsSuccess()
     * @return Generator
     */
    public function provideTestSetTagsSuccess()
    {
        $tagArr = ['TEST_TAG1', 'TAG2'];
        $expectedArray = $tagArr;
        array_walk($expectedArray, function($item) {strtoupper(str_replace(' ', '_', $item));});
        yield [$tagArr, EntityDataHelper::filterDataArray($expectedArray)];

        yield ['TEST_TAG4', ['TEST_TAG4']];

        $tagArr = ['TEST_TAG5', ' '];
        $expectedArray = $tagArr;
        array_walk($expectedArray, function($item) {strtoupper(str_replace(' ', '_', $item));});
        yield [$tagArr, EntityDataHelper::filterDataArray($expectedArray)];

        $tagArr = [' ', 'TEST_TAG5'];
        $expectedArray = $tagArr;
        array_walk($expectedArray, function($item) {strtoupper(str_replace(' ', '_', $item));});
        yield [$tagArr, EntityDataHelper::filterDataArray($expectedArray)];
    }

    /**
     * DataProvider for testSetBirthdaySuccess
     * @return array[]
     * @throws \Exception
     */
    public function provideTestSetBirthdaySuccess()
    {
        $faker = Faker\Factory::create();

        $unixTime = $faker->unixTime($max = 'now');
        $dateTime = $faker->dateTime($max = 'now', $timezone = null);
        $dateTimeAD = $faker->dateTimeAD($max = 'now', $timezone = null);
        $iso = $faker->iso8601($max = 'now');
        $date = $faker->date($format = 'Y-m-d', $max = 'now');
        $string1 = $faker->dayOfMonth($max = 'now').'-'.$faker->month($max = 'now').'-'.$faker->year($max = 'now');
        $string2 = $faker->dayOfMonth($max = 'now').'-'.$faker->monthName($max = 'now').'-'.$faker->year($max = 'now');
        $string3 = $faker->dateTime($max = 'now', $timezone = null)->format('Ymd');

        $isoExpected = new DateTime($iso);
        $isoExpected = $isoExpected->format('Ymd');

        $dateExpected = new DateTime($date);
        $dateExpected = $dateExpected->format('Ymd');

        $string1Expected = new DateTime($string1);
        $string1Expected = $string1Expected->format('Ymd');

        $string2Expected = new DateTime($string2);
        $string2Expected = $string2Expected->format('Ymd');

        return [
            [$unixTime, gmdate("Ymd", $unixTime)],
            [$dateTime, $dateTime->format('Ymd')],
            [$dateTimeAD, $dateTimeAD->format('Ymd')],
            [$iso, $isoExpected],
            [$date, $dateExpected],
            [$string1, $string1Expected],
            [$string2, $string2Expected],
            [$string3, $string3]
        ];
    }

    /**
     * DataProvider - for testSetBirthdayFail
     * @throws \Exception
     */
    public function provideTestSetBirthdayFail()
    {
        yield [false];
        yield ['Don'];
        yield ['test'];
        yield [8219038012983908230182390810923109237129037];
    }

    /**
     * DataProvider - for testSetItemsAsArraySuccess()
     */
    public function provideTestSetItemsAsArraySuccess()
    {
        $dummyData = [];

        $faker = Faker\Factory::create();

        $options = [
            Options::TAGS        => $faker->words($nb = 3, $asText = false),
            Options::R_TAGS      => $faker->words($nb = 3, $asText = false),
            Options::F_OPT_IN    => $faker->boolean,
            Options::F_OPT_OUT   => $faker->boolean,
            Options::F_P_OPT_IN  => $faker->boolean,
            Options::F_P_OPT_OUT => $faker->boolean,
            Options::N_EMAIL     => $faker->email,
            Options::CREATED_ON  => $faker->unixTime($max = 'now'),
        ];

        if(is_array($options[Options::TAGS])) {
            array_walk($options[Options::TAGS], function($item) { strtoupper(str_replace(' ', '_', $item));});
        }

        if(is_array($options[Options::R_TAGS])) {
            array_walk($options[Options::R_TAGS], function($item) { strtoupper(str_replace(' ', '_', $item));});
        }

        $optionsExpected = [
            Options::TAGS        => is_array($options[Options::TAGS])
                ? EntityDataHelper::filterDataArray($options[Options::TAGS])
                : [trim(str_replace(' ', '_', $options[Options::TAGS]))],
            Options::R_TAGS      => is_array($options[Options::R_TAGS])
                ? EntityDataHelper::filterDataArray($options[Options::R_TAGS])
                : [trim(str_replace(' ', '_', $options[Options::R_TAGS]))],
            Options::F_OPT_IN    => $options[Options::F_OPT_IN],
            Options::F_OPT_OUT   => $options[Options::F_OPT_OUT],
            Options::F_P_OPT_IN  => $options[Options::F_P_OPT_IN],
            Options::F_P_OPT_OUT => $options[Options::F_P_OPT_OUT],
            Options::N_EMAIL     => $options[Options::N_EMAIL],
            Options::CREATED_ON  => $options[Options::CREATED_ON],
        ];

        foreach ($options as $key => $val) {
            array_push($dummyData, ['get'.ucfirst($key), array($key => $val), $optionsExpected[$key]]);
        }
        return $dummyData;
    }

    public function testSetForceOptInWithSetForceOptOut()
    {
        $Options = new Options();
        $faker = Faker\Factory::create();

        $status = $faker->boolean;
        $Options->setForceOptIn($status);

        $this->assertEquals($Options->getForceOptIn(), $status);
        $this->assertEquals($Options->getForceOptOut(), !$status);
    }

    public function testForcePhoneOptInWithForcePhoneOptOut()
    {
        $Options = new Options();
        $faker = Faker\Factory::create();

        $status = $faker->boolean;
        $Options->setForcePhoneOptIn($status);

        $this->assertEquals($Options->getForcePhoneOptIn(), $status);
        $this->assertEquals($Options->getForcePhoneOptOut(), !$status);
    }

    public function testSetNewEmail()
    {
        $Options = new Options();
        $faker = Faker\Factory::create();

        $email = $faker->email;
        $Options->setNewEmail($email);

        $this->assertEquals($Options->getNewEmail(), $email);
    }

    /**
     * @dataProvider provideTestSetCreatedOn
     * @param $inputDate
     * @param $expectedTime
     */
    public function testSetCreatedOn($inputDate, $expectedTime) {
        $Options = new Options();
        $faker = Faker\Factory::create();

        $email = $faker->email;
        $Options->setCreatedOn($email);

        $this->assertEquals($Options->getCreatedOn(), $email);
    }

    /**
     * DataProvider for testSetCreatedOn()
     */
    public function provideTestSetCreatedOn()
    {
        $faker = Faker\Factory::create();

        $unixTime = $faker->unixTime($max = 'now');
        $dateTime = $faker->dateTime($max = 'now', $timezone = null);
        $dateTimeAD = $faker->dateTimeAD($max = 'now', $timezone = null);
        $iso = $faker->iso8601($max = 'now');
        $date = $faker->date($format = 'Y-m-d', $max = 'now');
        $string1 = $faker->dayOfMonth($max = 'now').'-'.$faker->month($max = 'now').'-'.$faker->year($max = 'now');
        $string2 = $faker->dayOfMonth($max = 'now').'-'.$faker->monthName($max = 'now').'-'.$faker->year($max = 'now');
        $string3 = $faker->dateTime($max = 'now', $timezone = null)->format('Ymd');

        $isoExpected = new DateTime($iso);
        $isoExpected = $isoExpected->format('Ymd');

        $dateExpected = new DateTime($date);
        $dateExpected = $dateExpected->format('Ymd');

        $string1Expected = new DateTime($string1);
        $string1Expected = $string1Expected->format('Ymd');

        $string2Expected = new DateTime($string2);
        $string2Expected = $string2Expected->format('Ymd');

        return [
            [$unixTime, gmdate("Ymd", $unixTime)],
            [$dateTime, $dateTime->format('Ymd')],
            [$dateTimeAD, $dateTimeAD->format('Ymd')],
            [$iso, $isoExpected],
            [$date, $dateExpected],
            [$string1, $string1Expected],
            [$string2, $string2Expected],
            [$string3, $string3]
        ];
    }
}

