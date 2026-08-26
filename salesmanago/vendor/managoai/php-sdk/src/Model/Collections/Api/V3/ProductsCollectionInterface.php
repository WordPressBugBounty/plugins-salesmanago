<?php

namespace SALESmanago\Model\Collections\Api\V3;

use SALESmanago\Entity\Api\V3\Product\ProductEntityInterface;
use SALESmanago\Model\Collections\Collection;

interface ProductsCollectionInterface extends Collection
{
    const PRODUCTS = 'products';

    /**
     * @param ProductEntityInterface $object
     * @return ProductsCollectionInterface
     */
    public function addItem($object): ProductsCollectionInterface;

    /**
     * @return array
     */
    public function toArray(): array;

    /**
     * @return array
     */
    public function jsonSerialize(): array;

    /**
     * Return array with products with special attributes
     *
     * @param array $attributes
     * @return array
     */
    public function toSpecialArray(array $attributes): array;
}