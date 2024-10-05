<?php

namespace Tests\Unit\Model\Api\V3;

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
}