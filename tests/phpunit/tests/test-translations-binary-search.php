<?php

require_once __DIR__ . '/translations-test-trait.php';

use WP_Syntex\DynaMo\Plugin as Plugin;

class Translations_Binary_Search_Test extends WP_UnitTestCase {
	use Translations_Test_Trait;

	public function set_up() {
		( new Plugin() )->add_hooks();
		load_textdomain( 'default', TEST_DATA_DIR . 'sl_SI_without_hash_table.mo' );
	}
}

