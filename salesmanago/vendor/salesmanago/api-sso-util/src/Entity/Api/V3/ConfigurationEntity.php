<?php

namespace SALESmanago\Entity\Api\V3;

use SALESmanago\Entity\AbstractConfiguration;

class ConfigurationEntity extends AbstractConfiguration implements ConfigurationInterface
{
    /**
     * @var null|string
     */
    protected $apiV3Key = null;

    /**
     * @var string
     */
    protected $apiV3Endpoint = 'https://api.salesmanago.com';

    /**
     * @return string|null
     */
    public function getApiV3Key()
    {
        return $this->apiV3Key;
    }

    /**
     * @param $apiKey
     * @return $this|ConfigurationEntity
     */
    public function setApiV3Key($apiKey)
    {
        $this->apiV3Key = $apiKey;
        return $this;
    }

    /**
     * @param $endpoint
     * @return $this|ConfigurationEntity
     */
    public function setApiV3Endpoint( $endpoint)
    {
        $this->apiV3Endpoint = $endpoint;
        return $this;
    }

    /**
     * @return string
     */
    public function getApiV3Endpoint()
    {
        return $this->apiV3Endpoint;
    }
}