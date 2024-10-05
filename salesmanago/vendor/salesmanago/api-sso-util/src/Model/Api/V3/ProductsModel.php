<?php

namespace SALESmanago\Model\Api\V3;

use SALESmanago\Entity\Api\V3\CatalogEntityInterface;
use SALESmanago\Exception\ApiV3Exception;
use SALESmanago\Model\Collections\Api\V3\ProductsCollectionInterface;

class ProductsModel
{
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
}