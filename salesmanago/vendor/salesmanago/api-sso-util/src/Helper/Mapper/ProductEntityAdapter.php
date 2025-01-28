<?php

namespace SALESmanago\Helper\Mapper;

use SALESmanago\Entity\Api\V3\Product\CustomDetailsEntity;
use SALESmanago\Entity\DetailsInterface;
use SALESmanago\Helper\Mapper\AdapterInterface;
use SALESmanago\Helper\Mapper\Map\ItemEntityInterface;
use SALESmanago\Helper\Mapper\Adapter;

class ProductEntityAdapter extends Adapter implements AdapterInterface
{

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
        if ($toObject instanceof DetailsInterface) {
            $detailNumber = substr($objMethod, -1);
            $toObject->set($mapItem->getValue(), $detailNumber);
        } else {
            $toObject->$objMethod($mapItem->getValue());
        }

        return $toObject;
    }
}
