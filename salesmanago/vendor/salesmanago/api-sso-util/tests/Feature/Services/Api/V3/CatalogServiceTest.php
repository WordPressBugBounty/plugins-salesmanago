<?php

namespace Tests\Feature\Services\Api\V3;

use SALESmanago\Entity\Api\V3\CatalogEntityInterface;
use SALESmanago\Exception\ApiV3Exception;
use SALESmanago\Exception\Exception;
use SALESmanago\Services\Api\V3\CatalogService;
use SALESmanago\Entity\Api\V3\ConfigurationEntity;
use Tests\Feature\CatalogsTestTrait;

class CatalogServiceTest extends TestAbstractBasicV3Service
{
    use CatalogsTestTrait;

    /**
     * Test get catalog success
     *
     * @return void
     * @throws Exception
     * @throws ApiV3Exception
     */
    public function testGetCatalogsSuccess()
    {
        //create configuration for request service
        $this->createConfigurationEntity();

        //create & setup CatalogService:
        $CatalogService = new CatalogService(
            ConfigurationEntity::getInstance()//created with $this->createConfigurationEntity()
        );

        //get catalogs:
        $catalogs = $CatalogService->getCatalogs();

        //checked if result is an array:
        $this->assertIsArray($catalogs);

        //checked if result implements interface
        foreach ($catalogs as $catalog) {
            $this->assertInstanceOf(CatalogEntityInterface::class, $catalog);
        }
    }

    /**
     * Checking creation of catalog with different create catalog entity methods
     *
     * @dataProvider provideCatalogEntityData
     * @return void
     * @throws ApiV3Exception
     */
    public function testCreateCatalogSuccess(CatalogEntityInterface $Catalog)
    {
        //create catalog service
        $CatalogService = $this->createCatalogService();

        //upsert catalog
        $response = $CatalogService->createCatalog($Catalog);

        //check
        $this->assertIsArray($response);
        $this->assertTrue(!empty($response['requestId']));
        $this->assertTrue(!empty($response['catalogId']));
    }


    /**
     * Test get catalogs limits for vendor
     *
     * @return void
     * @throws ApiV3Exception
     * @throws Exception
     */
    public function testGetCatalogsLimitSuccess()
    {
        //create configuration for request service
        $this->createConfigurationEntity();

        //create & setup CatalogService:
        $CatalogService = new CatalogService(
            ConfigurationEntity::getInstance()//created with $this->createConfigurationEntity()
        );

        //get limits:
        $limit = $CatalogService->getLimit();

        //checked if result is not null:
        $this->assertTrue($limit != null);

        //checked if result is numeric:
        $this->assertIsNumeric($limit);
    }
}
