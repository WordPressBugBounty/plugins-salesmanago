<?php

namespace SALESmanago\Helper\Mapper\Map;

use SALESmanago\Helper\Mapper\Map\EntityInterface;

class Entity implements EntityInterface
{
    /**
     * @var array map fields
     */
    protected array $fields = [];

    /**
     * @inheritDoc
     */
    public function setField(string $fieldName, ItemEntityInterface $item): EntityInterface
    {
        $this->fields[$fieldName] = $item;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getField(string $fieldName): ItemEntityInterface
    {
        return $this->fields[$fieldName];
    }

    /**
     * @inheritDoc
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        if (empty($this->fields)) {
           return [];
        }

        $result = [];

        foreach ($this->fields as $fieldName => $mapItemEntity) {
            $result[$fieldName] = $mapItemEntity->jsonSerialize();
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public static function jsonDeserialize(string $json): self
    {
        $data = json_decode($json, true);

        $item = new self();

        foreach ($data as $fieldName => $itemData) {
            $item->setField($fieldName, ItemEntity::jsonDeserialize(json_encode($itemData)));
        }

        return $item;
    }
}
