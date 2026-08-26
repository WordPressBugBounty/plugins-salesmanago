<?php

namespace bhr\Admin\Model;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use bhr\Admin\Entity\Configuration;
use bhr\Includes\Integrations\Wpml\WpmlLocationResolver;
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
	 * Normalizes cached Manago AI catalogs into a catalog-id index.
	 *
	 * @return array
	 */
	public function getCachedCatalogsById(): array {
		$catalogs = json_decode(
			$this->adminModel->getConfiguration()->getCatalogs(),
			true
		);
		$catalogs = is_array( $catalogs ) ? $catalogs : [];
		$catalogsById = [];

		foreach ( $catalogs as $catalog ) {
			if ( !is_array( $catalog ) ) {
				continue;
			}

			$rawCatalogId = $catalog['catalogId'] ?? '';
			$rawLocation = $catalog['location'] ?? '';

			if ( !is_string( $rawCatalogId ) || !is_string( $rawLocation ) ) {
				continue;
			}

			$catalogId = sanitize_text_field( $rawCatalogId );
			$location = sanitize_text_field( $rawLocation );

			if ( '' !== $catalogId && '' !== $location ) {
				$catalogsById[ $catalogId ] = [
					'location' => $location
				];
			}
		}

		return $catalogsById;
	}

	/**
	 * Builds sorted catalog rows for the multi-catalog admin view.
	 *
	 * Catalogs assigned to active WPML locations appear before unassignable catalogs.
	 * Each location can have at most one selected catalog.
	 *
	 * @param  array  $languagesByLocation
	 *
	 * @return array
	 */
	public function getMultiCatalogRows( array $languagesByLocation ): array {
		$catalogs = json_decode(
			$this->adminModel->getConfiguration()->getCatalogs(),
			true
		);
		$catalogs = is_array( $catalogs ) ? $catalogs : [];
		$selected = $this->adminModel->getConfiguration()->getActiveCatalogsByLocation();

		$rows = [];

		foreach ( $catalogs as $catalog ) {
			if ( !is_array( $catalog ) ) {
				continue;
			}

			$rawCatalogId = $catalog['catalogId'] ?? '';
			$rawLocation = $catalog['location'] ?? '';

			if ( !is_string( $rawCatalogId ) || !is_string( $rawLocation ) ) {
				continue;
			}

			$catalogId = sanitize_text_field( $rawCatalogId );
			$location = sanitize_text_field( $rawLocation );

			if ( '' === $catalogId || '' === $location ) {
				continue;
			}

			$name = isset( $catalog['name'] ) && is_string( $catalog['name'] )
				? sanitize_text_field( $catalog['name'] )
				: '';

			$currency = isset( $catalog['currency'] ) && is_string( $catalog['currency'] )
				? sanitize_text_field( $catalog['currency'] )
				: '';

			$rows[] = [
				'catalogId'      => $catalogId,
				'name'           => $name,
				'currency'       => $currency,
				'location'       => $location,
				'languages'      => $languagesByLocation[ $location ] ?? [],
				'canSynchronize' => isset( $languagesByLocation[ $location ] ),
				'selected'       => ( $selected[ $location ] ?? '' ) === $catalogId,
			];
		}

		usort( $rows, static function ( array $left, array $right ): int {
			if ( $left['canSynchronize'] !== $right['canSynchronize'] ) {
				return $left['canSynchronize'] ? -1 : 1;
			}

			$location_comparison = strcmp( $left['location'], $right['location'] );

			return 0 !== $location_comparison
				? $location_comparison
				: strcmp( $left['name'], $right['name'] );
		} );

		return $rows;
	}

	/**
	 * Persists one selected catalog ID for each location.
	 *
	 * @param  array  $mapping
	 *
	 * @return void
	 */
	public function saveActiveCatalogsByLocation( array $mapping ): void {
		$this->adminModel->getConfiguration()->setActiveCatalogsByLocation( $mapping );
		$this->adminModel->saveConfiguration();
	}

	/**
	 * Assigns a newly created catalog to its selected location.
	 *
	 * @param  string  $location
	 * @param  string  $catalogId
	 *
	 * @return void
	 */
	public function assignCatalogToLocation( string $location, string $catalogId ): void {
		$mapping = $this->adminModel->getConfiguration()->getActiveCatalogsByLocation();
		$mapping[ $location ] = $catalogId;

		$this->saveActiveCatalogsByLocation( $mapping );
	}

    /**
     * Build and set Catalog Entity
     *
     * @param array $catalog_data
     * @return void
     */
	public function buildCatalogEntity( $catalog_data ) {
		$location = ! empty( $catalog_data['location'] )
			? $catalog_data['location']
			: WpmlLocationResolver::resolve(
				$this->adminModel->getConfiguration()->getLocation(),
				$this->adminModel->getConfiguration()->getMultilocations(),
				$this->adminModel->getPlatformSettings()->isWpmlMultilocationEnabled()
			);

		$this->CatalogEntity
			->setName( $catalog_data['name'] )
			->setLocation( $location )
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
	            'id' => $result[ 'attribute_id' ],
	            'type' => 'attribute'
			];
		}

		return $attributes;
	}

    /**
     * @deprecated since 3.9.0
     *
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
	 * @deprecated since 3.9.0
	 *
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
