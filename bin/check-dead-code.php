<?php
/**
 * Aponta funções do plugin que não são chamadas nem registradas como callback.
 *
 * Callbacks do WordPress são referenciados por string ('santo_do_dia_x'),
 * por isso a busca considera qualquer ocorrência do nome fora da definição.
 *
 * @package WPSantoDoDia
 */

$source = (string) file_get_contents( dirname( __DIR__ ) . '/santododia.php' );
$tokens = token_get_all( $source );
$names  = array();
$uses   = array();

foreach ( $tokens as $i => $token ) {
	if ( ! is_array( $token ) || T_STRING !== $token[0] ) {
		continue;
	}

	$previous = $i - 1;
	while ( $previous >= 0 && is_array( $tokens[ $previous ] ) && T_WHITESPACE === $tokens[ $previous ][0] ) {
		--$previous;
	}

	if ( $previous >= 0 && is_array( $tokens[ $previous ] ) && T_FUNCTION === $tokens[ $previous ][0] ) {
		$names[] = $token[1];
	} else {
		$uses[ $token[1] ] = true;
	}
}

foreach ( $tokens as $token ) {
	if ( is_array( $token ) && T_CONSTANT_ENCAPSED_STRING === $token[0] ) {
		$uses[ trim( $token[1], "'\"" ) ] = true;
	}
}

$dead = array_values( array_filter( $names, static fn( $name ) => ! isset( $uses[ $name ] ) ) );

foreach ( $dead as $name ) {
	fwrite( STDERR, "Função sem uso: $name()\n" );
}

if ( $dead ) {
	exit( 1 );
}

printf( "Código morto: nenhuma das %d funções está sem uso\n", count( $names ) );
