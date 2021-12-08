<?php
/**
 * MO Class
 *
 * @package DynaMo
 */

namespace WP_Syntex\DynaMo;

/**
 * A class defining objects usable in the WordPress global $l10n array.
 *
 * @since 1.0
 */
class MO {

	/**
	 * Empty array.
	 *
	 * It is there only in case someone attempts to directly merge an instance
	 * of this class into a WordPress MO object.
	 *
	 * @var \Translation_Entry[]
	 */
	public $entries = array();

	/**
	 * Stores all translations for (maybe) next calls.
	 *
	 * @var string[]
	 */
	protected $cache = array();

	/**
	 * An array of objects handling the translations search in files loaded by this class.
	 *
	 * @var Search_Handler[]
	 */
	protected $items = array();

	/**
	 * An instance of the WordPress Plural_Forms class.
	 *
	 * @var \Plural_Forms
	 */
	protected $plural_forms_handler;

	/**
	 * Imports a MO file.
	 * Required by the implicit WordPress interface.
	 *
	 * The whole file is copied into the memory as it's expected to slightly increase
	 * the speed of the numerous calls to fseek() and fread().
	 *
	 * @since 1.0
	 *
	 * @param string $filename Path to the MO file.
	 * @return bool
	 */
	public function import_from_file( $filename ) {
		$file_handle = fopen( $filename, 'rb' );
		$mem_handle  = fopen( 'php://memory', 'w+b' );

		if ( ! $file_handle || ! $mem_handle ) {
			return false;
		}

		stream_copy_to_stream( $file_handle, $mem_handle );
		fclose( $file_handle );

		$reader = new MO_Reader();
		if ( ! $reader->parse( $mem_handle ) ) {
			return false;
		}

		$this->plural_forms_handler = new \Plural_Forms( $reader->get_plural_expression() );

		$this->items[] = $reader->get_search_handler();
		return true;
	}

	/**
	 * Merges an existing MO file into this one.
	 * Required by the implicit WordPress interface.
	 *
	 * @since 1.0
	 *
	 * @param MO $other Other instance to merge to the current instance.
	 * @return void
	 */
	public function merge_with( &$other ) {
		if ( $other instanceof MO ) {
			$this->items = array_merge( $other->items, $this->items );
		}
	}

	/**
	 * Retrieves translated string with gettext context.
	 * Required by the implicit WordPress interface.
	 *
	 * @since 1.0
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

		if ( isset( $this->cache[ $key ] ) ) {
			return $this->cache[ $key ];
		}

		foreach ( $this->items as $item ) {
			$translation = $item->get_translation( $key );
			if ( ! empty( $translation ) ) {
				$this->cache[ $key ] = $translation;
				return $translation;
			}
		}

		$this->cache[ $key ] = $singular; // Default in case we don't find a translation.
		return $singular;
	}

	/**
	 * Translates and retrieves the singular or plural form based on the supplied number, with gettext context.
	 * Required by the implicit WordPress interface.
	 *
	 * @since 1.0
	 *
	 * @param string      $singular The text to be used if the number is singular.
	 * @param string      $plural   The text to be used if the number is plural.
	 * @param int         $count    The number to compare against to use either the singular or plural form.
	 * @param string|null $context  Context information for the translators.
	 * @return string
	 */
	public function translate_plural( $singular, $plural, $count, $context = null ) {
		$key = ! $context ? $singular : $context . "\4" . $singular;

		if ( ! isset( $this->cache[ $key ] ) ) {
			foreach ( $this->items as $item ) {
				$translation = $item->get_translation( $key );
				if ( ! empty( $translation ) ) {
					$this->cache[ $key ] = $translation;
					break;
				}
			}
		}

		if ( isset( $this->cache[ $key ] ) ) {
			$translations = explode( "\0", $this->cache[ $key ] );
			$index        = $this->plural_forms_handler->get( $count );
			if ( isset( $translations[ $index ] ) ) {
				return $translations[ $index ];
			}
		}

		return 1 === (int) $count ? $singular : $plural;
	}
}
