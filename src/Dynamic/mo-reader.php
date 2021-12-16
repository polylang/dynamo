<?php
/**
 * MO_Reader class
 *
 * @package DynaMo
 */

namespace WP_Syntex\DynaMo\Dynamic;

/**
 * A class to read MO files.
 *
 * @since 1.0
 */
class MO_Reader {

	/**
	 * The object handling the translations search algorithm.
	 *
	 * @var Search_Handler
	 */
	protected $search_handler;

	/**
	 * The plural expression founded in the MO file.
	 *
	 * @var string
	 */
	protected $plural_expression;

	/**
	 * Parses the MO file.
	 *
	 * @see https://www.gnu.org/software/gettext/manual/gettext.html#MO-Files
	 * @see https://github.com/WordPress/WordPress/blob/5.8.2/wp-includes/pomo/mo.php#L213-L301
	 *
	 * @since 1.0
	 *
	 * @param resource $handle Stream handle.
	 * @return bool True if successful, false otherwise.
	 */
	public function parse( $handle ) {
		rewind( $handle );

		// Read the magic number and get the endianness of the file.
		$magic = fread( $handle, 4 );
		if ( ! $magic ) {
			return false;
		}

		$endian = self::get_byteorder( $magic );
		if ( false === $endian ) {
			return false;
		}

		// Read and parse the header.
		$header = fread( $handle, 24 );
		if ( ! $header || $this->strlen( $header ) !== 24 ) {
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

		// Seek to data blocks.
		fseek( $handle, $header['originals_lengths_addr'] );

		// Read originals' indices.
		$originals_lengths_length = $header['translations_lengths_addr'] - $header['originals_lengths_addr'];
		if ( $originals_lengths_length !== $header['total'] * 8 ) {
			return false;
		}

		$originals = self::read_and_unpack( $handle, $endian, $header['total'] * 2 );
		if ( ! $originals ) {
			return false;
		}

		// Read translations' indices.
		$translations_lengths_length = $header['hash_addr'] - $header['translations_lengths_addr'];
		if ( $translations_lengths_length !== $header['total'] * 8 ) {
			return false;
		}

		$translations = self::read_and_unpack( $handle, $endian, $header['total'] * 2 );
		if ( ! $translations ) {
			return false;
		}

		if ( $header['hash_length'] > 0 && PHP_INT_SIZE === 8 ) {
			// If we have a hash table and PHP supports 64 bits, read it and use it for searching translations.
			$hashes = self::read_and_unpack( $handle, $endian, $header['hash_length'] );
			if ( ! $hashes ) {
				return false;
			}

			// The usage of SplFixedArray instead of arrays should slightly increase the access speed.
			$this->search_handler = new Hash_Search(
				$handle,
				\SplFixedArray::fromArray( $originals, false ),
				\SplFixedArray::fromArray( $translations, false ),
				\SplFixedArray::fromArray( $hashes, false ),
				$header['hash_length']
			);
		} else {
			// Otherwise use the binary search.
			$this->search_handler = new Binary_Search(
				$handle,
				\SplFixedArray::fromArray( $originals, false ),
				\SplFixedArray::fromArray( $translations, false ),
				$header['total']
			);
		}

		// Search the translation headers (usually at first position) and use them to get the plural expression.
		$headers_idx = array_search( 0, $originals, true );
		if ( false === $headers_idx ) {
			return false;
		}

		fseek( $handle, $translations[ (int) $headers_idx + 1 ] );
		$headers = fread( $handle, $translations[ (int) $headers_idx ] );
		if ( ! $headers ) {
			return false;
		}

		$this->plural_expression = $this->parse_plural_forms_expression( $headers );
		return true;
	}

	/**
	 * Returns an instance to the MO search handler.
	 *
	 * @since 1.0
	 *
	 * @return Search_Handler
	 */
	public function get_search_handler() {
		return $this->search_handler;
	}

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
