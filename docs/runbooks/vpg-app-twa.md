# VPG · App im Store (PWA → TWA) — Runbook

**Stand:** Die PWA ist app-fähig — Manifest (jetzt mit `id` und
Shortcuts: Map/Magazine/Dashboard), Service-Worker mit Shell-Cache und
**Offline-Issues**, installierbar über den Browser. Der Store-Schritt ist
Verpackung, kein Umbau.

## Play Store (Android · TWA)

1. `npm i -g @bubblewrap/cli` → `bubblewrap init
   --manifest=https://viennaphotogroup.com/wp-content/themes/vpg-v3-gallery/manifest.json`
2. `bubblewrap build` → signiertes `.aab`.
3. **Digital Asset Links**: die von Bubblewrap ausgegebene
   `assetlinks.json` unter
   `https://viennaphotogroup.com/.well-known/assetlinks.json` ablegen
   (sonst zeigt die App eine Browser-Leiste).
4. Play Console: einmalig $25, App als „Photography" einreichen.

## iOS

Kein TWA-Äquivalent; Optionen der Reihe nach:
1. **Nichts tun** — iOS installiert die PWA über „Zum Home-Bildschirm";
   Offline-Issues funktionieren dort ebenfalls.
2. Später Capacitor-Wrapper, wenn Push-Notifications auf iOS gebraucht
   werden (Web-Push geht auf iOS ab 16.4 auch in der PWA).

## Vorher prüfen

- Lighthouse-PWA-Audit grün (installability).
- Maskable Icon im Manifest (512px, `purpose: "maskable"`) ergänzen —
  einziges noch offenes Asset.
- Offline-Fallback-Seite `/offline/` existiert (Setup-Wizard-Seite prüfen).
