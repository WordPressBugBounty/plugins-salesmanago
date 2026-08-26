<?php

namespace SALESmanago\Entity\Api\V3\Product\Collection;

interface EntityInterface
{
    /**
     * Set uuid for ProductCollectionEntity
     * @param string $uuid
     * @return EntityInterface
     */
    public function setUuid(string $uuid): EntityInterface;

    /**
     * Get uuid for ProductCollectionEntity
     * @return string|null
     */
    public function getUuid(): ?string;

    /**
     * Set name for product collection
     *
     * @param string $name
     * @return EntityInterface
     */
    public function setName(string $name): EntityInterface;

    /**
     * Get name for product collection
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Set product catalog id
     *
     * @param string $productCatalogId
     * @return EntityInterface
     */
    public function setProductCatalogId(string $productCatalogId): EntityInterface;

    /**
     * Get product catalog id
     *
     * @return string|null
     */
    public function getProductCatalogId(): ?string;

    /**
     * @return array;
     */
    public function toArray(): array;
}