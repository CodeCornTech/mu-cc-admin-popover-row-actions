#!/usr/bin/env bash
# ╭────────────────────────────────────────────────────────────╮
# │  CodeCorn™ CI Test Script — mu-cc-admin-popover-row-actions │
# ╰────────────────────────────────────────────────────────────╯
set -euo pipefail

echo "🧩 Running local CI package test…"
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

# 1️⃣ Setup PHPCS environment
COMPOSER_HOME="$(composer global config home --absolute)"
export COMPOSER_HOME
export PHPCS_BIN="$COMPOSER_HOME/vendor/bin/phpcs"

composer global config --no-plugins allow-plugins.dealerdirect/phpcodesniffer-composer-installer true

# 2️⃣ Install dependencies globally (cached)
composer global require --no-interaction --no-progress \
    squizlabs/php_codesniffer:^3.9 \
    wp-coding-standards/wpcs:^3.0 \
    phpcsstandards/phpcsextra:^1.4 \
    phpcsstandards/phpcsutils:^1.1

# 3️⃣ Register standards
"$PHPCS_BIN" --config-set installed_paths \
    "$COMPOSER_HOME/vendor/wp-coding-standards/wpcs,$COMPOSER_HOME/vendor/phpcsstandards/phpcsextra,$COMPOSER_HOME/vendor/phpcsstandards/phpcsutils"

# 4️⃣ Syntax check
echo "🧠 Syntax check (php -l)"
find . -type f -name "*.php" -not -path "./vendor/*" -print0 | xargs -0 -n1 -P4 php -l

# 5️⃣ PHPCS validation
echo "🔍 Running PHPCS..."
"$PHPCS_BIN" --standard=phpcs.xml.dist .

echo "✅ Local CI package test completed successfully."
