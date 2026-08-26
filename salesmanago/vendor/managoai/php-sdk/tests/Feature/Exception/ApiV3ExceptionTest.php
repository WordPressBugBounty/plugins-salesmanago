<?php

namespace Tests\Feature\Services\Api\V3;

use Faker;
use SALESmanago\Entity\Api\V3\CatalogEntity;
use SALESmanago\Entity\Api\V3\CatalogEntityInterface;
use SALESmanago\Entity\Api\V3\ConfigurationEntity;
use SALESmanago\Entity\Api\V3\Product\CustomDetailsEntity;
use SALESmanago\Entity\Api\V3\Product\ProductEntity;
use SALESmanago\Entity\Api\V3\Product\ProductEntityInterface;
use SALESmanago\Entity\Api\V3\Product\SystemDetailsEntity;
use SALESmanago\Exception\ApiV3Exception;
use SALESmanago\Exception\Exception;
use SALESmanago\Model\Collections\Api\V3\ProductsCollection;
use SALESmanago\Services\Api\V3\CatalogService;
use SALESmanago\Services\Api\V3\ProductService;
use Tests\Feature\ProductCatalogs\CatalogsTestTrait;
use Tests\Feature\TestAbstractBasicV3Service;

class ApiV3ExceptionTest extends TestAbstractBasicV3Service
{
    use CatalogsTestTrait;
    use ProductTestTrait;

    /**
     * Test throwing ApiV3Exception in case of API SM returns response with bad request data validations
     *
     * @return void
     * @throws ApiV3Exception
     * @throws Exception
     */
    public function testThrowApiV3ExceptionAfterApiResponseSuccess()
    {
        $countProds = $this->faker->numberBetween(1, 100);//up to 100 products per request

        //create products collection
        $ProductsCollection = $this->createProductsCollection(
            $countProds,
            function () {
                return $this->createBadProduct();
            }
        );

        //get or create catalog
        $Catalog = $this->getCatalogToUpsertProducts();

        $this->createConfigurationEntity();
        $ProductService = new ProductService(ConfigurationEntity::getInstance());

        $this->expectException(ApiV3Exception::class);
        $ProductService->upsertProducts($Catalog, $ProductsCollection);
    }

    /**
     * Testing throwing ApiV3Exception with Nullable code for grouped API SM response
     *
     * @return void
     * @throws ApiV3Exception
     * @throws Exception
     */
    public function testThrowApiV3ExceptionAfterUpsertProductsFail()
    {
        $countProds = $this->faker->numberBetween(1, 100);//up to 100 products per request

        //create products collection
        $ProductsCollection = $this->createProductsCollection(
            $countProds,
            function () {
                return $this->createBadProduct();
            }
        );

        //get or create catalog
        $Catalog = $this->getCatalogToUpsertProducts();

        $this->createConfigurationEntity();
        $ProductService = new ProductService(ConfigurationEntity::getInstance());

        try {
            $ProductService->upsertProducts($Catalog, $ProductsCollection);
        } catch (ApiV3Exception $e) {
            $this->assertNotEmpty($e->getCombined());
            $this->assertNotEmpty($e->getCodes());
            $this->assertNotEmpty($e->getMessages());
            $this->assertEquals(400, $e->getCode());
        }
    }

    /**
     * Checks after refactor getAllViewMessages() method if new method getFields()
     * correctly set required field
     */
    public function testGetRequiredFieldsAfterFailedCatalogUpsert()
    {
        try {
            $this->createConfigurationEntity();
            $Catalog = new CatalogEntity();

            $Catalog
                ->setName('')
                ->setCurrency('')
                ->setLocation('time'.time())
                ->setSetAsDefault($this->faker->boolean());
            //create catalog service to get data
            $CatalogService = new CatalogService(ConfigurationEntity::getInstance());
            $CatalogService->createCatalog($Catalog);

        } catch (ApiV3Exception $e) {
            $this->assertNotEmpty($e->getFields());
            $this->assertNotEmpty($e->getAllViewMessages());
            $this->assertSame(400, $e->getCode());
        }
    }

