<?php
/**
 * MO Class
 *
 * @package DynaMo
 */

namespace WP_Syntex\DynaMo\Full;

/**
 * A class defining objects usable in the WordPress global $l10n array.
 *
 * @since 1.1
 */
class MO extends \WP_Syntex\DynaMo\MO {

	/**
	 * Cache group name.
	 */
	const CACHE_GROUP = 'DynaMo';

	/**
	 * Stores all translations for (maybe) next calls.
	 *
	 * @var string[]
	 */
	protected $container = array();

	/**
	 * An instance of the WordPress Plural_Forms class.
	 *
	 * @var \Plural_Forms
	 */
	protected $plural_forms;


	/**
	 * Cleans the translations cache.
	 *
	 * @since 1.1
	 *
	 * @return void
	 */
	public static function clean_cache() {
		wp_cache_delete( 'last_changed', self::CACHE_GROUP );
	}

	/**
	 * Imports a MO file.
	 * Required by the implicit WordPress interface.
	 *
	 * @since 1.1
	 *
	 * @param string $filename Path to the MO file.
	 * @return bool
	 */
	public function import_from_file( $filename ) {
		$using_ext_cache = wp_using_ext_object_cache();

		// Attempts to get the translation from the object cache only if external object cache is available.
		if ( $using_ext_cache ) {
			add_action( 'upgrader_process_complete', array( __CLASS__, 'clean_cache' ) ); // Cleans the cache each time there is an update.

			wp_cache_add_global_groups( self::CACHE_GROUP ); // The cache is common to all sites on multisite.

			$last_changed = wp_cache_get_last_changed( self::CACHE_GROUP );
			$key          = md5( $filename ) . ":$last_changed";
			$cache        = wp_cache_get( $key, self::CACHE_GROUP );

			if ( false !== $cache ) {
				$this->container    = $cache['translations'];
				$this->plural_forms = new \Plural_Forms( $cache['plural_forms'] );
				return true;
			}
		}

		if ( ! is_readable( $filename ) ) {
			return false;
		}

		// Read and parse the translation file.
		$file_handle = fopen( $filename, 'rb' );

		if ( ! $file_handle ) {
			return false;
		}

		$reader = new MO_Reader();
		$parsed = $reader->parse( $file_handle );
		fclose( $file_handle );

		if ( ! $parsed ) {
			return false;
		}

		$plural_expression  = $reader->get_plural_expression();
		$this->plural_forms = new \Plural_Forms( $plural_expression );
		$this->container    = $reader->get_translations();

		// It's useless to cache anything if external object cache is not available.
		if ( $using_ext_cache ) {
			/**
			 * Filters the cache expiration limit.
			 *
			 * @since 1.1
			 *
			 * @param int    $expire   When to expire the cache contents, in seconds.
			 *                         0 means no expiration, defaults to 12 hours.
			 * @param string $filename Translation file name.
			 */
			$expire = apply_filters( 'dynamo_cache_expire', 12 * HOUR_IN_SECONDS, $filename );

			wp_cache_set(
				$key,
				array(
					'plural_forms' => $plural_expression,
					'translations' => $this->container,
				),
				self::CACHE_GROUP,
				$expire // phpcs:ignore WordPressVIPMinimum.Performance.LowExpiryCacheTime.CacheTimeUndetermined
			);
		}

		return true;
	}

	/**
	 * Merges an existing MO file into this one.
	 * Required by the implicit WordPress interface.
	 *
	 * @since 1.1
	 *
	 * @param MO $other Other instance to merge to the current instance.
	 * @return void
	 */
	public function merge_with( &$other ) {
		if ( $other instanceof MO ) {
			$this->container = array_merge( $this->container, $other->container );
		}
	}

	/**
	 * Retrieves translated string with gettext context.
	 * Required by the implicit WordPress interface.
	 *
	 * @since 1.1
	 *
	 * @param string      $singular Text to translate.
	 * @param string|null $context  Context information for the translators.
	 * @return string
	 */
	public function translate( $singular, $context = null ) {
		// _get_plugin_data_markup_translate() may call translate() with an empty string.
		if ( empty( $singular ) ) {
			return $singular;
		}

		$key = ! $context ? $singular : $context . "\4" . $singular;

		if ( isset( $this->container[ $key ] ) ) {
			return $this->container[ $key ];
		}

		return $singular;
	}

	/**
	 * Translates and retrieves the singular or plural form based on the supplied number, with gettext context.
	 * Required by the implicit WordPress interface.
	 *
	 * @since 1.1
	 *
	 * @param string      $singular The text to be used if the number is singular.
	 * @param string      $plural   The text to be used if the number is plural.
	 * @param int         $count    The number to compare against to use either the singular or plural form.
	 * @param string|null $context  Context information for the translators.
	 * @return string
	 */
	public function translate_plural( $singular, $plural, $count, $context = null ) {
		$key = ! $context ? $singular : $context . "\4" . $singular;

		if ( isset( $this->container[ $key ] ) ) {
			$translations = explode( "\0", $this->container[ $key ] );
			$index        = $this->plural_forms->get( $count );
			if ( isset( $translations[ $index ] ) ) {
				return $translations[ $index ];
			}
		}

		return 1 === (int) $count ? $singular : $plural;
	}
}
