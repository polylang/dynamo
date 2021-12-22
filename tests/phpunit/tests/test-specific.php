<?php

/**
 * This class adds specific tests for bugs encountered with specific translation files.
 */
class Specific_Test extends WP_UnitTestCase {
	use File_Loader_Provider_Trait;

	public function tear_down() {
		unset( $GLOBALS['l10n'] );
	}

	/**
	 * Test a WC Shipment tracking translation file which generated a notice
	 * during the development of the inital version.
	 *
	 * @dataProvider mo_provider
	 *
	 * @param string $class Class loader to instantiate.
	 */
	public function test_shipment_tracking( $class ) {
		$this->init( $class );
		load_textdomain( 'shipment-tracking', TEST_DATA_DIR . 'woocommerce-shipment-tracking-fr_FR.mo' );

		// Call done by _get_plugin_data_markup_translate().
		$this->assertSame( '1.6.3', translate( '1.6.3', 'shipment-tracking' ) ); // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction
	}
}
