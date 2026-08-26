<?php

namespace bhr\Includes\Integrations\Wpml;

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

use bhr\Includes\LocationValidator;

/**
 * Resolves Manago AI location values for WPML language contexts.
 *
 * This resolver does not read plugin entities directly. Callers provide the base location,
 * configured multilocations, and the enabled/disabled flag explicitly.
 *
 * Resolution order:
 * 1. If multilocations are disabled, return the base location.
 * 2. Resolve language_code from the provided argument or current WPML language.
 * 3. If a valid custom location exists for the language, return it.
 * 4. If generated {base}_{language} is valid, return it.
 * 5. Fallback to the base location.
 */
class WpmlLocationResolver {

	private WpmlLanguageContext $context;

	/**
	 * @param  WpmlLanguageContext|null  $context
	 */
	public function __construct( ?WpmlLanguageContext $context = null ) {
		$this->context = $context ?? new WpmlLanguageContext();
	}

	/**
	 * Resolves the effective location using a new resolver instance.
	 *
	 * When multilocations are disabled, this returns the base location without attempting
	 * to read WPML state.
	 *
	 * @param  string  $base_location
	 * @param  array  $multilocations
	 * @param  bool  $enabled
	 * @param  string|null  $language_code
	 *
	 * @return string
	 */
	public static function resolve(
		string $base_location,
		array $multilocations,
		bool $enabled,
		?string $language_code = null
	): string {
		if ( !$enabled ) {
			return $base_location;
		}

		return ( new self() )->resolve_location(
			$base_location,
			$multilocations,
			true,
			$language_code
		);
	}

	/**
	 * Resolves the effective location using this resolver's WPML context.
	 *
	 * When multilocations are disabled or WPML is unavailable,
	 * this returns the base location without attempting to read WPML state.
	 *
	 * @param  string  $base_location
	 * @param  array  $multilocations
	 * @param  bool  $enabled
	 * @param  string|null  $language_code
	 *
	 * @return string
	 */
	public function resolve_location(
		string $base_location,
		array $multilocations,
		bool $enabled,
		?string $language_code = null
	): string {
		if ( !$enabled || !$this->context->can_resolve_multilocations() ) {
			return $base_location;
		}

		return $this->resolve_location_language( $base_location, $multilocations, $language_code );
	}

	/**
	 * Resolves a language-specific location after enabled/WPML availability checks.
	 *
	 * If no language code is provided, the current WPML language is used.
	 * If no language can be resolved, the base location is returned.
	 *
	 * @param  string  $base_location
	 * @param  array  $multilocations
	 * @param  string|null  $language_code
	 *
	 * @return string
	 */
	private function resolve_location_language(
		string $base_location,
		array $multilocations,
		?string $language_code = null
	): string {
		$language_code = $language_code ?? $this->context->get_current_language_code();

		if ( empty( $language_code ) ) {
			return $base_location;
		}

		$language_code = sanitize_key( $language_code );

		if ( '' === $language_code ) {
			return $base_location;
		}

		if ( isset( $multilocations[ $language_code ] ) ) {
			$custom_location = LocationValidator::sanitize( $multilocations[ $language_code ] );

			if ( LocationValidator::is_valid( $custom_location ) ) {
				return $custom_location;
			}
		}

		$default_location = $base_location . '_' . $language_code;

		if ( LocationValidator::is_valid( $default_location ) ) {
			return $default_location;
		}

		return $base_location;
	}
}