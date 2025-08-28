<?php

namespace bhr\Admin\Model;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use bhr\Admin\Entity\Configuration;
use SALESmanago\Entity\Api\V3\CatalogEntity;

class ProductCatalogModel extends AbstractModel {

	/**
	 * @var AdminModel
	 */
	private $adminModel;

	/**
	 * @var CatalogEntity
	 */
	private $CatalogEntity;

	/**
	 * @param AdminModel $adminModel
	 */
	public function __construct( $adminModel ) {
		parent::__construct();
		$this->adminModel    = $adminModel;
		$this->CatalogEntity = new CatalogEntity();
		if ( ! function_exists( 'get_woocommerce_currency' ) ) {
				Helper::loadSMPluginLast();
		}
	}

	/**
	 * Save Api v3 key to Configuration
	 *
	 * @param string $apiV3Key
	 * @return void
	 */
	public function saveApiV3Key( $apiV3Key ) {
		Configuration::getInstance()->setApiV3Key( trim( $apiV3Key ) );
		$this->adminModel->saveConfiguration();
	}

	/**
	 * Save Product Catalogs to Configuration
	 *
	 * @param array $catalogs
	 */
	public function saveCatalogs( $catalogs ) {
		$collection = array();

		foreach ( $catalogs as $Catalog ) {
			$collection[] = $Catalog->jsonSerialize();
		}

		Configuration::getInstance()->setCatalogs( json_encode( $collection ) );
		$this->adminModel->saveConfiguration();
	}

    /**
     * Build and set Catalog Entity
     *
     * @param array $catalog_data
     * @return void
     */
	public function buildCatalogEntity( $catalog_data ) {
		$this->CatalogEntity
			->setName( $catalog_data['name'] )
			->setLocation( Configuration::getInstance()->getLocation() )
			->setSetAsDefault( (bool) $catalog_data['recommendation_frames'] )
			->setCurrency( $catalog_data['currency'] );
	}

	/**
	 * @return CatalogEntity
	 */
	public function getCatalogEntity() {
		return $this->CatalogEntity;
	}

	/**
	 * @param string $catalog
	 * @return void
	 */
	public function setActiveCatalog( $catalog ) {
		Configuration::getInstance()->setActiveCatalog( $catalog );
		$this->adminModel->saveConfiguration();
	}

	/**
	 * @return array
	 */
	public function getAttributesFromDb() {
		$query = "SELECT * FROM {$this->db->prefix}woocommerce_attribute_taxonomies";
		$results = $this->db->get_results( $query, ARRAY_A );

		$attributes = [];
		foreach ( $results as $result ) {
            $attributes[] = [
                'name' => $result[ 'attribute_name' ],
                'value' => '',
                'label' => $result[ 'attribute_label' ],
			];
		}

		return $attributes;
	}

    /**
     * @return array
     */
    public function getCustomAttributesFromDb() {
        $query = "
            SELECT post_id, meta_value AS attributes
            FROM {$this->db->prefix}postmeta
            WHERE meta_key = '_product_attributes'
            ";

        $results = $this->db->get_results( $query, ARRAY_A );
        $customAttributes = array();

        foreach ( $results as $product ) {
            $attributes = maybe_unserialize( $product[ 'attributes' ] );

            if ( is_array( $attributes ) ) {
                foreach ( $attributes as $attribute ) {
                    if ( isset( $attribute[ 'is_taxonomy' ]) && !$attribute[ 'is_taxonomy' ]) {
                        $customAttributes[] = [
                            'name' => $attribute['name'],
                            'value' => $attribute['value'],
                            'label' => $attribute['name'],
                        ];
                    }
                }
            }
        }

        return $customAttributes;
    }

	/**
	 * @param array $attributesArray
	 *
	 * @return array
	 */
	public function getAttributesNamesFromArray( array $attributesArray ) {
		$attributesNames = array();

		foreach ( $attributesArray as $attribute ) {
			if ( isset( $attribute[ 'name' ] ) ) {
				$attributesNames[] = $attribute[ 'name' ];
			}
		}

        sort( $attributesNames );

		return $attributesNames;
	}
}
