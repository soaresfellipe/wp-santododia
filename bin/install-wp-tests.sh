#!/bin/sh
# Prepara o WordPress usado pelos testes de integração em build/wp/, sobre SQLite
# (sem MySQL). Uso: bin/install-wp-tests.sh [versão-do-wordpress]
set -eu

ROOT=$(cd "$(dirname "$0")/.." && pwd)
WP_VERSION=${1:-7.1}
DIR="$ROOT/build/wp"
CORE="$DIR/wordpress"

mkdir -p "$DIR"

if [ ! -f "$CORE/wp-settings.php" ] || [ "$(cat "$DIR/.version" 2>/dev/null)" != "$WP_VERSION" ]; then
	rm -rf "$CORE"
	curl -fsSL "https://wordpress.org/wordpress-${WP_VERSION}.tar.gz" | tar -xz -C "$DIR"
	echo "$WP_VERSION" > "$DIR/.version"
fi

if [ ! -f "$CORE/wp-content/db.php" ]; then
	curl -fsSL -o "$DIR/sqlite.zip" https://downloads.wordpress.org/plugin/sqlite-database-integration.latest-stable.zip
	rm -rf "$CORE/wp-content/plugins/sqlite-database-integration"
	unzip -q "$DIR/sqlite.zip" -d "$CORE/wp-content/plugins"
	rm "$DIR/sqlite.zip"
	sed -e "s#{SQLITE_IMPLEMENTATION_FOLDER_PATH}#$CORE/wp-content/plugins/sqlite-database-integration#" \
		-e "s#{SQLITE_PLUGIN}#sqlite-database-integration/load.php#" \
		"$CORE/wp-content/plugins/sqlite-database-integration/db.copy" > "$CORE/wp-content/db.php"
fi

cat > "$DIR/wp-tests-config.php" <<PHP
<?php
define( 'ABSPATH', '$CORE/' );
define( 'DB_NAME', 'wp_tests' );
define( 'DB_USER', '' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST', '' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );
define( 'DB_DIR', '$DIR/' );
define( 'DB_FILE', 'wp-tests.sqlite' );
define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Santo do Dia Tests' );
define( 'WP_PHP_BINARY', 'php' );
define( 'WPLANG', '' );
\$table_prefix = 'wptests_';
PHP

echo "WordPress $WP_VERSION pronto em $CORE"
