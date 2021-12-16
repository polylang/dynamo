<?php

use WP_Syntex\DynaMo\Plugin as Plugin;

/**
 * This class adds specific tests for bugs encountered with specific translation files.
 */
class Specific_Test extends WP_UnitTestCase {

	public function tear_down() {
		unset( $GLOBALS['l10n'] );
	}

	public function mo_provider() {
		return array(
			array( 'WP_Syntex\DynaMo\Dynamic\MO' ),
			array( 'WP_Syntex\DynaMo\Full\MO' ),
		);
	}

	protected function init( $class ) {
		add_filter(
			'dynamo_file_loader',
			function() use ( $class ) {
				return new $class();
			}
		);
		( new Plugin() )->add_hooks();
	}

	/**
	 * Test a WC Shipment tracking translation file which generated a notice
	 * during the development of the inital version.
	 *
	 * @dataProvider mo_provider
	 */
	public function test_shipment_tracking( $class ) {
		$this->init( $class );
		load_textdomain( 'shipment-tracking', TEST_DATA_DIR . 'woocommerce-shipment-tracking-fr_FR.mo' );

		// Call done by _get_plugin_data_markup_translate().
		$this->assertSame( '1.6.3', translate( '1.6.3', 'shipment-tracking' ) ); // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction
	}
}
