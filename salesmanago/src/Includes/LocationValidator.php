<?php

namespace bhr\Includes;

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validates Manago AI location values.
 *
 * This class centralizes location sanitization and validation for the global location field
 * and WPML multilocation values.
 */
class LocationValidator {

	/**
	 * Regex matching the Manago AI API V3 location constraints.
	 */
	private const LOCATION_PATTERN = '/^[a-zA-Z_][a-zA-Z0-9_]{2,254}$/';

	/**
	 * Sanitizes a location value received from WordPress request data.
	 *
	 * Sanitization does not guarantee the value is a valid Manago AI location,
	 * so callers should still validate the returned value with is_valid().
	 *
	 * @param $location
	 *
	 * @return string
	 */
	public static function sanitize( $location ): string {
		if ( !is_scalar( $location ) ) {
			return '';
		}

		return sanitize_text_field( wp_unslash( $location ) );
	}

	/**
	 * Checks whether a location value matches the API V3 location constraints.
	 *
	 * @param  string|null  $location
	 *
	 * @return bool
	 */
	public static function is_valid( ?string $location ): bool {
		if ( null === $location ) {
			return false;
		}

		return 1 === preg_match( self::LOCATION_PATTERN, $location );
	}

	/**
	 * Sanitizes and validates a location value, returning a sanitized fallback on failure.
	 *
	 * @param $location
	 * @param  string  $default
	 *
	 * @return string
	 */
	public static function validate_or_default( $location, string $default ): string {
		$location = self::sanitize( $location );

		if ( self::is_valid( $location ) ) {
			return $location;
		}

		$default = self::sanitize( $default );

		return self::is_valid( $default ) ? $default : '';
	}
}