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
class MO_Reader extends \WP_Syntex\DynaMo\MO_Reader {

	/**
	 * The object handling the translations search algorithm.
	 *
	 * @var Search_Handler
	 */
	protected $search_handler;

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

		$header = self::read_header( $handle, $endian );
		if ( ! $header ) {
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
}
