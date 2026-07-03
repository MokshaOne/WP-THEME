# Variable font (#83/#10)

> **v4.80:** `inter-tight.woff2` / `jetbrains-mono.woff2` here already ARE the
> variable files — every @font-face weight points at them (one request per
> family). The optional `inter-tight-var.woff2` drop below now only buys the
> hero weight-morph effect, not a request reduction.

Drop the Inter Tight **variable** woff2 here as `inter-tight-var.woff2`
(download from https://fonts.google.com/specimen/Inter+Tight → variable axes,
or rsms/inter releases). When the file exists the theme automatically declares
it (it wins all weights → 3 static files become 1 request) and enables the
hero weight-morph effect at the "cinematic" motion level. No settings needed.
