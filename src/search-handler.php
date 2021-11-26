<?php
/**
 * Search_Handler interface
 *
 * @package DynaMo
 */

namespace WP_Syntex\DynaMo;

/**
 * Interface for algorithms to search translations in a MO file.
 *
 * @since 1.0
 */
interface Search_Handler {
	/**
	 * Returns the translation(s) given an original key.
	 *
	 * @since 1.0
	 *
	 * @param string $key The key of the string to translate (includes the context and singular string).
	 * @return string|false
	 */
	public function get_translation( $key );
}
