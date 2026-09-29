<?php
/**
 * Testes de integração contra um WordPress real.
 *
 * @package WPSantoDoDia
 */

/**
 * Ciclo de vida, persistência, HTTP e shortcode com o core do WordPress.
 */
final class PluginIntegrationTest extends WP_UnitTestCase {
	/**
	 * Respostas HTTP simuladas e contador de chamadas.
	 *
	 * @var array{calls: int, response: array<string, mixed>|WP_Error}
	 */
	private $http;

	public function set_up(): void {
		parent::set_up();

		santo_do_dia_create_table();
		$this->http = array(
			'calls'    => 0,
			'response' => new WP_Error( 'offline', 'offline' ),
		);
		add_filter( 'pre_http_request', array( $this, 'fake_http' ), 10, 3 );
	}

	public function tear_down(): void {
		global $wpdb;

		remove_filter( 'pre_http_request', array( $this, 'fake_http' ), 10 );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', santo_do_dia_table_name() ) ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->query( $wpdb->prepare( 'DELETE FROM %i', santo_do_dia_table_name() ) );
		}
		delete_transient( SANTO_DO_DIA_API_PAUSE_KEY );
		parent::tear_down();
	}

	/**
	 * Intercepta as chamadas à API.
	 *
	 * @param false|array<string, mixed> $pre  Resposta curto-circuitada.
	 * @param array<string, mixed>       $args Argumentos da requisição.
	 * @param string                     $url  URL.
	 * @return array<string, mixed>|WP_Error|false
	 */
	public function fake_http( $pre, $args, $url ) {
		if ( 0 !== strpos( $url, SANTO_DO_DIA_API_URL ) ) {
			return $pre;
		}

		++$this->http['calls'];

		return $this->http['response'];
	}

	private function api_returns( string $nome ): void {
		$this->http['response'] = array(
			'headers'  => array(),
			'body'     => wp_json_encode(
				array(
					array(
						'title'            => array( 'rendered' => $nome ),
						'imagem_destacada' => 'https://example.org/santo.jpg',
						'link'             => 'https://example.org/santo',
					),
				)
			),
			'response' => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'cookies'  => array(),
		);
	}

	public function test_activation_creates_table_and_schedules_jobs(): void {
		global $wpdb;

		$this->api_returns( 'Santa Clara' );
		santo_do_dia_install();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$columns = $wpdb->get_col( $wpdb->prepare( 'SELECT * FROM %i LIMIT 0', santo_do_dia_table_name() ) );
		$this->assertNotFalse( $columns );
		$this->assertNotFalse( wp_next_scheduled( 'santo_do_dia_cron_diario' ) );
		$this->assertNotFalse( wp_next_scheduled( 'santo_do_dia_cron_fallback' ) );
		$this->assertSame( SANTO_DO_DIA_VERSION, get_option( 'santo_do_dia_version' ) );
	}

	public function test_shortcode_fetches_saves_and_caches(): void {
		$this->api_returns( 'Santa Clara &#8211; virgem' );

		$html = do_shortcode( '[santododia]' );

		$this->assertStringContainsString( 'Santa Clara – virgem', $html );
		$this->assertSame( 1, $this->http['calls'] );

		list( $dia, $mes ) = santo_do_dia_current_date();
		$this->assertSame( $html, get_transient( 'santo_do_dia_html_' . $dia . '_' . $mes ) );

		do_shortcode( '[santododia]' );
		$this->assertSame( 1, $this->http['calls'], 'A segunda renderização vem do cache.' );
	}

	public function test_api_failure_opens_the_circuit(): void {
		$html = do_shortcode( '[santododia]' );
		$this->assertStringContainsString( 'temporariamente indisponível', $html );

		do_shortcode( '[santododia]' );
		$this->assertSame( 1, $this->http['calls'], 'Com a pausa ativa a API não é chamada de novo.' );

		$this->api_returns( 'Santa Clara' );
		do_action( 'santo_do_dia_cron_diario' );
		$this->assertSame( 2, $this->http['calls'] );
		$this->assertFalse( get_transient( SANTO_DO_DIA_API_PAUSE_KEY ) );
		$this->assertStringContainsString( 'Santa Clara', do_shortcode( '[santododia]' ) );
	}

	public function test_uninstall_removes_table_and_options(): void {
		global $wpdb;

		// A biblioteca de testes troca CREATE/DROP TABLE por tabelas temporárias; aqui
		// queremos o comportamento real da desinstalação.
		remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
		remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );
		santo_do_dia_create_table();

		santo_do_dia_uninstall();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', santo_do_dia_table_name() ) );
		$this->assertNull( $exists );
		$this->assertFalse( get_option( 'santo_do_dia_version' ) );
		$this->assertFalse( wp_next_scheduled( 'santo_do_dia_cron_diario' ) );
	}
}
