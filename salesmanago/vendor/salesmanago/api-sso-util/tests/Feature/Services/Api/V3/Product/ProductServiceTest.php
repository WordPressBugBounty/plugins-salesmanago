<?php

namespace Tests\Feature\Services\Api\V3\Product;

use SALESmanago\Entity\UnionConfigurationEntity;
use SALESmanago\Exception\ApiV3Exception;
use SALESmanago\Exception\Exception;
use SALESmanago\Services\Api\V3\Product\ProductService;
use Tests\Feature\ProductCatalogs\CatalogsTestTrait;
use Tests\Feature\Services\Api\V3\ProductTestTrait;
use Tests\Feature\TestAbstractBasicV3Service;

class ProductServiceTest extends TestAbstractBasicV3Service
{
    use ProductTestTrait;
    use CatalogsTestTrait;

    /**
     * Test updateQuantities method
     *
     * @throws Exception
     * @throws ApiV3Exception
     */
    public function testUpdateQuantitiesSuccess()
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

        //init configuration:
        $this->initConfWithApiV3();
        $ProductService = new ProductService(UnionConfigurationEntity::getInstance());

        //test method:
        $response = $ProductService->updateQuantities($Catalog, $ProductsCollection);

        //assertions:
        $this->assertArrayNotHasKey('problems', $response);
        $this->assertArrayHasKey('requestId', $response);
    }

    public function testUpdateQtySuccess(): void
    {        //create product:
        $Product = $this->createProduct();

        //get or create catalog
        $Catalog = $this->getCatalogToUpsertProducts();

        //init configuration:
        $this->initConfWithApiV3();
        $ProductService = new ProductService(UnionConfigurationEntity::getInstance());

        //test method:
        $response = $ProductService->updateQty($Catalog, (int) $Product->getProductId(), (int) $Product->getQuantity());

        //assertions:
        $this->assertArrayNotHasKey('problems', $response);
        $this->assertArrayHasKey('requestId', $response);
    }
}