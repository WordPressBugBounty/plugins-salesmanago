<?php

namespace SALESmanago\Entity\Api\V3\ProductCollection;

use SALESmanago\Entity\AbstractEntity;

/**
 * @deprecated since 3.2.0
 * @see \SALESmanago\Entity\Api\V3\Product\Collection\Entity
 */
class Entity extends AbstractEntity implements EntityInterface
{
    /**
     * @var string uuid
     */
    private $uuid;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var null|string
     */
    private $productCatalogId;

    /**
     * @inheritDoc
     */
    public function setUuid(string $uuid): EntityInterface
    {
        $this->uuid = $uuid;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * @param string $name
     * @return EntityInterface
     */
    public function setName(string $name): EntityInterface
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name'              => $this->name,
            'uuid'              => $this->uuid,
            'productCatalogId'  => $this->productCatalogId,
        ];
    }

    /**
     * @param string $productCatalogId
     * @return EntityInterface
     */
    public function setProductCatalogId(string $productCatalogId): EntityInterface
    {
        $this->productCatalogId = $productCatalogId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getProductCatalogId(): ?string
    {
        return $this->productCatalogId;
    }
}