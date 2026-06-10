#!/usr/bin/env bash
# #197 — bump the theme version everywhere in one go.
# usage: bin/nr-bump.sh 4.46.0
set -euo pipefail
v="${1:?usage: nr-bump.sh <version>}"
root="$(cd "$(dirname "$0")/.." && pwd)"
sed -i.bak -E "s/define\( 'NR_THEME_VERSION', '[0-9.]+' \);/define( 'NR_THEME_VERSION', '$v' );/" "$root/functions.php"
sed -i.bak -E "s/^Version: [0-9.]+/Version: $v/" "$root/style.css"
sed -i.bak -E "s/^Stable tag: [0-9.]+/Stable tag: $v/" "$root/readme.txt"
rm -f "$root"/*.bak
echo "Bumped to $v in functions.php, style.css, readme.txt"
