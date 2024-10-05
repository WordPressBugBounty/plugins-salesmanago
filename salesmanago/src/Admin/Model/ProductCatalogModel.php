<?php

namespace bhr\Admin\Model;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use bhr\Admin\Entity\Configuration;
use SALESmanago\Entity\Api\V3\CatalogEntity;

class ProductCatalogModel {

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
}
