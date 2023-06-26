<?php
/**
 * MO_Reader abstract class
 *
 * @package DynaMo
 */

namespace WP_Syntex\DynaMo;

/**
 * An abstract class helping to read MO files.
 *
 * @since 1.1
 */
abstract class MO_Reader {

	/**
	 * The plural expression founded in the MO file.
	 *
	 * @var string
	 */
	protected $plural_expression = '';

	/**
	 * Parses the MO file.
	 *
	 * @since 1.0
	 *
	 * @param resource $handle Stream handle.
	 * @return bool True if successful, false otherwise.
	 */
	abstract public function parse( $handle );

	/**
	 * Returns the plural expression.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function get_plural_expression() {
		return $this->plural_expression;
	}

	/**
	 * Returns the unpack format for the endianness of the file.
	 *
	 * @see https://github.com/WordPress/WordPress/blob/5.8.2/wp-includes/pomo/mo.php#L192-L211
	 *
	 * @since 1.0
	 *
	 * @param string $magic Magic read from the file.
	 * @return string|false
	 */
	protected static function get_byteorder( $magic ) {
		$magic = unpack( 'V', $magic );
		if ( ! $magic ) {
			return false;
		}

		$magic = reset( $magic );

		// Little endian, second case for 32 bits.
		if ( 0x950412de === $magic || ( PHP_INT_SIZE === 4 && -1794895138 === $magic ) ) {
			return 'V';
		}

		// Big endian, second case for 32 bits.
		if ( 0xde120495 === $magic || ( PHP_INT_SIZE === 4 && -569244523 === $magic ) ) {
			return 'N';
		}

		return false;
	}

	/**
	 * Reads and parses the header.
	 *
	 * @since 1.1
	 *
	 * @param resource $handle Stream handle.
	 * @param string   $endian 'N' or 'V' depending on the endianness of the file.
	 * @return int[]|false
	 */
	protected static function read_header( $handle, $endian ) {
		$header = fread( $handle, 24 );
		if ( ! $header || self::strlen( $header ) !== 24 ) {
			return false;
		}

		$header = unpack( "{$endian}revision/{$endian}total/{$endian}originals_lengths_addr/{$endian}translations_lengths_addr/{$endian}hash_length/{$endian}hash_addr", $header );
		if ( ! is_array( $header ) ) {
			return false;
		}

		// Support revision 0 of MO format specs, only.
		if ( 0 !== $header['revision'] ) {
			return false;
		}

		return $header;
	}

	/**
	 * Reads and unpacks a table of integers, typically used to read the tables
	 * for original strings table, translations table and hashes table.
	 *
	 * @since 1.0
	 *
	 * @param resource $handle Stream handle.
	 * @param string   $endian 'N' or 'V' depending on the endianness of the file.
	 * @param int      $length The number of values to read.
	 * @return int[]|false
	 */
	protected static function read_and_unpack( $handle, $endian, $length ) {
		$strings = fread( $handle, $length * 4 );
		if ( ! $strings || self::strlen( $strings ) !== $length * 4 ) {
			return false;
		}

		return unpack( $endian . $length, $strings );
	}

	/**
	 * Returns the length of a string.
	 *
	 * @see https://github.com/WordPress/WordPress/blob/5.8.2/wp-includes/pomo/streams.php#L99-L109
	 *
	 * @since 1.0
	 *
	 * @param string $string The string.
	 * @return int
	 */
	protected static function strlen( $string ) {
		if ( function_exists( 'mb_strlen' ) && ( (int) ini_get( 'mbstring.func_overload' ) & 2 ) ) { // phpcs:ignore PHPCompatibility.IniDirectives.RemovedIniDirectives.mbstring_func_overloadDeprecated
			return mb_strlen( $string, 'ascii' );
		} else {
			return strlen( $string );
		}
	}

	/**
	 * Sets the plural expression from the translations headers.
	 *
	 * @see https://github.com/WordPress/WordPress/blob/5.8.2/wp-includes/pomo/translations.php#L265-L295
	 * @see https://github.com/WordPress/WordPress/blob/5.8.2/wp-includes/pomo/translations.php#L202-L214
	 *
	 * @since 1.0
	 *
	 * @param string $headers Translations headers read from the MO file.
	 * @return string
	 */
	protected function parse_plural_forms_expression( $headers ) {
		// Sometimes \n's are used instead of real new lines.
		$headers = str_replace( '\n', "\n", $headers );
		$lines   = explode( "\n", $headers );

		foreach ( $lines as $line ) {
			if ( false !== strpos( $line, 'Plural-Forms' ) ) {
				$parts = explode( ':', $line, 2 );
				if ( preg_match( '/^\s*nplurals\s*=\s*(\d+)\s*;\s+plural\s*=\s*(.+)$/', $parts[1], $matches ) ) {
					return rtrim( trim( $matches[2] ), ';' );
				}
			}
		}

		return 'n != 1'; // The default value (en_US).
	}
}
