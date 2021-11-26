<?php
/**
 * Binary_Search class
 *
 * @package DynaMo
 */

namespace WP_Syntex\DynaMo;

/**
 * Implements the search algorithm using a binary search, useful when no hash table is provided.
 * See dcigettext.c in GNU gettext.
 *
 * @since 1.0
 */
class Binary_Search implements Search_Handler {
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
	 * The total count of translated strings.
	 *
	 * @var int
	 */
	protected $total;

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
	 * @param int                 $total              The total count of translated strings.
	 */
	public function __construct( $handle, $originals_table, $translations_table, $total ) {
		$this->handle = $handle;

		$this->originals_table    = $originals_table;
		$this->translations_table = $translations_table;
		$this->total              = $total;
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
		$left  = 0;
		$right = $this->total;

		while ( $left < $right ) {
			$pos = (int) ( ( $left + $right ) / 2 );
			$i   = $pos * 2; // Due to position and length of strings stored alternately in the same flat array.

			if ( ! isset( $this->originals[ $pos ] ) ) {
				if ( $this->originals_table[ $i ] > 0 ) {
					\fseek( $this->handle, (int) $this->originals_table[ $i + 1 ] );
					$this->originals[ $pos ] = (string) \fread( $this->handle, (int) $this->originals_table[ $i ] );
				} else {
					$this->originals[ $pos ] = '';
				}
			}

			/*
			 * For plurals, we limit the comparison to the length of the searched key
			 * because the key doesn't include the plural form while the original does.
			 */
			$length = \strpos( $this->originals[ $pos ], "\0" );
			$cmp    = $length ? \strncmp( $key, $this->originals[ $pos ], $length ) : \strcmp( $key, $this->originals[ $pos ] );

			if ( $cmp < 0 ) {
				$right = $pos;
			} elseif ( $cmp > 0 ) {
				$left = $pos + 1;
			} else {
				\fseek( $this->handle, (int) $this->translations_table[ $i + 1 ] );
				return \fread( $this->handle, (int) $this->translations_table[ $i ] );
			}
		}

		return false;
	}
}
