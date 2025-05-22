<?php

namespace SALESmanago\Model\Collections\Api\V3;

use SALESmanago\Entity\Api\V3\Product\ProductEntityInterface;
use SALESmanago\Model\Collections\AbstractCollection;
use SALESmanago\Model\Collections\Collection;

class ProductsCollection extends AbstractCollection implements ProductsCollectionInterface, ProductsIdsCollectionInterface
{
    /**
     * @param ProductEntityInterface $object
     * @return ProductsCollectionInterface
     */
    public function addItem($object): ProductsCollectionInterface
    {
        $this->collection[] = $object;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $answer = [];

        if (empty($this->collection)) {
            return $answer;
        }

        foreach ($this->collection as $collectionItem) {
            $answer[] = $collectionItem->jsonSerialize();
        }

        return $answer;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Return array with product ids
     *
     * @return array
     */
    public function idsToArray(): array
    {
        $answer = [];

        if (empty($this->collection)) {
            return $answer;
        }

        foreach ($this->collection as $collectionItem) {
            $answer[] = $collectionItem->getProductId();
        }

        return $answer;
    }

    /**
     * Return array of product with special attributes
     *
     * @param array $attributes
     * @return array
     */
    public function toSpecialArray(array $attributes): array
    {
        $answer = [];

        if (empty($this->collection)) {
            return $answer;
        }

        foreach ($this->collection as $collectionItem) {
            $arr = [];

            foreach ($attributes as $attribute) {
                $method = 'get' . ucfirst($attribute);
                $arr[$attribute] = $collectionItem->$method();
            }

            $answer[] = $arr;
        }

        return $answer;
    }

    /**
     * Split the collection into array of collections of the specified size
     *
     * @param int $size
     * @return array
     */
    public function chunk(int $size = 100): array
    {
        $chunks = [];

        if (empty($this->collection)) {
            return $chunks;
        }

        foreach (array_chunk($this->collection, $size) as $array) {
            $collection = new self();

            foreach ($array as $item) {
                $collection->addItem($item);
            }

            $chunks[] = $collection;
        }

        return $chunks;
    }
}