#!/usr/bin/env bash
# #195 — one-command build: lint everything, then cut the installable ZIP.
# usage: bin/nr-build.sh            (lint + zip current version)
#        bin/nr-build.sh 4.47.0     (bump first, then lint + zip)
set -euo pipefail
here="$(cd "$(dirname "$0")/.." && pwd)"
theme="$(basename "$here")"
root="$(cd "$here/.." && pwd)"            # .../Raveenthiran.com
repo="$(cd "$root/.." && pwd)"

[ "${1:-}" ] && bash "$here/bin/nr-bump.sh" "$1"

ver="$(grep -oE "NR_THEME_VERSION', '[0-9.]+'" "$here/functions.php" | grep -oE '[0-9.]+')"
echo "▸ Linting PHP…"; find "$here/inc" "$here" -maxdepth 1 -name '*.php' -print0 | xargs -0 -n1 php -l >/dev/null
for f in "$here"/inc/*.php; do php -l "$f" >/dev/null; done
echo "▸ Linting JS…";  node --check "$here/assets/js/theme.js"
echo "▸ CSS brace balance…"; php -r '$s=file_get_contents($argv[1]);exit(substr_count($s,"{")===substr_count($s,"}")?0:1);' "$here/assets/css/theme.css"
echo "▸ Building ZIP for v$ver…"
cd "$root"
rm -f "$repo/$theme-v$ver.zip"
zip -rq "$repo/$theme-v$ver.zip" "$theme" \
  -x "$theme/tests/*" -x "$theme/playwright.config.js" -x "$theme/lighthouserc.json" \
  -x "$theme/.eslintrc.json" -x "$theme/.prettierrc.json" -x "$theme/.stylelintrc.json" \
  -x "$theme/.editorconfig" -x "$theme/bin/*" -x '*/.DS_Store'
echo "✓ $repo/$theme-v$ver.zip"
