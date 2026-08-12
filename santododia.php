<?php
/**
 * Plugin Name: Santo do Dia
 * Plugin URI: https://fellipesoares.com.br/wp-santo-do-dia
 * Description: Exiba o Santo do dia através do shortcode [santododia].
 * Version: 2.2.0
 * Requires at least: 6.3
 * Requires PHP: 7.4
 * Author: Fellipe Soares
 * Author URI: https://fellipesoares.com.br
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: santo-do-dia
 *
 * @package WPSantoDoDia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SANTO_DO_DIA_VERSION', '2.2.0' );
define( 'SANTO_DO_DIA_API_URL', 'https://catolicoapp.com/wp-json/wp/v2/santos' );

/**
 * Returns the plugin table name.
 *
 * @return string
 */
function santo_do_dia_table_name() {
	global $wpdb;

	return $wpdb->prefix . 'santo_do_dia';
}

/**
 * Creates or updates the plugin table.
 *
 * @return void
 */
function santo_do_dia_create_table() {
	global $wpdb;

	$table_name      = santo_do_dia_table_name();
	$charset_collate = $wpdb->get_charset_collate();
	$sql             = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		dia tinyint(2) NOT NULL,
		mes tinyint(2) NOT NULL,
		nome varchar(255) NOT NULL,
		imagem text NOT NULL,
		url text NOT NULL,
		atualizado timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		UNIQUE KEY data (dia,mes)
	) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}

/**
 * Registers the recurring jobs used by the plugin.
 *
 * @return void
 */
function santo_do_dia_schedule_events() {
	if ( ! wp_next_scheduled( 'santo_do_dia_cron_diario' ) ) {
		$next_midnight = new DateTimeImmutable( 'tomorrow midnight', wp_timezone() );
		wp_schedule_event( $next_midnight->getTimestamp(), 'daily', 'santo_do_dia_cron_diario' );
	}

	if ( ! wp_next_scheduled( 'santo_do_dia_cron_fallback' ) ) {
		wp_schedule_event( time(), 'sixhours', 'santo_do_dia_cron_fallback' );
	}
}

/**
 * Installs the database table and recurring jobs.
 *
 * @return void
 */
function santo_do_dia_install() {
	santo_do_dia_create_table();
	santo_do_dia_schedule_events();
	update_option( 'santo_do_dia_version', SANTO_DO_DIA_VERSION );
	santo_do_dia_obter_dados();
}
register_activation_hook( __FILE__, 'santo_do_dia_install' );

/**
 * Applies schema and schedule changes to already active installations.
 *
 * @return void
 */
function santo_do_dia_maybe_upgrade() {
	if ( SANTO_DO_DIA_VERSION === get_option( 'santo_do_dia_version' ) ) {
		return;
	}

	santo_do_dia_create_table();
	santo_do_dia_schedule_events();
	update_option( 'santo_do_dia_version', SANTO_DO_DIA_VERSION );
}
add_action( 'plugins_loaded', 'santo_do_dia_maybe_upgrade' );

/**
 * Returns the current day and month in the WordPress timezone.
 *
 * @return int[]
 */
function santo_do_dia_current_date() {
	$now = current_datetime();

	return array(
		(int) $now->format( 'd' ),
		(int) $now->format( 'm' ),
	);
}

/**
 * Reports an API or persistence error to integrations.
 *
 * @param string $code    Stable error code.
 * @param string $message Error message.
 * @return WP_Error
 */
function santo_do_dia_error( $code, $message ) {
	$error = new WP_Error( $code, $message );

	/**
	 * Fires when the plugin cannot retrieve or persist the saint data.
	 *
	 * @param WP_Error $error Error details.
	 */
	do_action( 'santo_do_dia_api_error', $error );

	return $error;
}

/**
 * Fetches and persists the saint for the current date.
 *
 * @param bool $force Whether an existing record should be refreshed.
 * @return true|WP_Error
 */
