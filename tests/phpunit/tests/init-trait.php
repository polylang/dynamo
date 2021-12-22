<?php

use WP_Syntex\DynaMo\Plugin as Plugin;

trait Init_Trait {

	/**
	 * Initialize the plugin with the provided file loader class.
	 *
	 * @param string $class Class loader to instantiate.
	 */
	protected function init( $class ) {
		add_filter(
			'dynamo_file_loader',
			function() use ( $class ) {
				return new $class();
			}
		);
		( new Plugin() )->add_hooks();
	}
}
