#!/usr/bin/env python3
"""Generate templates/partials/, content/services/ and pages/ from the legacy page files.

Dev-only (not shipped). Uses only the Python standard library.

Usage:
    python3 -I scripts/extract.py [--src data|legacy] [--all-raw]
    (--src defaults to legacy/ once the old page files have been moved there)

What it does:
  1. Reads each legacy page file ({src}/*.php, a nowdoc returning the full body).
  2. Checks the utility bar / header / ribbon / footer are identical and writes
     them to templates/partials/*.php.
  3. For every page with the shared hero + article-layout structure, extracts
     the content into JSON and asks scripts/roundtrip.php (real WordPress, real
     template) to render it. Only pages whose output is byte-identical to the
     original <main> become "service" pages; everything else becomes "raw"
     (pages/{slug}.php holding just the <main> element).
  4. Adds "template" and "content" to each entry in data/manifest.php.

Pages listed in scripts/raw-overrides.txt are always kept raw (fallbacks found
by scripts/compare.mjs); see tests/fallbacks.md for the reasons.
"""
import glob
import html
import json
import os
import re
import shutil
import subprocess
import sys
import tempfile
from html.parser import HTMLParser

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
GUARD = "if (!defined('ABSPATH')) { http_response_code(404); exit; }"
VOID = {'img', 'br', 'hr', 'input', 'meta', 'link', 'source', 'area', 'col', 'embed', 'wbr', 'path', 'circle'}
CHROME = [('utility-bar', 'div', 'utility'), ('header', 'header', 'main-header'), ('product-ribbon', 'div', 'product-ribbon')]


class Fail(Exception):
    """Page does not fit the service layout."""


# ---------------------------------------------------------------- parsing
class Node:
    def __init__(self, tag, attrs, start, parent):
        self.tag, self.attrs, self.start, self.parent = tag, dict(attrs), start, parent
        self.children, self.end, self.open_end = [], None, None

    def cls(self):
        return self.attrs.get('class') or ''

    def has(self, c):
        return c in self.cls().split()


class Parser(HTMLParser):
    """Builds a tree with exact source offsets (start of open tag, end of close tag)."""

    def __init__(self, src):
        super().__init__(convert_charrefs=False)
        self.root = Node('#root', [], 0, None)
        self.cur = self.root
        self.line_starts = [0] + [m.end() for m in re.finditer('\n', src)]

    def _off(self):
        line, col = self.getpos()
        return self.line_starts[line - 1] + col

    def handle_starttag(self, tag, attrs):
        n = Node(tag, attrs, self._off(), self.cur)
        n.open_end = n.start + len(self.get_starttag_text())
        self.cur.children.append(n)
        if tag in VOID:
            n.end = n.open_end
        else:
            self.cur = n

    def handle_startendtag(self, tag, attrs):
        n = Node(tag, attrs, self._off(), self.cur)
        n.open_end = n.end = n.start + len(self.get_starttag_text())
        self.cur.children.append(n)

    def handle_endtag(self, tag):
        if tag in VOID:
            return
        n = self.cur
        while n is not None and n.tag != tag:
            n = n.parent
        if n is None:
            raise ValueError('stray </%s>' % tag)
        n.end = self._off() + len('</%s>' % tag)
        self.cur = n.parent


def parse(src):
    p = Parser(src)
    p.feed(src)
    p.close()
    return p.root


def read_nowdoc(path, marker):
    src = open(path, encoding='utf-8').read()
    m = re.search(r"return <<<'%s'\n(.*)\n%s;\s*$" % (marker, marker), src, re.S)
    if not m:
        raise SystemExit('cannot read nowdoc in %s' % path)
    return m.group(1)


def nowdoc_file(body, marker='VGNYC_PAGE', comment=''):
    if re.search(r'^\s*%s\b' % marker, body, re.M):
        raise SystemExit('nowdoc marker collision')
    head = '<?php\n' + (comment + '\n' if comment else '') + GUARD + '\n'
    return head + "return <<<'%s'\n%s\n%s;\n" % (marker, body, marker)


def write(path, text):
    os.makedirs(os.path.dirname(path), exist_ok=True)
    with open(path, 'w', encoding='utf-8', newline='') as f:
        f.write(text)


# ---------------------------------------------------------------- service extraction
def inner(b, n):
    return b[n.open_end:n.end - len('</%s>' % n.tag)]


