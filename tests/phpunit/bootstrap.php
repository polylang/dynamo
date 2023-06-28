<?php

$_root_dir  = dirname( dirname( __DIR__ ) );
$_tests_dir = $_root_dir . '/tmp/wordpress-tests-lib';
require_once $_tests_dir . '/includes/functions.php';

require_once $_root_dir . '/vendor/autoload.php';

define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_root_dir . '/vendor/yoast/phpunit-polyfills/' );
require_once $_tests_dir . '/includes/bootstrap.php';

if ( ! defined( 'DIR_TESTROOT' ) ) {
	define( 'DIR_TESTROOT', $_tests_dir );
}

if ( ! defined( 'TEST_DATA_DIR' ) ) {
	define( 'TEST_DATA_DIR', __DIR__ . '/data/' );
}

printf(
	'Testing DynaMo with WordPress %1$s...' . PHP_EOL,
	$GLOBALS['wp_version']
);
