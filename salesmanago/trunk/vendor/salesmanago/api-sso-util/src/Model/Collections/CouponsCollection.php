<?php

namespace SALESmanago\Model\Collections;

use SALESmanago\Entity\Contact\Coupon;
use SALESmanago\Exception\Exception;
use SALESmanago\Model\CouponModel;

class CouponsCollection extends AbstractCollection
{

    /**
     * @throws Exception
     * @param Coupon $object
     * @return AbstractCollection|void
     */
    public function addItem($object)
    {
        if(!($object instanceof Coupon)) {
            throw new Exception('Not right entity type');
        }

        $this->collection[] = $object;
        return $this;
    }

    /**
     * Parse Collection to array
     * @return array
     */
    public function toArray(): array
    {
        $coupons = [];

        if (!$this->isEmpty()) {
            array_walk($this->collection, function ($item, $key) use (&$coupons) {
                if (!empty(CouponModel::toArray($item))) {
                    $coupons[] = CouponModel::toArray($item);
                }
            });
        }

        return $coupons;
    }
}