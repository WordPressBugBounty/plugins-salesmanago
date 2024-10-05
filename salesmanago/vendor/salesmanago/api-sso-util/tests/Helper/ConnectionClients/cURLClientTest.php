<?php


namespace Tests\Helper\ConnectionClients;


use PHPUnit\Framework\TestCase;
use SALESmanago\Exception\Exception;
use SALESmanago\Helper\ConnectionClients\cURLClient;
use Faker;
use SALESmanago\Entity\cUrlClientConfiguration;

class cURLClientTest extends TestCase
{
    /**
     * Send request body ($data) to external endpoint (*.pipedream.net)
     * -> receive the same body form external host (endpoint)
     * -> Compare body which was sent with body which was returned - to confirm that curl method works fine;
     */
    public function testRequestSuccess()
    {
        $cURLClient = new cURLClient();
        $faker = Faker\Factory::create();

        $cURLClient
            ->setTimeOut(1000)
            //url could be replaced by other, which will return the same body
            ->setUrl('https://entk8a4v9cmou60.m.pipedream.net');

        $data = [
            'name' => $faker->name,
            'email' => $faker->email,
            'text' => $faker->text
        ];

        $cURLClient->request($data);
        $this->assertTrue($this->arraysAreSimilar($data, $cURLClient->responseJsonDecode()));
    }

    public function testRequestFail()
    {
        $cURLClient = new cURLClient();
        $faker = Faker\Factory::create();

        $cURLClient
            ->setTimeOut(1000)
            ->setUrl('https://notexistingutl.si');

        $data = [
            'name' => $faker->name,
            'email' => $faker->email,
            'text' => $faker->text
        ];


        $this->expectException(Exception::class);
        $cURLClient->request($data);
    }

    /**
     * Determine if two associative arrays are similar
     *
     * Both arrays must have the same indexes with identical values
     * without respect to key ordering
     *
     * @param array $a
     * @param array $b
     * @return bool
     */
    public function arraysAreSimilar($a, $b) {
        if (count(array_diff_assoc($a, $b))) {
            return false;
        }

        foreach($a as $k => $v) {
            if ($v !== $b[$k]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if exception is thrown when cURLClient::timeOutMs is reached
     *
     * @return void
     * @throws Exception
     */
    public function testcURLClientTimeOutMsExpectExceptionSucceed() {
        $this->expectException(Exception::class);

        $faker      = Faker\Factory::create();
        $cURLClient = new cURLClient();
        $arrConf    = [
            'url' => 'https://sm.requestcatcher.com',
            'timeOutMs' => 1
        ];

        $RequestClientConf = new cUrlClientConfiguration($arrConf);
        $cURLClient->setConfiguration($RequestClientConf);

        $data = [
            'name'  => $faker->name,
            'email' => $faker->email
        ];

        $cURLClient->request($data);
    }

    /**
     * Checks if exception is thrown when cURLClient::connectTimeOutMs is reached
     *
     * @return void
     * @throws Exception
     */
    public function testcURLClientConnectTimeOutMsExpectExceptionSucceed() {
        $this->expectException(Exception::class);

        $faker      = Faker\Factory::create();
        $cURLClient = new cURLClient();
        $arrConf    = [
            'url' => 'https://sm.requestcatcher.com',
            'connectTimeOutMs' => 1
        ];

        $RequestClientConf = new cUrlClientConfiguration($arrConf);
        $cURLClient->setConfiguration($RequestClientConf);

        $data = [
            'name' => $faker->name,
            'email' => $faker->email
        ];

        $cURLClient->request($data);
    }

    /**
     * Checks if request is succeeded with standard timeOutMs and connectTimeOutMs
     *
     * @return void
     * @throws Exception
     */
    public function testcURLClientStandardTimeOutMsAndConnectTimeOutMsSucceed() {
        $faker      = Faker\Factory::create();
        $cURLClient = new cURLClient();
        $arrConf    = [
            'url' => 'https://sm.requestcatcher.com'
        ];

        $RequestClientConf = new cUrlClientConfiguration($arrConf);

        $cURLClient->setConfiguration($RequestClientConf);

        $data = [
            'name' => $faker->name,
            'email' => $faker->email
        ];

        $cURLClient->request($data);

        $this->assertTrue($cURLClient->getResponse() != null);
        $this->assertTrue(strlen($cURLClient->getResponse()) > 1);
    }
}