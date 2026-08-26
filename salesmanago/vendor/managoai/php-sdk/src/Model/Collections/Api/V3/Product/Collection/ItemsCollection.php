<?php

namespace SALESmanago\Model\Collections\Api\V3\Product\Collection;

use SALESmanago\Model\Collections\AbstractCollection;
use SALESmanago\Entity\Api\V3\Product\Collection\ItemEntityInterface;

class ItemsCollection extends AbstractCollection implements ItemsCollectionInterface
{
    const MAX_ITEMS = 1000;//max items that could be added to this collection

    /**
     * @var array - of ItemEntityInterface objets;
     */
    protected $collection;

    /**
     * Add item to collection
     *
     * @param ItemEntityInterface $object
     * @return ItemsCollectionInterface
     */
    public function addItem($object): ItemsCollectionInterface
    {
        $this->collection[] = $object;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getItems(): array
    {
        return $this->collection;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $arr = [];
        foreach ($this->collection as $item) {
            $arr[] = $item->toArray();
        }

        return $arr;
    }
}