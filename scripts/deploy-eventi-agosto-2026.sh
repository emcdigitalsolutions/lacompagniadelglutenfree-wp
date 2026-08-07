#!/usr/bin/env bash
# Deploy eventi "Novità & Eventi" agosto 2026 sul sito LIVE (Keliweb).
# Uso:  bash scripts/deploy-eventi-agosto-2026.sh
set -euo pipefail

KEY="C:/workspace/vm_access_key/keliweb/id_rsa"
HOST="gkvuonjr@lacompagniadelglutenfree.it"
REPO="$(cd "$(dirname "$0")/.." && pwd)"
THEME="~/public_html/wp-content/themes/lcgf-child"
IMPORT="~/eventi-import"

echo "== 1) creo cartella import sul server =="
ssh -i "$KEY" -o StrictHostKeyChecking=accept-new "$HOST" "mkdir -p $IMPORT"

echo "== 2) copio flyer + script import =="
scp -i "$KEY" \
  "$REPO/images/eventi/2026-08-12.jpg" \
  "$REPO/images/eventi/2026-08-13.jpg" \
  "$REPO/images/eventi/2026-08-20-21.jpg" \
  "$REPO/scripts/import-eventi-agosto-2026.php" \
  "$HOST:$IMPORT/"

echo "== 3) copio i 2 file di tema aggiornati =="
scp -i "$KEY" \
  "$REPO/wp-content/themes/lcgf-child/archive-lcgf_evento.php" \
  "$REPO/wp-content/themes/lcgf-child/single-lcgf_evento.php" \
  "$HOST:$THEME/"

echo "== 4) eseguo import + flush cache/transient =="
ssh -i "$KEY" "$HOST" "cd ~/public_html && \
  wp eval-file $IMPORT/import-eventi-agosto-2026.php && \
  wp cache flush && \
  wp transient delete --all && \
  echo '--- eventi creati ---' && \
  wp post list --post_type=lcgf_evento --fields=ID,post_title,post_status --format=table"

echo "== 5) verifica pagine live (HTTP 200 attesi) =="
for u in \
  "https://www.lacompagniadelglutenfree.it/fiere-eventi/" \
  "https://www.lacompagniadelglutenfree.it/evento/fuata-fest-2026-ravanusa/" \
  "https://www.lacompagniadelglutenfree.it/evento/napoli-incontra-ravanusa-2026/" \
  "https://www.lacompagniadelglutenfree.it/evento/st-julians-international-pizza-festival-2026/" ; do
  code=$(curl -s -o /dev/null -w '%{http_code}' "$u" || echo ERR)
  echo "  [$code] $u"
done

echo "== FATTO =="