function santo_do_dia_obter_dados( $force = false ) {
	global $wpdb;

	list( $dia, $mes ) = santo_do_dia_current_date();
	$table_name        = santo_do_dia_table_name();
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- The custom table is the source of truth for this date.
	$santo_exists = (int) $wpdb->get_var(
		$wpdb->prepare(
			'SELECT COUNT(*) FROM %i WHERE dia = %d AND mes = %d',
			$table_name,
			$dia,
			$mes
		)
	);

	if ( $santo_exists > 0 && ! $force ) {
		return true;
	}

	$url      = add_query_arg(
		array(
			'dia' => $dia,
			'mes' => $mes,
		),
		SANTO_DO_DIA_API_URL
	);
	$response = wp_safe_remote_get(
		$url,
		array(
			'timeout'             => 15,
			'redirection'         => 3,
			'limit_response_size' => 1024 * 1024,
			'headers'             => array( 'Accept' => 'application/json' ),
		)
	);

	if ( is_wp_error( $response ) ) {
		return santo_do_dia_error( 'santo_do_dia_http_error', $response->get_error_message() );
	}

	if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
		return santo_do_dia_error(
			'santo_do_dia_http_status',
			__( 'A API do Santo do Dia retornou uma resposta inesperada.', 'santo-do-dia' )
		);
	}

	$dados = json_decode( wp_remote_retrieve_body( $response ), true );
	if (
		JSON_ERROR_NONE !== json_last_error()
		|| ! is_array( $dados )
		|| empty( $dados[0] )
		|| ! is_array( $dados[0] )
		|| empty( $dados[0]['title']['rendered'] )
		|| empty( $dados[0]['imagem_destacada'] )
		|| empty( $dados[0]['link'] )
		|| ! is_string( $dados[0]['title']['rendered'] )
		|| ! is_string( $dados[0]['imagem_destacada'] )
		|| ! is_string( $dados[0]['link'] )
	) {
		return santo_do_dia_error(
			'santo_do_dia_invalid_response',
			__( 'A API do Santo do Dia retornou dados inválidos.', 'santo-do-dia' )
		);
	}

	$nome   = sanitize_text_field(
		wp_strip_all_tags(
			html_entity_decode( $dados[0]['title']['rendered'], ENT_QUOTES | ENT_HTML5, 'UTF-8' )
		)
	);
	$imagem = esc_url_raw( $dados[0]['imagem_destacada'], array( 'http', 'https' ) );
	$link   = esc_url_raw( $dados[0]['link'], array( 'http', 'https' ) );

	if ( '' === $nome || ! wp_http_validate_url( $imagem ) || ! wp_http_validate_url( $link ) ) {
		return santo_do_dia_error(
			'santo_do_dia_invalid_fields',
			__( 'A API do Santo do Dia retornou campos inválidos.', 'santo-do-dia' )
		);
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Persisting API data in the plugin's custom table.
	$result = $wpdb->replace(
		$table_name,
		array(
			'dia'    => $dia,
			'mes'    => $mes,
			'nome'   => $nome,
			'imagem' => $imagem,
			'url'    => $link,
		),
		array( '%d', '%d', '%s', '%s', '%s' )
	);

	if ( false === $result ) {
		return santo_do_dia_error(
			'santo_do_dia_database_error',
			__( 'Não foi possível salvar os dados do Santo do Dia.', 'santo-do-dia' )
		);
	}

	delete_transient( 'santo_do_dia_html_' . $dia . '_' . $mes );

	return true;
}

/**
 * Refreshes the current record during the daily cron job.
 *
 * @return true|WP_Error
 */
function santo_do_dia_atualizar_dados() {
	return santo_do_dia_obter_dados( true );
}
add_action( 'santo_do_dia_cron_diario', 'santo_do_dia_atualizar_dados' );

/**
 * Registers the six-hour fallback interval.
 *
 * @param array<string, array<string, int|string>> $schedules Existing schedules.
 * @return array<string, array<string, int|string>>
 */
function santo_do_dia_cron_schedules( $schedules ) {
	$schedules['sixhours'] = array(
		'interval' => 6 * HOUR_IN_SECONDS,
		'display'  => __( 'A cada 6 horas', 'santo-do-dia' ),
	);

	return $schedules;
}
add_filter( 'cron_schedules', 'santo_do_dia_cron_schedules' );

/**
 * Fetches the current data when the daily job did not populate it.
 *
 * @return void
 */
function santo_do_dia_verificar_dados() {
	global $wpdb;

	list( $dia, $mes ) = santo_do_dia_current_date();
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- The fallback must verify the custom table directly.
	$santo_exists = (int) $wpdb->get_var(
		$wpdb->prepare(
			'SELECT COUNT(*) FROM %i WHERE dia = %d AND mes = %d',
			santo_do_dia_table_name(),
			$dia,
			$mes
		)
	);

	if ( 0 === $santo_exists ) {
		santo_do_dia_obter_dados();
	}
}
add_action( 'santo_do_dia_cron_fallback', 'santo_do_dia_verificar_dados' );

