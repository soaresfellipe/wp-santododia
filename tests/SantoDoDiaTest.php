<?php

use PHPUnit\Framework\TestCase;

final class SantoDoDiaTest extends TestCase {
	protected function setUp(): void {
		$GLOBALS['wpdb']                              = new Santo_Do_Dia_Test_Wpdb();
		$GLOBALS['santo_do_dia_remote_calls']         = 0;
		$GLOBALS['santo_do_dia_remote_response']      = array();
		$GLOBALS['santo_do_dia_transients']           = array();
		$GLOBALS['santo_do_dia_deleted_transients']   = array();
		$GLOBALS['santo_do_dia_cleared_hooks']        = array();
		$GLOBALS['santo_do_dia_fired_actions']        = array();
		$GLOBALS['santo_do_dia_options']              = array();
		$GLOBALS['santo_do_dia_scheduled']            = array();
	}

	public function test_registers_the_correct_daily_cron_callback(): void {
		$this->assertContains(
			'santo_do_dia_atualizar_dados',
			$GLOBALS['santo_do_dia_hooks']['santo_do_dia_cron_diario']
		);
	}

	public function test_fetches_validates_and_saves_api_data(): void {
		$GLOBALS['santo_do_dia_remote_response'] = $this->validResponse(
			'<strong>Santa Clara</strong>',
			'https://example.test/clara.jpg',
			'https://example.test/santa-clara'
		);

		$result = santo_do_dia_obter_dados();

		$this->assertTrue( $result );
		$this->assertSame( 1, $GLOBALS['santo_do_dia_remote_calls'] );
		$this->assertSame( 'Santa Clara', $GLOBALS['wpdb']->records['12_8']['nome'] );
		$this->assertContains( 'santo_do_dia_html_12_8', $GLOBALS['santo_do_dia_deleted_transients'] );
		$this->assertSame( 1024 * 1024, $GLOBALS['santo_do_dia_last_request'][1]['limit_response_size'] );
	}

	public function test_does_not_fetch_an_existing_record_unless_forced(): void {
		$GLOBALS['wpdb']->records['12_8'] = $this->record();

		$this->assertTrue( santo_do_dia_obter_dados() );
		$this->assertSame( 0, $GLOBALS['santo_do_dia_remote_calls'] );

		$GLOBALS['santo_do_dia_remote_response'] = $this->validResponse(
			'Santa Clara atualizada',
			'https://example.test/nova.jpg',
			'https://example.test/nova'
		);

		santo_do_dia_atualizar_dados();
		$this->assertSame( 1, $GLOBALS['santo_do_dia_remote_calls'] );
		$this->assertSame( 'Santa Clara atualizada', $GLOBALS['wpdb']->records['12_8']['nome'] );
	}

