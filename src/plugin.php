<?php
/**
 * Plugin class
 *
 * @package DynaMo
 */

namespace WP_Syntex\DynaMo;

/**
 * The main plugin class.
 *
 * @since 1.0
 */
class Plugin {
	/**
	 * Add hooks
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function add_hooks() {
		add_filter( 'override_load_textdomain', array( $this, 'override_load_textdomain' ), 0, 3 );
	}

	/**
	 * Filters whether to override the .mo file loading.
	 *
	 * @since 1.0
	 *
	 * @param bool   $override Whether to override the .mo file loading.
	 * @param string $domain   Text domain. Unique identifier for retrieving translated strings.
	 * @param string $mofile   Path to the MO file.
	 * @return bool
	 */
	public function override_load_textdomain( $override, $domain, $mofile ) {
		global $l10n;

		$mo = wp_using_ext_object_cache() ? new Full\MO() : new Dynamic\MO();

		/**
		 * Filters the object used to load the translation file.
		 *
		 * @since 1.1
		 *
		 * @param MO     $mo     MO file loader.
		 * @param string $domain Text domain.
		 */
		$mo = apply_filters( 'dynamo_file_loader', $mo, $domain );

		if ( ! $mo->import_from_file( $mofile ) ) {
			return false;
		}

		if ( isset( $l10n[ $domain ] ) ) {
			$mo->merge_with( $l10n[ $domain ] );
		}

		$l10n[ $domain ] = &$mo; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		return true;
	}
}
