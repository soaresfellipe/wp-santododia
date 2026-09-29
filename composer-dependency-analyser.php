<?php
/**
 * Configuração do shipmonk/composer-dependency-analyser.
 *
 * @package WPSantoDoDia
 */

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;

return ( new Configuration() )
	->addPathToScan( __DIR__ . '/santododia.php', false )
	->addPathToScan( __DIR__ . '/tests', true )
	// Classes do core do WordPress: existem em tempo de execução, não via Composer.
	->ignoreUnknownClasses( array( 'WP_Error' ) );