/**
 * Renders the saint card shortcode.
 *
 * @return string
 */
function santo_do_dia() {
	global $wpdb;

	list( $dia, $mes ) = santo_do_dia_current_date();
	$cache_key         = 'santo_do_dia_html_' . $dia . '_' . $mes;
	$html              = get_transient( $cache_key );

	if ( false !== $html ) {
		return $html;
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- The rendered result is cached in a transient.
	$santo = $wpdb->get_row(
		$wpdb->prepare(
			'SELECT nome, imagem FROM %i WHERE dia = %d AND mes = %d LIMIT 1',
			santo_do_dia_table_name(),
			$dia,
			$mes
		)
	);

	if ( ! $santo ) {
		santo_do_dia_obter_dados();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Re-read after the attempted API refresh.
		$santo = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT nome, imagem FROM %i WHERE dia = %d AND mes = %d LIMIT 1',
				santo_do_dia_table_name(),
				$dia,
				$mes
			)
		);
	}

	if ( ! $santo ) {
		return '<p class="santo-do-dia-indisponivel">'
			. esc_html__( 'O Santo do Dia está temporariamente indisponível.', 'santo-do-dia' )
			. '</p>';
	}

	$base_url = 'https://catolicoapp.com/santo-do-dia';
	$html     = '<div class="santo-do-dia-container">';
	$html    .= '<h3>' . esc_html__( 'Santo do Dia', 'santo-do-dia' ) . '</h3>';
	$html    .= '<div class="santo-do-dia-card">';
	$html    .= '<a href="' . esc_url( $base_url ) . '" target="_blank" rel="noopener noreferrer">';
	$html    .= '<img src="' . esc_url( $santo->imagem ) . '" alt="' . esc_attr( $santo->nome ) . '" loading="lazy" decoding="async" />';
	$html    .= '</a>';
	$html    .= '<p><a href="' . esc_url( $base_url ) . '" target="_blank" rel="noopener noreferrer">';
	$html    .= esc_html( $santo->nome ) . '</a></p>';
	$html    .= '</div></div>';

	set_transient( $cache_key, $html, 12 * HOUR_IN_SECONDS );

	return $html;
}
add_shortcode( 'santododia', 'santo_do_dia' );

/**
 * Loads the stylesheet only on singular content containing the shortcode.
 *
 * @return void
 */
function santo_do_dia_enqueue_scripts() {
	global $post;

	if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'santododia' ) ) {
		wp_enqueue_style(
			'santododia-style',
			plugin_dir_url( __FILE__ ) . 'css/santododia.css',
			array(),
			SANTO_DO_DIA_VERSION
		);
	}
}
add_action( 'wp_enqueue_scripts', 'santo_do_dia_enqueue_scripts' );

/**
 * Stops recurring jobs while preserving user data.
 *
 * @return void
 */
function santo_do_dia_deactivate() {
	wp_clear_scheduled_hook( 'santo_do_dia_cron_diario' );
	wp_clear_scheduled_hook( 'santo_do_dia_cron_fallback' );
}
register_deactivation_hook( __FILE__, 'santo_do_dia_deactivate' );

/**
 * Removes all plugin data during uninstall.
 *
 * @return void
 */
function santo_do_dia_uninstall() {
	global $wpdb;

	// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange -- Removing the custom table is required during uninstall.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall must remove the custom table.
	$wpdb->query(
		$wpdb->prepare(
			'DROP TABLE IF EXISTS %i',
			santo_do_dia_table_name()
		)
	);
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange

	$transient_pattern = $wpdb->esc_like( '_transient_santo_do_dia_html_' ) . '%';
	$timeout_pattern   = $wpdb->esc_like( '_transient_timeout_santo_do_dia_html_' ) . '%';

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall must remove all date-specific transient options.
	$wpdb->query(
		$wpdb->prepare(
			'DELETE FROM %i WHERE option_name LIKE %s OR option_name LIKE %s',
			$wpdb->options,
			$transient_pattern,
			$timeout_pattern
		)
	);

	delete_option( 'santo_do_dia_version' );
	wp_clear_scheduled_hook( 'santo_do_dia_cron_diario' );
	wp_clear_scheduled_hook( 'santo_do_dia_cron_fallback' );
}
register_uninstall_hook( __FILE__, 'santo_do_dia_uninstall' );
