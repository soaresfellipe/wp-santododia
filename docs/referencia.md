# Referência do plugin

> Gerado por `bin/generate-reference.php` a partir de `santododia.php`. Não edite à mão:
> rode `composer reference`.

## Constantes

| Nome | Valor |
|---|---|
| `SANTO_DO_DIA_VERSION` | `'2.3.0'` |
| `SANTO_DO_DIA_API_URL` | `'https://catolicoapp.com/wp-json/wp/v2/santos'` |
| `SANTO_DO_DIA_API_PAUSE_KEY` | `'santo_do_dia_api_pausa'` |
| `SANTO_DO_DIA_API_PAUSE_SECONDS` | `15 * 60` |

## Hooks registrados

| Registro | Hook | Callback |
|---|---|---|
| `register_activation_hook` | (arquivo do plugin) | `santo_do_dia_install()` |
| `add_action` | `plugins_loaded` | `santo_do_dia_maybe_upgrade()` |
| `add_action` | `santo_do_dia_api_error` | `santo_do_dia_log_error()` |
| `add_action` | `santo_do_dia_cron_diario` | `santo_do_dia_atualizar_dados()` |
| `add_filter` | `cron_schedules` | `santo_do_dia_cron_schedules()` |
| `add_action` | `santo_do_dia_cron_fallback` | `santo_do_dia_verificar_dados()` |
| `add_shortcode` | `santododia` | `santo_do_dia()` |
| `add_action` | `wp_enqueue_scripts` | `santo_do_dia_enqueue_scripts()` |
| `register_deactivation_hook` | (arquivo do plugin) | `santo_do_dia_deactivate()` |
| `register_uninstall_hook` | (arquivo do plugin) | `santo_do_dia_uninstall()` |

## Actions disparadas pelo plugin

- `santo_do_dia_api_error`

## Funções

### `santo_do_dia_table_name()`

Returns the plugin table name.

- @return string

### `santo_do_dia_create_table()`

Creates or updates the plugin table.

- @return void

### `santo_do_dia_schedule_events()`

Registers the recurring jobs used by the plugin.

- @return void

### `santo_do_dia_install()`

Installs the database table and recurring jobs.

- @return void

### `santo_do_dia_maybe_upgrade()`

Applies schema and schedule changes to already active installations.

- @return void

### `santo_do_dia_current_date()`

Returns the current day and month in the WordPress timezone.

- @return int[]

### `santo_do_dia_error($code, $message)`

Reports an API or persistence error to integrations.

- @param string $code Stable error code.
- @param string $message Error message.
- @return WP_Error

### `santo_do_dia_scrub_log_message($message)`

Removes query strings and e-mail addresses from a log message.

- @param string $message Raw message.
- @return string

### `santo_do_dia_log_error($error)`

Writes plugin errors as one JSON line to debug.log when WP_DEBUG_LOG is on.

- @param WP_Error $error Error details.
- @return void

### `santo_do_dia_api_failure($code, $message)`

Reports an API failure and pauses non-forced requests for a while.

Without the pause, every uncached page view would wait for the API timeout
while the API is down.

- @param string $code Stable error code.
- @param string $message Error message.
- @return WP_Error

### `santo_do_dia_obter_dados($force = false)`

Fetches and persists the saint for the current date.

- @param bool $force Whether an existing record should be refreshed.
- @return true|WP_Error

### `santo_do_dia_atualizar_dados()`

Refreshes the current record during the daily cron job.

- @return void

### `santo_do_dia_cron_schedules($schedules)`

Registers the six-hour fallback interval.

- @param array<string, array<string, int|string>> $schedules Existing schedules.
- @return array<string, array<string, int|string>>

### `santo_do_dia_verificar_dados()`

Fetches the current data when the daily job did not populate it.

- @return void

### `santo_do_dia()`

Renders the saint card shortcode.

- @return string

### `santo_do_dia_enqueue_scripts()`

Loads the stylesheet only on singular content containing the shortcode.

- @return void

### `santo_do_dia_deactivate()`

Stops recurring jobs while preserving user data.

- @return void

### `santo_do_dia_uninstall()`

Removes all plugin data during uninstall.

- @return void
