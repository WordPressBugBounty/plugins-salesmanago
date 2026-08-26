<?php

namespace Tests\Feature\User\Vendor;

use SALESmanago\Controller\User\Vendor\AccountController;
use SALESmanago\Entity\AbstractConfiguration;
use SALESmanago\Entity\UnionConfigurationEntity;
use SALESmanago\Entity\User;
use Tests\Feature\TestCaseFeature;
use SALESmanago\Exception\ApiV3Exception;
use SALESmanago\Exception\Exception as SalesmanagoException;

class LoginTest extends TestCaseFeature
{

    public function setUp(): void
    {
        parent::setUp();

        $reflection = new \ReflectionProperty(AbstractConfiguration::class, 'instances');
        $reflection->setAccessible(true);
        $reflection->setValue(null, []);
    }

    /**
     * Test if login of vendor is success
     *
     * @runTestsInSeparateProcesses
     * @return void
     * @throws ApiV3Exception
     * @throws SalesmanagoException
     */
    public function testLoginSuccess()
    {
        $userEmail          = getenv('userEmail');
        $userPass           = getenv('userPass');
        $userCustomEndpoint = getenv('appEndpoint');
        $apiV3Endpoint      = getenv('ApiV3Endpoint');

        $unionConfiguration = UnionConfigurationEntity::getInstance();

        if (!empty($userCustomEndpoint)) {
            $unionConfiguration->setEndpoint($userCustomEndpoint);
            $unionConfiguration->setRequestClientConf(
                $unionConfiguration->getRequestClientConf()->setHost($userCustomEndpoint)
            );
        }

        if (!empty($apiV3Endpoint)) {
            $unionConfiguration->setApiV3Endpoint($apiV3Endpoint);
        }

        $AccountController = new AccountController($unionConfiguration, null);

        $Response = $AccountController->login(
            (new User())
                ->setEmail($userEmail)
                ->setPass($userPass)
        );

        $this->assertNotEmpty(UnionConfigurationEntity::getInstance()->getApiV3Key());
        $this->assertNotEmpty(UnionConfigurationEntity::getInstance()->getEndpoint());
        $this->assertNotEmpty(UnionConfigurationEntity::getInstance()->getApiKey());
        $this->assertNotEmpty(UnionConfigurationEntity::getInstance()->getSha());

        $this->assertNotEmpty($Response->getField('conf')->getClientId());
        $this->assertNotEmpty($Response->getField('conf')->getApiV3Key());
        $this->assertNotEmpty($Response->getField('conf')->getSmApp());

        $this->assertEquals($Response->getField('conf')->getClientId(),
            UnionConfigurationEntity::getInstance()->getClientId()
        );

        $this->assertEquals($Response->getField('conf')->getApiV3Key(),
            UnionConfigurationEntity::getInstance()->getApiV3Key()
        );
    }

    public function tearDown(): void
    {
        parent::tearDown();

        //clear configuration singleton
        $reflection = new \ReflectionProperty(AbstractConfiguration::class, 'instances');
        $reflection->setAccessible(true);
        $reflection->setValue(null, []);
    }
}