#!/usr/bin/env python3
"""Generate raveenthiran-headless/assets/admin-dark.css.

WordPress' admin has no theming hooks, so the dark skin is derived mechanically:
every colour declaration in the stock admin stylesheets (and ACF's) is re-emitted
under the same selector with the colour remapped onto the site palette. Selectors
get an `html ` prefix so the overrides win regardless of stylesheet order.

Re-run after a major WordPress / ACF update:

    python3 tools/build-admin-dark.py <wordpress-dir> [<dir-with-acf-css>] \
        > raveenthiran-headless/assets/admin-dark.css

Hand-written layout / type / component rules live in assets/admin.css.
"""
import colorsys
import os
import re
import sys

INK, CANVAS, PANEL, RAISED = '#F4F2ED', '#0B0B0B', '#141414', '#1c1c1c'
MUTED, DIM, LINE, CHIP, FAINT = '#b8b2a8', '#8a857e', '#2a2a2a', '#3a3a37', '#6f6a63'
ACCENT, ACCENT_DEEP = '#FF3B1D', '#C92A10'

WP_ADMIN = ['common', 'forms', 'admin-menu', 'dashboard', 'list-tables', 'edit', 'revisions',
            'media', 'themes', 'nav-menus', 'widgets', 'site-icon', 'l10n', 'site-health',
            'color-picker', 'code-editor', 'farbtastic', 'login']
WP_INC = ['buttons', 'admin-bar', 'media-views', 'editor', 'jquery-ui-dialog', 'wp-pointer', 'wp-auth-check']
ACF = ['acf-global', 'acf-input', 'acf-field-group', 'acf-pro-input', 'acf-pro-field-group']

NAMED = {'white': (255, 255, 255), 'black': (0, 0, 0)}
COLOR_RE = re.compile(
    r'#[0-9a-fA-F]{3,8}\b|rgba?\(\s*[\d.]+\s*,\s*[\d.]+\s*,\s*[\d.]+\s*(?:,\s*[\d.]+\s*)?\)|\b(?:white|black)\b')


def parse_color(tok):
    t = tok.lower()
    if t in NAMED:
        return NAMED[t] + (1.0,)
    if t.startswith('#'):
        h = t[1:]
        if len(h) in (3, 4):
            h = ''.join(c * 2 for c in h)
        if len(h) not in (6, 8):
            return None
        a = int(h[6:8], 16) / 255 if len(h) == 8 else 1.0
        return (int(h[0:2], 16), int(h[2:4], 16), int(h[4:6], 16), a)
    n = [float(x) for x in re.findall(r'[\d.]+', t)]
    return (n[0], n[1], n[2], n[3] if len(n) > 3 else 1.0)


def fmt(hexc, a):
    if a >= 0.999:
        return hexc
    r, g, b = int(hexc[1:3], 16), int(hexc[3:5], 16), int(hexc[5:7], 16)
    return 'rgba(%d,%d,%d,%s)' % (r, g, b, ('%.2f' % a).rstrip('0').rstrip('.'))


def classify(r, g, b):
    h, l, s = colorsys.rgb_to_hls(r / 255, g / 255, b / 255)
    hue = h * 360
    if s < 0.16 or l > 0.985 or l < 0.03:
        return 'grey', l
    if 190 <= hue <= 260:
        return 'blue', l
    if hue < 20 or hue >= 330:
        return 'red', l
    if 20 <= hue < 70:
        return 'yellow', l
    if 70 <= hue < 190:
        return 'green', l
    return 'other', l


def map_bg(c):
    r, g, b, a = c
    kind, l = classify(r, g, b)
    if kind == 'grey':
        if r == g == b == 0 and a < 1:
            return None                      # translucent black overlays stay
        if l >= 0.985: out = PANEL
        elif l >= 0.955: out = RAISED
        elif l >= 0.92: out = CANVAS
        elif l >= 0.80: out = LINE
        elif l >= 0.60: out = CHIP
        elif l >= 0.40: out = FAINT
        elif l >= 0.16: out = PANEL
        else: out = CANVAS
        return fmt(out, a)
    if l >= 0.85:                            # pale tints behind notices / highlighted rows
        return fmt({'blue': '#1e1e1e', 'red': '#2a110c', 'yellow': '#221c0c', 'green': '#0f2114'}.get(kind, RAISED), a)
    if kind == 'blue':
        return fmt(ACCENT_DEEP if l < 0.62 else ACCENT, a)
    return None                              # status reds / greens / yellows keep their meaning


def map_fg(c):
    r, g, b, a = c
    kind, l = classify(r, g, b)
    if kind == 'grey':
        if l < 0.30: out = INK
        elif l < 0.50: out = MUTED
        elif l < 0.72: out = DIM
        else: return None                    # already light: text on dark surfaces
        return fmt(out, a)
    if kind == 'blue':
        if l >= 0.85: return None
        return fmt(INK if 0.36 <= l < 0.62 else ACCENT, a)   # link = ink, hover / highlight = accent
    if l >= 0.62:
        return None
    if kind == 'red': return fmt('#FF6A55', a)
    if kind == 'green': return fmt('#5BD07A', a)
    if kind == 'yellow': return fmt('#E5B43A', a)
    return None


