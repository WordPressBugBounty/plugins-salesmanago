<?php

namespace SALESmanago\Entity;

use SALESmanago\Entity\Api\V3\ConfigurationInterface as V3ConfigurationInterface;

/**
 * Union of standard configuration interface and v3 configuration interface
 */
interface UnionConfigurationInterface extends ConfigurationInterface, V3ConfigurationInterface
{

}