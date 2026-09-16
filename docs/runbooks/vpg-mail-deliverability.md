# VPG · Mail-Zustellbarkeit (SMTP + SPF/DKIM/DMARC)

Verify-, Feedback- und Digest-Mails sind conversion-kritisch. Ohne diese
Einrichtung landen sie im Spam.

## 1 · SMTP aktivieren (Theme-seitig fertig)

In `wp-config.php` (niemals in der DB):

```php
define( 'VPG_SMTP_HOST',   'smtp.easyname.com' );   // Hoster-SMTP
define( 'VPG_SMTP_PORT',   587 );
define( 'VPG_SMTP_USER',   'hallo@viennaphotogroup.com' );
define( 'VPG_SMTP_PASS',   '…' );
define( 'VPG_SMTP_FROM',   'hallo@viennaphotogroup.com' );
define( 'VPG_SMTP_SECURE', 'tls' );
```

Kontrolle: WP-Admin → Magazine → **📮 Mail log** zeigt Transport und die
letzten 50 Sends inkl. Fehlern.

## 2 · DNS-Records (beim Domain-Provider)

| Typ | Name | Wert |
|---|---|---|
| TXT (SPF) | `@` | `v=spf1 include:spf.easyname.com ~all` *(Include des Mail-Hosters)* |
| TXT (DKIM) | `<selector>._domainkey` | vom Hoster generieren lassen (Mail-Panel → DKIM aktivieren) |
| TXT (DMARC) | `_dmarc` | `v=DMARC1; p=quarantine; rua=mailto:hallo@viennaphotogroup.com; fo=1` |

Regeln:
- **Nur ein** SPF-Record; neue Sender via `include:` ergänzen, nie zweiten
  TXT anlegen.
- DMARC erst mit `p=none` starten, eine Woche Reports lesen, dann auf
  `p=quarantine` schärfen.
- From-Adresse (`VPG_SMTP_FROM`) muss zur Domain der DKIM-Signatur passen
  (Alignment), sonst scheitert DMARC.

## 3 · Verifizieren

```bash
dig TXT viennaphotogroup.com +short          # SPF sichtbar?
dig TXT _dmarc.viennaphotogroup.com +short   # DMARC sichtbar?
```

Dann eine Test-Anmeldung machen und die Verify-Mail an einen
Gmail-Account schicken → „Original anzeigen": `SPF: PASS`, `DKIM: PASS`,
`DMARC: PASS` müssen alle grün sein. Alternativ https://www.mail-tester.com
(free) — Ziel ≥ 9/10.

## 4 · Wenn Mails weiter im Spam landen

1. Mail log prüfen (FAILED-Zeilen → SMTP-Zugangsdaten/Port).
2. mail-tester-Report lesen — er benennt den fehlenden Record.
3. Reverse-DNS des SMTP-Hosts prüfen (Sache des Hosters).
4. Keine Massen-Mails vom Shared-Host: der Digest bleibt klein; wächst die
   Liste über ~500, auf dedizierten Versand (z. B. Postal, self-hosted,
   free) wechseln.