def map_line(c):
    r, g, b, a = c
    kind, l = classify(r, g, b)
    if kind == 'grey':
        if r == g == b == 0 and a < 1:
            return None                      # drop shadows stay
        if l >= 0.70: out = LINE
        elif l >= 0.45: out = FAINT
        else: out = FAINT
        return fmt(out, a)
    if kind == 'blue':
        return fmt(INK, a)                   # focus rings
    if l >= 0.85:
        return fmt(LINE, a)
    return None


def remap(prop, value):
    p = prop.lower()
    if p.startswith('background'):
        fn = map_bg
    elif p in ('color', 'fill', 'stroke', '-webkit-text-fill-color', 'caret-color'):
        fn = map_fg
    elif p.startswith(('border', 'outline', 'box-shadow', 'text-decoration', 'column-rule')) or p == '-webkit-box-shadow':
        fn = map_line
    else:
        return None
    changed = False
    out, last = [], 0
    # leave url(...) payloads alone (data-URI SVGs carry their own colours)
    protected = [(m.start(), m.end()) for m in re.finditer(r'url\([^)]*\)', value)]
    for m in COLOR_RE.finditer(value):
        if any(s <= m.start() < e for s, e in protected):
            continue
        c = parse_color(m.group(0))
        new = fn(c) if c else None
        if new is None or new.lower() == m.group(0).lower():
            continue
        out.append(value[last:m.start()])
        out.append(new)
        last = m.end()
        changed = True
    if not changed:
        return None
    out.append(value[last:])
    return ''.join(out)


def split_top(s, sep):
    parts, depth, cur, q = [], 0, [], None
    for ch in s:
        if q:
            cur.append(ch)
            if ch == q: q = None
            continue
        if ch in '"\'':
            q = ch; cur.append(ch); continue
        if ch in '([': depth += 1
        elif ch in ')]': depth -= 1
        if ch == sep and depth == 0:
            parts.append(''.join(cur)); cur = []
        else:
            cur.append(ch)
    parts.append(''.join(cur))
    return parts


def prefix(sel):
    out = []
    for s in split_top(sel, ','):
        s = ' '.join(s.split())
        if not s:
            continue
        out.append(s if re.match(r'(html|:root)\b', s) else 'html ' + s)
    return ','.join(out)


def blocks(css):
    """Yield (prelude, body) for each top-level block of `css`."""
    i, n, start = 0, len(css), 0
    while i < n:
        ch = css[i]
        if ch in '"\'':
            j = css.find(ch, i + 1)
            i = (j if j != -1 else n) + 1
            continue
        if ch == ';':
            start = i + 1
        elif ch == '{':
            depth, j, q = 1, i + 1, None
            while j < n and depth:
                cj = css[j]
                if q:
                    if cj == q: q = None
                elif cj in '"\'': q = cj
                elif cj == '{': depth += 1
                elif cj == '}': depth -= 1
                j += 1
            yield css[start:i].strip(), css[i + 1:j - 1]
            i = start = j
            continue
        i += 1


def convert(css, out):
    for prelude, body in blocks(css):
        if prelude.startswith('@'):
            if re.match(r'@(media|supports|layer|container)\b', prelude):
                inner = []
                convert(body, inner)
                if inner:
                    out.append(prelude + '{' + ''.join(inner) + '}')
            continue                         # @keyframes, @font-face, … carry nothing to remap
        decls = []
        for d in split_top(body, ';'):
            if ':' not in d:
                continue
            prop, value = d.split(':', 1)
            prop, value = prop.strip(), value.strip()
            if not prop or prop.startswith('--'):
                continue
            new = remap(prop, value)
            if new:
                decls.append(prop + ':' + new)
        if decls:
            out.append(prefix(prelude) + '{' + ';'.join(decls) + '}')


def main():
    wp = sys.argv[1]
    acf = sys.argv[2] if len(sys.argv) > 2 else None
    files = [os.path.join(wp, 'wp-admin', 'css', f + '.css') for f in WP_ADMIN]
    files += [os.path.join(wp, 'wp-includes', 'css', f + '.css') for f in WP_INC]
    if acf:
        files += [os.path.join(acf, f + '.css') for f in ACF]
    ver = ''
    try:
        ver = re.search(r"\$wp_version = '([^']+)'", open(os.path.join(wp, 'wp-includes', 'version.php')).read()).group(1)
    except Exception:
        pass
    print('/* GENERATED by tools/build-admin-dark.py from WordPress %s admin CSS%s — do not edit by hand. */'
          % (ver, ' + ACF' if acf else ''))
    for f in files:
        if not os.path.exists(f):
            sys.stderr.write('skip (missing): %s\n' % f)
            continue
        css = re.sub(r'/\*.*?\*/', '', open(f, encoding='utf-8').read(), flags=re.S)
        out = []
        convert(css, out)
        print('/* %s */' % os.path.basename(f))
        print('\n'.join(out))


if __name__ == '__main__':
    main()
