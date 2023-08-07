<?php

namespace WP_Syntex\DynaMo\Tests;

class MO extends \WP_Syntex\DynaMo\TestCase {

	public function tear_down() {
		unset( $GLOBALS['l10n'] );
		remove_filter( 'locale', array( $this, 'filter_set_locale_to_german' ) );
	}

	/**
	 * Make sure load_textdomain() loads our class.
	 *
	 * @testWith ["WP_Syntex\\DynaMo\\Dynamic\\MO"]
	 *           ["WP_Syntex\\DynaMo\\Full\\MO"]
	 *
	 * @param string $the_class Class loader to instantiate.
	 */
	public function test_instance( $the_class ) {
		$this->init( $the_class );
		load_textdomain( 'domain', TEST_DATA_DIR . 'some_translations.mo' );
		$this->assertTrue( $GLOBALS['l10n']['domain'] instanceof $the_class );
	}

	/**
	 * Test to read a wrong file.
	 *
	 * @testWith ["Dynamic"]
	 *           ["Full"]
	 *
	 * @param string $the_class Class loader to instantiate.
	 */
	public function test_unreadable_file( $the_class ) {
		$this->init( $the_class );
		load_textdomain( 'domain', 'some_unreadable_file.mo' );
		$this->assertEmpty( $GLOBALS['l10n'] );
	}

	/**
	 * Test loading two files for same domain with different strings.
	 *
	 * @testWith ["Dynamic"]
	 *           ["Full"]
	 *
	 * @param string $the_class Class loader to instantiate.
	 */
	public function test_loading_two_files_should_include_strings_of_both_files( $the_class ) {
		$this->init( $the_class );
		load_textdomain( 'default', TEST_DATA_DIR . 'automatic.mo' );
		load_textdomain( 'default', TEST_DATA_DIR . 'some_translations.mo' );

		// From automatic.mo.
		$this->assertSame( 'En regardant par la fenêtre, il a vu un clown passer.', __( 'As he looked out the window, he saw a clown walk by.' ) );

		// From some_translations.mo.
		$this->assertSame( 'Les fourmis ont plus apprécié le barbecue que la famille.', __( 'The ants enjoyed the barbecue more than the family.' ) );
	}

	/**
	 * Test loading two files for same domain with same strings.
	 *
	 * @testWith ["Dynamic"]
	 *           ["Full"]
	 *
	 * @param string $the_class Class loader to instantiate.
	 */
	public function test_loading_two_files_should_not_overwrite_first_strings( $the_class ) {
		$this->init( $the_class );
		load_textdomain( 'default', TEST_DATA_DIR . 'alternative.mo' );
		load_textdomain( 'default', TEST_DATA_DIR . 'automatic.mo' );

		// From alternative.mo.
		$this->assertSame( 'En regardant par la fenêtre, il vit un clown passer.', __( 'As he looked out the window, he saw a clown walk by.' ) );
	}

	/**
	 * Test merging two files.
	 *
	 * @testWith ["WP_Syntex\\DynaMo\\Dynamic\\MO"]
	 *           ["WP_Syntex\\DynaMo\\Full\\MO"]
	 *
	 * @param string $the_class Class loader to instantiate.
	 */
	public function test_merge_should_keep_other_strings( $the_class ) {
		$mo = new $the_class();
		$mo->import_from_file( TEST_DATA_DIR . 'alternative.mo' );

		$other = new $the_class();
		$other->import_from_file( TEST_DATA_DIR . 'automatic.mo' );

		$mo->merge_with( $other );
		// From automatic.mo.
		$this->assertSame( 'Il ne comprenait pas pourquoi l\'oiseau voulait faire du vélo.', $mo->translate( 'He didn\'t understand why the bird wanted to ride the bicycle.' ) );
	}

	/**
	 * Just test that we don't have any error.
	 *
	 * @testWith ["WP_Syntex\\DynaMo\\Dynamic\\MO"]
	 *           ["WP_Syntex\\DynaMo\\Full\\MO"]
	 *
	 * @param string $the_class Class loader to instantiate.
	 */
	public function test_merge_into_WP_MO( $the_class ) {
		$wp_mo  = new \MO();
		$our_mo = new $the_class();
		$wp_mo->merge_with( $our_mo );
		$this->assertTrue( $wp_mo instanceof \MO );
	}

	public function filter_set_locale_to_german() {
		return 'de_DE';
	}

	/**
	 * For this test, we use the data from the WordPress tests.
	 * This is required because we cannot define our own value for WP_LANG_DIR.
	 *
	 * @see https://github.com/WordPress/wordpress-develop/blob/5.8.2/tests/phpunit/includes/bootstrap.php#L215
	 *
	 * The test is directly inspired from a WordPress test
	 *
	 * @see https://github.com/WordPress/wordpress-develop/blob/5.8.2/tests/phpunit/tests/l10n/loadTextdomainJustInTime.php#L63-L80
	 *
	 * @testWith ["WP_Syntex\\DynaMo\\Dynamic\\MO"]
	 *           ["WP_Syntex\\DynaMo\\Full\\MO"]
	 *
	 * @param string $the_class Class loader to instantiate.
	 */
	public function test_load_just_in_time( $the_class ) {
		$this->init( $the_class );

		add_filter( 'locale', array( $this, 'filter_set_locale_to_german' ) );

		require_once DIR_TESTDATA . '/plugins/internationalized-plugin.php';

		$this->assertFalse( is_textdomain_loaded( 'internationalized-plugin' ) );
		$this->assertSame( 'Das ist ein Dummy Plugin', i18n_plugin_test() );
		$this->assertTrue( is_textdomain_loaded( 'internationalized-plugin' ) );
		$this->assertTrue( $GLOBALS['l10n']['internationalized-plugin'] instanceof $the_class );
	}

	/**
	 * WPML can generate MO files without translations headers.
	 *
	 * @see https://github.com/polylang/dynamo/issues/23
	 *
	 * @testWith ["Dynamic"]
	 *           ["Full"]
	 *
	 * @param string $the_class Class loader to instantiate.
	 */
	public function test_mofile_without_translations_headers( $the_class ) {
		$this->init( $the_class );
		load_textdomain( 'gravity_form-1', TEST_DATA_DIR . 'gravity_form-1-de_DE.mo' );

		$this->assertSame( 'First Name', __( 'field-1-label', 'gravity_form-1' ) );
	}
}
