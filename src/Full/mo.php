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
	 * Imports a MO file.
	 * Required by the implicit WordPress interface.
	 *
	 * @since 1.1
	 *
	 * @param string $filename Path to the MO file.
	 * @return bool
	 */
	public function import_from_file( $filename ) {
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

		$this->plural_forms = new \Plural_Forms( $reader->get_plural_expression() );
		$this->container    = $reader->get_translations();
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
