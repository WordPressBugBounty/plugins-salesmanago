<?php


namespace Tests\Exception;

use PHPUnit\Framework\TestCase;
use Faker;

use SALESmanago\Exception\Exception;

class ExceptionTest extends TestCase
{
    /**
     * test for get exception for log files
     */
    public function testGetRightLogMessageSuccess()
    {
        $faker = Faker\Factory::create();
        $thrownMessage = $faker->text(100);

        try {
            throw new Exception($thrownMessage);
        } catch (Exception $e) {
            $expectedMessage = Exception::EXCEPTION_HEADER_NAME;
            $expectedMessage.= Exception::MESSAGE;
            $expectedMessage.= $thrownMessage . PHP_EOL;
            $expectedMessage.= Exception::FILE . $e->getFile() . PHP_EOL;
            $expectedMessage.= Exception::LINE . $e->getLine() . PHP_EOL;
            $expectedMessage.= Exception::TRACE;
            $expectedMessage.= $e->getTraceAsString() . PHP_EOL;
            print_r($expectedMessage);
            die;
            $this->assertEquals($expectedMessage, $e->getLogMessage());
        }
    }

    /**
     * test for get exception for view
     */
    public function testGetRightViewMessageSuccess()
    {
        $faker = Faker\Factory::create();
        $thrownMessage = $faker->text(100);

        try {
            throw new Exception($thrownMessage);
        } catch (Exception $e) {
            $expectedMessage = $e->getMessage() . ': ';
            $expectedMessage.= $e->getFile() . ': ';
            $expectedMessage.= $e->getLine() . PHP_EOL;

            $this->assertEquals($expectedMessage, $e->getViewMessage());
        }


    }
}