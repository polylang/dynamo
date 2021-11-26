<?php

use WP_Syntex\DynaMo\Plugin as Plugin;

class MO_Test extends WP_UnitTestCase {

	public function tear_down() {
		unset( $GLOBALS['l10n'] );
		remove_filter( 'locale', array( $this, 'filter_set_locale_to_german' ) );
	}

	/**
	 * Make sure load_textdomain() loads our class.
	 */
	public function test_instance() {
		( new Plugin() )->add_hooks();
		load_textdomain( 'domain', TEST_DATA_DIR . 'some_translations.mo' );
		$this->assertTrue( $GLOBALS['l10n']['domain'] instanceof WP_Syntex\DynaMo\MO );
	}

	public function test_loading_two_files_should_include_strings_of_both_files() {
		( new Plugin() )->add_hooks();
		load_textdomain( 'default', TEST_DATA_DIR . 'automatic.mo' );
		load_textdomain( 'default', TEST_DATA_DIR . 'some_translations.mo' );

		// From automatic.mo.
		$this->assertSame( 'En regardant par la fenêtre, il a vu un clown passer.', __( 'As he looked out the window, he saw a clown walk by.' ) );

		// From some_translations.mo.
		$this->assertSame( 'Les fourmis ont plus apprécié le barbecue que la famille.', __( 'The ants enjoyed the barbecue more than the family.' ) );
	}

	public function test_loading_two_files_should_not_overwrite_first_strings() {
		( new Plugin() )->add_hooks();
		load_textdomain( 'default', TEST_DATA_DIR . 'alternative.mo' );
		load_textdomain( 'default', TEST_DATA_DIR . 'automatic.mo' );

		// From alternative.mo.
		$this->assertSame( 'En regardant par la fenêtre, il vit un clown passer.', __( 'As he looked out the window, he saw a clown walk by.' ) );
	}

	public function test_merge_should_keep_other_strings() {
		$mo = new WP_Syntex\DynaMo\MO();
		$mo->import_from_file( TEST_DATA_DIR . 'alternative.mo' );

		$other = new WP_Syntex\DynaMo\MO();
		$other->import_from_file( TEST_DATA_DIR . 'automatic.mo' );

		$mo->merge_with( $other );
		// From automatic.mo.
		$this->assertSame( 'Il ne comprenait pas pourquoi l\'oiseau voulait faire du vélo.', $mo->translate( 'He didn\'t understand why the bird wanted to ride the bicycle.' ) );
	}

	/**
	 * Just test that we don't have any error.
	 */
	public function test_merge_into_WP_MO() {
		$wp_mo  = new \MO();
		$our_mo = new WP_Syntex\DynaMo\MO();

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
	 */
	public function test_load_just_in_time() {
		( new Plugin() )->add_hooks();

		add_filter( 'locale', array( $this, 'filter_set_locale_to_german' ) );

		require_once DIR_TESTDATA . '/plugins/internationalized-plugin.php';

		$this->assertFalse( is_textdomain_loaded( 'internationalized-plugin' ) );
		$this->assertSame( 'Das ist ein Dummy Plugin', i18n_plugin_test() );
		$this->assertTrue( is_textdomain_loaded( 'internationalized-plugin' ) );
		$this->assertTrue( $GLOBALS['l10n']['internationalized-plugin'] instanceof WP_Syntex\DynaMo\MO );
	}
}
