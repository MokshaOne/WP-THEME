# VPG · KI-Alt-Texte (self-hosted) — Runbook

**Stand:** Die Integration ist fertig implementiert (`inc/advanced.php`):
ein täglicher Cron beschreibt bis zu 10 Bilder ohne Alt-Text über einen
OpenAI-kompatiblen Vision-Endpoint. Ohne Konfiguration passiert nichts.

## Aktivieren

1. Self-hosted Vision-Server starten (free, lokal/NAS/VPS):
   ```bash
   # llama.cpp mit einem LLaVA/Moondream-Modell (klein & CPU-tauglich)
   ./llama-server -m moondream2.gguf --mmproj moondream2-mmproj.gguf --port 8080
   ```
2. In `wp-config.php`:
   ```php
   define( 'VPG_CAPTION_URL',   'http://127.0.0.1:8080/v1/chat/completions' );
   define( 'VPG_CAPTION_MODEL', 'moondream2' ); // optional
   ```
3. Fertig — der Cron `vpg_caption_run` füllt fehlende Alt-Texte
   (max. 30 Wörter, sanitized). Manuell anstoßen: `wp cron event run
   vpg_caption_run`.

## Grenzen & Regeln

- Shared Hosting erreicht `127.0.0.1` nicht — dann den Server auf dem
  eigenen NAS/VPS laufen lassen und per HTTPS+Basic-Auth erreichbar machen
  (URL entsprechend setzen). Bilder verlassen trotzdem nie eure
  Infrastruktur Richtung Dritter.
- KI-Texte sind Barrierefreiheits-Hilfe, keine Bildunterschrift: Captions
  und Credits schreiben weiterhin Menschen.
- Bereits gesetzte Alt-Texte werden nie überschrieben.
