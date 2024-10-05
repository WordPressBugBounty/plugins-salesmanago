<?php

namespace Tests\Feature\ProductCatalogs;

use Faker;
use Generator;
use SALESmanago\Entity\Api\V3\CatalogEntity;
use SALESmanago\Entity\Api\V3\CatalogEntityInterface;
use SALESmanago\Entity\UnionConfigurationEntity;
use SALESmanago\Exception\Exception;
use SALESmanago\Services\Api\V3\CatalogService;
use SALESmanago\Exception\ApiV3Exception;

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
     * Help function to create catalogs
     *
     * @param bool $withRemoveCallback
     * @return CatalogEntity
     * @throws ApiV3Exception
     * @throws Exception
     */
    protected function createDummyCatalogInSalesmanago(bool $withRemoveCallback = true): CatalogEntity
    {
        //create product catalog service:
        $CatalogService = $this->createCatalogService();

        //create catalog entity:
        $CatalogEntity = $this->createCatalogEntityWithDummyData();

        //set id for entity after creation:
        $CatalogEntity->setId($CatalogService->createCatalog($CatalogEntity)['catalogId']);

        //add method to remove entity from api after test:
        if ($withRemoveCallback) {
            $this->arrayOfCallbacksToCleanup[] = function () use ($CatalogEntity) {
                ($this->createCatalogService())
                    ->delete($CatalogEntity);
            };
        }

        return $CatalogEntity;
    }

    /**
     * Create catalog in app or return existed catalog
     *
     * @return CatalogEntity
     * @throws ApiV3Exception
     * @throws Exception
     */
    protected function createCatalogInSalesmanagoOtherwiseGetExistedCatalog(): CatalogEntity
    {
        //get limit for catalogs:
        $limit = $this->createCatalogService()->getLimit();

        //get actual catalogs form app:
        $catalogsFormApp = $this->createCatalogService()->getCatalogs();

        //count catalogs:
        $countCatalogsFromApp = count($catalogsFormApp);

        if ($limit > $countCatalogsFromApp) {
            return $this->createDummyCatalogInSalesmanago();
        }
        return $this->getRandomCatalogFromSalesmanago();
    }

    /**
     * Get random catalog from SALESmanago:
     *
     * @return CatalogEntity
     * @throws ApiV3Exception
     * @throws Exception
     */
    protected function getRandomCatalogFromSalesmanago(): CatalogEntity
    {
        $Catalogs = $this->createCatalogService()->getCatalogs();

        //chose random catalog:
        return $Catalogs[array_rand($Catalogs)];
    }

    /**
     * @param CatalogService $CatalogService
     * @return CatalogEntityInterface
     * @throws ApiV3Exception
     */
    protected function createCatalog(CatalogService $CatalogService)
    {
        $Catalog = new CatalogEntity();

        $Catalog
            ->setCatalogName('Catalog ' . $this->faker->word)
            ->setCurrency($this->faker->currencyCode)
            ->setLocation('time'.time())
            ->setSetAsDefault($this->faker->boolean());

        $Catalog->setCatalogId($CatalogService->createCatalog($Catalog)['catalogId']);

        return $Catalog;
    }

    /**
     * Creates instance of CatalogService
     *
     * @return CatalogService
     * @throws Exception
     * @throws ApiV3Exception
     */
    protected function createCatalogService()
    {
        //create configuration for request service
        $this->initConfWithApiV3();

        //create & setup CatalogService:
        return new CatalogService(
            UnionConfigurationEntity::getInstance()//created with $this->createConfigurationEntity()
        );
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
                "location"     => 'sm' . $this->faker->randomNumber() . 'time'.time()
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
                "location"     => 'sm' . $this->faker->randomNumber() . 'time'.time()
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
        $this->faker = Faker\Factory::create();

        return (new CatalogEntity())
            ->setName($this->faker->text(65))
            ->setCurrency($this->faker->currencyCode . $this->faker->randomDigit)
            ->setLocation($this->faker->uuid)
            ->setSetAsDefault($this->faker->boolean());
    }

    /**
     * Parse SALESmanago response to catalog Entity
     *
     * @param array $response
     * @param CatalogEntityInterface $catalogEntity - in case of extend from send entity.
     * @return CatalogEntityInterface
     */
    protected function catalogResponseToCatalogEntity(array $response, CatalogEntityInterface $catalogEntity): CatalogEntityInterface
    {
        return $catalogEntity->setId($response['catalogId']);
    }

    /**
     * @return mixed|CatalogEntity|CatalogEntityInterface
     * @throws ApiV3Exception
     * @throws Exception
     */
    protected function getCatalogToUpsertProducts()
    {
        //create ConfigurationEntity singleton
        $this->initConfWithApiV3();

        //create catalog service to get data
        $CatalogService = new CatalogService(UnionConfigurationEntity::getInstance());

        $catalogsArr = $CatalogService->getCatalogs();

        if (!empty($catalogsArr)) {
            return $catalogsArr[array_rand($catalogsArr, 1)];
        }

        return $this->createCatalog($CatalogService);
    }
}