<?php

namespace Tests\Feature\ProductCollection;

use SALESmanago\Entity\Api\V3\CatalogEntityInterface;
use SALESmanago\Entity\Api\V3\Product\Collection\Entity as ProductCollectionEntity;
use SALESmanago\Entity\Api\V3\Product\Collection\EntityInterface as ProductCollectionEntityInterface;
use Faker;
use SALESmanago\Entity\Api\V3\Product\Collection\ItemEntity as ProductCollectionItem;
use SALESmanago\Entity\UnionConfigurationEntity;
use SALESmanago\Exception\ApiV3Exception;
use SALESmanago\Exception\Exception;
use SALESmanago\Services\Api\V3\Product\CatalogService;
use SALESmanago\Services\Api\V3\Product\CollectionService as ProductCollectionService;


trait ProductCollectionTrait
{
    /**
     * @var CatalogEntityInterface
     */
    protected $createdProductCatalog = null;

    /**
     * Generates product collection entity with only name;
     *
     * @return ProductCollectionEntityInterface
     */
    protected function generateProductCollectionEntityForCreate()
    {
        //generate collection name:
        $CollectionName = Faker\Factory::create()->domainName . '-test-collection';

        //create collection and set name:
        return (new ProductCollectionEntity())
            ->setName($CollectionName);
    }

    /**
     * Creates new instance of ProductCollectionService
     *
     * @return ProductCollectionService
     * @throws ApiV3Exception
     * @throws Exception
     */
    protected function createProductCollectionService(): ProductCollectionService
    {
        //create configuration for request service
        $this->initConfWithApiV3();

        //create & setup CatalogService:
        return new ProductCollectionService(
            UnionConfigurationEntity::getInstance()//created with $this->createConfigurationEntity()
        );
    }

    /**
     * Create Dummy ProductCollection in SALESmanago for test purpose:
     *
     * @param CatalogEntityInterface|null $createdInAppCatalogEntity
     * @return ProductCollectionEntity
     * @throws ApiV3Exception
     * @throws Exception
     */
    protected function createDummyProductCollectionInSalesmanago(
        ?CatalogEntityInterface $createdInAppCatalogEntity,
        bool $withRemoveCallback = true
    ): ProductCollectionEntity
    {
        //in some cases we need to know in test to which catalog product collection is created:
        if (empty($createdInAppCatalogEntity)) {
            //create product catalog service and check how many catalogs exist in application SALESmanago:
            $ProductCatalogService = new CatalogService($this->configuration);
            $catalogAmountAvailableToCreate = $ProductCatalogService->getAmountAvailableToCreate();

            if ($catalogAmountAvailableToCreate) {
                //create product catalog in SALESmanago:
                $createdInAppCatalogEntity = $this->createDummyCatalogInSalesmanago();

                //useful when created elements in test must be removed after tests:
                $this->createdProductCatalog = $createdInAppCatalogEntity;
            } else {
                //get random catalog from SALESmanago:
                $createdInAppCatalogEntity = $this->getRandomCatalogFromSalesmanago();
            }
        }

        //create new product collection:
        $ProductCollectionEntity  = $this->generateProductCollectionEntityForCreate();
        $ProductCollectionService = new ProductCollectionService($this->configuration);

        $ProductCollectionEntity = $ProductCollectionService->add(
            $ProductCollectionEntity,
            $createdInAppCatalogEntity
        );

        //add method to remove entity from api after test:
        if ($withRemoveCallback) {
            $this->arrayOfCallbacksToCleanup[] = function () use ($ProductCollectionEntity) {
                ($this->createProductCollectionService())
                    ->delete($ProductCollectionEntity);
            };
        }

        //add product collection to random catalog:
        return $ProductCollectionEntity;
    }

    /**
     * Add products to specified Product Collection and Contact in Salesmanago
     *
     * @param ProductCollectionEntityInterface $ProductCollectionEntity
     * @param ProductCollectionItem $Item
     * @return ProductCollectionItem - added item to Product Collection
     * @throws ApiV3Exception
     * @throws Exception
     */
    protected function addProductsToProductCollectionInSalesmanago(
        ProductCollectionEntityInterface $ProductCollectionEntity,
        ProductCollectionItem $Item
    ): ProductCollectionItem
    {
        //create instance of product collection service:
        $productCollectionService = $this->createProductCollectionService();

        //add products to selected ProductCollectionEntity
        $productCollectionService->addProducts($ProductCollectionEntity, $Item);

        //return created item
        return $Item;
    }
}