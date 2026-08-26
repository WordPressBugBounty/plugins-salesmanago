<?php

namespace SALESmanago\Entity;

use SALESmanago\Entity\Api\V3\ConfigurationEntityTrait;

class UnionConfigurationEntity extends Configuration implements UnionConfigurationInterface
{
    use ConfigurationEntityTrait;
}