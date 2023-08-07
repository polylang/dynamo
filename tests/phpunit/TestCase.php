<?php

namespace WP_Syntex\DynaMo;

use WP_Syntex\DynaMo\Plugin;

class TestCase extends \WP_UnitTestCase {

	/**
	 * Initialize the plugin with the provided file loader class.
	 *
	 * @param string $the_class Class loader to instantiate.
	 */
	protected function init( $the_class ) {
		add_filter(
			'dynamo_file_loader',
			function() use ( $the_class ) {
				if ( false === strpos( $the_class, '\MO' ) ) {
					$the_class = "WP_Syntex\\DynaMo\\{$the_class}\\MO";
				}
				return new $the_class();
			}
		);
		( new Plugin() )->add_hooks();
	}
}
