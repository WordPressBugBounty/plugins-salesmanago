<?php

namespace bhr\Includes\Integrations\Wpml;

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides an adapter around WPML language hooks.
 *
 * WPML is treated as an optional dependency. This class never references WPML classes or constants directly,
 * it only uses WordPress hooks exposed by WPML and falls back safely when those are unavailable.
 */
class WpmlLanguageContext {

	/**
	 * Checks whether WPML plugin is installed, active, and is able to list WPML languages.
	 *
	 * @return bool
	 */
	public function can_resolve_multilocations(): bool {
		return $this->can_list_active_languages();
	}

	/**
	 * Returns active WPML languages normalized for plugin use.
	 *
	 * Each language is keyed by a sanitized language code and contains a 'code' and 'name' field.
	 *
	 * @return array
	 */
	public function get_active_languages(): array {
		if ( !$this->can_list_active_languages() ) {
			return [];
		}

		try {
			$languages = apply_filters( 'wpml_active_languages', null, 'orderby=code&order=asc' );
		} catch ( \Throwable $e ) {
			return [];
		}

		if ( !is_array( $languages ) ) {
			return [];
		}

		$result = [];

		foreach ( $languages as $language ) {
			if ( empty( $language['language_code'] ) || !is_string( $language['language_code'] ) ) {
				continue;
			}

			$code = sanitize_key( $language['language_code'] );

			if ( '' === $code ) {
				continue;
			}

			$result[ $code ] = [
				'code' => $code,
				'name' => !empty($language['native_name']) ? sanitize_text_field( $language['native_name'] ) : $code
			];
		}

		return $result;
	}

	/**
	 * Returns the WPML language code assigned to a post.
	 *
	 * @param $post_id
	 *
	 * @return string|null
	 */
	public function get_post_language_code( $post_id ): ?string {
		if ( (int) $post_id <= 0 || !$this->can_resolve_post_language() ) {
			return null;
		}

		try {
			$language_details = apply_filters( 'wpml_post_language_details', null, $post_id );
		} catch ( \Throwable $e ) {
			return null;
		}

		if ( !is_array( $language_details )) {
			return null;
		}

		$language_code = $language_details['language_code'] ?? null;

		if ( !is_string( $language_code ) || '' === trim( $language_code ) ) {
			return null;
		}

		return trim( $language_code );
	}

	/**
	 * Temporarily switches the WPML language context.
	 *
	 * @param  string|null  $language_code
	 *
	 * @return array
	 */
	public function switch_to_language( ?string $language_code ): array {
		if ( empty( $language_code ) || !$this->can_switch_language() ) {
			return [
				'switched' => false,
				'previous' => null
			];
		}

		$previous_language = $this->get_current_language_code();

		try {
			do_action( 'wpml_switch_language', $language_code );
		} catch ( \Throwable $e ) {
			return [
				'switched' => false,
				'previous' => null
			];
		}

		return [
			'switched' => true,
			'previous' => $previous_language
		];
	}

	/**
	 * Restores the WPML language context after switch_to_language().
	 *
	 * @param  array  $language_context
	 *
	 * @return void
	 */
	public function restore_language( array $language_context ): void {
		if ( true !== ( $language_context['switched'] ?? false ) || !$this->can_switch_language() ) {
			return;
		}

		try {
			do_action( 'wpml_switch_language', $language_context['previous'] ?? null );
		} catch ( \Throwable $e ) {
			return;
		}
	}

	/**
	 * Returns the current WPML language code.
	 *
	 * Used by location resolution and before temporary language switches,
	 * so the previous language context can be restored afterward.
	 *
	 *
	 * @return string|null
	 */
	public function get_current_language_code(): ?string {
		if ( !has_filter( 'wpml_current_language' )) {
			return null;
		}

		try {
			$current_language = apply_filters( 'wpml_current_language', null );
		} catch ( \Throwable $e ) {
			return null;
		}

		if ( !is_string( $current_language ) ||  '' === $current_language ) {
			return null;
		}

		return $current_language;
	}

	/**
	 * Checks whether product permalink generation can be scoped by post language.
	 *
	 * This requires resolving product language and temporarily switching the WPML language context
	 * before calling WooCommerce permalink generation.
	 *
	 * @return bool
	 */
	public function can_scope_post_permalink(): bool {
		return $this->can_resolve_post_language() && $this->can_switch_language();
	}

	/**
	 * Checks whether WPML can return details for posts.
	 *
	 * @return bool
	 */
	private function can_resolve_post_language(): bool {
		return has_filter( 'wpml_post_language_details' );
	}

	/**
	 * Checks whether WPML can switch the current language context.
	 *
	 * @return bool
	 */
	private function can_switch_language(): bool {
		return has_action( 'wpml_switch_language' );
	}

	/**
	 * Checks whether WPML exposes the active languages filter.
	 *
	 * @return bool
	 */
	private function can_list_active_languages(): bool {
		return has_filter( 'wpml_active_languages' );
	}
}