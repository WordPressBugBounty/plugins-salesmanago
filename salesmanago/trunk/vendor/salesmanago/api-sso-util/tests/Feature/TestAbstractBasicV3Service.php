<?php

namespace Tests\Feature;

use SALESmanago\Entity\Api\V3\ConfigurationEntity;
use SALESmanago\Entity\UnionConfigurationEntity;
use SALESmanago\Exception\ApiV3Exception;
use SALESmanago\Exception\Exception;

class TestAbstractBasicV3Service extends TestCaseFeature
{
    /**
     * Create Configuration entity for api v3
     *
     * @return void
     * @throws ApiV3Exception|Exception
     * @deprecated - since 3.1.10
     * @see self::initConfWithApiV3() - use that istead of createConfigurationEntity
     */
    protected function createConfigurationEntity()
    {
        ConfigurationEntity::getInstance()
            ->setApiV3Endpoint(getenv('ApiV3Endpoint'))
            ->setApiV3Key(getenv('ApiV3Key'));

        UnionConfigurationEntity::getInstance()
            ->setApiV3Endpoint(getenv('ApiV3Endpoint'))
            ->setApiV3Key(getenv('ApiV3Key'));

        $this->initConfWithApiV3();
    }
}