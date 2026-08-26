<?php

namespace SALESmanago\Entity\Api\V3\ProductCollection;

use SALESmanago\Model\Collections\Api\V3\ProductsCollection;
use SALESmanago\Model\Collections\Api\V3\ProductsIdsCollectionInterface;

/**
 * @deprecated since 3.2.0
 * @see \SALESmanago\Entity\Api\V3\Product\Collection\ItemEntityInterface
 */
interface ItemEntityInterface
{
    /**
     * Sets ContactId for Product Collection Item
     *
     * @param string $contactId
     * @return ItemEntityInterface
     */
    public function setContactId(string $contactId): ItemEntityInterface;

    /**
     * Gets ContactId for Product Collection Item
     *
     * @return string
     */
    public function getContactId(): string;

    /**
     * Set products ids to Product Collection Item;
     *
     * @param ProductsIdsCollectionInterface $productsCollection
     * @return ItemEntityInterface
     */
    public function setProducts(ProductsIdsCollectionInterface $productsCollection): ItemEntityInterface;

    /**
     * Set products ids to Product Collection Item
     *
     * @param array $ids
     * @return ItemEntityInterface
     */
    public function setProductsIds(array $ids): ItemEntityInterface;

    /**
     * Gets products ids for Product Collection item
     *
     * @return array - of product ids
     */
    public function getProducts(): array;
}