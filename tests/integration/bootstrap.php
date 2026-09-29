<?php
/**
 * Bootstrap dos testes de integração: carrega o WordPress real (bin/install-wp-tests.sh).
 *
 * @package WPSantoDoDia
 */

$santo_do_dia_root = dirname( __DIR__, 2 );
$santo_do_dia_cfg  = $santo_do_dia_root . '/build/wp/wp-tests-config.php';

if ( ! file_exists( $santo_do_dia_cfg ) ) {
	fwrite( STDERR, "Rode bin/install-wp-tests.sh antes dos testes de integração.\n" );
	exit( 1 );
}

putenv( 'WP_PHPUNIT__TESTS_CONFIG=' . $santo_do_dia_cfg );
define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $santo_do_dia_root . '/vendor/yoast/phpunit-polyfills' );

require_once $santo_do_dia_root . '/vendor/autoload.php';
require_once $santo_do_dia_root . '/vendor/wp-phpunit/wp-phpunit/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	static function () use ( $santo_do_dia_root ) {
		require $santo_do_dia_root . '/santododia.php';
	}
);

require $santo_do_dia_root . '/vendor/wp-phpunit/wp-phpunit/includes/bootstrap.php';
