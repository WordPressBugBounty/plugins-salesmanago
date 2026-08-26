<?php


namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Faker;

use ReflectionClass;
use ReflectionMethod;
use ReflectionException;

use SALESmanago\Controller\LoginController;
use SALESmanago\Controller\User\Vendor\AccountController;
use SALESmanago\Entity\Configuration;
use SALESmanago\Entity\UnionConfigurationEntity;
use SALESmanago\Entity\User;
use SALESmanago\Exception\Exception;
use SALESmanago\Exception\ApiV3Exception;


class TestCaseFeature extends TestCase
{
    /**
     * @var Faker\Generator
     */
    protected $faker;

    /**
     * @var array - callbacks to cleanup after test
     */
    protected $arrayOfCallbacksToCleanup = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->faker = Faker\Factory::create();
    }

    /**
     * @return mixed|Configuration
     * @throws Exception
     */
    protected function initConf()
    {
        $userEmail          = getenv('userEmail');
        $userPass           = getenv('userPass');
        $userCustomEndpoint = getenv('appEndpoint');

        $conf = Configuration::getInstance();
        $user = new User();

        if (!empty($userCustomEndpoint)) {
            $conf->setEndpoint($userCustomEndpoint);
            $conf->setRequestClientConf(
                $conf->getRequestClientConf()->setHost($userCustomEndpoint)
            );
        }

        $user
            ->setEmail($userEmail)
            ->setPass($userPass);

        $loginController = new LoginController($conf);
        $loginController->login($user);//this one for property configuration create

        return $conf;
    }

    /**
     * Login user, sets configuration with api v3 key;
     *
     * @return void
     * @throws Exception
     * @throws ApiV3Exception
     */
    protected function initConfWithApiV3()
    {
        $userEmail          = getenv('userEmail');
        $userPass           = getenv('userPass');
        $userCustomEndpoint = getenv('appEndpoint');
        $apiV3Endpoint      = getenv('ApiV3Endpoint');
        $apiV3Key           = getenv('ApiV3Key');

        $unionConfiguration = UnionConfigurationEntity::getInstance();

        $User = new User();

        $User
            ->setEmail($userEmail)
            ->setPass($userPass);

        if (!empty($userCustomEndpoint)) {
            $unionConfiguration->setEndpoint($userCustomEndpoint);
            $unionConfiguration->setRequestClientConf(
                $unionConfiguration->getRequestClientConf()->setHost($userCustomEndpoint)
            );
        }

        if (!empty($apiV3Endpoint)) {
            $unionConfiguration->setApiV3Endpoint($apiV3Endpoint);
        }

        if (!empty($apiV3Key)) {
            $unionConfiguration->setApiV3Key($apiV3Key);
        }

        $AccountController = new AccountController($unionConfiguration, null);

        $AccountController->login($User);

    }

    /**
     * @param $name
     * @param mixed $classObj
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    protected function getMethod($name, $classObj)
    {
        $class = new ReflectionClass($classObj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->runCleanupCallbacks();
    }

    /**
     * @return true|void
     */
    protected function runCleanupCallbacks()
    {
        if (empty($this->arrayOfCallbacksToCleanup)) {
            return;
        }

        foreach ($this->arrayOfCallbacksToCleanup as $callback) {
            $callback();
        }

        return true;
    }
}