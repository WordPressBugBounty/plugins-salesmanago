<?php

namespace Tests\Feature\ProductCollection;

use Faker\Factory;
use SALESmanago\Entity\Api\V3\ConfigurationInterface;
use SALESmanago\Entity\Api\V3\Product\ProductEntity;
use SALESmanago\Entity\Api\V3\Product\Collection\ItemEntity;
use SALESmanago\Entity\Contact\Contact;
use SALESmanago\Entity\UnionConfigurationEntity;
use SALESmanago\Exception\ApiV3Exception;
use SALESmanago\Exception\Exception;
use SALESmanago\Model\Collections\Api\V3\ProductsCollection;
use SALESmanago\Services\Api\V3\Product\CatalogService as CatalogService;
use SALESmanago\Services\Api\V3\Product\CollectionService as ProductCollectionService;
use Tests\Feature\ProductCatalogs\CatalogsTestTrait;
use Tests\Feature\TestCaseFeature;
use SALESmanago\Entity\Api\V3\Product\Collection\EntityInterface;

class ProductCollectionServiceTest extends TestCaseFeature
{
    use CatalogsTestTrait;
    use ProductCollectionTrait;
    use ProductCollectionItemTestTrait;

    /**
     * @var ConfigurationInterface
     */
    public $configuration;

    /**
     * @return void
     * @throws Exception
     */
    public function setUp(): void
    {
        //in case when we develop smth on test environment:
        $endpoint = UnionConfigurationEntity::getInstance()->getApiV3Endpoint();
        $endpoint = !empty(getenv('ApiV3Endpoint')) ? getenv('ApiV3Endpoint') : $endpoint;
        UnionConfigurationEntity::getInstance()->setApiV3Endpoint($endpoint);

        //login user, get api key v3, add basic data from salesmanago:
        $this->initConfWithApiV3();

        //assign configuration:
        $this->configuration = UnionConfigurationEntity::getInstance();
        parent::setUp();
    }

    /**
     * Test product collection create success
     *
     * @return void
     * @throws Exception
     */
    public function testCreateAndRemoveProductCollectionSuccess(): void
    {
        //create product catalog service and check how many catalogs exist in application SALESmanago:
        $ProductCatalogService = new CatalogService($this->configuration);
        $catalogAmountAvailableToCreate = $ProductCatalogService->getAmountAvailableToCreate();

        //set flags for cleanup stage:
        $isNewCatalogWasCreated = false;

        if ($catalogAmountAvailableToCreate) {
            //create product catalog in SALESmanago:
            $catalogEntity = $this->createDummyCatalogInSalesmanago();
            $isNewCatalogWasCreated = true;
        } else {
            //get random catalog from SALESmanago:
            $catalogEntity = $this->getRandomCatalogFromSalesmanago();
        }

        //create product collection service:
        $ProductCollectionService = new ProductCollectionService($this->configuration);

        //remove one product collection if limit exceeded
        if ($ProductCollectionService->getAmountAvailableToCreate() === 0) {
            $list = $ProductCollectionService->list();
            $ProductCollectionToRemove = $list[array_key_last($list)];

            $ProductCollectionService->delete($ProductCollectionToRemove);
        }

        //create new product collection:
        $ProductCollectionEntity  = $this->generateProductCollectionEntityForCreate();

        //add product collection to random catalog:
        $ProductCollectionEntity = $ProductCollectionService->add(
            $ProductCollectionEntity,
            $catalogEntity
        );

        //check thought uuid that product collection was created:
        $this->assertNotEmpty($ProductCollectionEntity->getUuid());

        //remove product collection from SALESmanago:
        $isDeleteSuccess = $ProductCollectionService->delete($ProductCollectionEntity);

        //check that product collection delete was success:
        $this->assertTrue($isDeleteSuccess);

        //cleanup. Remove created product catalog:
        if ($isNewCatalogWasCreated) {
            $ProductCatalogService->delete($catalogEntity);
        }
    }

    /**
     * Test for success collection listing
     *
     * @return void
     */
    public function testListProductCollectionsSuccess(): void
    {
        //create & setup CatalogService:
        $CollectionService = $this->createProductCollectionService();

        //get catalogs:
        $collections = $CollectionService->list();

        //checked if result is an array:
        $this->assertIsArray($collections);

        //checked if result implements interface
        foreach ($collections as $collection) {
            $this->assertInstanceOf(EntityInterface::class, $collection);
        }
    }

    /**
     * Get limit of product collections for vendor
     *
     * @return void
     * @throws ApiV3Exception
     * @throws Exception
     */
    public function testGetLimitOfProductCollectionsSuccess(): void
    {
        //create & setup CatalogService:
        $CollectionService = $this->createProductCollectionService();

        //get catalogs:
        $limit = $CollectionService->getLimit();

        //checked if result is an integer:
        $this->assertIsInt($limit);
    }

