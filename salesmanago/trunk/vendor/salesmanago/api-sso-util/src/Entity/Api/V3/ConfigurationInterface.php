<?php

namespace SALESmanago\Entity\Api\V3;

interface ConfigurationInterface
{
    /**
     * @return string
     */
    public function getApiV3Key();

    /**
     * @param string $apiKey
     * @return $this
     */
    public function setApiV3Key($apiKey);

    /**
     * @param string $endpoint
     * @return $this
     */
    public function setApiV3Endpoint($endpoint);

    /**
     * @return string
     */
    public function getApiV3Endpoint();
}