def text(s):
    """Plain-text field: must have no markup; stored decoded (the template re-escapes)."""
    if '<' in s or '>' in s:
        raise Fail('markup in text field: %r' % s[:60])
    return html.unescape(s)


def before_svg(b, n, sep=''):
    s = inner(b, n)
    i = s.find(sep + '<svg')
    if i < 0:
        raise Fail('expected icon in %r' % s[:60])
    return text(s[:i])


def links(b, nodes):
    return [{'label': before_svg(b, a), 'href': html.unescape(a.attrs['href'])} for a in nodes]


def expect(n, tag, cls=None):
    if n is None or n.tag != tag or (cls and not n.has(cls)):
        raise Fail('expected %s.%s, got %s.%s' % (tag, cls, n and n.tag, n and n.cls()))
    return n


def extract_service(b, main):
    kids = main.children
    if not kids or not (kids[0].tag == 'section' and kids[0].has('product-hero')):
        raise Fail('no product hero')
    hero, i = kids[0], 1
    c = {}

    # hero copy
    d = expect(hero.children[0], 'div')
    hk = list(d.children)
    bc = expect(hk.pop(0), 'p', 'breadcrumb')
    prefix = '<a href="/nyc/">New York</a> / '
    if not inner(b, bc).startswith(prefix):
        raise Fail('breadcrumb prefix')
    c['breadcrumb'] = text(inner(b, bc)[len(prefix):])
    c['eyebrow'] = text(inner(b, expect(hk.pop(0), 'p', 'eyebrow')))
    c['h1'] = text(inner(b, expect(hk.pop(0), 'h1')))
    c['lead'] = text(inner(b, expect(hk.pop(0), 'p', 'lead')))
    if hk and hk[0].has('review-note'):
        c['review_note'] = text(inner(b, hk.pop(0)))
    actions = expect(hk.pop(0), 'div', 'hero-actions')
    a1, a2 = actions.children
    c['primary_cta'] = {'label': before_svg(b, a1, ' '), 'href': html.unescape(a1.attrs['href'])}
    c['secondary_cta'] = {'label': before_svg(b, a2, ' '), 'href': html.unescape(a2.attrs['href'])}

    fig = expect(hero.children[1], 'figure', 'product-photo')
    img = expect(fig.children[0], 'img')
    m = re.fullmatch(r'/nyc/assets/(.+)-(\d+)\.webp', img.attrs['src'])
    if not m:
        raise Fail('hero image src')
    sizes = [int(x) for x in re.findall(r'-(\d+)\.webp \d+w', img.attrs.get('srcset', ''))]
    c['hero_image'] = {
        'base': m.group(1),
        'sizes': sizes,
        'alt': img.attrs.get('alt', ''),
        'caption': text(inner(b, expect(fig.children[1], 'figcaption'))),
    }

    # optional product family grid
    c['product_family'] = None
    if i < len(kids) and kids[i].has('product-family'):
        pf = kids[i]
        c['product_family'] = {'h2': text(inner(b, expect(pf.children[0], 'h2'))),
                               'links': links(b, expect(pf.children[1], 'div', 'product-links').children)}
        i += 1

    # article + aside
    layout = kids[i] if i < len(kids) else None
    if layout is None or not layout.has('article-layout'):
        raise Fail('no article-layout')
    i += 1
    article = expect(layout.children[0], 'article')
    aside = expect(layout.children[1], 'aside', 'product-aside')

    c['sections'], c['faqs'] = [], None
    for s in article.children:
        expect(s, 'section')
        if s.has('faqs'):
            items = []
            for det in s.children[1:]:
                summ = expect(det.children[0], 'summary')
                q = inner(b, summ)
                if not q.endswith('<span>+</span>'):
                    raise Fail('faq summary')
                items.append({'q': text(q[:-len('<span>+</span>')]), 'a_html': b[summ.end:det.end - len('</details>')]})
            c['faqs'] = {'h2': text(inner(b, expect(s.children[0], 'h2'))), 'items': items}
            continue
        sk = list(s.children)
        sec = {'type': 'text', 'id': s.attrs.get('id'), 'eyebrow': None}
        if sk and sk[0].tag == 'p' and sk[0].has('eyebrow'):
            sec['eyebrow'] = text(inner(b, sk.pop(0)))
        h2 = expect(sk.pop(0), 'h2')
        sec['h2'] = text(inner(b, h2))
        if len(sk) == 1 and sk[0].has('options-grid'):
            sec['type'] = 'options'
            sec['items'] = [{'h3': text(inner(b, expect(x.children[0], 'h3'))), 'p': text(inner(b, expect(x.children[1], 'p')))} for x in sk[0].children]
        elif len(sk) == 1 and sk[0].has('check-list'):
            sec['type'] = 'checklist'
            sec['items'] = []
            for li in sk[0].children:
                s_li = inner(b, li)
                if not s_li.startswith('<svg'):
                    raise Fail('check-list icon')
                sec['items'].append(text(s_li[s_li.index('</svg>') + 6:]))
        else:
            sec['html'] = b[h2.end:s.end - len('</section>')]
        c['sections'].append(sec)

    hr = [n for n in aside.children if n.tag == 'hr']
    after = aside.children[aside.children.index(hr[0]) + 2:] if hr else []
    c['related'] = links(b, after)

    # closing blocks
    rest = kids[i:]
    c['cta_band'] = bool(rest and rest[0].has('cta-band'))
    if c['cta_band']:
        rest = rest[1:]
    c['extra_sections'] = [b[n.start:n.end] for n in rest]
    # Anything between the last child and </main> (e.g. comments) must be kept too.
    tail = b[(rest[-1].end if rest else kids[-1].end):main.end - len('</main>')]
    if tail:
        c['extra_sections'].append(tail)
    return order(c)