    /**
     * @TODO:remove sleep();
     * Test add products to products collection success
     *
     * @return void
     * @throws ApiV3Exception
     * @throws Exception
     */
    public function testAddProductsToProductsAndGetFromProductsCollectionSuccess(): void
    {
        //create/get product catalog in/from application:
        $CatalogEntity = $this->createCatalogInSalesmanagoOtherwiseGetExistedCatalog();

        //create product collection entity in SALESmanago application:
        $ProductCollectionEntity = $this->createDummyProductCollectionInSalesmanago($CatalogEntity);

        //generate dummy item/record of/to Product Collection:
        $ItemEntity = $this->generateDummyItemEntity();

        //wait while contact will be added to SM, this is optimal whey now, we don't have contact lists method
        sleep(60);//todo

        //add products to selected ProductCollectionEntity
        $ItemWhichWasAddedToProductCollection = $this->addProductsToProductCollectionInSalesmanago(
            $ProductCollectionEntity,
            $ItemEntity
        );

        //load products form application:
        $itemsWhichWasLoaded = ($this->createProductCollectionService())
            ->getProducts(
                $ProductCollectionEntity,
                (new Contact())->setContactId($ItemWhichWasAddedToProductCollection->getContactId())//Contact with id
            );

        //check:
        foreach ($ItemWhichWasAddedToProductCollection->getProducts() as $productId) {
            $this->assertTrue(in_array($productId, $itemsWhichWasLoaded));
        }
    }

    /**
     * Test add products to created collection and after that remove products from collection
     *
     * @return void
     * @throws ApiV3Exception
     * @throws Exception
     */
    public function testAddAndRemoveProductsFromProductCollection(): void
    {
    //create catalog entity:
        //create/get product catalog in/from application:
        $CatalogEntity = $this->createCatalogInSalesmanagoOtherwiseGetExistedCatalog();

    //create ProductCatalog entity:
        //create product collection entity in SALESmanago application:
        $ProductCollectionEntity = $this->createDummyProductCollectionInSalesmanago($CatalogEntity);

        //generate dummy item/record of/to Product Collection:
        $ItemEntity = $this->generateDummyItemEntity();

        //wait while contact will be added to SM, this is optimal whey now, we don't have contact lists method
        sleep(60);//todo

        //add products to selected ProductCollectionEntity
        $ItemWhichWasAddedToProductCollection = $this->addProductsToProductCollectionInSalesmanago(
            $ProductCollectionEntity,
            $ItemEntity
        );

        $addedProductIds = $ItemWhichWasAddedToProductCollection->getProducts();

        //create product collection service:
        $ProductCollectionService = $this->createProductCollectionService();

        //load products form application:
        $loadedFromAppProductsIdsAfterAdd = $ProductCollectionService
            ->getProducts(
                $ProductCollectionEntity,
                (new Contact())->setContactId($ItemWhichWasAddedToProductCollection->getContactId())//Contact with id
            );

        $ProductsIdsToRemove = [];
        $howManyProdDelete = Factory::create()->numberBetween(1, count($addedProductIds));

        //randomly gets product id to delete:
        for ($i=1; $i<=$howManyProdDelete; $i++) {
            $id = $addedProductIds[array_rand($addedProductIds)];

            if (in_array($id, $ProductsIdsToRemove)) {
                continue;
            }

            $ProductsIdsToRemove[] = $id;
        }

        //create collection of products for ProductCollection item-record:
        $ProductCollection = new ProductsCollection();

        //create/add products to product collection:
        foreach ($ProductsIdsToRemove as $id) {
            $ProductCollection->addItem((new ProductEntity())->setProductId($id));
        }

        //create new item with products to remove:
        $ItemToRemove = (new ItemEntity())
            ->setProducts($ProductCollection)
            ->setContactId($ItemWhichWasAddedToProductCollection->getContactId());


        //remove item product form collection:
        $ProductCollectionService->deleteProducts(
            $ProductCollectionEntity,
            $ItemToRemove
        );

        //load products form application:
        $productIdsFromApplicationAfterDelete = $ProductCollectionService
            ->getProducts(
                $ProductCollectionEntity,
                (new Contact())->setContactId($ItemWhichWasAddedToProductCollection->getContactId())//Contact with id
            );

        foreach ($ProductsIdsToRemove as $removedProdId) {
            $this->assertNotContains($removedProdId, $productIdsFromApplicationAfterDelete);

            //check that removed ids was previously in collection of product which was prepared to add:
            $this->assertContains($removedProdId, $addedProductIds);

            //check that removed ids was previously added to application from application response:
            $this->assertContains($removedProdId, $loadedFromAppProductsIdsAfterAdd);
        }
    }
}