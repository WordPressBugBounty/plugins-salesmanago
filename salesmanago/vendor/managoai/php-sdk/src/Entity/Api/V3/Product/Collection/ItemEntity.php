<?php

namespace SALESmanago\Entity\Api\V3\Product\Collection;

use SALESmanago\Entity\AbstractEntity;
use SALESmanago\Helper\ArrayableInterface;
use SALESmanago\Model\Collections\Api\V3\ProductsIdsCollectionInterface;

class ItemEntity extends AbstractEntity implements ItemEntityInterface, ArrayableInterface
{
    /**
     * @var string - salesmanago contact id;
     */
    protected $contactId;

    /**
     * @var array - of products ids;
     */
    protected $productsIds;

    /**
     * Sets ContactId for Product Collection Item
     *
     * @param string $contactId
     * @return ItemEntityInterface
     */
    public function setContactId(string $contactId): ItemEntityInterface
    {
        $this->contactId = $contactId;
        return $this;
    }

    /**
     * Gets ContactId for Product Collection Item
     *
     * @return string
     */
    public function getContactId(): string
    {
        return $this->contactId;
    }

    /**
     * Set products ids to Product Collection Item;
     *
     * @param ProductsIdsCollectionInterface $productsCollection
     * @return ItemEntityInterface
     */
    public function setProducts(ProductsIdsCollectionInterface $productsCollection): ItemEntityInterface
    {
        $this->productsIds = $productsCollection->idsToArray();
        return $this;
    }

    /**
     * Set products ids to Product Collection Item
     *
     * @param array $ids
     * @return ItemEntityInterface
     */
    public function setProductsIds(array $ids): ItemEntityInterface
    {
        $this->productsIds = $ids;
        return $this;
    }

    /**
     * Gets products ids for Product Collection item
     *
     * @return array
     */
    public function getProducts(): array {
        return $this->productsIds;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'contactId'  => $this->contactId,
            'productIds' => $this->productsIds
        ];
    }
}