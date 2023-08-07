<?php

namespace WP_Syntex\DynaMo\Tests;

class Translations extends \WP_Syntex\DynaMo\TestCase {

	public function tear_down() {
		unset( $GLOBALS['l10n'] );
	}

	public function setup_test( $the_class, $mofile ) {
		$this->init( $the_class );
		load_textdomain( 'default', TEST_DATA_DIR . $mofile );
	}

	public function data_provider() {
		return array(
			array(
				// WordPress, to make sure that we reproduce the same behavior.
				'\MO',
				'sl_SI_with_hash_table.mo',
			),
			array(
				// Hash search.
				'\WP_Syntex\DynaMo\Dynamic\MO',
				'sl_SI_with_hash_table.mo',
			),
			array(
				// Binary search.
				'\WP_Syntex\DynaMo\Dynamic\MO',
				'sl_SI_without_hash_table.mo',
			),
			array(
				// Full load.
				'\WP_Syntex\DynaMo\Full\MO',
				'sl_SI_without_hash_table.mo',
			),
		);
	}

	/**
	 * Translate singular strings.
	 * Especially test strings which are substrings of others.
	 *
	 * @dataProvider data_provider
	 *
	 * @param string $the_class Class loader to instantiate.
	 * @param string $mofile    Translation file to load.
	 */
	public function test_translate_singular( $the_class, $mofile ) {
		$this->setup_test( $the_class, $mofile );
		$this->assertSame( '&laquo; Nazaj', __( '&laquo; Previous' ) );
		$this->assertSame( '&laquo; Prejšnja stran', __( '&laquo; Previous Page' ) );
		$this->assertSame( 'Omogoči', __( 'Activate' ) );
		$this->assertSame( 'Omogoči in objavi', __( 'Activate &amp; Publish' ) );
	}

	/**
	 * Translate singular string with context.
	 *
	 * @dataProvider data_provider
	 *
	 * @param string $the_class Class loader to instantiate.
	 * @param string $mofile    Translation file to load.
	 */
	public function test_translate_with_context( $the_class, $mofile ) {
		$this->setup_test( $the_class, $mofile );
		$this->assertSame( 'Kategorija', _x( 'Category', 'taxonomy singular name' ) );
	}

	/**
	 * Translate plural strings.
	 * Test the 4 possible cases.
	 *
	 * @dataProvider data_provider
	 *
	 * @param string $the_class Class loader to instantiate.
	 * @param string $mofile    Translation file to load.
	 */
	public function test_translate_plurals( $the_class, $mofile ) {
		$this->setup_test( $the_class, $mofile );
		$this->assertSame( '%s razpoložljiva posodobitev', _n( '%s update available', '%s updates available', 101 ) ); // 1, 101, 201
		$this->assertSame( '%s razpoložljivi posodobitvi', _n( '%s update available', '%s updates available', 102 ) ); // 2, 102, 202
		$this->assertSame( '%s razpoložljive posodobitve', _n( '%s update available', '%s updates available', 103 ) ); // 3, 4, 103
		$this->assertSame( '%s razpoložljivih posodobitev', _n( '%s update available', '%s updates available', 5 ) ); // 0, 5, 6
	}

	/**
	 * Test untranslated singular string.
	 *
	 * @dataProvider data_provider
	 *
	 * @param string $the_class Class loader to instantiate.
	 * @param string $mofile    Translation file to load.
	 */
	public function test_non_existing_singular( $the_class, $mofile ) {
		$this->setup_test( $the_class, $mofile );
		$this->assertSame( 'Bla Bla Bla', __( 'Bla Bla Bla' ) );
	}

	/**
	 * Test untranslated singular string wit context.
	 *
	 * @dataProvider data_provider
	 *
	 * @param string $the_class Class loader to instantiate.
	 * @param string $mofile    Translation file to load.
	 */
	public function test_non_existing_with_context( $the_class, $mofile ) {
		$this->setup_test( $the_class, $mofile );
		$this->assertSame( 'Bla Bla Bla', _x( 'Bla Bla Bla', 'Any context' ) );
	}

	/**
	 * Test untranslated plural strings.
	 *
	 * @dataProvider data_provider
	 *
	 * @param string $the_class Class loader to instantiate.
	 * @param string $mofile    Translation file to load.
	 */
	public function test_non_existing_plural( $the_class, $mofile ) {
		$this->setup_test( $the_class, $mofile );
		$this->assertSame( '%s horse galloping in the meadow', _n( '%s horse galloping in the meadow', '%s horses galloping in the meadow', 1 ) );
		$this->assertSame( '%s horses galloping in the meadow', _n( '%s horse galloping in the meadow', '%s horses galloping in the meadow', 101 ) );
		$this->assertSame( '%s horses galloping in the meadow', _n( '%s horse galloping in the meadow', '%s horses galloping in the meadow', 102 ) );
	}

