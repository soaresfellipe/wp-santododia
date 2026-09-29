# AGENTS.md — WP Santo do Dia

Plugin WordPress de arquivo único (`santododia.php`) que exibe o santo do dia pelo shortcode
`[santododia]`. Os dados vêm da API `SANTO_DO_DIA_API_URL` (`https://catolicoapp.com/wp-json/wp/v2/santos`)
e ficam em cache na tabela `{prefix}santo_do_dia`, atualizada por wp-cron. Fluxo completo e
dependências: `docs/arquitetura.md`.

## Pré-requisitos

- PHP 7.4 ou superior (a CI testa 7.4, 8.2 e 8.4)
- Composer 2
- Para rodar o plugin num WordPress local: Docker e Node.js (para o `@wordpress/env`)
- Ou abra o repositório no devcontainer (`.devcontainer/devcontainer.json`), que já traz tudo isso.

## Do clone ao verde

```sh
composer install      # dependências de dev; também ativa o hook de pre-commit (.githooks)
composer check        # lint + análise estática + dependências + testes (o mesmo que a CI)
```

| Comando              | O que faz                                                             |
|----------------------|-----------------------------------------------------------------------|
| `composer lint`      | `phpcs` com WordPress-Core/Docs/Extra/Security e limites de complexidade (`phpcs.xml.dist`) |
| `composer format`    | `phpcbf`: corrige automaticamente o que o lint aponta                  |
| `composer analyse`   | PHPStan nível 6 com stubs do WordPress (`phpstan.neon.dist`)          |
| `composer deps`      | dependências não usadas ou não declaradas (`composer-dependency-analyser.php`) |
| `composer test`      | testes unitários com stubs, em ordem aleatória (`phpunit.xml.dist`)    |
| `composer test:integration` | baixa WordPress + SQLite em `build/wp` e roda `tests/integration` contra o core real (`phpunit-integration.xml.dist`) |
| `composer dead`      | funções `santo_do_dia_*` sem chamada nem callback (`bin/check-dead-code.php`) |
| `composer duplicates`| código duplicado com jscpd (`.jscpd.json`, precisa de Node)           |
| `composer docs`      | confere se este arquivo só cita scripts e arquivos que existem e se `docs/referencia.md` está atualizado |
| `composer reference` | regenera `docs/referencia.md` a partir dos docblocks (rode ao mudar funções, hooks ou constantes) |
| `composer check`     | todos acima, exceto `format`, `duplicates` e `test:integration` (rodam na CI) |
| `composer hooks`     | reativa o hook de pre-commit                                           |

Não há variáveis de ambiente nem `.env`: a configuração é constante em `santododia.php`.

## WordPress local

O `.wp-env.json` na raiz sobe um WordPress com o plugin montado:

```sh
npx @wordpress/env start    # http://localhost:8888  (admin / password)
npx @wordpress/env stop
```

Depois de ativar o plugin, crie uma página com `[santododia]`. Para forçar a sincronização:

```sh
npx @wordpress/env run cli wp cron event run santo_do_dia_cron_diario
```

Com `WP_DEBUG_LOG` ligado, erros da API aparecem como JSON em `wp-content/debug.log`.

## Testes

- `tests/bootstrap.php` **não** carrega o WordPress: ele define stubs de `WP_Error`, `$wpdb`
  (`Santo_Do_Dia_Test_Wpdb`) e das funções do core usadas pelo plugin. Se o plugin passar a usar
  uma função nova do WordPress, adicione o stub correspondente ali.
- O stub de `$wpdb` registra toda query em `$queries`; `test_rendering_keeps_a_fixed_query_budget`
  fixa quantas queries uma renderização pode fazer. Se mudar, ajuste o teste de propósito.
- Arquivos de teste precisam terminar em `Test.php`. A ordem é aleatória: reproduza uma falha com
  `vendor/bin/phpunit --random-order-seed <seed>` (a seed aparece no topo da saída).
- `beStrictAboutOutputDuringTests`, `failOnRisky` e `failOnWarning` estão ligados.
- A CI mede cobertura no job PHP 8.2 e reprova abaixo do mínimo (`bin/coverage-check.php`).

- `tests/integration/` usa `WP_UnitTestCase` sobre um WordPress real com SQLite (sem MySQL);
  a API é simulada pelo filtro `pre_http_request`.

## Estrutura do plugin

- Ativação (`santo_do_dia_install`): cria a tabela via `dbDelta` e agenda os eventos de cron.
- `plugins_loaded` → `santo_do_dia_maybe_upgrade`: reaplica schema/eventos quando a versão muda.
- Cron `santo_do_dia_cron_diario` → `santo_do_dia_atualizar_dados` (busca forçada na API).
- Cron `santo_do_dia_cron_fallback` → `santo_do_dia_verificar_dados` (repõe dado ausente).
- Shortcode `santododia` → `santo_do_dia()` (renderiza; mensagem de fallback se não há dados).
- Circuit breaker: após falha da API, buscas não forçadas ficam pausadas por
  `SANTO_DO_DIA_API_PAUSE_SECONDS`; o cron diário ignora a pausa.
- Toda falha dispara `santo_do_dia_api_error` (usada pelo log JSON; ponto de extensão para sites).
- Desativação só remove os eventos; tabela e transients são apagados só na desinstalação.

## Convenções

- WordPress Coding Standards (tabs, Yoda conditions, escaping em toda saída).
- Funções com prefixo `santo_do_dia_`; text domain `santo-do-dia`.
- O plugin é distribuído no WordPress.org: **não** adicione telemetria, analytics ou chamada
  externa nova sem consentimento explícito do administrador (diretriz 7 do WordPress.org).
- Arquivo novo de desenvolvimento entra no `.distignore` **e** em `archive.exclude` do `composer.json`.
- Release e compatibilidade: `notes/rotina-compatibilidade-wordpress.md`. Atualize juntos o
  cabeçalho `Version`, `SANTO_DO_DIA_VERSION`, `Stable tag` e o changelog de `README.md` e `README.txt`.
