<?php

define( 'ABSPATH', __DIR__ . '/wordpress/' );
define( 'HOUR_IN_SECONDS', 3600 );

class WP_Error {
	private $code;
	private $message;

	public function __construct( $code, $message ) {
		$this->code    = $code;
		$this->message = $message;
	}

	public function get_error_code() {
		return $this->code;
	}

	public function get_error_message() {
		return $this->message;
	}
}

class Santo_Do_Dia_Test_Wpdb {
	public $prefix = 'wp_';
	public $options = 'wp_options';
	public $records = array();
	public $queries = array();
	public $replace_result = 1;

	public function get_charset_collate() {
		return 'DEFAULT CHARACTER SET utf8mb4';
	}

	public function prepare( $query, ...$args ) {
		foreach ( $args as $arg ) {
			if ( false !== strpos( $query, '%i' ) ) {
				$query = preg_replace( '/%i/', '`' . $arg . '`', $query, 1 );
			} elseif ( false !== strpos( $query, '%d' ) ) {
				$query = preg_replace( '/%d/', (string) (int) $arg, $query, 1 );
			} else {
				$query = preg_replace( '/%s/', "'" . addslashes( $arg ) . "'", $query, 1 );
			}
		}

		return $query;
	}

	public function get_var( $query ) {
		if ( preg_match( '/dia = (\d+) AND mes = (\d+)/', $query, $matches ) ) {
			return isset( $this->records[ $matches[1] . '_' . $matches[2] ] ) ? 1 : 0;
		}

		return 0;
	}

	public function replace( $table, $data, $formats ) {
		unset( $table, $formats );

		if ( false === $this->replace_result ) {
			return false;
		}

		$this->records[ $data['dia'] . '_' . $data['mes'] ] = $data;

		return $this->replace_result;
	}

	public function get_row( $query ) {
		if ( preg_match( '/dia = (\d+) AND mes = (\d+)/', $query, $matches ) ) {
			$key = $matches[1] . '_' . $matches[2];
			if ( isset( $this->records[ $key ] ) ) {
				return (object) $this->records[ $key ];
			}
		}

		return null;
	}

	public function query( $query ) {
		$this->queries[] = $query;

		return true;
	}

	public function esc_like( $value ) {
		return addcslashes( $value, '_%\\' );
	}
}

$GLOBALS['wpdb']                         = new Santo_Do_Dia_Test_Wpdb();
$GLOBALS['santo_do_dia_hooks']          = array();
$GLOBALS['santo_do_dia_remote_response'] = array();
$GLOBALS['santo_do_dia_remote_calls']   = 0;
$GLOBALS['santo_do_dia_transients']     = array();
$GLOBALS['santo_do_dia_deleted_transients'] = array();
$GLOBALS['santo_do_dia_cleared_hooks']  = array();
$GLOBALS['santo_do_dia_options']        = array();

function add_action( $hook, $callback ) {
	$GLOBALS['santo_do_dia_hooks'][ $hook ][] = $callback;
}

function add_filter( $hook, $callback ) {
	add_action( $hook, $callback );
}

function do_action( $hook, ...$args ) {
	unset( $args );
	$GLOBALS['santo_do_dia_fired_actions'][] = $hook;
}

function register_activation_hook( $file, $callback ) {
	unset( $file );
	$GLOBALS['santo_do_dia_activation_hook'] = $callback;
}

function register_deactivation_hook( $file, $callback ) {
	unset( $file );
	$GLOBALS['santo_do_dia_deactivation_hook'] = $callback;
}

function register_uninstall_hook( $file, $callback ) {
	unset( $file );
	$GLOBALS['santo_do_dia_uninstall_hook'] = $callback;
}

function add_shortcode( $tag, $callback ) {
	$GLOBALS['santo_do_dia_shortcodes'][ $tag ] = $callback;
}

function current_datetime() {
	return new DateTimeImmutable( '2026-08-12 11:00:00', new DateTimeZone( 'America/Sao_Paulo' ) );
}

function wp_timezone() {
	return new DateTimeZone( 'America/Sao_Paulo' );
}

function add_query_arg( $args, $url ) {
	return $url . '?' . http_build_query( $args );
}

function wp_safe_remote_get( $url, $args ) {
	$GLOBALS['santo_do_dia_remote_calls']++;
	$GLOBALS['santo_do_dia_last_request'] = array( $url, $args );

	return $GLOBALS['santo_do_dia_remote_response'];
}

function wp_remote_retrieve_response_code( $response ) {
	return $response['response']['code'] ?? 0;
}

function wp_remote_retrieve_body( $response ) {
	return $response['body'] ?? '';
}

function wp_json_encode( $value ) {
	return json_encode( $value );
}

function is_wp_error( $value ) {
	return $value instanceof WP_Error;
}

function sanitize_text_field( $value ) {
	return trim( preg_replace( '/\s+/', ' ', strip_tags( $value ) ) );
}

function wp_strip_all_tags( $value ) {
	return strip_tags( $value );
}

function esc_url_raw( $value, $protocols = null ) {
	unset( $protocols );
	return filter_var( $value, FILTER_VALIDATE_URL ) ? $value : '';
}

function wp_http_validate_url( $value ) {
	return (bool) filter_var( $value, FILTER_VALIDATE_URL );
}

function esc_url( $value ) {
	return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
}

function esc_attr( $value ) {
	return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
}

function esc_html( $value ) {
	return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
}

function __( $value, $domain = null ) {
	unset( $domain );
	return $value;
}

function esc_html__( $value, $domain = null ) {
	return esc_html( __( $value, $domain ) );
}

function get_transient( $key ) {
	return $GLOBALS['santo_do_dia_transients'][ $key ] ?? false;
}

function set_transient( $key, $value, $expiration ) {
	$GLOBALS['santo_do_dia_transients'][ $key ] = $value;
	$GLOBALS['santo_do_dia_transient_expirations'][ $key ] = $expiration;
	return true;
}

function delete_transient( $key ) {
	$GLOBALS['santo_do_dia_deleted_transients'][] = $key;
	unset( $GLOBALS['santo_do_dia_transients'][ $key ] );
	return true;
}

function get_option( $key ) {
	return $GLOBALS['santo_do_dia_options'][ $key ] ?? false;
}

function update_option( $key, $value ) {
	$GLOBALS['santo_do_dia_options'][ $key ] = $value;
	return true;
}

function delete_option( $key ) {
	unset( $GLOBALS['santo_do_dia_options'][ $key ] );
	return true;
}

function wp_next_scheduled( $hook ) {
	return $GLOBALS['santo_do_dia_scheduled'][ $hook ] ?? false;
}

function wp_schedule_event( $timestamp, $recurrence, $hook ) {
	$GLOBALS['santo_do_dia_scheduled'][ $hook ] = array( $timestamp, $recurrence );
	return true;
}

function wp_clear_scheduled_hook( $hook ) {
	$GLOBALS['santo_do_dia_cleared_hooks'][] = $hook;
	return 1;
}

function plugin_dir_url( $file ) {
	unset( $file );
	return 'https://example.test/wp-content/plugins/wp-santododia/';
}

function wp_enqueue_style( ...$args ) {
	$GLOBALS['santo_do_dia_enqueued_styles'][] = $args;
}

function has_shortcode( $content, $tag ) {
	return false !== strpos( $content, '[' . $tag . ']' );
}

require dirname( __DIR__ ) . '/santododia.php';
