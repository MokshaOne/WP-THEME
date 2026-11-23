#!/usr/bin/env bash
#
# Build the Astro frontend from live WordPress content and publish dist/ to
# easyname over FTPS. Runs on the NAS (cron, or inside a node:22 container via
# .gitea/workflows/deploy-frontend.yml), so publishing does not need GitHub.
#
# Usage:
#   ./nas-deploy.sh --local-only   build only, no network
#   ./nas-deploy.sh --dry-run      build, then print the upload plan
#   ./nas-deploy.sh                build and publish
#
# Needed to publish: FTP_USER, FTP_PASS
# Optional: WP_BASE (default https://wp.m1o.at/wp-json/wp/v2; on the NAS use a
#           LAN address to skip the Cloudflare round trip), FTP_HOST
#           (default ftp.easyname.com), FTP_DIR (default /html/), APP_DIR,
#           STATE_DIR, LOG_FILE, MANIFEST, LOCK_FILE
#
# Cron example:
#   15 3,15 * * * /volume1/wp-theme/tools/deploy/nas-deploy.sh >> /volume1/wp-theme/deploy.log 2>&1
#
# Exit codes: 0 ok, 1 preflight/config, 2 build, 3 upload.

set -Eeuo pipefail

SELF_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="${APP_DIR:-$SELF_DIR/../../frontends/raveenthiran/astro}"
WP_BASE="${WP_BASE:-https://wp.m1o.at/wp-json/wp/v2}"
FTP_HOST="${FTP_HOST:-ftp.easyname.com}"
FTP_DIR="${FTP_DIR:-/html/}"
STATE_DIR="${STATE_DIR:-$HOME/.local/state/rvn-deploy}"
LOG_FILE="${LOG_FILE:-$STATE_DIR/deploy.log}"
MANIFEST="${MANIFEST:-$STATE_DIR/published.manifest}"
LOCK_FILE="${LOCK_FILE:-$STATE_DIR/deploy.lock}"

DRY_RUN=0
LOCAL_ONLY=0

log() { printf '%s  %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$*" | tee -a "$LOG_FILE" >&2; }
die() { local code=$1; shift; log "ERROR: $*"; exit "$code"; }

for arg in "$@"; do
  case "$arg" in
    --dry-run)    DRY_RUN=1 ;;
    --local-only) LOCAL_ONLY=1 ;;
    -h|--help)    sed -n '4,22p' "${BASH_SOURCE[0]}" | sed 's/^# \{0,1\}//'; exit 0 ;;
    *)            echo "unknown argument: $arg" >&2; exit 1 ;;
  esac
done

mkdir -p "$STATE_DIR"

if command -v flock >/dev/null 2>&1; then
  exec 9>"$LOCK_FILE"
  flock -n 9 || die 1 "another deploy is running (lock: $LOCK_FILE)"
else
  mkdir "$LOCK_FILE.d" 2>/dev/null || die 1 "another deploy is running (lock: $LOCK_FILE.d)"
  trap 'rmdir "$LOCK_FILE.d" 2>/dev/null || true' EXIT
fi

log "--- preflight ---"
[ -f "$APP_DIR/package.json" ] || die 1 "no package.json at APP_DIR=$APP_DIR"
command -v node >/dev/null 2>&1 || die 1 "node not on PATH - this host cannot build"
command -v npm >/dev/null 2>&1 || die 1 "npm not on PATH"
NODE_MAJOR="$(node -p 'process.versions.node.split(".")[0]' 2>/dev/null || echo 0)"
[ "$NODE_MAJOR" -ge 18 ] || die 1 "Node $NODE_MAJOR too old - Astro 5 needs 18.17+"
command -v curl >/dev/null 2>&1 || die 1 "curl not on PATH"
curl -V | grep -qi 'ftp' || die 1 "curl lacks FTP support"

if [ "$LOCAL_ONLY" -eq 0 ] && [ "$DRY_RUN" -eq 0 ]; then
  [ -n "${FTP_USER:-}" ] || die 1 "FTP_USER is not set"
  [ -n "${FTP_PASS:-}" ] || die 1 "FTP_PASS is not set"
fi

log "node $(node --version) | npm $(npm --version)"
log "app  $APP_DIR"
log "wp   $WP_BASE"

log "--- build ---"
cd "$APP_DIR"
WP_REQUIRE_LIVE=1 WP_BASE="$WP_BASE" npm ci --no-audit --no-fund >>"$LOG_FILE" 2>&1 \
  || die 2 "npm ci failed (see $LOG_FILE)"
WP_REQUIRE_LIVE=1 WP_BASE="$WP_BASE" npm run build >>"$LOG_FILE" 2>&1 \
  || die 2 "build failed - WordPress unreachable or zero published projects (see $LOG_FILE)"

DIST="$APP_DIR/dist"
[ -d "$DIST" ] || die 2 "build produced no dist/"
FILE_COUNT="$(find "$DIST" -type f | wc -l | tr -d ' ')"
[ "$FILE_COUNT" -gt 0 ] || die 2 "dist/ is empty"
log "built $FILE_COUNT files, $(du -sh "$DIST" 2>/dev/null | cut -f1)"

if [ "$LOCAL_ONLY" -eq 1 ]; then
  log "local-only: nothing uploaded"
  exit 0
fi

BASE_URL="ftp://$FTP_HOST${FTP_DIR%/}"
log "--- upload to $BASE_URL ---"

cd "$DIST"
mapfile -t FILES < <(find . -type f -printf '%P\n' | sort)
NEW_MANIFEST="$(mktemp)"
trap 'rm -f "$NEW_MANIFEST"' EXIT

FAILED=0
for f in "${FILES[@]}"; do
  if [ "$DRY_RUN" -eq 1 ]; then
    log "DRY   put $f"
    printf '%s\n' "$f" >> "$NEW_MANIFEST"
    continue
  fi
  if curl --silent --show-error --fail --ssl-reqd --ftp-create-dirs \
           --connect-timeout 20 --max-time 300 \
           --user "$FTP_USER:$FTP_PASS" \
           --upload-file "$f" "$BASE_URL/$f" >>"$LOG_FILE" 2>&1; then
    printf '%s\n' "$f" >> "$NEW_MANIFEST"
  else
    log "upload failed: $f"
    FAILED=$((FAILED + 1))
  fi
done

[ "$FAILED" -eq 0 ] || die 3 "$FAILED file(s) failed to upload - manifest untouched"

if [ -f "$MANIFEST" ]; then
  REMOVED=0
  while IFS= read -r old; do
    [ -n "$old" ] || continue
    if ! grep -Fxq -- "$old" "$NEW_MANIFEST"; then
      if [ "$DRY_RUN" -eq 1 ]; then
        log "DRY   delete $old"
      else
        if curl --silent --fail --ssl-reqd --connect-timeout 20 --max-time 120 \
                --user "$FTP_USER:$FTP_PASS" -Q "DELE ${FTP_DIR%/}/$old" \
                "$BASE_URL/" >>"$LOG_FILE" 2>&1; then
          REMOVED=$((REMOVED + 1))
        else
          log "could not delete stale $old (continuing)"
        fi
      fi
    fi
  done < "$MANIFEST"
  log "pruned $REMOVED stale file(s)"
fi

if [ "$DRY_RUN" -eq 0 ]; then
  cp "$NEW_MANIFEST" "$MANIFEST"
  log "manifest updated: $(wc -l < "$MANIFEST" | tr -d ' ') entries"
else
  log "dry-run: $(wc -l < "$NEW_MANIFEST" | tr -d ' ') file(s) would be uploaded"
fi

log "done"
