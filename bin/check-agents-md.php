<?php
/**
 * Confere se o AGENTS.md só cita scripts do Composer e arquivos que existem.
 *
 * @package WPSantoDoDia
 */

$root     = dirname( __DIR__ );
$agents   = (string) file_get_contents( $root . '/AGENTS.md' );
$composer = json_decode( (string) file_get_contents( $root . '/composer.json' ), true );
$builtin  = array( 'install', 'update', 'require', 'remove', 'validate' );
$errors   = array();
$ignore   = array( 'Test.php' ); // Sufixo citado no texto, não um arquivo.

preg_match_all( '/`composer ([a-z][a-z-]*)/', $agents, $matches );
foreach ( array_unique( $matches[1] ) as $script ) {
	if ( ! in_array( $script, $builtin, true ) && ! isset( $composer['scripts'][ $script ] ) ) {
		$errors[] = "script do Composer inexistente: composer $script";
	}
}

// Caminhos entre crases que parecem arquivos do repositório (têm extensão ou barra).
preg_match_all( '/`((?:\.?[\w-]+\/)*\.?[\w-]+\.(?:php|md|json|xml|dist|yml|neon))`/', $agents, $paths );
foreach ( array_unique( $paths[1] ) as $path ) {
	if ( ! in_array( $path, $ignore, true ) && ! file_exists( $root . '/' . $path ) && ! file_exists( $root . '/tests/' . $path ) ) {
		$errors[] = "arquivo citado não existe: $path";
	}
}

foreach ( $errors as $error ) {
	fwrite( STDERR, "AGENTS.md: $error\n" );
}

if ( $errors ) {
	exit( 1 );
}

echo "AGENTS.md: referências válidas\n";
