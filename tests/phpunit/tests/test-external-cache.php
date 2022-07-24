<?php

use WP_Syntex\DynaMo\Plugin as Plugin;

class External_Cache_Test extends WP_UnitTestCase {

	const CACHE_GROUP = 'DynaMo';

	public function set_up() {
		$this->using_ext_cache = (bool) wp_using_ext_object_cache();
		wp_using_ext_object_cache( true ); // Fake external object cache.
	}

	public function tear_down() {
		wp_using_ext_object_cache( $this->using_ext_cache );
		wp_cache_delete( 'last_changed', self::CACHE_GROUP );

		unset( $GLOBALS['l10n'] );
	}

	public function test_read_translations_from_cache() {
		$filename     = TEST_DATA_DIR . 'some_translations.mo';
		$last_changed = microtime();
		$key          = md5( $filename ) . ':' . $last_changed;
		$to_cache     = array(
			'plural_forms' => 'n > 1',
			'translations' => array(
				'She let the balloon float up into the air with her hopes and dreams.' => 'Elle a laissé le ballon flotter dans les airs avec ses espoirs et ses rêves.',
			),
		);

		wp_cache_set( 'last_changed', $last_changed, self::CACHE_GROUP );
		wp_cache_set( $key, $to_cache, self::CACHE_GROUP );

		( new Plugin() )->add_hooks();
		load_textdomain( 'default', $filename );

		$this->assertSame( 'Elle a laissé le ballon flotter dans les airs avec ses espoirs et ses rêves.', __( 'She let the balloon float up into the air with her hopes and dreams.' ) );
	}

	public function test_write_translations_to_cache() {
		$filename     = TEST_DATA_DIR . 'some_translations.mo';
		$last_changed = microtime();
		$key          = md5( $filename ) . ':' . $last_changed;

		wp_cache_set( 'last_changed', $last_changed, self::CACHE_GROUP );

		( new Plugin() )->add_hooks();
		load_textdomain( 'default', $filename );

		$cache = wp_cache_get( $key, self::CACHE_GROUP );

		$this->assertCount( 2, $cache );
		$this->assertArrayHasKey( 'plural_forms', $cache );
		$this->assertIsString( $cache['plural_forms'] );
		$this->assertArrayHasKey( 'translations', $cache );
		$this->assertCount( 5, $cache['translations'] ); // The number of translations in this .mo file, including the empty string.
	}

	public function test_write_to_cache_only_once() {
		$count = 0;

		// A trick to count how many times the cache is written.
		add_filter(
			'dynamo_cache_expire',
			function( $expire ) use ( &$count ) {
				$count++;
				return $expire;
			}
		);

		$filename = TEST_DATA_DIR . 'some_translations.mo';
		( new Plugin() )->add_hooks();

		load_textdomain( 'default', $filename );
		$this->assertSame( 1, $count );

		load_textdomain( 'default', $filename );
		$this->assertSame( 1, $count );
	}

	public function test_mo_from_file_and_from_cache_are_equals() {
		$filename = TEST_DATA_DIR . 'some_translations.mo';
		( new Plugin() )->add_hooks();

		load_textdomain( 'default', $filename );
		$mo_from_file = $GLOBALS['l10n']['default'];
		unset( $GLOBALS['l10n'] );

		load_textdomain( 'default', $filename );
		$mo_from_cache = $GLOBALS['l10n']['default'];

		// The 2 objects are clones but not the same.
		$this->assertEquals( $mo_from_file, $mo_from_cache );
		$this->assertNotSame( $mo_from_file, $mo_from_cache );
	}
}
