<?php

namespace Tests\Feature;

use Generator;
use Faker;
use SALESmanago\Entity\Api\V3\CatalogEntity;
use SALESmanago\Entity\Api\V3\ConfigurationEntity;
use SALESmanago\Exception\Exception;
use SALESmanago\Services\Api\V3\CatalogService;

trait CatalogsTestTrait
{
    /**
     * Provide data for testCreateCatalogSuccess
     *
     * @return Generator
     * @throws Exception
     */
    public function provideCatalogEntityData()
    {
        yield [$this->createCatalogEntityWithDummyData()];
        yield [$this->createCatalogEntityThroughtStandardizedMethodsWithDummyData()];
    }

    /**
     * @throws Exception
     */
    protected function createCatalogEntityWithDummyData()
    {
        $this->faker = Faker\Factory::create();

        return new CatalogEntity(
            [
                "catalogName"  => 'Catalog ' . $this->faker->word,
                "currency"     => $this->faker->currencyCode,
                "location"     => 'time'.time()
            ]
        );
    }

    /**
     * @throws Exception
     */
    protected function createCatalogEntityThroughtStandardizedMethodsWithDummyData()
    {
        $this->faker = Faker\Factory::create();

        return new CatalogEntity(
            [
                "name"         => 'Catalog ' . $this->faker->word,
                "currency"     => $this->faker->currencyCode,
                "location"     => 'time'.time()
            ]
        );
    }

    /**
     * Generate CatalogEntity with wrong data
     *
     * @return CatalogEntity
     */
    protected function createCatalogEntityWithNotValidDummyData()
    {
        return (new CatalogEntity())
            ->setName($this->faker->text(65))
            ->setCurrency($this->faker->currencyCode . $this->faker->randomDigit)
            ->setLocation($this->faker->uuid)
            ->setSetAsDefault($this->faker->boolean());
    }

    /**
     * Creates instance of CatalogService
     *
     * @requires Tests\Feature\Services\Api\V3\TestAbstractBasicV3Service::createConfigurationEntity()

     * @return CatalogService
     */
    protected function createCatalogService()
    {
        //create configuration for request service
        $this->createConfigurationEntity();

        //create & setup CatalogService:
        return new CatalogService(
            ConfigurationEntity::getInstance()//created with $this->createConfigurationEntity()
        );
    }
}