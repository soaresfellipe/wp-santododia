<?php
/**
 * Imprime as notas de uma versão a partir do changelog do README.txt
 * e confere se a versão bate com santododia.php e o Stable tag.
 *
 * Uso: php bin/release-notes.php <versão>
 *
 * @package WPSantoDoDia
 */

$root    = dirname( __DIR__ );
$version = $argv[1] ?? '';
$readme  = (string) file_get_contents( $root . '/README.txt' );
$plugin  = (string) file_get_contents( $root . '/santododia.php' );
$found   = array(
	'Version (santododia.php)'      => preg_match( '/^\s*\*\s*Version:\s*(\S+)/m', $plugin, $m ) ? $m[1] : '',
	'SANTO_DO_DIA_VERSION'          => preg_match( "/'SANTO_DO_DIA_VERSION',\s*'([^']+)'/", $plugin, $m ) ? $m[1] : '',
	'Stable tag (README.txt)'       => preg_match( '/^Stable tag:\s*(\S+)/m', $readme, $m ) ? $m[1] : '',
);

$ok = true;
foreach ( $found as $where => $value ) {
	if ( $value !== $version ) {
		fwrite( STDERR, "$where é '$value', esperado '$version'\n" );
		$ok = false;
	}
}

if ( ! preg_match( '/^= ' . preg_quote( $version, '/' ) . ' =\R(.*?)(?=^= |^== |\z)/ms', $readme, $section ) ) {
	fwrite( STDERR, "Changelog do README.txt não tem a seção = $version =\n" );
	$ok = false;
}

if ( ! $ok ) {
	exit( 1 );
}

echo trim( $section[1] ), "\n";