def order(c):
    """Key order matching the documented schema; drop optional empties."""
    keys = ['breadcrumb', 'eyebrow', 'h1', 'lead', 'review_note', 'primary_cta', 'secondary_cta', 'hero_image',
            'product_family', 'sections', 'faqs', 'related', 'cta_band', 'extra_sections']
    out = {k: c[k] for k in keys if k in c}
    for s in out['sections']:
        for k in ('id', 'eyebrow'):
            if s.get(k) is None:
                s.pop(k, None)
    if out['product_family'] is None:
        del out['product_family']
    if not out['extra_sections']:
        del out['extra_sections']
    return out


# ---------------------------------------------------------------- manifest
def update_manifest(assign):
    path = os.path.join(ROOT, 'data', 'manifest.php')
    src = open(path, encoding='utf-8').read()
    src = re.sub(r'\n\s*"(?:template|content)": "[^"]*",?(?=\n)', '', src)  # idempotent

    def repl(m):
        indent, name, comma = m.group(1), m.group(2), m.group(3)
        slug = name[:-4]
        if slug not in assign:
            return m.group(0)
        tpl, content = assign[slug]
        return '%s"file": "%s",\n%s"template": "%s",\n%s"content": "%s"%s' % (indent, name, indent, tpl, indent, content, comma)
    src = re.sub(r'^(\s*)"file": "([^"]+)"(,?)$', repl, src, flags=re.M)
    write(path, src)


# ---------------------------------------------------------------- main
def mysql_socket():
    env = os.environ.get('VGNYC_MYSQL_SOCKET')
    if env:
        return env
    found = glob.glob(os.path.expanduser('~/Library/Application Support/Local/run/*/mysql/mysqld.sock'))
    return found[0] if found else None


def roundtrip(candidates, mains):
    tmp = tempfile.mkdtemp(prefix='vgnyc-roundtrip-')
    try:
        for slug, data in candidates.items():
            write(os.path.join(tmp, slug + '.json'), json.dumps(data, ensure_ascii=False, indent=2))
            write(os.path.join(tmp, slug + '.main.html'), mains[slug])
        cmd = ['php']
        sock = mysql_socket()
        if sock:
            cmd += ['-d', 'mysqli.default_socket=' + sock]
        cmd += [os.path.join(ROOT, 'scripts', 'roundtrip.php'), tmp]
        res = subprocess.run(cmd, capture_output=True, text=True)
        if res.returncode != 0:
            raise SystemExit('roundtrip.php failed:\n' + res.stderr + res.stdout)
        return json.loads(res.stdout[res.stdout.index('{'):])
    finally:
        shutil.rmtree(tmp, ignore_errors=True)


