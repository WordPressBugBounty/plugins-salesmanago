<?php

namespace SALESmanago\Model\Collections\Api\V3;

use SALESmanago\Entity\Api\V3\Product\ProductEntityInterface;
use SALESmanago\Model\Collections\Collection;

interface ProductCollectionItemsCollectionInterface extends Collection
{
    /**
     * @param ProductEntityInterface $object
     * @return ProductsCollectionInterface
     */
    public function addItem($object): ProductCollectionItemsCollectionInterface;

    /**
     * Return items added to collection
     *
     * @return array
     */
    public function getItems(): array;

    /**
     * @return array
     */
    public function toArray(): array;
}