    public function testGetViewMessagesWithFieldWhileExportProductsSuccess()
    {
        $countProds = $this->faker->numberBetween(1, 100);//up to 100 products per request

        //create products collection
        $ProductsCollection = $this->createProductsCollection(
            $countProds,
            function () {
                return $this->createBadProduct();
            }
        );

        //get or create catalog
        $Catalog = $this->getCatalogToUpsertProducts();

        $this->createConfigurationEntity();
        $ProductService = new ProductService(ConfigurationEntity::getInstance());

        try {
            $ProductService->upsertProducts($Catalog, $ProductsCollection);
        } catch (ApiV3Exception $e) {
          $this->assertIsArray($e->getAllViewMessages());
        }
    }

    public function testGetViewMessagesWithFieldWhileCreateCatalogSuccess()
    {
        $countProds = $this->faker->numberBetween(1, 100);//up to 100 products per request

        //create catalog service:
        $CatalogService = $this->createCatalogService();

        //create CatalogEntity with not valid data:
        $Catalog = $this->createCatalogEntityWithNotValidDummyData();

        try {
            //upsert catalog:
            $CatalogService->createCatalog($Catalog);
        } catch (ApiV3Exception $e) {
            $this->assertIsArray($e->getAllViewMessages());
            $this->assertEquals(2, count($e->getAllViewMessages()));//2 - expected errors
        }
    }

    /**
     * Test get log message success
     *
     * @return void
     * @throws ApiV3Exception
     * @throws Exception
     */
    public function testGetLogMessagesWhileExportProductsSuccess()
    {
        $countProds = $this->faker->numberBetween(1, 100);//up to 100 products per request

        //create products collection
        $ProductsCollection = $this->createProductsCollection(
            $countProds,
            function () {
                return $this->createBadProduct();
            }
        );

        //get or create catalog
        $Catalog = $this->getCatalogToUpsertProducts();

        $this->createConfigurationEntity();
        $ProductService = new ProductService(ConfigurationEntity::getInstance());

        try {
            $ProductService->upsertProducts($Catalog, $ProductsCollection);
        } catch (ApiV3Exception $e) {
            $this->assertIsString($e->getAllLogMessages());
        }
    }

    /**
     * Test get log message success
     *
     * @return void
     * @throws ApiV3Exception
     * @throws Exception
     */
    public function testGetLogMessagesWithoutDuplicatesWhileExportProductsSuccess()
    {
        $countProds = $this->faker->numberBetween(1, 100);//up to 100 products per request

        //create products collection
        $ProductsCollection = $this->createProductsCollection(
            $countProds,
            function () {
                return $this->createBadProduct();
            }
        );

        //get or create catalog
        $Catalog = $this->getCatalogToUpsertProducts();

        $this->createConfigurationEntity();
        $ProductService = new ProductService(ConfigurationEntity::getInstance());

        try {
            $ProductService->upsertProducts($Catalog, $ProductsCollection);
        } catch (ApiV3Exception $e) {
            $allLogMessages = $e->getAllLogMessages();
            $allLogMessagesWithoutDuplicates = $e->getAllLogMessages(true);

            $this->assertIsString($allLogMessages);
            $this->assertIsString($allLogMessagesWithoutDuplicates);

            $allLogMessagesLen = strlen($allLogMessages);
            $allLogMessagesWithoutDuplicatesLen = strlen($allLogMessagesWithoutDuplicates);

            //check if len of log without duplicates smaller than standard log:
            $this->assertGreaterThan($allLogMessagesWithoutDuplicatesLen, $allLogMessagesLen);
        }
    }

    /**
     * @return mixed|CatalogEntity|CatalogEntityInterface
     * @throws ApiV3Exception
     * @throws Exception
     */
    protected function getCatalogToUpsertProducts()
    {
        //create ConfigurationEntity singleton
        $this->createConfigurationEntity();

        //create catalog service to get data
        $CatalogService = new CatalogService(ConfigurationEntity::getInstance());

        $catalogsArr = $CatalogService->getCatalogs();

        if (!empty($catalogsArr)) {
           return $catalogsArr[array_rand($catalogsArr, 1)];
        }

        $Catalog = $this->createCatalog($CatalogService);

        return $Catalog;
    }

