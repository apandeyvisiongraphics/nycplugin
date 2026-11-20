#!/usr/bin/env python3
"""Build a blog post (template "blog") from a Google Docs HTML export. Dev-only, stdlib only.

Usage:
    python3 -I scripts/build_blog.py <doc.html> [--date YYYY-MM-DD] [--alts alts.json] [--eyebrow Resources] [--dry-run]

The doc layout this expects (same as the writer's blog docs):
    title line, "URL:", "Meta title:", "Meta description:", optional hero image,
    intro paragraphs, then H2 sections (each may start with an image), an "FAQ" H2 whose
    questions are H3s, and a last H2 that becomes the closing form section.

Writes:
    content/blog/{slug}.json             the post (sections: article*, faq, inquiry)
    content/images.json                  one slot per doc image (alt from --alts, keyed by image file name)
    assets/images/{slot}-{w}.webp         when `cwebp` is installed; otherwise the originals are
                                          copied to images-inbox/ for the local site to convert
Copy is never reworded. Mechanical fixes only: Google redirect links are unwrapped,
https://www.visiondoesit.com/nyc/... becomes /nyc/..., and /nyc/contact/ links become #inquiry
(the post has its own form). Add or update the route in data/manifest.php yourself
(template "blog", content "content/blog/{slug}.json"); the script prints the entry.
"""
import json
import os
import re
import shutil
import struct
import subprocess
import sys
import tempfile

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
sys.path.insert(0, os.path.join(ROOT, 'scripts'))
import parse_doc  # noqa: E402

IMG = re.compile(r'\[\[IMG:([^\]]+)\]\]')
QUOTE = '[[QUOTE]]'
RATIOS = ['16:9', '16:10', '3:2', '4:3', '1:1', '4:5', '2:3']


def slugify(t, n=48):
    """URL-safe slug, cut at a word boundary so it stays at most n characters."""
    s = re.sub(r'[^a-z0-9]+', '-', t.lower()).strip('-')
    if len(s) > n:
        s = s[:n + 1].rsplit('-', 1)[0]
    return s


def jpeg_size(path):
    """(width, height) of a JPEG or PNG without third-party libraries."""
    with open(path, 'rb') as f:
        data = f.read()
    if data[:8] == b'\x89PNG\r\n\x1a\n':
        return struct.unpack('>II', data[16:24])
    i = 2
    while i < len(data):
        if data[i] != 0xFF:
            i += 1
            continue
        marker = data[i + 1]
        if marker in (0xC0, 0xC1, 0xC2):
            h, w = struct.unpack('>HH', data[i + 5:i + 9])
            return w, h
        i += 2 + struct.unpack('>H', data[i + 2:i + 4])[0]
    return 0, 0


def nearest_ratio(w, h):
    r = w / h if h else 1.5
    return min(RATIOS, key=lambda s: abs(r - int(s.split(':')[0]) / int(s.split(':')[1])))


def mark_source(src):
    """Keep the doc's images and indented (quoted) paragraphs as text markers the parser preserves."""
    css = ''.join(re.findall(r'<style[^>]*>(.*?)</style>', src, re.S))
    indented = {n for n, b in re.findall(r'\.(c\d+)\{([^}]*)\}', css) if re.search(r'margin-left:\s*[1-9]', b) and re.search(r'margin-right:\s*[1-9]', b)}

    def quote(m):
        classes = set(m.group(1).split())
        return m.group(0) + (QUOTE if classes & indented else '')
    src = re.sub(r'<p class="([^"]*)"[^>]*>', quote, src)
    return re.sub(r'<img\b[^>]*\bsrc="([^"]+)"[^>]*>', lambda m: '[[IMG:%s]]' % os.path.basename(m.group(1)), src)


def fix_links(h):
    h = re.sub(r'href="https?://(?:www\.)?visiondoesit\.com(/nyc/[^"]*)"', r'href="\1"', h)
    h = re.sub(r'href="/nyc/contact/(?:#[^"]*)?"', 'href="#inquiry"', h)
    h = re.sub(r'<strong>(<a [^>]*>.*?</a>)</strong>', r'\1', h)
    return h


def plain(h):
    return re.sub(r'</?(?:strong|em)>', '', h).strip()