def main():
    args = sys.argv[1:]
    default_src = 'legacy' if os.path.isdir(os.path.join(ROOT, 'legacy')) else 'data'
    src_dir = os.path.join(ROOT, args[args.index('--src') + 1] if '--src' in args else default_src)
    all_raw = '--all-raw' in args
    ov_file = os.path.join(ROOT, 'scripts', 'raw-overrides.txt')
    overrides = set()
    if os.path.exists(ov_file):
        overrides = {l.split('#')[0].strip() for l in open(ov_file, encoding='utf-8')} - {''}

    files = sorted(f for f in os.listdir(src_dir) if f.endswith('.php') and f not in ('manifest.php', 'index.php'))
    bodies = {f[:-4]: read_nowdoc(os.path.join(src_dir, f), 'VGNYC_PAGE') for f in files}

    # 1. shared chrome
    chrome, mains, has_chrome = None, {}, {}
    for slug, b in bodies.items():
        root = parse(b)
        mains_nodes = [n for n in root.children if n.tag == 'main']
        if len(mains_nodes) != 1:
            raise SystemExit('%s: expected exactly one <main>' % slug)
        mn = mains_nodes[0]
        parts = {}
        for name, tag, cls in CHROME:
            n = [x for x in root.children if x.tag == tag and x.has(cls)]
            parts[name] = b[n[0].start:n[0].end] if n else None
        foot = [x for x in root.children if x.tag == 'footer']
        parts['footer'] = b[foot[0].start:foot[0].end] if foot else None
        if None not in parts.values() and chrome is None:
            chrome = parts
        prefix = ''.join(parts[n] for n, _, _ in CHROME) if None not in parts.values() else None
        ok = chrome is not None and prefix == ''.join(chrome[n] for n, _, _ in CHROME) \
            and b == prefix + b[mn.start:mn.end] + chrome['footer']
        has_chrome[slug] = ok
        mains[slug] = (b[mn.start:mn.end], root, mn) if ok else (b, root, mn)

    for name, text_ in chrome.items():
        write(os.path.join(ROOT, 'templates', 'partials', name + '.php'),
              nowdoc_file(text_, 'VGNYC_PARTIAL', '// Shared site chrome. Generated by scripts/extract.py from the original page markup.'))

    # 2. service candidates
    candidates, reasons = {}, {}
    for slug, (m, root, mn) in mains.items():
        if all_raw or slug in overrides or not has_chrome[slug]:
            reasons[slug] = 'all-raw run' if all_raw else 'listed in scripts/raw-overrides.txt' if slug in overrides else 'non-standard chrome'
            continue
        try:
            candidates[slug] = extract_service(m, parse(m).children[0])
        except Fail as e:
            reasons[slug] = 'layout: %s' % e
        except (IndexError, KeyError, ValueError, AttributeError) as e:
            reasons[slug] = 'layout: %s' % e
    results = roundtrip(candidates, {s: mains[s][0] for s in candidates}) if candidates else {}

    # 3. write content + pages
    os.makedirs(os.path.join(ROOT, 'content', 'services'), exist_ok=True)
    os.makedirs(os.path.join(ROOT, 'pages'), exist_ok=True)
    assign = {}
    for slug in bodies:
        page_file = os.path.join(ROOT, 'pages', slug + '.php')
        json_file = os.path.join(ROOT, 'content', 'services', slug + '.json')
        if results.get(slug) == 'ok':
            write(json_file, json.dumps(candidates[slug], ensure_ascii=False, indent=2) + '\n')
            if os.path.exists(page_file):
                os.remove(page_file)  # stale output of an earlier extractor run
            assign[slug] = ('service', 'content/services/%s.json' % slug)
        else:
            if slug in results:
                reasons[slug] = 'round-trip: ' + results[slug][:300]
            write(page_file, nowdoc_file(mains[slug][0], 'VGNYC_PAGE', '// Page body (<main> only). The shared header/footer come from templates/partials/.'))
            if os.path.exists(json_file):
                os.remove(json_file)  # stale output of an earlier extractor run
            assign[slug] = ('raw', 'pages/%s.php' % slug)
    update_manifest(assign)

    # chrome:false for pages whose body does not use the shared chrome
    no_chrome = [s for s, ok in has_chrome.items() if not ok]
    if no_chrome:
        print('WARNING: pages without the shared chrome (need "chrome": false):', ', '.join(no_chrome))

    svc = sorted(s for s, (t, _) in assign.items() if t == 'service')
    raw = sorted(s for s, (t, _) in assign.items() if t == 'raw')
    print('service (%d): %s' % (len(svc), ' '.join(svc)))
    print('raw (%d):' % len(raw))
    for s in raw:
        print('  %-36s %s' % (s, reasons.get(s, '')))


if __name__ == '__main__':
    main()
