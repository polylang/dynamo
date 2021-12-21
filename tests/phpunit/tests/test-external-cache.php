<?php

use WP_Syntex\DynaMo\Plugin as Plugin;

class External_Cache_Test extends WP_UnitTestCase {

	const CACHE_GROUP = 'DynaMo';

	const LAST_CHANGED = 1;

	public function set_up() {
		$this->using_ext_cache = (bool) wp_using_ext_object_cache();
		wp_using_ext_object_cache( true ); // Fake external object cache.
		wp_cache_set( 'last_changed', self::LAST_CHANGED, self::CACHE_GROUP );
	}

	public function tear_down() {
		wp_using_ext_object_cache( $this->using_ext_cache );
		wp_cache_delete( 'last_changed', self::CACHE_GROUP );

		unset( $GLOBALS['l10n'] );
	}

	public function test_read_translations_from_cache() {
		$last_changed = wp_cache_get_last_changed( self::CACHE_GROUP );
		$key          = md5( 'some_translations.mo' ) . ':' . self::LAST_CHANGED;
		$to_cache     = array(
			'plural_forms' => 'n > 1',
			'translations' => array(
				'She let the balloon float up into the air with her hopes and dreams.' => 'Elle a laissé le ballon flotter dans les airs avec ses espoirs et ses rêves.',
			),
		);
		wp_cache_set( $key, $to_cache, self::CACHE_GROUP );

		( new Plugin() )->add_hooks();
		load_textdomain( 'default', 'some_translations.mo' );

		$this->assertSame( 'Elle a laissé le ballon flotter dans les airs avec ses espoirs et ses rêves.', __( 'She let the balloon float up into the air with her hopes and dreams.' ) );
	}

	public function test_write_translations_to_cache() {
		$filename = TEST_DATA_DIR . 'some_translations.mo';

		( new Plugin() )->add_hooks();
		load_textdomain( 'default', $filename );

		$key   = md5( $filename ) . ':' . self::LAST_CHANGED;
		$cache = wp_cache_get( $key, self::CACHE_GROUP );

		$this->assertCount( 2, $cache );
		$this->assertArrayHasKey( 'plural_forms', $cache );
		$this->assertIsString( $cache['plural_forms'] );
		$this->assertArrayHasKey( 'translations', $cache );
		$this->assertCount( 4, $cache['translations'] ); // The number of translations in this .mo file.
	}
}