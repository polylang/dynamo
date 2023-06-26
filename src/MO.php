<?php
/**
 * MO abstract Class
 *
 * @package DynaMo
 */

namespace WP_Syntex\DynaMo;

/**
 * An abstract class defining the interface for objects usable in the WordPress global $l10n array.
 *
 * This is an abstract class and not an interface due to WordPress accessing directly the public property.
 *
 * @since 1.1
 */
abstract class MO {

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
	 * Imports a MO file.
	 *
	 * @since 1.1
	 *
	 * @param string $filename Path to the MO file.
	 * @return bool
	 */
	abstract public function import_from_file( $filename );

	/**
	 * Merges an existing MO file into this one.
	 *
	 * @since 1.1
	 *
	 * @param MO $other Other instance to merge to the current instance.
	 * @return void
	 */
	abstract public function merge_with( &$other );

	/**
	 * Retrieves translated string with gettext context.
	 *
	 * @since 1.1
	 *
	 * @param string      $singular Text to translate.
	 * @param string|null $context  Context information for the translators.
	 * @return string
	 */
	abstract public function translate( $singular, $context = null );

	/**
	 * Translates and retrieves the singular or plural form based on the supplied number, with gettext context.
	 *
	 * @since 1.1
	 *
	 * @param string      $singular The text to be used if the number is singular.
	 * @param string      $plural   The text to be used if the number is plural.
	 * @param int         $count    The number to compare against to use either the singular or plural form.
	 * @param string|null $context  Context information for the translators.
	 * @return string
	 */
	abstract public function translate_plural( $singular, $plural, $count, $context = null );
}
