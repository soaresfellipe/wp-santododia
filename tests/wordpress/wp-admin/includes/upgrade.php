<?php
/**
 * Stub de wp-admin/includes/upgrade.php para os testes unitários.
 *
 * @package WPSantoDoDia
 */

function dbDelta( $sql ) {
	$GLOBALS['santo_do_dia_dbdelta'][] = $sql;
	return array();
}
