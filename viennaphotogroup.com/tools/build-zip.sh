#!/usr/bin/env bash
# VPG · Release-ZIP für easyname Shared Hosting.
#
# Baut vpg-v3-gallery als installierbares Theme-ZIP — inklusive vendor/
# (mPDF), weil auf dem Shared Host kein Composer läuft. Aufruf aus
# viennaphotogroup.com/: ./tools/build-zip.sh
set -euo pipefail

cd "$(dirname "$0")/.."
THEME=vpg-v3-gallery
[ -d "$THEME" ] || { echo "✗ $THEME/ nicht gefunden — aus viennaphotogroup.com/ aufrufen." >&2; exit 1; }

VERSION=$(sed -n 's/^[[:space:]]*Version:[[:space:]]*//p' "$THEME/style.css" | head -1)
VERSION=${VERSION:-dev}
OUT="dist/${THEME}-${VERSION}.zip"

# mPDF muss mit ins Paket — lokal installieren, wenn es fehlt.
if [ ! -f "$THEME/vendor/autoload.php" ]; then
    command -v composer >/dev/null || { echo "✗ vendor/ fehlt und composer ist nicht installiert." >&2; exit 1; }
    ( cd "$THEME" && composer install --no-dev --quiet )
fi
[ -d "$THEME/vendor/mpdf" ] || { echo "✗ vendor/mpdf fehlt — composer install prüfen." >&2; exit 1; }
[ -d "$THEME/assets/fonts" ] || { echo "✗ assets/fonts fehlt (PDF-Schriften)." >&2; exit 1; }

# Syntax-Gate: kein ZIP mit kaputtem PHP.
find "$THEME" -path "$THEME/vendor" -prune -o -name '*.php' -print0 \
    | xargs -0 -n1 php -l >/dev/null

# Shared-Hosting-Diät: mPDF bringt 88 MB Weltschriften mit — wir embedden
# Archivo selbst und behalten nur DejaVu als Fallback. Tests, utils und
# nested .git-Ordner haben im Release nichts verloren.
mkdir -p dist
rm -f "$OUT"
zip -qr "$OUT" "$THEME" \
    -x "$THEME/node_modules/*" "$THEME/.git/*" "$THEME/tests/*" \
       "$THEME/*.zip" "$THEME/.DS_Store" "$THEME/**/.DS_Store" \
       "$THEME/vendor/*/.git/*" "$THEME/vendor/*/*/.git/*" \
       "$THEME/vendor/*/*/tests/*" "$THEME/vendor/*/*/local-tests/*" \
       "$THEME/vendor/mpdf/mpdf/utils/*" \
       "$THEME/vendor/mpdf/mpdf/ttfonts/*"

# DejaVu-Fallback wieder hineinlegen (falls die Archivo-TTFs je fehlen).
zip -q "$OUT" "$THEME"/vendor/mpdf/mpdf/ttfonts/DejaVuSans.ttf \
              "$THEME"/vendor/mpdf/mpdf/ttfonts/DejaVuSans-Bold.ttf \
              "$THEME"/vendor/mpdf/mpdf/ttfonts/DejaVuSans-Oblique.ttf \
              "$THEME"/vendor/mpdf/mpdf/ttfonts/DejaVuSans-BoldOblique.ttf \
              "$THEME"/vendor/mpdf/mpdf/ttfonts/DejaVuSansMono.ttf 2>/dev/null || true

echo "✓ $OUT ($(du -h "$OUT" | cut -f1)) — über WP-Admin → Themes → Hochladen installieren."
