<?php

namespace Tests\Unit\Model\Api\V3;

use Exception;
use SALESmanago\Helper\Mapper\Adapter as MapperAdapter;
use SALESmanago\Helper\Mapper\AdapterInterface;
use SALESmanago\Helper\Mapper\Map\ItemEntityInterface;
use SALESmanago\Model\Collections\Api\V3\ProductsCollection;
use Tests\Unit\TestCaseUnit;
use SALESmanago\Entity\Api\V3\CatalogEntityInterface;
use SALESmanago\Entity\Api\V3\Product\ProductEntityInterface;
use SALESmanago\Exception\ApiV3Exception;
use SALESmanago\Model\Api\V3\ProductsModel;
use SALESmanago\Model\Collections\Api\V3\ProductsCollectionInterface;

class ProductsModelTest extends TestCaseUnit
{
    /**
     * Create ProductEntityInterface mock
     *
     * @return ProductEntityInterface
     */
    public function createProductEntityInterfaceMock(): ProductEntityInterface
    {
        $Product = $this->createMock(ProductEntityInterface::class);
        $Product->method('getProductId')->willReturn($this->faker->uuid());
        $Product->method('getName')->willReturn($this->faker->text(20));
        $Product->method('getMainCategory')->willReturn($this->faker->word());
        $Product->method('getCategoryExternalId')->willReturn($this->faker->randomNumber());
        $Product->method('getCategories')->willReturn(
            function () {
                $categories = [];
                for ($i=0; $i<$this->faker->randomNumber(1, 10); $i++) {
                    $categories[] = $this->faker->word();
                }
                return $categories;
            }
        );
        $Product->method('getDescription')->willReturn($this->faker->text(100));
        $Product->method('getPrice')->willReturn($this->faker->randomFloat(2, 1, 1000));
        $Product->method('getUnitPrice')->willReturn($this->faker->randomFloat(2, 1, 1000));
        $Product->method('getDiscountPrice')->willReturn($this->faker->randomFloat(2, 1, 1000));
        $Product->method('getQuantity')->willReturn($this->faker->randomNumber());
        $Product->method('getActive')->willReturn($this->faker->boolean());
        $Product->method('getProductUrl')->willReturn($this->faker->url());
        $Product->method('getMainImageUrl')->willReturn($this->faker->imageUrl());
        $Product->method('getImageUrls')->willReturn(
            function () {
                $imageUrls = [];
                for ($i=0; $i<$this->faker->randomNumber(1, 10); $i++) {
                    $imageUrls[] = $this->faker->imageUrl();
                }
                return $imageUrls;
            }
        );
        $Product->method('getCategories')->willReturn(
            function () {
                $categories = [];
                for ($i=0; $i<$this->faker->numberBetween(1, 10); $i++) {
                    $categories[] = $this->faker->word();
                }
                return $categories;
            }
        );

        $Product->method('jsonSerialize')->willReturn([
            'productId' => $Product->getProductId(),
            'name' => $Product->getName(),
            'mainCategory' => $Product->getMainCategory(),
            'categoryExternalId' => $Product->getCategoryExternalId(),
            'categories' => $Product->getCategories(),
            'description' => $Product->getDescription(),
            'price' => $Product->getPrice(),
            'unitPrice' => $Product->getUnitPrice(),
            'discountPrice' => $Product->getDiscountPrice(),
            'quantity' => $Product->getQuantity(),
            'active' => $Product->getActive(),
            'productUrl' => $Product->getProductUrl(),
            'mainImageUrl' => $Product->getMainImageUrl(),
            'imageUrls' => $Product->getImageUrls()
        ]);

        return $Product;
    }

    /**
     * Test getProductsToUpsert method
     *
     * @covers \SALESmanago\Model\Api\V3\ProductsModel::getProductsToUpsert
     * @return void
     * @throws ApiV3Exception
     */
    public function testGetProductsToUpsert(): void
    {
        $catalogId = $this->faker->uuid;

        $Catalog = $this->createMock(CatalogEntityInterface::class);
        $Catalog->method('getId')->willReturn($catalogId);

        $ProductsCollection = new ProductsCollection();

        $products = [];
        for ($i=0; $i<$this->faker->numberBetween(1, 10); $i++) {
            $ProductEntity = $this->createProductEntityInterfaceMock();
            $ProductsCollection->addItem($ProductEntity);
            $products[] = $ProductEntity->jsonSerialize();
        }

        $ProductsModel = new ProductsModel();
        $result = $ProductsModel->getProductsToUpsert($Catalog, $ProductsCollection);

        $this->assertEquals([
            CatalogEntityInterface::CATALOG_ID => $catalogId,
            ProductsCollectionInterface::PRODUCTS => $products
        ], $result);
    }

    /**
     * Test getProductForUpdateQty method
     *
     * @covers \SALESmanago\Model\Api\V3\ProductsModel::getProductForUpdateQty
     * @return void
     */
    public function testGetProductForUpdateQty(): void
    {
        $Catalog = $this->createMock(CatalogEntityInterface::class);

        $catalogId = $this->faker->uuid;
        $Catalog->method('getId')->willReturn($catalogId);

        $ProductsModel = new ProductsModel();

        $productId = $this->faker->randomNumber;
        $productQty = $this->faker->randomNumber;

        $result = $ProductsModel->getProductForUpdateQty($Catalog, $productId, $productQty);

        $this->assertEquals([
            CatalogEntityInterface::CATALOG_ID => $catalogId,
            'products' => [
                [
                    'productId' => $productId,
                    'quantity'  => $productQty
                ]
            ]
        ], $result);
    }