	/**
	 * Test 2 consecutive translations of singular string.
	 *
	 * @dataProvider data_provider
	 *
	 * @param string $the_class Class loader to instantiate.
	 * @param string $mofile    Translation file to load.
	 */
	public function test_translate_two_times_singular( $the_class, $mofile ) {
		$this->setup_test( $the_class, $mofile );
		$this->assertSame( 'november', __( 'November' ) );
		$this->assertSame( 'november', __( 'November' ) );
	}

	/**
	 * Test 2 consecutive translations of singular string with context.
	 *
	 * @dataProvider data_provider
	 *
	 * @param string $the_class Class loader to instantiate.
	 * @param string $mofile    Translation file to load.
	 */
	public function test_translate_two_times_with_context( $the_class, $mofile ) {
		$this->setup_test( $the_class, $mofile );
		$this->assertSame( 'novembra', _x( 'November', 'genitive' ) );
		$this->assertSame( 'novembra', _x( 'November', 'genitive' ) );
	}

	/**
	 * Test same string, with and without context, with context last.
	 *
	 * @dataProvider data_provider
	 *
	 * @param string $the_class Class loader to instantiate.
	 * @param string $mofile    Translation file to load.
	 */
	public function test_translate_singular_and_with_context_after( $the_class, $mofile ) {
		$this->setup_test( $the_class, $mofile );
		$this->assertSame( 'november', __( 'November' ) );
		$this->assertSame( 'novembra', _x( 'November', 'genitive' ) );
	}

	/**
	 * Test same string, with and without context, with context first.
	 *
	 * @dataProvider data_provider
	 *
	 * @param string $the_class Class loader to instantiate.
	 * @param string $mofile    Translation file to load.
	 */
	public function test_translate_with_context_and_singular_after( $the_class, $mofile ) {
		$this->setup_test( $the_class, $mofile );
		$this->assertSame( 'novembra', _x( 'November', 'genitive' ) );
		$this->assertSame( 'november', __( 'November' ) );
	}

	/**
	 * Although the sniff recommends not to translate empty strings, WordPress may do it
	 * in the function _get_plugin_data_markup_translate() if some translatable fields
	 * haven't been filled in the plugin header.
	 *
	 * @dataProvider data_provider
	 *
	 * @param string $the_class Class loader to instantiate.
	 * @param string $mofile    Translation file to load.
	 */
	public function test_translate_empty_string( $the_class, $mofile ) {
		$this->setup_test( $the_class, $mofile );
		$this->assertSame( '', translate( '' ) ); // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings, WordPress.WP.I18n.LowLevelTranslationFunction
	}

	/**
	 * It's possible that a plugin translates the same (singular) string with __() and _n().
	 * This doesn't break WordPress and is exploited at least in WPML and Query Monitor.
	 * Here, the call to __() is made before the right call to _n() to test the impact of cache.
	 *
	 * @dataProvider data_provider
	 *
	 * @param string $the_class Class loader to instantiate.
	 * @param string $mofile    Translation file to load.
	 */
	public function test_translate_singular_plural_form_with_singular_singular_first( $the_class, $mofile ) {
		$this->setup_test( $the_class, $mofile );
		$this->assertSame( '%s razpoložljiva posodobitev', __( '%s update available' ) );
		$this->assertSame( '%s razpoložljiva posodobitev', _n( '%s update available', '%s updates available', 1 ) ); // 1, 101, 201
	}

	/**
	 * It's possible that a plugin translates the same (singular) string with __() and _n().
	 * This doesn't break WordPress and is exploited at least in WPML and Query Monitor.
	 * Here, the call to __() is made after the right call to _n() to test the impact of cache.
	 *
	 * @dataProvider data_provider
	 *
	 * @param string $the_class Class loader to instantiate.
	 * @param string $mofile    Translation file to load.
	 */
	public function test_translate_singular_plural_form_with_singular_plural_first( $the_class, $mofile ) {
		$this->setup_test( $the_class, $mofile );
		$this->assertSame( '%s razpoložljiva posodobitev', _n( '%s update available', '%s updates available', 1 ) ); // 1, 101, 201
		$this->assertSame( '%s razpoložljiva posodobitev', __( '%s update available' ) );
	}
}