def body_html(blocks):
    """Paragraphs, H3s, lists (consecutive <li>) and quoted paragraphs as clean HTML."""
    out, lis = [], []
    for b in blocks:
        if b['tag'] == 'li':
            lis.append(fix_links(b['html']))
            continue
        if lis:
            out.append('<ul>' + ''.join('<li>%s</li>' % x for x in lis) + '</ul>')
            lis = []
        h = fix_links(b['html'])
        if b['tag'] in ('h3', 'h4'):
            out.append('<h3>%s</h3>' % plain(h))
        elif h.startswith(QUOTE):
            out.append('<blockquote><p>%s</p></blockquote>' % h[len(QUOTE):].strip())
        elif b['tag'] == 'table':
            out.append(h)
        else:
            out.append('<p>%s</p>' % h)
    if lis:
        out.append('<ul>' + ''.join('<li>%s</li>' % x for x in lis) + '</ul>')
    return ''.join(out).replace(QUOTE, '')


def is_checklist(blocks):
    """Two or more bold-only lines, each followed by a list: the writer's copy-this checklist."""
    heads = [i for i, b in enumerate(blocks) if b['tag'] == 'p' and re.fullmatch(r'<strong>[^<]+</strong>', b['html'])]
    return len(heads) >= 2 and all(i + 1 < len(blocks) and blocks[i + 1]['tag'] == 'li' for i in heads)


