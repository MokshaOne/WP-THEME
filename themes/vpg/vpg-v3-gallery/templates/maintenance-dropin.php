<?php
/**
 * VPG · maintenance drop-in (0678). Copied once to wp-content/maintenance.php
 * — WordPress shows it during core/plugin updates instead of the bare default.
 */
header( 'HTTP/1.1 503 Service Unavailable' );
header( 'Retry-After: 120' );
?><!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Viennaphotogroup — kurz im Dunkelraum</title>
<style>
body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;
  background:#FFFFFF;color:#0B0B0B;font-family:'Archivo',system-ui,-apple-system,sans-serif;text-align:center}
.wrap{padding:32px;max-width:520px}
.mark{font-weight:900;letter-spacing:.04em;font-size:14px;text-transform:uppercase;margin-bottom:36px}
.mark i{color:#E5341F;font-style:normal}
h1{font-weight:900;text-transform:uppercase;font-size:clamp(34px,8vw,56px);line-height:.95;letter-spacing:-.01em;margin:0 0 18px}
h1 i{color:#E5341F;font-style:normal}
p{color:#6A6A6A;font-size:15px;line-height:1.6;margin:0}
@media (prefers-color-scheme:dark){body{background:#0B0B0B;color:#F5F4F1}p{color:#A5A29C}}
</style>
</head>
<body>
<div class="wrap">
  <div class="mark">VIENNAPHOTOGROUP<i>.</i></div>
  <h1>Kurz im Dunkelraum<i>.</i></h1>
  <p>Wir entwickeln gerade — ein Update läuft. In ein, zwei Minuten hängt alles wieder an der Wand. Danke für die Geduld.</p>
</div>
</body>
</html>