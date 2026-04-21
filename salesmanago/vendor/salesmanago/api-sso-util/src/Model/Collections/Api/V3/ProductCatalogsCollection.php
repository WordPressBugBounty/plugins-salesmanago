<?php

namespace SALESmanago\Model\Collections\Api\V3;

use SALESmanago\Entity\Api\V3\CatalogEntity;
use SALESmanago\Entity\Api\V3\CatalogEntityInterface;
use SALESmanago\Model\Collections\AbstractCollection;

class ProductCatalogsCollection extends AbstractCollection
{
    public ?CatalogEntity $Catalog = null;

    /**
     * @param CatalogEntity $Catalog
     * @return ProductCatalogsCollection
     */
    public function setCatalog(CatalogEntity $Catalog): ProductCatalogsCollection {
        $this->Catalog = $Catalog;

        return $this;
    }

    /**
     * @return CatalogEntity|null
     */
    public function getCatalog(): ?CatalogEntity {
        return $this->Catalog;
    }

    /**
     * Adds an item to the product catalogs collection.
     *
     * @param mixed $object
     * @return ProductCatalogsCollection
     */
    public function addItem($object): ProductCatalogsCollection {
        $this->collection[] = [
            CatalogEntityInterface::CATALOG_ID => isset($this->Catalog) ? $this->Catalog->getId() : null,
            ProductsCollectionInterface::PRODUCTS => $object
        ];

        return $this;
    }

    /**
     * Adds a catalog with its associated products to the product catalogs collection.
     *
     * @param CatalogEntity $catalog
     * @param ProductsCollection $productCollection
     * @return ProductCatalogsCollection
     */
    public function addCatalog(CatalogEntity $catalog, ProductsCollection $productsCollection): ProductCatalogsCollection {
        $this->collection[] = [
            CatalogEntityInterface::CATALOG_ID => $catalog->getId(),
            ProductsCollectionInterface::PRODUCTS => $productsCollection
        ];

        return $this;
    }

    /**
     * Converts the collection to an array representation.
     *
     * @return array
     */
    public function toArray(): array {
        $answer = [];

        if (empty($this->collection)) {
            return $answer;
        }

        foreach ($this->collection as $collectionItem) {
            $catalogId = $collectionItem[CatalogEntityInterface::CATALOG_ID];
            $products = $collectionItem[ProductsCollectionInterface::PRODUCTS]->toArray();
            $answer[] = [
                CatalogEntityInterface::CATALOG_ID => $catalogId,
                ProductsCollectionInterface::PRODUCTS => $products
            ];
        }

        return $answer;
    }
}
