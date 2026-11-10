# VPG · Übersetzungs-Workflow (DE/EN)

Das Theme ist vollständig i18n-ready: **922 Strings** in `vpg-v2.pot`
(automatisch extrahiert), Textdomain `vpg-v2` wird aus diesem Ordner geladen.

## Deutsch übersetzen

1. `vpg-v2.pot` in [Poedit](https://poedit.net) (free) öffnen → „Neue
   Übersetzung erstellen" → Sprache `de_AT`.
2. Übersetzen (Poedit kann maschinell vorübersetzen, dann Korrektur lesen).
3. Speichern als `vpg-v2-de_AT.po` → Poedit erzeugt automatisch die
   `vpg-v2-de_AT.mo` daneben. Beide Dateien in diesen Ordner legen.

## Umschalter

Der Sprachwechsel läuft über `?lang=de` / `?lang=en` (Cookie, 1 Jahr) —
implementiert in `inc/platform.php`. Sobald die `.mo` liegt, greift er
sofort. Link-Beispiel für Header/Footer:

```php
<a href="<?php echo esc_url( add_query_arg( 'lang', 'de' ) ); ?>">DE</a> ·
<a href="<?php echo esc_url( add_query_arg( 'lang', 'en' ) ); ?>">EN</a>
```

## POT aktualisieren

Nach String-Änderungen im Theme das Extraktions-Snippet aus dem Repo-Verlauf
erneut ausführen (oder `wp i18n make-pot . languages/vpg-v2.pot`, wenn WP-CLI
mit i18n-Command verfügbar ist), dann in Poedit „Aus POT-Datei aktualisieren".
