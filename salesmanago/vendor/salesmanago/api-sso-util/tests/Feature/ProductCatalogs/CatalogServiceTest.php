<?php

namespace Tests\Feature\ProductCatalogs;

use Faker\Factory;
use SALESmanago\Entity\Api\V3\CatalogEntityInterface;
use SALESmanago\Exception\ApiV3Exception;
use SALESmanago\Exception\Exception;
use Tests\Feature\TestAbstractBasicV3Service;

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
        //create & setup CatalogService:
        $CatalogService = $this->createCatalogService();

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
        //create catalog service:
        $CatalogService = $this->createCatalogService();

        //upsert catalog:
        $response = $CatalogService->createCatalog($Catalog);

        //check:
        $this->assertIsArray($response);
        $this->assertTrue(!empty($response['requestId']));
        $this->assertTrue(!empty($response['catalogId']));

//cleanup:
        //set catalog id from SALESmanago to entity object:
        $Catalog->setId($response['requestId']);

        //upsert/delete:
        $CatalogService->delete($Catalog);
    }

    /**
     * Test of successfully delete of product catalog form SALESmanago
     *
     * @return void
     * @throws ApiV3Exception
     * @throws Exception
     */
    public function testDeleteCatalogSuccess()
    {
        //create catalog service:
        $CatalogService = $this->createCatalogService();

        //create catalog entity:
        $CatalogEntity = $this->createCatalogEntityWithDummyData();

        //upsert/create catalog:
        $responseCreate = $CatalogService->createCatalog($CatalogEntity);

        //set catalog id from SALESmanago to entity object:
        $CatalogEntity->setId($responseCreate['catalogId']);

        //upsert/delete:
        $responseDelete = $CatalogService->delete($CatalogEntity);

        //check
        $this->assertIsArray($responseDelete);
        $this->assertTrue(!empty($responseDelete['requestId']));
        $this->assertTrue(!empty($responseDelete['catalogId']));
        $this->assertEquals($responseDelete['catalogId'], $responseCreate['catalogId']);
        $this->assertEquals($responseDelete['catalogId'], $CatalogEntity->getId());
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
        //create & setup CatalogService:
        $CatalogService = $this->createCatalogService();

        //get limits:
        $limit = $CatalogService->getLimit();

        //checked if result is not null:
        $this->assertTrue($limit != null);

        //checked if result is numeric:
        $this->assertIsNumeric($limit);
    }

    /**
     * Test get amount of available product catalogs to create
     *
     * @return void
     * @throws ApiV3Exception
     * @throws Exception
     */
    public function testGetAmountAvailableToCreateSuccess(): void
    {
        //create catalog service:
        $CatalogService = $this->createCatalogService();

        //get limit of product catalogs:
        $limitMax = $CatalogService->getLimit();

        //get actual catalogs:
        $Catalogs = $CatalogService->getCatalogs();

        //count actual catalogs:
        $availableCatalogsAmount = count($Catalogs);

        //count amount of available to create:
        $availableAmountToCreate = $limitMax-$availableCatalogsAmount;

        //get amount of available to create from service method:
        $predictAvailableAmountToCreate = $CatalogService->getAmountAvailableToCreate();

        //check:
        $this->assertEquals($availableAmountToCreate, $predictAvailableAmountToCreate);

        //next we test get limit with newly created catalogs:
        if ($availableCatalogsAmount < $limitMax) {
            //count how much more catalogs could be created:
            $nbOfCatalogsToBeCreated = $limitMax - $availableCatalogsAmount;

            //set number of catalogs to create:
            $nbCatalogsToCreate = Factory::create()->numberBetween(0, $nbOfCatalogsToBeCreated);
            $createdTestCatalogs = [];

            //create catalogs:
            while ($nbCatalogsToCreate) {
                $createdTestCatalogs[] = $this->createDummyCatalogInSalesmanago();
                $nbCatalogsToCreate--;
            }

            //get actual catalogs:
            $Catalogs = $CatalogService->getCatalogs();

            //count actual catalogs:
            $availableCatalogsAmount = count($Catalogs);

            //count amount of available to create:
            $availableAmountToCreate = $limitMax-$availableCatalogsAmount;

            //get amount of available to create from service method:
            $predictAvailableAmountToCreate = $CatalogService->getAmountAvailableToCreate();

            //check:
            $this->assertEquals($availableAmountToCreate, $predictAvailableAmountToCreate);

            //cleanup:
            if (!empty($createdTestCatalogs)) {
                foreach ($createdTestCatalogs as $catalog) {
                    $CatalogService->delete($catalog);
                }
            }
        }
    }
}
