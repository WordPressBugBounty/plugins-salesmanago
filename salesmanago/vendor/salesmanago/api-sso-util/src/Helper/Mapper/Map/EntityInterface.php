<?php

namespace SALESmanago\Helper\Mapper\Map;

use JsonSerializable;
use SALESmanago\Helper\JsonDeserializable;

interface EntityInterface extends JsonSerializable, JsonDeserializable
{
    /**
     * Set field
     *
     * @param string $fieldName
     * @param ItemEntityInterface $item
     * @return EntityInterface
     */
    public function setField(string $fieldName, ItemEntityInterface $item): EntityInterface;

    /**
     * Get field
     *
     * @param string $fieldName
     * @return ItemEntityInterface
     */
    public function getField(string $fieldName): ItemEntityInterface;

    /**
     * Get fields
     *
     * @return array
     */
    public function getFields(): array;
}