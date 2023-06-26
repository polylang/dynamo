<?php
/**
 * MO_Reader class
 *
 * @package DynaMo
 */

namespace WP_Syntex\DynaMo\Full;

/**
 * A class to read MO files.
 *
 * @since 1.1
 */
class MO_Reader extends \WP_Syntex\DynaMo\MO_Reader {

	/**
	 * The array of translations.
	 *
	 * @var string[]
	 */
	protected $translations;

	/**
	 * Parses the MO file.
	 *
	 * @see https://www.gnu.org/software/gettext/manual/gettext.html#MO-Files
	 * @see https://github.com/WordPress/WordPress/blob/5.8.2/wp-includes/pomo/mo.php#L213-L301
	 *
	 * @since 1.1
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

		$o = array();

		for ( $i = 0; $i < $header['total']; $i++ ) {
			if ( $originals[ 2 * $i + 1 ] > 0 ) {
				\fseek( $handle, (int) $originals[ 2 * $i + 2 ] );
				$original = (string) \fread( $handle, (int) $originals[ 2 * $i + 1 ] );
				$parts    = explode( "\0", $original ); // Remove the plural forms.
				$o[ $i ]  = $parts[0];
			} else {
				$o[ $i ] = '';
			}
		}

		for ( $i = 0; $i < $header['total']; $i++ ) {
			if ( $translations[ 2 * $i + 1 ] > 0 ) {
				\fseek( $handle, (int) $translations[ 2 * $i + 2 ] );
				$translation = (string) \fread( $handle, (int) $translations[ 2 * $i + 1 ] );
				if ( '' === $o[ $i ] ) {
					$this->plural_expression = $this->parse_plural_forms_expression( $translation );
				} else {
					$this->translations[ $o[ $i ] ] = $translation;
				}
			}
		}

		return true;
	}

	/**
	 * Returns the array of translations.
	 *
	 * @since 1.1
	 *
	 * @return string[]
	 */
	public function get_translations() {
		return $this->translations;
	}
}
