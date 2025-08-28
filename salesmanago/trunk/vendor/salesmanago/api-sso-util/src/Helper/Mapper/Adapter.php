<?php

namespace SALESmanago\Helper\Mapper;

use SALESmanago\Helper\Mapper\AdapterInterface;
use SALESmanago\Helper\Mapper\Map\ItemEntityInterface;

class Adapter implements AdapterInterface
{
    /**
     * Map adapter method to field
     *
     * @var array $adaptedFields
     */
    public array $adaptedFields = [];


    /**
     * Assign value to object
     *
     * @param mixed $toObject
     * @param mixed $objMethod
     * @param ItemEntityInterface $mapItem
     * @return void
     */
    public function assign(&$toObject, $objMethod, ItemEntityInterface $mapItem)
    {
        $toObject->$objMethod($mapItem->getValue());
        return $toObject;
    }

    /**
     * Get adapted fields
     *
     * @return array
     */
    public function getAdaptedFields(): array
    {
        return $this->adaptedFields;
    }

    /**
     * Get adapted field
     *
     * @param $name
     * @return string|null
     */
    public function getAdapterMethodForField($name): ?string
    {
        if (!empty($this->adaptedFields) && array_key_exists($name, $this->adaptedFields)) {
            return $this->adaptedFields[$name];
        }

        return null;
    }
}
