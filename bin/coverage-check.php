<?php
/**
 * Reprova quando a cobertura de linhas do clover.xml fica abaixo do mínimo.
 *
 * Uso: php bin/coverage-check.php <clover.xml> <percentual-mínimo>
 *
 * @package WPSantoDoDia
 */

if ( $argc < 3 ) {
	fwrite( STDERR, "Uso: php bin/coverage-check.php <clover.xml> <percentual-mínimo>\n" );
	exit( 2 );
}

$clover  = simplexml_load_file( $argv[1] );
$metrics = $clover->project->metrics;
$total   = (int) $metrics['statements'];
$covered = (int) $metrics['coveredstatements'];
$minimum = (float) $argv[2];
$percent = $total > 0 ? 100 * $covered / $total : 0.0;

printf( "Cobertura de linhas: %.2f%% (%d de %d); mínimo: %.2f%%\n", $percent, $covered, $total, $minimum );

exit( $percent + 1e-9 < $minimum ? 1 : 0 );
