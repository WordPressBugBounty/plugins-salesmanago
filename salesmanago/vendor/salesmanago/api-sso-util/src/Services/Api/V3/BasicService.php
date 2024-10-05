<?php

namespace SALESmanago\Services\Api\V3;

use SALESmanago\Entity\Api\V3\ConfigurationInterface;
use SALESmanago\Entity\cUrlClientConfiguration;
use SALESmanago\Entity\RequestClientConfigurationInterface;
use SALESmanago\Exception\Exception;

class BasicService
{
    const
        REQUEST_METHOD_POST = 'POST',
        REQUEST_METHOD_GET  = 'GET';

    /**
     * @var RequestService
     */
    protected $RequestService;

    /**
     * @var ConfigurationInterface
     */
    protected $configuration;

    /**
     * @param ConfigurationInterface $ConfigurationV3
     * @param RequestClientConfigurationInterface|null $cUrlClientConf
     */
    public function __construct(
        ConfigurationInterface $ConfigurationV3,
        RequestClientConfigurationInterface $cUrlClientConf = null
    ) {
        $this->configuration  = $ConfigurationV3;
        $cUrlClientConf       = $cUrlClientConf ?? $this->setCurlClientConfiguration();
        $this->RequestService = new RequestService($cUrlClientConf);
    }

    /**
     * @return RequestClientConfigurationInterface
     */
    private function setCurlClientConfiguration() {
        $cUrlClientConfiguration = new cUrlClientConfiguration();

        $cUrlClientConfiguration
            ->setHeaders(['API-KEY' => $this->configuration->getApiV3Key()])
            ->setHost($this->configuration->getApiV3Endpoint());

        return $cUrlClientConfiguration;
    }
}