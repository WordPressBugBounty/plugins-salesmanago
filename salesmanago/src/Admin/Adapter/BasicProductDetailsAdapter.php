<?php

namespace bhr\Admin\Adapter;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use bhr\Includes\GlobalConstant;
use SALESmanago\Helper\Mapper\ProductEntityAdapter;

class BasicProductDetailsAdapter extends ProductEntityAdapter {

    /**
     * Map adapter method to field
     *
     * @var array
     */
    public array $adaptedFields = [
        GlobalConstant::MAP_BRAND        => 'adaptStringDetail',
        GlobalConstant::MAP_MANUFACTURER => 'adaptStringDetail',
        GlobalConstant::MAP_SEASON       => 'adaptStringDetail',
        GlobalConstant::MAP_COLOR        => 'adaptStringDetail',
        GlobalConstant::MAP_DETAIL_1     => 'adaptStringDetail',
        GlobalConstant::MAP_DETAIL_2     => 'adaptStringDetail',
        GlobalConstant::MAP_DETAIL_3     => 'adaptStringDetail',
        GlobalConstant::MAP_DETAIL_4     => 'adaptStringDetail',
        GlobalConstant::MAP_DETAIL_5     => 'adaptStringDetail',
    ];

    /**
     * @param mixed $object
     * @param string $objMethod
     * @param ItemEntityInterface $mapItem
     * @param wc_product $fromObject
     *
     * @return object
     */
    public function adaptStringDetails( $object, $objMethod, $mapItem, $fromObject ) {
        return $this->setMappedDetailValueToObject( $object, $objMethod, $mapItem, $fromObject );
    }

    /**
     * @param mixed $object
     * @param string $objMethod
     * @param ItemEntityInterface $mapItem
     * @param wc_product $fromObject
     *
     * @return mixed
     */
    private function setMappedDetailValueToObject($object, $objMethod, $mapItem, $fromObject) {
        $value = $this->getProductAttributeValue( $mapItem->getName(), $fromObject );

        if ( strpos( $objMethod, 'Detail' ) ) {
            //custom detail
            $detailNumber = substr( $objMethod, -1, 1 );
            $object->set( $value, $detailNumber );
        } else {
            //system detail
            $object->$objMethod( $value );
        }

        return $object;
    }

    /**
     * @param string $mapItemName
     * @param wc_product $fromObject
     *
     * @return string
     */
    private function getProductAttributeValue( $mapItemName, $fromObject ) {
        $mappedValue = array();
        $attributes = $fromObject[ 'attributes' ] ?? array();

        foreach ( $attributes as $attributeId => $attributeValue ) {
            if ( $mapItemName == $attributeId ) {
                $mappedValue[] = $attributeValue;
            }
        }

        return implode( ',', $mappedValue );
    }
}