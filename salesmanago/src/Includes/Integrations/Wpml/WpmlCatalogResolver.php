<?php

namespace bhr\Includes\Integrations\Wpml;

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves WPML-aware catalog and location information for multi-catalog mode.
 *
 * This class acts as a small service layer over `WpmlLanguageContext` and `WpmlLocationResolver`, providing:
 * - detection of whether WPML multi-catalog mode can be used,
 * - grouping active WPML languages by resolved location,
 * - validation of selectable locations,
 * - lookup of a product's catalog based on its WPML language,
 * - lookup of a selected catalog by resolved location.
 */
class WpmlCatalogResolver {

	private WpmlLanguageContext $context;

	/**
	 * @param  WpmlLanguageContext|null  $context
	 */
	public function __construct( ?WpmlLanguageContext $context = null ) {
		$this->context = $context ?? new WpmlLanguageContext();
	}

	/**
	 * Checks whether WPML multilocation catalog routing is available.
	 *
	 * @param  bool  $enabled
	 *
	 * @return bool
	 */
	public function isMultiCatalogMode( bool $enabled ): bool {
		return $enabled && $this->context->can_resolve_multilocations();
	}

	/**
	 * Returns active WPML languages grouped by their resolved location.
	 *
	 * @param  string  $baseLocation
	 * @param  array  $multilocations
	 * @param  bool  $enabled
	 *
	 * @return array
	 */
	public function getActiveLocations( string $baseLocation, array $multilocations, bool $enabled ): array {
		if ( !$this->isMultiCatalogMode( $enabled ) ) {
			return [];
		}

		$locations = [];

		foreach ( $this->context->get_active_languages() as $language ) {
			$location = WpmlLocationResolver::resolve(
				$baseLocation,
				$multilocations,
				true,
				$language['code']
			);

			$locations[ $location ][] = $language;
		}

		return $locations;
	}

	/**
	 * Checks whether a location can be selected for multi-catalog synchronization.
	 *
	 * Explicit Integration Settings locations are accepted even when WPML language hooks are unavailable
	 * during an Admin POST. Generated defaults are accepted only for active WPML languages.
	 *
	 * @param  string  $location
	 * @param  string  $baseLocation
	 * @param  array  $multilocations
	 * @param  bool  $enabled
	 *
	 * @return bool
	 */
	public function isSelectableLocation(
		string $location,
		string $baseLocation,
		array $multilocations,
		bool $enabled
	): bool {
		foreach ( $multilocations as $configuredLocation ) {
			if ( is_string( $configuredLocation ) && sanitize_text_field( $configuredLocation ) === $location ) {
				return true;
			}
		}

		return isset(
			$this->getActiveLocations( $baseLocation, $multilocations, $enabled )[ $location ]
		);
	}

	/**
	 * Resolves the WPML language code assigned to a product.
	 *
	 * @param  int  $productId
	 *
	 * @return string|null
	 */
	public function getProductLanguage( int $productId ): ?string {
		$language = $this->context->get_post_language_code( $productId );

		if ( empty( $language ) && function_exists( 'wc_get_product' ) ) {
			$product = wc_get_product( $productId );

			if ( $product && $product->get_parent_id() ) {
				$language = $this->context->get_post_language_code( $product->get_parent_id() );
			}
		}

		$language = is_string( $language ) ? sanitize_key( $language ) : '';

		return '' === $language ? null : $language;
	}

	/**
	 * Resolves the selected Manago AI catalog for a WooCommerce product.
	 *
	 * @param  int  $productId
	 * @param  string  $baseLocation
	 * @param  array  $multilocations
	 * @param  array  $catalogsByLocation
	 * @param  bool  $enabled
	 *
	 * @return string|null
	 */
	public function getCatalogIdForProduct(
		int $productId,
		string $baseLocation,
		array $multilocations,
		array $catalogsByLocation,
		bool $enabled
	): ?string {
		if ( !$this->isMultiCatalogMode( $enabled )) {
			return null;
		}

		$language = $this->getProductLanguage( $productId );

		if ( null === $language ) {
			return null;
		}

		$location = WpmlLocationResolver::resolve(
			$baseLocation,
			$multilocations,
			true,
			$language
		);

		$catalogId = $catalogsByLocation[ $location ] ?? null;

		return is_string( $catalogId ) && '' !== $catalogId
			? $catalogId
			: null;
	}

	/**
	 * Resolves the selected catalog for a WPML location.
	 *
	 * @param string $location
	 * @param array<string, string> $catalogsByLocation
	 * @param bool $enabled
	 * @return string|null
	 */
	public function getCatalogIdForLocation(
		string $location,
		array $catalogsByLocation,
		bool $enabled
	): ?string {
		if ( ! $this->isMultiCatalogMode( $enabled ) ) {
			return null;
		}

		$catalogId = $catalogsByLocation[ $location ] ?? null;

		return is_string( $catalogId ) && '' !== $catalogId
			? $catalogId
			: null;
	}
}
