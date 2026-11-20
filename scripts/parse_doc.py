#!/usr/bin/env python3
"""Parse a Google Docs HTML export (Phase 2 content pipeline). Dev-only, stdlib only.

Usage:
    python3 -I scripts/parse_doc.py <doc.html|doc.zip> [--out inbox/reports]
    (a doc may hold several pages; each starts with a title line followed by "URL:")

Writes {out}/{slug}-parsed.json with:
    url, route, slug, meta_title, meta_description, h1,
    sections: [{n, label, blocks: [{tag, html, text}]}]   (writer's numbered sections)
    images:   [{section, prompt}]                          ("Image AI Image:" notes)
    links:    [{text, href, section}]                      (Google redirects unwrapped)

Cleaning: drops <style>, inline styles, Google c1/c2 classes, empty spans,
comments and &nbsp; padding; keeps headings, paragraphs, bold/italic (from the
doc's CSS classes), lists, tables and links. Copy is never reworded.
"""
import html
import json
import os
import re
import sys
import tempfile
import urllib.parse
import zipfile
from html.parser import HTMLParser

KEEP = {'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'ul', 'ol', 'li', 'table', 'thead', 'tbody', 'tr', 'td', 'th', 'a', 'br', 'strong', 'em'}
BLOCK = {'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'li', 'td', 'th'}
SITE = re.compile(r'^https?://(?:www\.)?visiondoesit\.com(/nyc/[^\s#?]*)?', re.I)


def unwrap(href):
    href = html.unescape(href)
    p = urllib.parse.urlparse(href)
    if p.netloc.endswith('google.com') and p.path == '/url':
        href = urllib.parse.parse_qs(p.query).get('q', [href])[0]
    return urllib.parse.unquote(href)


def style_classes(src):
    """Map Google's generated classes to bold / italic from the doc stylesheet."""
    css = ''.join(re.findall(r'<style[^>]*>(.*?)</style>', src, re.S))
    out = {}
    for name, body in re.findall(r'\.(c\d+)\{([^}]*)\}', css):
        out[name] = {'b': 'font-weight:700' in body, 'i': 'font-style:italic' in body}
    return out


class Cleaner(HTMLParser):
    def __init__(self, styles):
        super().__init__(convert_charrefs=True)
        self.styles, self.out, self.stack, self.skip = styles, [], [], 0

    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        if tag in ('style', 'script', 'head', 'title'):
            self.skip += 1
            return
        if self.skip:
            return
        closers = []
        if tag == 'span':
            cls = (a.get('class') or '').split()
            b = any(self.styles.get(c, {}).get('b') for c in cls)
            i = any(self.styles.get(c, {}).get('i') for c in cls)
            if b:
                self.out.append('<strong>'); closers.insert(0, '</strong>')
            if i:
                self.out.append('<em>'); closers.insert(0, '</em>')
            self.stack.append(('span', closers))
            return
        if tag in KEEP:
            if tag == 'a':
                href = unwrap(a.get('href', ''))
                if not href:
                    self.stack.append(('a', []))
                    return
                self.out.append('<a href="%s">' % html.escape(href, quote=True))
            elif tag == 'br':
                self.out.append('<br>')
                return
            else:
                self.out.append('<%s>' % tag)
            self.stack.append((tag, ['</%s>' % tag]))
        else:
            self.stack.append((tag, []))

    def handle_startendtag(self, tag, attrs):
        if tag == 'br' and not self.skip:
            self.out.append('<br>')

    def handle_endtag(self, tag):
        if tag in ('style', 'script', 'head', 'title'):
            self.skip = max(0, self.skip - 1)
            return
        if self.skip or tag == 'br':
            return
        while self.stack:
            t, closers = self.stack.pop()
            self.out.extend(closers)
            if t == tag:
                break

    def handle_data(self, data):
        if not self.skip:
            self.out.append(html.escape(data.replace('\xa0', ' '), quote=False))


def tidy(h):
    h = re.sub(r'<(strong|em)>(\s*)</\1>', r'\2', h)
    h = re.sub(r'</strong>(\s*)<strong>', r'\1', h)
    h = re.sub(r'</em>(\s*)<em>', r'\1', h)
    h = re.sub(r'<a href="[^"]*">\s*</a>', ' ', h)      # Google's empty &nbsp; anchors
    h = re.sub(r'<(strong|em)>(\s+)', r'\2<\1>', h)
    h = re.sub(r'(\s+)</(strong|em)>', r'</\2>\1', h)
    h = re.sub(r'[ \t]+', ' ', h)
    h = re.sub(r'\s+(</(?:p|li|h\d|td|th)>)', r'\1', h)
    h = re.sub(r'(<(?:p|li|h\d|td|th)>)\s+', r'\1', h)
    return h


def text_of(h):
    return re.sub(r'\s+', ' ', html.unescape(re.sub(r'<[^>]+>', '', h))).strip()


def table_html(t):
    """Rebuild a doc table as plain <table><tr><td> markup (first row becomes the header)."""
    rows = []
    for tr in re.findall(r'<tr>(.*?)</tr>', t, re.S):
        cells = [re.sub(r'</?p>', '', c).strip() for c in re.findall(r'<t[dh]>(.*?)</t[dh]>', tr, re.S)]
        rows.append(cells)
    if not rows:
        return ''
    head = '<tr>' + ''.join('<th>%s</th>' % re.sub(r'</?strong>', '', c) for c in rows[0]) + '</tr>'
    body = ''.join('<tr>' + ''.join('<td>%s</td>' % c for c in r) + '</tr>' for r in rows[1:])
    return '<table><thead>%s</thead><tbody>%s</tbody></table>' % (head, body)


def blocks(clean):
    out = []
    for m in re.finditer(r'<table>(.*?)</table>|<(h[1-6]|p|li)>(.*?)</\2>', clean, re.S):
        if m.group(1) is not None:
            h = table_html(m.group(1))
            out.append({'tag': 'table', 'html': h, 'text': text_of(h.replace('</t', ' </t'))})
            continue
        t = text_of(m.group(3))
        if t:
            out.append({'tag': m.group(2), 'html': m.group(3).strip(), 'text': t})
    return out


SECTION = re.compile(r'^(\d+)\.\s*(.*)$')


def parse(path):
    if path.endswith('.zip'):
        tmp = tempfile.mkdtemp(prefix='vgnyc-doc-')
        with zipfile.ZipFile(path) as z:
            name = [n for n in z.namelist() if n.endswith('.html')][0]
            z.extract(name, tmp)
        path = os.path.join(tmp, name)
    src = open(path, encoding='utf-8').read()
    body = re.search(r'<body[^>]*>(.*)</body>', src, re.S)
    c = Cleaner(style_classes(src))
    c.feed(body.group(1) if body else src)
    clean = tidy(''.join(c.out))
    bl = blocks(clean)

    # One doc can hold several pages: each starts with a title line followed by "URL:".
    starts = [i for i, b in enumerate(bl) if b['text'].lower().startswith('url:')]
    starts = [i - 1 if i > 0 and not SECTION.match(bl[i - 1]['text']) else i for i in starts] or [0]
    starts[0] = 0
    bounds = list(zip(starts, starts[1:] + [len(bl)]))
    return [page(bl[a:b], os.path.basename(path)) for a, b in bounds]


def page(bl, source):
    clean = ''.join('<%s>%s</%s>' % (b['tag'], b['html'], b['tag']) for b in bl)
    doc = {'source': source, 'url': None, 'route': None, 'slug': None, 'meta_title': None,
           'meta_description': None, 'h1': None, 'title_line': None, 'sections': [], 'images': [], 'links': []}
    cur = {'n': 0, 'label': '(preamble)', 'blocks': []}
    doc['sections'].append(cur)
    for b in bl:
        t = b['text']
        m = SECTION.match(t)
        if m and b['tag'] in ('h1', 'h2', 'h3', 'p') and len(t) < 120:
            cur = {'n': int(m.group(1)), 'label': m.group(2).strip(), 'blocks': []}
            doc['sections'].append(cur)
            continue
        low = t.lower()
        if low.startswith('url:'):
            doc['url'] = t.split(':', 1)[1].strip()
            continue
        if low.startswith('meta title:'):
            doc['meta_title'] = t.split(':', 1)[1].strip()
            continue
        if low.startswith('meta description:'):
            doc['meta_description'] = t.split(':', 1)[1].strip()
            continue
        if low.startswith('h1:') and not doc['h1']:
            doc['h1'] = t.split(':', 1)[1].strip()
        if re.match(r'^image\b', low):
            doc['images'].append({'section': cur['n'], 'prompt': re.sub(r'^Image\s*(?:AI Image)?\s*:\s*', '', t)})
        if cur['n'] == 0 and doc['title_line'] is None and not low.startswith(('url', 'meta')):
            doc['title_line'] = t
        cur['blocks'].append(b)
        for href, txt in re.findall(r'<a href="([^"]*)">(.*?)</a>', b['html'], re.S):
            doc['links'].append({'text': text_of(txt), 'href': html.unescape(href), 'section': cur['n']})

    if doc['url']:
        m = SITE.match(doc['url'])
        if m and m.group(1):
            doc['route'] = m.group(1) if m.group(1).endswith('/') else m.group(1) + '/'
            s = doc['route'].strip('/').split('/', 1)
            doc['slug'] = (s[1] if len(s) > 1 else 'home').replace('/', '--')
    doc['clean_html'] = clean
    return doc


def main():
    args = sys.argv[1:]
    out = args[args.index('--out') + 1] if '--out' in args else os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), 'inbox', 'reports')
    for path in [a for a in args if not a.startswith('--') and a != out]:
        for n, doc in enumerate(parse(path)):
            slug = doc['slug'] or '%s-%d' % (os.path.splitext(os.path.basename(path))[0], n + 1)
            os.makedirs(out, exist_ok=True)
            with open(os.path.join(out, slug + '-parsed.json'), 'w', encoding='utf-8') as f:
                json.dump(doc, f, ensure_ascii=False, indent=2)
            print('%s -> %s (%d sections, %d images, %d links)' % (os.path.basename(path), doc['route'], len(doc['sections']) - 1, len(doc['images']), len(doc['links'])))


if __name__ == '__main__':
    main()