	public function test_rejects_malformed_api_data(): void {
		$GLOBALS['santo_do_dia_remote_response'] = array(
			'response' => array( 'code' => 200 ),
			'body'     => '{"unexpected":true}',
		);

		$result = santo_do_dia_obter_dados();

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'santo_do_dia_invalid_response', $result->get_error_code() );
		$this->assertEmpty( $GLOBALS['wpdb']->records );
		$this->assertContains( 'santo_do_dia_api_error', $GLOBALS['santo_do_dia_fired_actions'] );
	}

	public function test_reports_remote_failures(): void {
		$GLOBALS['santo_do_dia_remote_response'] = new WP_Error( 'timeout', 'Timed out' );

		$result = santo_do_dia_obter_dados();

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'santo_do_dia_http_error', $result->get_error_code() );
	}

	public function test_pauses_non_forced_requests_after_an_api_failure(): void {
		$GLOBALS['santo_do_dia_remote_response'] = new WP_Error( 'timeout', 'Timed out' );

		santo_do_dia_obter_dados();
		$this->assertSame( 1, $GLOBALS['santo_do_dia_remote_calls'] );
		$this->assertSame( 15 * 60, $GLOBALS['santo_do_dia_transient_expirations']['santo_do_dia_api_pausa'] );

		$result = santo_do_dia_obter_dados();
		$this->assertSame( 'santo_do_dia_api_paused', $result->get_error_code() );
		$this->assertSame( 1, $GLOBALS['santo_do_dia_remote_calls'], 'Com o circuito aberto não há chamada remota.' );

		$GLOBALS['santo_do_dia_remote_response'] = $this->validResponse(
			'Santa Clara',
			'https://example.test/clara.jpg',
			'https://example.test/clara'
		);

		$this->assertTrue( santo_do_dia_obter_dados( true ), 'O cron diário (forçado) ignora a pausa.' );
		$this->assertSame( 2, $GLOBALS['santo_do_dia_remote_calls'] );
		$this->assertArrayNotHasKey( 'santo_do_dia_api_pausa', $GLOBALS['santo_do_dia_transients'] );
	}

	public function test_logs_errors_as_scrubbed_json_when_debug_log_is_on(): void {
		if ( ! defined( 'WP_DEBUG_LOG' ) ) {
			define( 'WP_DEBUG_LOG', true );
		}

		$log      = tempnam( sys_get_temp_dir(), 'santo' );
		$previous = ini_set( 'error_log', $log );

		santo_do_dia_log_error(
			new WP_Error( 'santo_do_dia_http_error', 'Falha em https://api.test/x?token=abc para fulano@example.com' )
		);

		ini_set( 'error_log', (string) $previous );
		$line = trim( (string) file_get_contents( $log ) );
		unlink( $log );

		$entry = json_decode( substr( $line, (int) strpos( $line, '{' ) ), true );
		$this->assertSame( 'santo_do_dia_http_error', $entry['code'] );
		$this->assertSame( 'Falha em https://api.test/x?[removido] para [email removido]', $entry['message'] );
		$this->assertSame( 12, $entry['dia'] );
		$this->assertSame( 8, $entry['mes'] );
	}

	public function test_renders_and_caches_escaped_card_markup(): void {
		$GLOBALS['wpdb']->records['12_8'] = $this->record(
			'Clara <script>alert(1)</script>',
			'https://example.test/image.jpg?x=1&y=2'
		);

		$html = santo_do_dia();

		$this->assertStringNotContainsString( '<script>', $html );
		$this->assertStringContainsString( '&lt;script&gt;', $html );
		$this->assertStringContainsString( 'rel="noopener noreferrer"', $html );
		$this->assertStringContainsString( 'loading="lazy"', $html );
		$this->assertSame( $html, $GLOBALS['santo_do_dia_transients']['santo_do_dia_html_12_8'] );
	}

	public function test_rendering_keeps_a_fixed_query_budget(): void {
		$GLOBALS['santo_do_dia_remote_response'] = $this->validResponse(
			'Santa Clara',
			'https://example.test/clara.jpg',
			'https://example.test/clara'
		);

		santo_do_dia();
		$this->assertCount( 4, $GLOBALS['wpdb']->queries, 'Sem dados: lê, confere, grava e relê.' );

		$GLOBALS['wpdb']->queries           = array();
		$GLOBALS['santo_do_dia_transients'] = array();
		santo_do_dia();
		$this->assertCount( 1, $GLOBALS['wpdb']->queries, 'Com dados e sem cache: uma leitura.' );

		$GLOBALS['wpdb']->queries = array();
		santo_do_dia();
		$this->assertCount( 0, $GLOBALS['wpdb']->queries, 'Com cache: nenhuma query.' );
	}

	public function test_returns_safe_fallback_when_data_remains_unavailable(): void {
		$GLOBALS['santo_do_dia_remote_response'] = new WP_Error( 'timeout', 'Timed out' );

		$html = santo_do_dia();

		$this->assertStringContainsString( 'temporariamente indisponível', $html );
		$this->assertArrayNotHasKey( 'santo_do_dia_html_12_8', $GLOBALS['santo_do_dia_transients'] );
	}

	public function test_deactivation_preserves_data_and_only_clears_jobs(): void {
		$GLOBALS['wpdb']->records['12_8'] = $this->record();

		santo_do_dia_deactivate();

		$this->assertNotEmpty( $GLOBALS['wpdb']->records );
		$this->assertEmpty( $GLOBALS['wpdb']->queries );
		$this->assertSame(
			array( 'santo_do_dia_cron_diario', 'santo_do_dia_cron_fallback' ),
			$GLOBALS['santo_do_dia_cleared_hooks']
		);
	}

	public function test_uninstall_removes_table_options_and_jobs(): void {
		$GLOBALS['santo_do_dia_options']['santo_do_dia_version'] = SANTO_DO_DIA_VERSION;

		santo_do_dia_uninstall();

		$this->assertCount( 2, $GLOBALS['wpdb']->queries );
		$this->assertStringContainsString( 'DROP TABLE IF EXISTS', $GLOBALS['wpdb']->queries[0] );
		$this->assertArrayNotHasKey( 'santo_do_dia_version', $GLOBALS['santo_do_dia_options'] );
		$this->assertSame(
			array( 'santo_do_dia_cron_diario', 'santo_do_dia_cron_fallback' ),
			$GLOBALS['santo_do_dia_cleared_hooks']
		);
	}

	private function validResponse( string $name, string $image, string $link ): array {
		return array(
			'response' => array( 'code' => 200 ),
			'body'     => wp_json_encode(
				array(
					array(
						'title'             => array( 'rendered' => $name ),
						'imagem_destacada' => $image,
						'link'              => $link,
					),
				)
			),
		);
	}

	private function record(
		string $name = 'Santa Clara',
		string $image = 'https://example.test/clara.jpg'
	): array {
		return array(
			'dia'    => 12,
			'mes'    => 8,
			'nome'   => $name,
			'imagem' => $image,
			'url'    => 'https://example.test/santa-clara',
		);
	}
}
