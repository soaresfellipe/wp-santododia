<?php
/**
 * Gera docs/referencia.md a partir de santododia.php.
 *
 * Uso: php bin/generate-reference.php          (escreve o arquivo)
 *      php bin/generate-reference.php --check  (falha se o arquivo estiver desatualizado)
 *
 * @package WPSantoDoDia
 */

$root   = dirname( __DIR__ );
$source = (string) file_get_contents( $root . '/santododia.php' );
$target = $root . '/docs/referencia.md';

preg_match_all( "/define\\(\\s*'([A-Z_]+)',\\s*(.+?)\\s*\\);/", $source, $constants, PREG_SET_ORDER );
preg_match_all( "/(add_action|add_filter|add_shortcode|register_(?:activation|deactivation|uninstall)_hook)\\(\\s*('[^']+'|__FILE__),\\s*'([^']+)'/", $source, $hooks, PREG_SET_ORDER );
preg_match_all( "/do_action\\(\\s*'([^']+)'/", $source, $fired );
preg_match_all( '#/\*\*\s*\n((?:(?!\*/).)*)\*/\s*\nfunction\s+(\w+)\s*\(([^)]*)\)#s', $source, $functions, PREG_SET_ORDER );

$out   = array();
$out[] = '# Referência do plugin';
$out[] = '';
$out[] = '> Gerado por `bin/generate-reference.php` a partir de `santododia.php`. Não edite à mão:';
$out[] = '> rode `composer reference`.';
$out[] = '';
$out[] = '## Constantes';
$out[] = '';
$out[] = '| Nome | Valor |';
$out[] = '|---|---|';
foreach ( $constants as $c ) {
	$out[] = '| `' . $c[1] . '` | `' . $c[2] . '` |';
}

$out[] = '';
$out[] = '## Hooks registrados';
$out[] = '';
$out[] = '| Registro | Hook | Callback |';
$out[] = '|---|---|---|';
foreach ( $hooks as $h ) {
	$hook  = '__FILE__' === $h[2] ? '(arquivo do plugin)' : '`' . trim( $h[2], "'" ) . '`';
	$out[] = '| `' . $h[1] . '` | ' . $hook . ' | `' . $h[3] . '()` |';
}

$out[] = '';
$out[] = '## Actions disparadas pelo plugin';
$out[] = '';
foreach ( array_unique( $fired[1] ) as $action ) {
	$out[] = '- `' . $action . '`';
}

$out[] = '';
$out[] = '## Funções';
foreach ( $functions as $f ) {
	$lines   = array_map( static fn( $l ) => preg_replace( '/^\s*\*\s?/', '', $l ), explode( "\n", trim( $f[1] ) ) );
	$summary = array();
	$tags    = array();
	foreach ( $lines as $line ) {
		if ( '' !== $line && '@' === $line[0] ) {
			$tags[] = '- ' . preg_replace( '/\s+/', ' ', $line );
		} elseif ( ! $tags ) {
			$summary[] = $line;
		}
	}
	$out[] = '';
	$out[] = '### `' . $f[2] . '(' . trim( preg_replace( '/\s+/', ' ', $f[3] ) ) . ')`';
	$out[] = '';
	$out[] = trim( implode( "\n", $summary ) );
	if ( $tags ) {
		$out[] = '';
		array_push( $out, ...$tags );
	}
}

$markdown = implode( "\n", $out ) . "\n";

if ( in_array( '--check', $argv, true ) ) {
	if ( ! file_exists( $target ) || file_get_contents( $target ) !== $markdown ) {
		fwrite( STDERR, "docs/referencia.md está desatualizado: rode composer reference\n" );
		exit( 1 );
	}
	echo "docs/referencia.md atualizado\n";
	exit( 0 );
}

file_put_contents( $target, $markdown );
echo "docs/referencia.md gerado\n";
