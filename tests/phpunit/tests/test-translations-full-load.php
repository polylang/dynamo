<?php

use WP_Syntex\DynaMo\Plugin as Plugin;

class Translations_Full_Load_Test extends WP_UnitTestCase {
	use Translations_Test_Trait;

	public function set_up() {
		add_filter(
			'dynamo_file_loader',
			function() {
				return new \WP_Syntex\DynaMo\Full\MO();
			}
		);
		( new Plugin() )->add_hooks();
		load_textdomain( 'default', TEST_DATA_DIR . 'sl_SI_without_hash_table.mo' );
	}
}

