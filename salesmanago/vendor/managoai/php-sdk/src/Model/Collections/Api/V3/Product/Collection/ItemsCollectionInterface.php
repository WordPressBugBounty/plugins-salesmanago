<?php

namespace SALESmanago\Model\Collections\Api\V3\Product\Collection;

use SALESmanago\Entity\Api\V3\Product\ProductEntityInterface;
use SALESmanago\Model\Collections\Collection;

interface ItemsCollectionInterface extends Collection
{
    /**
     * @param ProductEntityInterface $object
     * @return ProductsCollectionInterface
     */
    public function addItem($object): ItemsCollectionInterface;

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