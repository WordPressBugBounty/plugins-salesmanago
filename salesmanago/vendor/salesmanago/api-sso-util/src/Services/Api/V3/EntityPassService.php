<?php

namespace SALESmanago\Services\Api\V3;

use SALESmanago\Entity\Api\V3\ConfigurationInterface;
use SALESmanago\Entity\cUrlClientConfiguration;
use SALESmanago\Entity\RequestClientConfigurationInterface;
use SALESmanago\Exception\Exception;

class EntityPassService
{
    const
        REQUEST_METHOD_POST = 'POST',
        REQUEST_METHOD_GET  = 'GET';

    /**
     * @var RequestService
     */
    protected $RequestService;

    /**
     * @param ConfigurationInterface $ConfigurationV3
     * @param RequestClientConfigurationInterface|null $cUrlClientConf
     */
    public function __construct(
        ConfigurationInterface $ConfigurationV3,
        RequestClientConfigurationInterface $cUrlClientConf = null
    ) {
        $cUrlClientConf = $cUrlClientConf ?? $this->setCurlClientConfiguration($ConfigurationV3);
        $this->RequestService = new RequestService($cUrlClientConf);
    }

    /**
     * @param ConfigurationInterface $ConfigurationV3
     * @return RequestClientConfigurationInterface
     */
    private function setCurlClientConfiguration(ConfigurationInterface $ConfigurationV3) {
        $cUrlClientConfiguration = new cUrlClientConfiguration();

        $cUrlClientConfiguration
            ->setHeaders(['API-KEY' => $ConfigurationV3->getApiV3Key()])
            ->setHost($ConfigurationV3->getApiV3Endpoint());

        return $cUrlClientConfiguration;
    }
}