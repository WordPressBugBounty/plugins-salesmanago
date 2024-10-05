<?php

namespace SALESmanago\Model\Api\V3;

use SALESmanago\Entity\Api\V3\ConfigurationInterface;

class ConfigurationBuilderModel
{
    /**
     * @param ConfigurationInterface $Configuration
     * @param array $responseCreateApiKey
     * @return ConfigurationInterface
     */
    public function setApiKeyToConfiguration(
        ConfigurationInterface $Configuration,
        array $responseCreateApiKey
    ): ConfigurationInterface {
         $Configuration->setApiV3Key($responseCreateApiKey['apiKey']);
         return $Configuration;
    }
}