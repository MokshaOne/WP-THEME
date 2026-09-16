# VPG · Deployment auf easyname — Runbook

viennaphotogroup.com läuft als WordPress auf **easyname Shared Hosting**
(Webspace, kein Root, Composer auf dem Server nicht garantiert). Daraus
folgt die wichtigste Regel: **das Theme wird fertig gebaut hochgeladen** —
inklusive `vendor/` (mPDF). Nichts wird auf dem Server kompiliert.

## 1 · ZIP bauen (lokal, einmal pro Release)

```bash
cd viennaphotogroup.com
./tools/build-zip.sh          # erzeugt dist/vpg-v3-gallery-<version>.zip
```

Das Skript führt `composer install --no-dev` im Theme aus, prüft dass
`vendor/mpdf` und die Font-Dateien da sind, und packt alles ohne
Entwicklungs-Ballast (node_modules, .git, tests). Der ZIP ist direkt
über WP-Admin installierbar.

## 2 · Hochladen — zwei Wege

**A · WP-Admin (einfachster Weg):** Design → Themes → Hinzufügen →
Theme hochladen → ZIP wählen → Aktivieren. Bei einem Update vorher das
alte Theme kurz auf ein Default-Theme umschalten oder „Ersetzen"
bestätigen (WP fragt seit 5.5 selbst).

**B · SFTP (bei großen ZIPs oder PHP-Upload-Limit):** easyname
Control Panel → FTP-Zugang; Ziel ist
`/html/wordpress/wp-content/themes/vpg-v3-gallery/` (der genaue
Pfad steht im Control Panel unter Hosting → Verzeichnis). Erst als
`vpg-v3-gallery-new/` hochladen, dann per Umbenennen tauschen —
so gibt es keine halb-kopierte Live-Minute.

## 3 · easyname-Einstellungen (einmalig)

| Wo | Was |
|---|---|
| Control Panel → Hosting → PHP | **PHP ≥ 8.1** wählen (Theme-Minimum) |
| `.user.ini` im WP-Root | `memory_limit = 256M` — mPDF + GD brauchen Luft beim Heft-Rendern |
| Control Panel → Cronjobs | `https://viennaphotogroup.com/wp-cron.php?doing_wp_cron` alle **15 Minuten** aufrufen |
| `wp-config.php` | `define( 'DISABLE_WP_CRON', true );` — der echte Cronjob übernimmt |
| `wp-config.php` | `VPG_SMTP_*`-Konstanten → siehe `vpg-mail-deliverability.md` (smtp.easyname.com) |

Der echte Cronjob ist auf Shared Hosting kein Nice-to-have: Digest,
Verify-Reminder, Event-Erinnerungen und der Alt-Text-Lauf hängen an
wp_cron, und besucherausgelöstes Pseudo-Cron feuert auf einer kleinen
Seite zu unregelmäßig.

## 4 · Nach jedem Deploy

1. Einstellungen → Permalinks → **Speichern** (registriert CPTs, Feeds,
   ICS-/Export-/Verify-Endpoints neu).
2. Eine Seite im Inkognito-Fenster laden — prüft Service-Worker-Update
   und dass die Fonts lokal kommen.
3. Bei Cloudflare davor: einmal „Cache leeren" für CSS/JS (oder auf die
   gehashten Dateinamen vertrauen, siehe `vpg-caching-cloudflare.md`).

## 5 · Was NICHT auf den Server gehört

`tests/`, `tools/`, `docs/`, `composer.lock`-Experimente, `.git` — das
Build-Skript lässt sie ohnehin weg. Backups laufen getrennt über
`tools/backup.sh` (siehe `vpg-backup-staging.md`); easyname-eigene
Backups sind Zusatz, kein Ersatz.
