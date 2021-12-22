<?php

use WP_Syntex\DynaMo\Plugin as Plugin;

trait File_Loader_Provider_Trait {

	public function mo_provider() {
		return array(
			array( 'WP_Syntex\DynaMo\Dynamic\MO' ),
			array( 'WP_Syntex\DynaMo\Full\MO' ),
		);
	}

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
