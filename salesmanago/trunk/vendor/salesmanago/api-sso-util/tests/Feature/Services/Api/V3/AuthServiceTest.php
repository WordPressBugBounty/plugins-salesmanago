<?php


namespace Tests\Unit\Services\Api\V3;

use SALESmanago\Entity\Api\V3\Auth\ApiKeyMetaEntity;
use SALESmanago\Entity\Api\V3\ConfigurationInterface;
use SALESmanago\Exception\ApiV3Exception;
use SALESmanago\Exception\Exception;
use SALESmanago\Model\Api\V3\AuthBuilderModel;
use SALESmanago\Model\Api\V3\ConfigurationBuilderModel;
use SALESmanago\Services\Api\V3\AuthService;
use SALESmanago\Entity\AbstractConfiguration;
use SALESmanago\Entity\User;

use Tests\Feature\TestCaseFeature;

class AuthServiceTest extends TestCaseFeature
{
    public function setUp(): void
    {
        parent::setUp();

        $reflection = new \ReflectionProperty(AbstractConfiguration::class, 'instances');
        $reflection->setAccessible(true);
        $reflection->setValue(null, []);
    }

    /**
     * Test if the method successfully handles the possibility of not having 'apiKey' in response
     *
     * @throws Exception
     * @throws ApiV3Exception
     */
    public function testDoNotCallSetApiKeyToConfigurationIfApiV3KeyNotPresent()
    {
        $userMock = $this->createMock(User::class);
        $apiKeyMetaMock = $this->createMock(ApiKeyMetaEntity::class);
        $authModelMock = $this->createMock(AuthBuilderModel::class);
        $configurationBuilderModelMock = $this->createMock(ConfigurationBuilderModel::class);
        $configurationMock = $this->createMock(ConfigurationInterface::class);

        $requestServiceMock = new class {
            // simulate response from api.salesmanago.com/v3/auth/create without 'apiKey'
            public function request(): array
            {
                return [];
            }
        };

        $service = new AuthService($configurationMock);

        $this->setProtectedProperty($service, 'AuthModel', $authModelMock);
        $this->setProtectedProperty($service, 'RequestService', $requestServiceMock);
        $this->setProtectedProperty($service, 'ConfigurationBuilderModel', $configurationBuilderModelMock);
        $this->setProtectedProperty($service, 'configuration', $configurationMock);

        $authModelMock
            ->expects($this->once())
            ->method('getCreate')
            ->with($userMock, $apiKeyMetaMock)
            ->willReturn(['some' => 'data']);

        $configurationBuilderModelMock
            ->expects($this->never())
            ->method('setApiKeyToConfiguration');

        $response = $service->create($userMock, $apiKeyMetaMock);

        $this->assertTrue($response->getStatus());
        $this->assertArrayHasKey('conf', $response->getFields());
        $this->assertSame($configurationMock, $response->getField('conf'));
    }

    private function setProtectedProperty($object, string $property, $value): void
    {
        $reflection = new \ReflectionClass($object);

        if (!$reflection->hasProperty($property)) {
            throw new \InvalidArgumentException(sprintf(
                'Property "%s" does not exist in class "%s".',
                $property,
                get_class($object)
            ));
        }

        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($object, $value);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $reflection = new \ReflectionProperty(AbstractConfiguration::class, 'instances');
        $reflection->setAccessible(true);
        $reflection->setValue(null, []);
    }
}