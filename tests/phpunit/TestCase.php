<?php

namespace WP_Syntex\DynaMo;

use WP_Syntex\DynaMo\Plugin as Plugin;

class TestCase extends \WP_UnitTestCase {

	/**
	 * Initialize the plugin with the provided file loader class.
	 *
	 * @param string $class Class loader to instantiate.
	 */
	protected function init( $class ) {
		add_filter(
			'dynamo_file_loader',
			function() use ( $class ) {
				if ( false === strpos( $class, '\MO' ) ) {
					$class = "WP_Syntex\\DynaMo\\{$class}\\MO";
				}
				return new $class();
			}
		);
		( new Plugin() )->add_hooks();
	}
}