    /**
     * Test getProductsForUpdateQuantities method
     *
     * @covers \SALESmanago\Model\Api\V3\ProductsModel::getProductsForUpdateQuantities
     * @return void
     * @throws ApiV3Exception
     */
    public function testGetProductsForUpdateQuantities(): void
    {
        $catalogId = $this->faker->uuid;

        $Catalog = $this->createMock(CatalogEntityInterface::class);
        $Catalog->method('getId')->willReturn($catalogId);

        $ProductsCollection = new ProductsCollection();

        $products = [];
        for ($i=0; $i<$this->faker->numberBetween(1, 10); $i++) {
            $ProductEntity = $this->createProductEntityInterfaceMock();
            $ProductsCollection->addItem($ProductEntity);
            $products[] = ['productId' => $ProductEntity->getProductId(), 'quantity' => $ProductEntity->getQuantity()];
        }

        $ProductsModel = new ProductsModel();
        $result = $ProductsModel->getProductsForUpdateQuantities($Catalog, $ProductsCollection);

        $this->assertEquals([
            CatalogEntityInterface::CATALOG_ID => $catalogId,
            ProductsCollectionInterface::PRODUCTS => $products
        ], $result);
    }

    /**
     * Test get mapped product entity
     *
     * @covers ProductsModel::getMappedProductToUpsert
     * @throws Exception
     */
    public function testGetMappedProductToUpsertSuccess(): void
    {
        $ExampleOfProduct = $this->createUniversalObject($this->generateDummyProductArray());

        $ProductsModel = new ProductsModel();

        $Catalog = $this->createCatalogMock();

        $map = json_encode($this->generateMapArray());
        $adapter = $this->createAdapterMock();

        $productsUpsertArray = $ProductsModel->getMappedProductToUpsert($Catalog, $map, $adapter, $ExampleOfProduct);

        $this->assertArrayHasKey(CatalogEntityInterface::CATALOG_ID, $productsUpsertArray);
        $this->assertArrayHasKey(ProductsCollectionInterface::PRODUCTS, $productsUpsertArray);

        //check if product entity has been adapted, and if description is concatenated from price, quantity and description:
        $this->assertStringContainsString($ExampleOfProduct->getDescription(), $productsUpsertArray[ProductsCollectionInterface::PRODUCTS][0]['description']);
    }

    /**
     * Creates catalog mock
     *
     * @return CatalogEntityInterface
     */
    protected function createCatalogMock():CatalogEntityInterface
    {
        $Catalog = $this->createMock(CatalogEntityInterface::class);
        $catalogId = $this->faker->uuid;
        $Catalog->method('getId')->willReturn($catalogId);

        return $Catalog;
    }

    /**
     * Generate dummy product array
     *
     * @return array
     */
    protected function generateDummyProductArray(): array
    {
        //create dummy product array with key value pairs:
        return [
            'id' => $this->faker->numberBetween(1, 1000),
            'name' => $this->faker->sentence($this->faker->numberBetween(1, 5)),
            'description' => $this->faker->text($this->faker->numberBetween(10, 100)),
            'price' => $this->faker->randomFloat(2, 1, 10000),
            'quantity' => $this->faker->numberBetween(1, 100),
            'category' => $this->faker->word,
            'brand' => $this->faker->word,
            'url' => $this->faker->url,
            'image' => $this->faker->url . "/{$this->faker->word}.jpg",
            'created_at' => $this->faker->dateTime()->format('Y-m-d H:i:s'),
            'updated_at' => $this->faker->dateTime()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Create universal object
     *
     * @param array $fields
     * @return object
     */
    protected function createUniversalObject(array $fields): object
    {
        return new class($fields) {

            public function __construct($productFieldsArray = [])
            {
                //set dynamic fields
                foreach ($productFieldsArray as $key => $value) {
                    $method = 'set' . ucfirst($key);
                    $this->$method($value);
                }
            }

            /**
             * Magic method to get and set properties
             *
             * @param $name
             * @param $arguments
             * @return $this|null|mixed
             */
            public function __call($name, $arguments)
            {
                $action = substr($name, 0, 3);

                switch ($action) {
                    case 'get':
                        return $this->{lcfirst(substr($name, 3))};
                    case 'set':
                        $this->{lcfirst(substr($name, 3))} = $arguments[0];
                        return $this;
                    default:
                        return null;
                }
            }
        };
    }

    /**
     * Create adapter mock
     *
     * @return AdapterInterface
     */
    protected function createAdapterMock(): AdapterInterface
    {
        return new class() extends MapperAdapter {

            /**
             * Map adapter method to field
             *
             * @var array $adaptedFields
             */
            public array $adaptedFields = [
                'description' => 'adaptDescription',
            ];

            /**
             * Assign modified value to object
             *
             * @param mixed $toObject
             * @param $objMethod
             * @param ItemEntityInterface $mapItem
             * @param $fromObject
             * @return mixed
             */
            public function adaptDescription($toObject, $objMethod, $mapItem, $fromObject)
            {
                //set sample description for tests as concatenation of price, quantity and description:
                return $toObject->setDescription(
                    $fromObject->getPrice() . $fromObject->getQuantity() . $fromObject->getDescription()
                );
            }
        };
    }

    /**
     * Generate map
     *
     * @return array
     */
    public function generateMapArray(): array
    {
        //generate map for static keys:
        return [
            'description'       => [
                'name'  => 'description',
                'value' => 'configuration_description',
                'label' => 'Product Configuration Description',
            ],
        ];
    }
}