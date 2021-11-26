<?php

trait Translations_Test_Trait {

	public function tear_down() {
		unset( $GLOBALS['l10n'] );
	}

	public function test_translate_singular() {
		$this->assertSame( '&laquo; Nazaj', __( '&laquo; Previous' ) );
		$this->assertSame( '&laquo; Prejšnja stran', __( '&laquo; Previous Page' ) );
		$this->assertSame( 'Omogoči', __( 'Activate' ) );
		$this->assertSame( 'Omogoči in objavi', __( 'Activate &amp; Publish' ) );
	}

	public function test_translate_with_context() {
		$this->assertSame( 'Kategorija', _x( 'Category', 'taxonomy singular name' ) );
	}

	public function test_translate_plurals() {
		$this->assertSame( '%s razpoložljiva posodobitev', _n( '%s update available', '%s updates available', 101 ) ); // 1, 101, 201
		$this->assertSame( '%s razpoložljivi posodobitvi', _n( '%s update available', '%s updates available', 102 ) ); // 2, 102, 202
		$this->assertSame( '%s razpoložljive posodobitve', _n( '%s update available', '%s updates available', 103 ) ); // 3, 4, 103
		$this->assertSame( '%s razpoložljivih posodobitev', _n( '%s update available', '%s updates available', 5 ) ); // 0, 5, 6
	}

	public function test_non_existing_singular() {
		$this->assertSame( 'Bla Bla Bla', __( 'Bla Bla Bla' ) );
	}

	public function test_non_existing_with_context() {
		$this->assertSame( 'Bla Bla Bla', _x( 'Bla Bla Bla', 'Any context' ) );
	}

	public function test_non_existing_plural() {
		$this->assertSame( '%s horse galloping in the meadow', _n( '%s horse galloping in the meadow', '%s horses galloping in the meadow', 1 ) );
		$this->assertSame( '%s horses galloping in the meadow', _n( '%s horse galloping in the meadow', '%s horses galloping in the meadow', 101 ) );
		$this->assertSame( '%s horses galloping in the meadow', _n( '%s horse galloping in the meadow', '%s horses galloping in the meadow', 102 ) );
	}

	public function test_translate_two_times_singular() {
		$this->assertSame( 'november', __( 'November' ) );
		$this->assertSame( 'november', __( 'November' ) );
	}

	public function test_translate_two_times_with_context() {
		$this->assertSame( 'novembra', _x( 'November', 'genitive' ) );
		$this->assertSame( 'novembra', _x( 'November', 'genitive' ) );
	}

	public function test_translate_singular_and_with_context_after() {
		$this->assertSame( 'november', __( 'November' ) );
		$this->assertSame( 'novembra', _x( 'November', 'genitive' ) );
	}

	public function test_translate_with_context_and_singular_after() {
		$this->assertSame( 'novembra', _x( 'November', 'genitive' ) );
		$this->assertSame( 'november', __( 'November' ) );
	}

	/**
	 * Although the sniff recommends not to translate empty strings, WordPress may do it
	 * in the function _get_plugin_data_markup_translate() if some translatable fields
	 * haven't been filled in the plugin header.
	 */
	public function test_translate_empty_string() {
		$this->assertSame( '', translate( '' ) ); // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings, WordPress.WP.I18n.LowLevelTranslationFunction
	}
}
