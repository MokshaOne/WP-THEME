# VPG · ActivityPub / Fediverse — Runbook

Ziel: Das Magazin von Mastodon & Co. abonnierbar (`@journal@viennaphotogroup.com`).
Ein eigenes AP-Protokoll im Theme wäre monatelange Arbeit — der richtige
Weg ist das offizielle **ActivityPub-Plugin** (Automattic, free/GPL, im
WP-Plugin-Verzeichnis).

## Setup

1. Plugin „ActivityPub" installieren + aktivieren.
2. Einstellungen → ActivityPub: Blog-Actor aktivieren (ein Account für die
   ganze Seite) statt Autoren-Actors — passt zum Kollektiv-Gedanken.
3. Welche Inhalte föderieren: Journal-Posts an; die gated CPTs (Magazine,
   Reviews, Tutorials, Events) **aus** — die sind members-only und gehören
   nicht ins Fediverse.
4. Profilbild = rotes Quadrat/Wordmark, Bio = der Claim.

## Theme-Kompatibilität (bereits gegeben)

- OG-/Share-Cards: Posts ohne Foto bekommen die generierte Card — die
  taucht auch in Fediverse-Previews auf.
- REST-Härtung: unsere 401-Sperre gilt für `/wp/v2/…`; das Plugin nutzt
  eigene `/activitypub/…`-Routen und bleibt unberührt. Nach Aktivierung
  einmal prüfen: `curl -H 'Accept: application/activity+json'
  https://viennaphotogroup.com/@journal` muss JSON liefern.

## Erwartung

Follower sehen neue Journal-Posts als Beiträge; Antworten landen (optional)
als WP-Kommentare — dann greift unsere Moderations-Queue (⚑ Reports).
Klein anfangen: nur Journal, Antworten zunächst aus.
