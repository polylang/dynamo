<?php

use WP_Syntex\DynaMo\Plugin as Plugin;

trait File_Loader_Provider_Trait {

	public function mo_provider() {
		return array(
			array( '\WP_Syntex\DynaMo\Dynamic\MO' ),
			array( '\WP_Syntex\DynaMo\Full\MO' ),
		);
	}
}
