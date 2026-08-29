# VPG · Backup- & Staging-Runbook

## 1 · Was gesichert werden muss

| Was | Wo | Frequenz |
|---|---|---|
| Datenbank | MySQL (alle Inhalte, Members, RSVPs, Newsletter-Liste) | täglich |
| `wp-content/uploads/` | Fotos, PDFs, Cover, OG-Cards | täglich (inkrementell) |
| Theme | liegt im Git-Repo — **kein** Backup nötig, nur deployen | — |
| `wp-config.php` | Secrets (SMTP, Salts) | bei Änderung, verschlüsselt |

## 2 · Backup-Skript (Shared Hosting, per Cron)

`viennaphotogroup.com/tools/backup.sh` im Repo. Auf dem Server ablegen,
Zugangsdaten eintragen, dann als Hoster-Cronjob täglich 04:00:

```
0 4 * * * /home/USER/tools/backup.sh >> /home/USER/backups/backup.log 2>&1
```

Das Skript rotiert 14 Tage; der `rsync`-Zielordner sollte NICHT im Webroot
liegen. Zusätzlich wöchentlich ein Offsite-Pull (z. B. vom eigenen NAS per
rsync/SFTP ziehen) — ein Backup auf demselben Server ist keins.

## 3 · Restore-Probe (vierteljährlich!)

1. DB-Dump in eine leere lokale DB einspielen (`mysql < dump.sql`).
2. Lokales WP mit Theme aus Git + Uploads-Kopie starten.
3. Login, eine Location, ein Issue-PDF öffnen → Restore gilt als bestanden.
Ein Backup, das nie probegespielt wurde, ist Hoffnung, kein Backup.

## 4 · Staging

- Subdomain `staging.viennaphotogroup.com` (per robots + HTTP-Auth sperren).
- Deploy dorthin zuerst: Theme aus Git, DB-Kopie von Prod
  (`wp search-replace 'viennaphotogroup.com' 'staging.viennaphotogroup.com'`
  wenn WP-CLI verfügbar ist, sonst Search-Replace-DB-Skript).
- Erst nach Klick-Durchlauf (Join → Verify → Submit → Approve) auf Prod
  deployen.
- Releases sind reine Theme-Deploys: ZIP hochladen/`git pull` — Assets sind
  filemtime-versioniert, kein Cache-Purge nötig (siehe Caching-Runbook).