    /**
     * @param CatalogService $CatalogService
     *
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
     * @return ProductEntity
     */
    protected function createBadProduct()
    {
        $this->faker = Faker\Factory::create();
        $Product = new ProductEntity();

        //create system details:
        $SystemDetails = $this->createSystemDetails();

        //create custom details:
        $CustomDetails = $this->createCustomDetails();

        $productId = hash('sha512', $this->faker->uuid);

        $Product
            ->setProductId($productId)
            ->setActive(true)
            ->setAvailable(true)
            ->setCategories($this->faker->words($this->faker->numberBetween(1, 5)))
            ->setCategoryExternalId($this->faker->uuid)
            ->setCustomDetails($CustomDetails)
            ->setDescription(implode(', ', $this->faker->words()))
            ->setDiscountPrice($this->faker->randomNumber())
            ->setProductUrl($this->faker->words(1)[0])
            ->setMainImageUrl($this->faker->words(1)[0])
            ->setImageUrls($this->createImagesUrls())
            ->setMainCategory($this->faker->words(1)[0])
            ->setName($this->faker->text(260))
            ->setPrice($this->faker->randomFloat(6))
            ->setUnitPrice($this->faker->randomFloat(6))
            ->setSystemDetails($SystemDetails)
            ->setQuantity($this->faker->randomFloat(6));

        return $Product;
    }

    /**
     * @return ProductEntityInterface
     */
    protected function createProduct()
    {
        $this->faker = Faker\Factory::create();
        $Product = new ProductEntity();

        //create system details:
        $SystemDetails = $this->createSystemDetails();

        //create custom details:
        $CustomDetails = $this->createCustomDetails();

        $productId = $this->faker->uuid;
        $productId = (count_chars($productId) > 32)
            ? substr($productId, 0, 31)
            : $productId;

        $Product
            ->setProductId($productId)
            ->setActive(true)
            ->setAvailable(true)
            ->setCategories($this->faker->words($this->faker->numberBetween(1, 5)))
            ->setCategoryExternalId($this->faker->uuid)
            ->setCustomDetails($CustomDetails)
            ->setDescription(implode(', ', $this->faker->words()))
            ->setDiscountPrice($this->faker->randomNumber())
            ->setProductUrl($this->faker->imageUrl())
            ->setMainImageUrl($this->faker->imageUrl())
            ->setImageUrls($this->createImagesUrls())
            ->setMainCategory($this->faker->words(1)[0])
            ->setName($this->faker->words(1)[0])
            ->setPrice($this->faker->randomNumber())
            ->setUnitPrice($this->faker->randomNumber())
            ->setSystemDetails($SystemDetails)
            ->setQuantity($this->faker->numberBetween(1, 100000));

        return $Product;
    }

    /**
     * Creates CustomDetails objects
     * @return CustomDetailsEntity
     */
    protected function createCustomDetails()
    {
        $this->faker = Faker\Factory::create();

        $CustomDetails = new CustomDetailsEntity();
        $numberOfDetails = $this->faker->numberBetween(1, 5);

        while ($numberOfDetails) {
            $CustomDetails->set($this->faker->words(1)[0], $numberOfDetails);
            --$numberOfDetails;
        }

        return $CustomDetails;
    }

    /**
     * @return SystemDetailsEntity
     */
    protected function createSystemDetails()
    {
        $this->faker = Faker\Factory::create();

        $SystemDetails = new SystemDetailsEntity();
        $SystemDetails
            ->setBrand($this->faker->words(1)[0])
            ->setManufacturer($this->faker->words(1)[0])
            ->setPopularity($this->faker->numberBetween(1, 100))
            ->setGender($this->faker->randomKey(['-1', '0', '1', '2', '4']))
            ->setSeason($this->faker->randomKey(['spring', 'summer', 'autumn', 'winter']))
            ->setColor($this->faker->colorName)
            ->setBestseller($this->faker->boolean())
            ->setNewProduct($this->faker->boolean());

        return $SystemDetails;
    }

    /**
     * Creates images url
     * @return array
     */
    protected function createImagesUrls()
    {
        $this->faker = Faker\Factory::create();

        $imgs = [];
        $cImages = $this->faker->numberBetween(1, 5);

        for ($i=0; $i < $cImages; $i++) {
            $imgs[] = $this->faker->imageUrl();
        }

        return $imgs;
    }
}
