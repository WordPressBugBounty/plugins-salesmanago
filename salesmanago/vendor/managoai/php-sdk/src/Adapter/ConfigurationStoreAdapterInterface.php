<?php

namespace SALESmanago\Adapter;

use SALESmanago\Entity\ConfigurationInterface;
use SALESmanago\Entity\UnionConfigurationInterface;
use SALESmanago\Entity\Api\V3\ConfigurationInterface as V3ConfigurationInterface;

interface ConfigurationStoreAdapterInterface
{
    /**
     * @param UnionConfigurationInterface $configuration
     * @return bool
     */
    public function storeUnionConfiguration(UnionConfigurationInterface $configuration): bool;

    /**
     * @param ConfigurationInterface $configuration
     * @return bool
     */
    public function storeConfiguration(ConfigurationInterface $configuration): bool;

    /**
     * @param V3ConfigurationInterface $configuration
     * @return bool
     */
    public function storeV3Configuration(V3ConfigurationInterface $configuration): bool;

    public function removeConfiguration();

    public function removeAuthConfiguration();
}