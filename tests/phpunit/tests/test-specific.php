<?php

use WP_Syntex\DynaMo\Plugin as Plugin;

/**
 * This class adds specific tests for bugs encountered with specific translation files.
 */
class Specific_Test extends WP_UnitTestCase {

	public function tear_down() {
		unset( $GLOBALS['l10n'] );
	}

	public function test_shipment_tracking() {
		( new Plugin() )->add_hooks();
		load_textdomain( 'shipment-tracking', TEST_DATA_DIR . 'woocommerce-shipment-tracking-fr_FR.mo' );

		// Call done by _get_plugin_data_markup_translate().
		$this->assertSame( '1.6.3', translate( '1.6.3', 'shipment-tracking' ) ); // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction
	}
}
