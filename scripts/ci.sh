#!/bin/bash
set -euo pipefail

# 1) Consenti il plugin PHPCS installer
composer global config allow-plugins.dealerdirect/phpcodesniffer-composer-installer true

# 2) Installa PHPCS + standards
composer global require --no-interaction --no-progress \
    squizlabs/php_codesniffer:^3.13 \
    wp-coding-standards/wpcs:^3.0 \
    phpcsstandards/phpcsextra:^1.4 \
    phpcsstandards/phpcsutils:^1.1

# 3) Registra paths
COMPOSER_HOME="$(composer global config home --absolute)"
PHPCS="${COMPOSER_HOME}/vendor/bin/phpcs"
WPCS_PATH="${COMPOSER_HOME}/vendor/wp-coding-standards/wpcs"
EXTRA_PATH="${COMPOSER_HOME}/vendor/phpcsstandards/phpcsextra"
UTILS_PATH="${COMPOSER_HOME}/vendor/phpcsstandards/phpcsutils"

"${PHPCS}" --config-delete installed_paths || true
"${PHPCS}" --config-set installed_paths "${WPCS_PATH},${EXTRA_PATH},${UTILS_PATH}"

# 4) Verifica standards e versione
"${PHPCS}" -i
"${PHPCS}" --version

# 5) PHP lint ricorsivo
find . -type f -name "*.php" -not -path "./vendor/*" -print0 | xargs -0 -n1 -P4 php -l

# 6) PHPCS con WordPress ruleset
"${PHPCS}" -p --standard=WordPress --ignore=vendor/*,node_modules/* --extensions=php .
