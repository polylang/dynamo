<?php
/**
 * Hash_Search class
 *
 * @package DynaMo
 */

namespace WP_Syntex\DynaMo;

/**
 * Implements the search algorithm using the hash table which should be a bit faster
 * than the alternative binary search.
 * See dcigettext.c in GNU gettext.
 *
 * @since 1.0
 */
class Hash_Search implements Search_Handler {
	/**
	 * Stream handle.
	 *
	 * @var resource
	 */
	protected $handle;

	/**
	 * An array containing the length and position of the original strings.
	 *
	 * @var \SplFixedArray<int>
	 */
	protected $originals_table;

	/**
	 * An array containing the length and position of the translated strings.
	 *
	 * @var \SplFixedArray<int>
	 */
	protected $translations_table;

	/**
	 * The hash table.
	 *
	 * @var \SplFixedArray<int>
	 */
	protected $hash_table;

	/**
	 * The length of the hash table.
	 *
	 * @var int
	 */
	protected $hash_length;

	/**
	 * An array to store strings already read.
	 *
	 * @var string[]
	 */
	private $originals;

	/**
	 * Constructor
	 *
	 * @since 1.0
	 *
	 * @param resource            $handle             Stream handle.
	 * @param \SplFixedArray<int> $originals_table    An array containing the length and position of the original strings.
	 * @param \SplFixedArray<int> $translations_table An array containing the length and position of the translated strings.
	 * @param \SplFixedArray<int> $hash_table         The hash table.
	 * @param int                 $hash_length        The length of the hash table.
	 */
	public function __construct( $handle, $originals_table, $translations_table, $hash_table, $hash_length ) {
		$this->handle = $handle;

		$this->originals_table    = $originals_table;
		$this->translations_table = $translations_table;
		$this->hash_table         = $hash_table;
		$this->hash_length        = $hash_length;
	}

	/**
	 * Returns the translation(s) given an original key.
	 *
	 * @since 1.0
	 *
	 * @param string $key The key of the string to translate (includes the context and singular string).
	 * @return string|false
	 */
	public function get_translation( $key ) {
		$length = \strlen( $key );

		/*
		 * Evaluates the hash with the "hashpjw" function by P.J. Weinberger.
		 * See hash-string.c in GNU gettext.
		 */
		$hash_val = 0;
		$chars    = \unpack( 'C*', $key );
		foreach ( (array) $chars as $char ) {
			$hash_val = ( $hash_val << 4 ) + $char;
			$g        = $hash_val & 0xF0000000;
			if ( 0 !== $g ) {
				$hash_val ^= $g >> 24;
				$hash_val ^= $g;
			}
		}

		$idx  = $hash_val % $this->hash_length;
		$incr = 1 + ( $hash_val % ( $this->hash_length - 2 ) );

		while ( ! empty( $this->hash_table[ $idx ] ) ) {
			$pos = $this->hash_table[ $idx ] - 1;
			$i   = $pos * 2; // Due to position and length of strings stored alternately in the same flat array.

			if ( ! isset( $this->originals[ $pos ] ) && $this->originals_table[ $i ] >= $length ) {
				\fseek( $this->handle, (int) $this->originals_table[ $i + 1 ] );
				$original = (string) \fread( $this->handle, (int) $this->originals_table[ $i ] );
				$parts    = explode( "\0", $original ); // Remove the plural forms for the comparison with the searched key.
				$this->originals[ $pos ] = $parts[0];
			}

			/*
			 * We limit the comparison to the length of the searched key
			 * because the key doesn't include the plural form while the original does.
			 */
			if ( isset( $this->originals[ $pos ] ) && $key === $this->originals[ $pos ] ) {
				\fseek( $this->handle, (int) $this->translations_table[ $i + 1 ] );
				return \fread( $this->handle, (int) $this->translations_table[ $i ] );
			}

			$max_idx = $this->hash_length - $incr;
			if ( $idx >= $max_idx ) {
				$idx -= $max_idx;
			} else {
				$idx += $incr;
			}
		}

		return false;
	}
}
