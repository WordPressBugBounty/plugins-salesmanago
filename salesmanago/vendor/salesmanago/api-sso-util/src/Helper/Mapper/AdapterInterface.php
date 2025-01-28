<?php

namespace SALESmanago\Helper\Mapper;

use SALESmanago\Helper\Mapper\Map\ItemEntityInterface;

interface AdapterInterface
{
    /**
     * Assign value to object
     *
     * @param $toObject
     * @param string $objMethod
     * @param array $mapItem
     * @return void
     */
    public function assign(&$toObject, string $objMethod, ItemEntityInterface $mapItem);

    /**
     * Get adapted fields
     *
     * @return array
     */
    public function getAdaptedFields(): array;

    /**
     * Get adapter method for field
     *
     * @param string $fieldName
     * @return string
     */
    public function getAdapterMethodForField(string $fieldName): ?string;
}