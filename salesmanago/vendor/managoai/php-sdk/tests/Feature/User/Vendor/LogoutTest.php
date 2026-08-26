<?php

namespace Tests\Feature\User\Vendor;

use SALESmanago\Adapter\ConfigurationStoreAdapterInterface;
use SALESmanago\Controller\User\Vendor\AccountController;
use SALESmanago\Entity\Api\V3\ConfigurationInterface as V3ConfigurationInterface;
use SALESmanago\Entity\ConfigurationInterface;
use SALESmanago\Entity\UnionConfigurationEntity;
use SALESmanago\Entity\UnionConfigurationInterface;
use SALESmanago\Entity\User;
use Tests\Feature\TestCaseFeature;
use SALESmanago\Exception\ApiV3Exception;
use SALESmanago\Exception\Exception;


class LogoutTest extends TestCaseFeature
{
    /**
     * @runTestsInSeparateProcesses
     * @return void
     * @throws ApiV3Exception
     * @throws Exception
     */
    public function testLogoutWithRemoveConfigurationThroughAdapterAndRevokeApiKeyV3Success()
    {
        $ConfigurationStoreAdapter = ( new class()
                implements ConfigurationStoreAdapterInterface {

                private $authConfigurationRemoved;
                private $wholeConfigurationRemoved;

                public function __construct()
                {
                    $this->authConfigurationRemoved  = false;
                    $this->wholeConfigurationRemoved = false;
                }

                public function storeUnionConfiguration(UnionConfigurationInterface $configuration): bool
                {
                    //doesn't matter for this test;
                    return false;
                }

                public function storeConfiguration(ConfigurationInterface $configuration): bool
                {
                    //doesn't matter for this test;
                    return false;
                }

                public function storeV3Configuration(V3ConfigurationInterface $configuration): bool
                {
                    //doesn't matter for this test;
                    return false;
                }

                public function removeConfiguration()
                {
                    $this->wholeConfigurationRemoved = true;
                }

                public function removeAuthConfiguration()
                {
                    $this->authConfigurationRemoved = true;
                }

                public function getAuthConfigurationRemoved(): bool
                {
                    return $this->authConfigurationRemoved;
                }

                public function getWholeConfigurationRemoved(): bool
                {
                    return $this->wholeConfigurationRemoved;
                }
            }
        );

        //init UnionConfigurationEntity configuration:
        $this->initConfWithApiV3();

        $AccountController = new AccountController(UnionConfigurationEntity::getInstance(), $ConfigurationStoreAdapter);
        $AccountController->logout();

        //after logout configuration must be removed:
        $this->assertTrue($ConfigurationStoreAdapter->getWholeConfigurationRemoved());
        $this->assertTrue($ConfigurationStoreAdapter->getAuthConfigurationRemoved());

        //after logout apiKeyV3 must be revoked.
        //after success revoked process in configuration apiKeyV3 must be set to null
        $this->assertEquals(null, UnionConfigurationEntity::getInstance()->getApiV3Key());
    }
}