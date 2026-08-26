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
use SALESmanago\Entity\UnionConfigurationEntity;
use SALESmanago\Exception\ApiV3Exception;
use SALESmanago\Exception\Exception;
use SALESmanago\Model\Collections\Api\V3\ProductsCollection;
use SALESmanago\Services\Api\V3\CatalogService;
use SALESmanago\Services\Api\V3\ProductService;
use Tests\Feature\ProductCatalogs\CatalogsTestTrait;
use Tests\Feature\TestAbstractBasicV3Service;

class ProductServiceTest extends TestAbstractBasicV3Service
{
    use ProductTestTrait;
    use CatalogsTestTrait;

    /**
     * @throws Exception
     * @throws ApiV3Exception
     */
    public function testUpsertProductsSuccess()
    {
        $countProds = $this->faker->numberBetween(1, 100);//up to 100 products per request

        //create products collection
        $ProductsCollection = $this->createProductsCollection(
            $countProds,
            function () {
                return $this->createProduct();
            }
        );

        //get or create catalog
        $Catalog = $this->getCatalogToUpsertProducts();

        $this->createConfigurationEntity();
        $ProductService = new ProductService(ConfigurationEntity::getInstance());

        $response = $ProductService->upsertProducts($Catalog, $ProductsCollection);

        $this->assertArrayNotHasKey('problems', $response);
        $this->assertArrayHasKey('requestId', $response);
        $this->assertArrayHasKey('productIds', $response);
    }

    /**
     * @throws Exception
     * @throws ApiV3Exception
     */
    public function testUpsertProductsWithSlashesInURLSuccess()
    {
        $countProds = $this->faker->numberBetween(1, 100);//up to 100 products per request

        //create products collection
        $ProductsCollection = $this->createProductsCollection(
            $countProds,
            function () {
               return $this->createProductWithDoubleSlashesInUrl();
            }
        );

        //get or create catalog
        $Catalog = $this->getCatalogToUpsertProducts();

        $this->createConfigurationEntity();
        $ProductService = new ProductService(ConfigurationEntity::getInstance());

        $response = $ProductService->upsertProducts($Catalog, $ProductsCollection);

        $this->assertArrayNotHasKey('problems', $response);
        $this->assertArrayHasKey('requestId', $response);
        $this->assertArrayHasKey('productIds', $response);
    }

    /**
     * Test throwing ApiV3Exception in case of API SM returns response with bad request data validations
     * @return void
     * @throws ApiV3Exception
     * @throws Exception
     */
    public function testUpsertProductsFailThrowApiV3ExceptionAfterApiResponse()
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
     * @return void
     * @throws ApiV3Exception
     * @throws Exception
     */
    public function testUpsertProductsFailThrowApiV3ExceptionWithNullErrorCodeAfterApiResponse()
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
            $this->assertEquals(400, $e->getCode());
        }
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
     * @return ProductEntity|ProductEntityInterface
     */
    protected function createProductWithDoubleSlashesInUrl() {
        $Product = $this->createProduct();
        $url = $this->createUrlWithDoubleSlashes();
        return $Product->setProductUrl($url);
    }

    /**
     * Creates url with double slashes for test purpose;
     *
     * @return string
     */
    protected function createUrlWithDoubleSlashes(): string
    {
        $fakerUrl = $this->faker->url;
        $position = 0;
        $positions = [];

        while ($position || $position === 0) {
            if ($position != 0) {
                $positions[] = $position;
            }
            $position = strpos($fakerUrl, '/', $position+1);
        }

        $finalPosition = empty($positions)
            ? strlen($fakerUrl)+1// get max position to append slash
            : $positions[array_rand($positions)];

        return substr_replace($fakerUrl, '//', $finalPosition, 1);
    }
}