def main():
    args = sys.argv[1:]

    def opt(name, default=None):
        return args[args.index(name) + 1] if name in args else default
    flags = {opt('--date'), opt('--alts'), opt('--eyebrow')}
    paths = [a for a in args if not a.startswith('--') and a not in flags]
    if not paths:
        sys.exit(__doc__)
    doc_path = os.path.abspath(paths[0])
    img_dir = os.path.join(os.path.dirname(doc_path), 'images')
    alts = json.load(open(opt('--alts'), encoding='utf-8')) if opt('--alts') else {}
    dry = '--dry-run' in args

    src = open(doc_path, encoding='utf-8').read()
    with tempfile.NamedTemporaryFile('w', suffix='.html', delete=False, encoding='utf-8') as t:
        t.write(mark_source(src))
    try:
        doc = parse_doc.parse(t.name)[0]
    finally:
        os.unlink(t.name)
    if not doc['route']:
        sys.exit('No "URL: https://www.visiondoesit.com/nyc/..." line found in the doc.')
    blocks = [b for s in doc['sections'] for b in s['blocks']]
    route, slug = doc['route'], doc['slug']
    short = route.strip('/').split('/')[-1]

    # Title line (first block), then skip URL / meta lines.
    title = parse_doc.text_of(blocks[0]['html'])
    rest = [b for b in blocks[1:] if not re.match(r'^(url|meta title|meta description)\s*:', b['text'], re.I)]

    # An H1 in the doc is the post title; what follows it (up to the first H2) is the intro.
    h1s = [b for b in rest if b['tag'] == 'h1']
    if h1s:
        title = plain(h1s[0]['html'])
        rest = [b for b in rest if b is not h1s[0]]

    # Split on H2.
    parts, cur = [], {'h2': None, 'blocks': []}
    for b in rest:
        if b['tag'] in ('h1', 'h2'):
            parts.append(cur)
            cur = {'h2': plain(b['html']), 'blocks': []}
        else:
            cur['blocks'].append(b)
    parts.append(cur)

    images = {}

    def take_image(part, key):
        """Pull the section's image marker out of its blocks; returns the slot id or None."""
        for i, b in enumerate(part['blocks']):
            m = IMG.search(b['html'])
            if m:
                rest_html = IMG.sub('', b['html']).strip()
                if rest_html:
                    b['html'] = rest_html
                else:
                    del part['blocks'][i]
                slot = 'nyc-%s-%s-01' % (short, key)
                images[slot] = m.group(1)
                return slot
        return None

    intro = parts[0]
    post = {'eyebrow': opt('--eyebrow', 'Resources'), 'h1': title, 'date': opt('--date', ''), 'author': 'Vision Graphics New York'}
    hero = take_image(intro, 'hero')
    post['lead_html'] = body_html(intro['blocks'])
    if hero:
        post['image'] = hero
    sections, used = [], set()
    for n, part in enumerate(parts[1:], 1):
        last = n == len(parts) - 1
        if part['h2'].strip().lower() in ('faq', 'faqs', 'frequently asked questions'):
            items, q = [], None
            for b in part['blocks']:
                if b['tag'] in ('h3', 'h4'):
                    q = {'q': parse_doc.text_of(b['html']), 'a_html': ''}
                    items.append(q)
                elif q is not None:
                    q['a_html'] += '<p>%s</p>' % fix_links(b['html'])
            sections.append({'type': 'faq', 'h2': part['h2'], 'items': items})
            continue
        if last:
            sections.append({'type': 'inquiry', 'h2': part['h2'], 'intro_html': body_html(part['blocks'])})
            continue
        key = slugify(part['h2'], 26)
        while key in used:
            key += '-x'
        used.add(key)
        s = {'type': 'article', 'id': slugify(part['h2']), 'h2': part['h2']}
        img = take_image(part, key)
        if img:
            s['image'] = img
        # Further images inside the section (e.g. under an H3) stay where the writer put them,
        # as [[image:slot]] tokens that templates/blog-post.php renders as figures.
        sub = key
        for b in part['blocks']:
            if b['tag'] in ('h3', 'h4'):
                sub = slugify(parse_doc.text_of(b['html']), 26)
            m = IMG.search(b['html'])
            if m:
                slot = 'nyc-%s-%s-01' % (short, sub)
                while slot in images:
                    slot = slot[:-3] + '-%02d' % (int(slot[-2:]) + 1)
                images[slot] = m.group(1)
                b['html'] = IMG.sub('[[image:%s]]' % slot, b['html'])
        if is_checklist(part['blocks']):
            s['variant'] = 'checklist'
        s['html'] = body_html(part['blocks'])
        sections.append(s)
    post['sections'] = sections
    # Button under the table of contents: the writer's own closing link text (e.g. "Request a sign quote").
    closing = [s for s in sections if s['type'] == 'inquiry']
    label = re.findall(r'<a href="#inquiry">(.*?)</a>', closing[0].get('intro_html', '')) if closing else []
    if label:
        post['aside_cta'] = {'label': parse_doc.text_of(label[-1]), 'href': '#inquiry'}

    out_file = os.path.join(ROOT, 'content', 'blog', slug + '.json')
    print('Post: %s -> %s (%d sections, %d images)' % (route, os.path.relpath(out_file, ROOT), len(sections), len(images)))
    if dry:
        print(json.dumps(post, ensure_ascii=False, indent=2))
        return
    os.makedirs(os.path.dirname(out_file), exist_ok=True)
    with open(out_file, 'w', encoding='utf-8') as f:
        json.dump(post, f, ensure_ascii=False, indent=2)
        f.write('\n')

    # Image slots + files.
    reg_file = os.path.join(ROOT, 'content', 'images.json')
    reg = json.load(open(reg_file, encoding='utf-8'))
    cwebp = shutil.which('cwebp')
    missing_alt = []
    for slot, name in images.items():
        path = os.path.join(img_dir, name)
        w, h = jpeg_size(path) if os.path.isfile(path) else (0, 0)
        hero_slot = slot.endswith('-hero-01')
        sizes = [480, 960, 1600] if hero_slot else [480, 960]
        alt = alts.get(name, reg.get(slot, {}).get('alt', ''))
        if not alt:
            missing_alt.append(name)
        reg[slot] = {'page': route, 'section': slot[len('nyc-%s-' % short):-3], 'ratio': nearest_ratio(w, h) if w else '16:10',
                     'min_width': 1600 if hero_slot else 960, 'sizes': sizes, 'alt': alt, 'prompt': '',
                     'notes': 'From the blog doc: images/%s' % name, 'caption': ''}
        if not os.path.isfile(path):
            print('  ! image file not found: %s' % path)
            continue
        if cwebp:
            for size in sizes:
                dest = os.path.join(ROOT, 'assets', 'images', '%s-%d.webp' % (slot, size))
                subprocess.run([cwebp, '-quiet', '-q', '82', '-m', '6', '-resize', str(min(size, w)), '0', path, '-o', dest], check=True)
        else:
            shutil.copyfile(path, os.path.join(ROOT, 'images-inbox', slot + os.path.splitext(name)[1].lower()))
    with open(reg_file, 'w', encoding='utf-8') as f:
        json.dump(reg, f, ensure_ascii=False, indent=2)
        f.write('\n')
    print('Images: %d slots registered%s' % (len(images), ' and converted to WebP' if cwebp else ' (copied to images-inbox/ for the local site to convert)'))
    if missing_alt:
        print('  ! no alt text for: %s (pass --alts alts.json)' % ', '.join(missing_alt))
    print('\nManifest entry (data/manifest.php):')
    print(json.dumps({route: {'title': doc['meta_title'], 'description': doc['meta_description'], 'file': slug + '.php',
                              'template': 'blog', 'content': 'content/blog/%s.json' % slug, 'service': False,
                              'image': hero or '', 'status': 'draft'}}, ensure_ascii=False, indent=2))


if __name__ == '__main__':
    main()
