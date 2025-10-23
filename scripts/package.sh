#!/usr/bin/env bash
set -euo pipefail

VER="${1:-}"
if [[ -z "$VER" ]]; then
    VER="$(grep -Eo '^\s*\*\s*Version:\s*[0-9]+\.[0-9]+\.[0-9]+' cc-admin-popover-row-actions.php | sed -E 's/.*Version:\s*//')"
fi

DIST="dist"
PKG="cc-admin-popover-row-actions-${VER}.zip"

rm -rf "$DIST" "$PKG"
mkdir -p "$DIST"/mu-plugins/codecorn/admin-popover-row-actions/assets/{css,js}

cp cc-admin-popover-row-actions.php "$DIST"/mu-plugins/
rsync -av --delete codecorn/admin-popover-row-actions/assets/ "$DIST"/mu-plugins/codecorn/admin-popover-row-actions/assets/ >/dev/null

(cd "$DIST" && zip -r "../${PKG}" mu-plugins >/dev/null)
echo "Built → ${PKG}"
