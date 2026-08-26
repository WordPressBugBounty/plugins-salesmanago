<?php

namespace SALESmanago\Model\Api\V3;

use SALESmanago\Entity\Api\V3\CatalogEntityInterface;
use SALESmanago\Entity\Api\V3\Product\ProductEntity;
use SALESmanago\Entity\Api\V3\Product\ProductEntityInterface;
use SALESmanago\Exception\ApiV3Exception;
use SALESmanago\Helper\Mapper\AdapterInterface;
use SALESmanago\Helper\Mapper\Builder;
use SALESmanago\Helper\Mapper\BuilderInterface;
use SALESmanago\Model\Collections\Api\V3\ProductsCollection;
use SALESmanago\Model\Collections\Api\V3\ProductsCollectionInterface;
use Exception;
use SALESmanago\Helper\Mapper\ProductEntityBuilder;

class ProductsModel
{
    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @param CatalogEntityInterface $Catalog
     * @param ProductsCollectionInterface $ProductsCollection
     * @return array
     * @throws ApiV3Exception
     */
    public function getProductsToUpsert(
        CatalogEntityInterface $Catalog,
        ProductsCollectionInterface $ProductsCollection
    ) {
        $catalogId = $Catalog->getId();
        if (empty($catalogId)) {
            throw new ApiV3Exception('Products model: catalog id is empty', 500);
        }

        $productsArray = $ProductsCollection->toArray();

        return [
            CatalogEntityInterface::CATALOG_ID => $Catalog->getId(),
            ProductsCollectionInterface::PRODUCTS => $productsArray
        ];
    }

    /**
     * Create array with product data for update quantity
     *
     * @param CatalogEntityInterface $Catalog
     * @param int $productId
     * @param int $productQty
     * @return array
     */
    public function getProductForUpdateQty(CatalogEntityInterface $Catalog, int $productId, int $productQty): array
    {
        return [
            CatalogEntityInterface::CATALOG_ID => $Catalog->getId(),
            'products' => [
                [
                    'productId' => $productId,
                    'quantity' => $productQty
                ]
            ]
        ];
    }

    /**
     * Create array with products data for update quantities
     *
     * @param CatalogEntityInterface $Catalog
     * @param ProductsCollectionInterface $ProductsCollection
     * @return array
     * @throws ApiV3Exception
     */
    public function getProductsForUpdateQuantities(
        CatalogEntityInterface $Catalog,
        ProductsCollectionInterface $ProductsCollection
    ) {
        $catalogId = $Catalog->getId();
        if (empty($catalogId)) {
            throw new ApiV3Exception('Products model: catalog id is empty', 500);
        }

        $productsArray = $ProductsCollection->toSpecialArray(['productId', 'quantity']);

        return [
            CatalogEntityInterface::CATALOG_ID => $Catalog->getId(),
            ProductsCollectionInterface::PRODUCTS => $productsArray
        ];
    }

    /**
     * Get mapped product entity
     *
     * @param CatalogEntityInterface $Catalog
     * @param string $map
     * @param AdapterInterface $adapter
     * @param mixed $platformProduct
     * @return array
     * @throws Exception
     */
    public function getMappedProductToUpsert(
        CatalogEntityInterface $Catalog,
        string $map,
        AdapterInterface $adapter,
        $platformProduct,
        ?ProductEntityInterface $productEntity = null
    ): array {
        $productEntity = $productEntity ?? new ProductEntity();

        $builder = isset($this->builder) ? $this->builder : new ProductEntityBuilder();
        $ProductEntity = $builder->build($productEntity, $map, $adapter, $platformProduct);

        return [
            CatalogEntityInterface::CATALOG_ID => $Catalog->getId(),
            ProductsCollectionInterface::PRODUCTS => [$ProductEntity->jsonSerialize()]
        ];
    }

    /**
     * Get mapped products collection
     *
     * @param CatalogEntityInterface $Catalog
     * @param array $platformProductsCollection
     * @param string $map
     * @param AdapterInterface $adapter
     * @return array
     * @throws ApiV3Exception
     * @throws Exception
     */
    public function getMappedProductsCollectionArrayToUpsert(
        CatalogEntityInterface $Catalog,
        array $platformProductsCollection,
        string $map,
        AdapterInterface $adapter
    ): array {
        $catalogId = $Catalog->getId();

        if (empty($catalogId)) {
            throw new ApiV3Exception('Products model: catalog id is empty', 500);
        }

        $ProductsCollection = new ProductsCollection();

        $builder = isset($this->builder) ? $this->builder : new ProductEntityBuilder();

        foreach ($platformProductsCollection as $platformProduct) {
            $ProductEntity = $builder->build((new ProductEntity()), $map, $adapter, $platformProduct);
            $ProductsCollection->addItem($ProductEntity);
        }

        return [
            CatalogEntityInterface::CATALOG_ID => $Catalog->getId(),
            ProductsCollectionInterface::PRODUCTS => $ProductsCollection->toArray()
        ];
    }

    /**
     * Set builder
     *
     * @param Builder $builder
     * @return ProductsModel
     */
    public function setBuilder(BuilderInterface $builder): ProductsModel
    {
        $this->builder = $builder;
        return $this;
    }

    /**
     * Create array with products data for update prices
     *
     * @param CatalogEntityInterface $Catalog
     * @param ProductsCollectionInterface $ProductsCollection
     * @return array
     * @throws ApiV3Exception
     */
    public function getProductsForUpdatePrices(
        CatalogEntityInterface $Catalog,
        ProductsCollectionInterface $ProductsCollection
    ) {
        $catalogId = $Catalog->getId();
        if (empty($catalogId)) {
            throw new ApiV3Exception('Products model: catalog id is empty', 500);
        }

        $productsArray = $ProductsCollection->toSpecialArray(['productId', 'price', 'discountPrice']);

        return [
            CatalogEntityInterface::CATALOG_ID => $Catalog->getId(),
            ProductsCollectionInterface::PRODUCTS => $productsArray
        ];
    }
}
