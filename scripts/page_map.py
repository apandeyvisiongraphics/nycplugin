#!/usr/bin/env python3
"""Write inbox/reports/{slug}-map.md from {slug}-parsed.json (Phase 2, Step 2). Dev-only, stdlib only.

Usage:
    python3 -I scripts/page_map.py <slug> [<slug> ...] [--issues issues.json] [--alts alts.json] [--no-net]

Section types are guessed from the writer's labels (see guess_type) and every
guess is printed in the map for review. Image slots follow
nyc-{page-slug}-{section}-{nn}. Links: internal /nyc/ links are checked against
data/manifest.php; others are fetched (redirects followed).
"""
import json
import os
import re
import subprocess
import sys
import urllib.request

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
REPORTS = os.path.join(ROOT, 'inbox', 'reports')
EXISTING = {'hero', 'faq', 'cta-band', 'inquiry'}  # section types the batch can map onto existing markup/CSS


def manifest():
    php = "define('ABSPATH', 1); echo json_encode(require %s);" % json.dumps(os.path.join(ROOT, 'data', 'manifest.php'))
    return json.loads(subprocess.check_output(['php', '-r', php]))


def guess_type(label, blocks):
    l = label.lower()
    if 'hero' in l:
        return 'hero'
    if any(k in l for k in ('proof', 'trust')):
        return 'trust-bar'
    if any(k in l for k in ('question', 'faq', 'answers')):
        return 'faq'
    if any(k in l for k in ('testimonial', 'clients say', 'reviews')):
        return 'testimonials'
    if any(k in l for k in ('how it works', 'happens next', 'process', 'steps')):
        return 'process-steps'
    if any(k in l for k in ('form', 'lead')) or any(b['text'].lower().startswith('form heading') for b in blocks):
        return 'inquiry'
    if 'confirmation' in l:
        return 'form-config'
    h3 = [b for b in blocks if b['tag'] == 'h3']
    imgs = [b for b in blocks if b['text'].lower().startswith('image')]
    if len(imgs) > 1 and h3:
        return 'card-grid'
    if h3:
        return 'features-grid'
    if imgs:
        return 'image-text'
    return 'rich-text'


def section_key(label, typ, n):
    l = re.sub(r'^h2:\s*', '', label, flags=re.I).lower()
    l = re.sub(r"[’']", '', l)
    words = [w for w in re.findall(r'[a-z0-9]+', l) if w not in ('the', 'and', 'in', 'nyc', 'of', 'a', 'h2', 'what', 'whats')]
    return typ if typ in ('hero', 'process-steps') else ('-'.join(words[:2]) or 'section-%d' % n)


def ratio(prompt):
    m = re.search(r'\b(\d+):(\d+)\b', prompt)
    return '%s:%s' % m.groups() if m else '3:2'


def check(url, cache={}):
    if url in cache:
        return cache[url]
    try:
        req = urllib.request.Request(url, headers={'User-Agent': 'vgnyc-link-check'})
        with urllib.request.urlopen(req, timeout=20) as r:
            hops = '' if r.geturl() == url else ' (redirects to %s)' % r.geturl()
            cache[url] = '%d%s' % (r.status, hops)
    except urllib.error.HTTPError as e:
        cache[url] = str(e.code)
    except Exception as e:  # noqa: BLE001
        cache[url] = 'ERR %s' % e.__class__.__name__
    return cache[url]


def build(slug, routes, net):
    d = json.load(open(os.path.join(REPORTS, slug + '-parsed.json'), encoding='utf-8'))
    route = d['route']
    status = 'REPLACEMENT' if route in routes else 'NEW'
    L = ['# Page map: %s' % route, '',
         '| | |', '|---|---|',
         '| Route | `%s` |' % route,
         '| Status | **%s**%s |' % (status, ' (current page stays live; draft previews at `?vgnyc_preview=1`)' if status == 'REPLACEMENT' else ' (admin-only draft until published)'),
         '| Source | `inbox/docs/%s` |' % d['source'],
         '| Meta title | %s (**%d** chars%s) |' % (d['meta_title'], len(d['meta_title'] or ''), '' if len(d['meta_title'] or '') <= 60 else ' ⚠️ over 60'),
         '| Meta description | %s (**%d** chars%s) |' % (d['meta_description'], len(d['meta_description'] or ''), '' if len(d['meta_description'] or '') <= 160 else ' ⚠️ over 160'),
         '| H1 | %s |' % d['h1'], '']
    L += ['## Sections in order', '']
    images, counters = [], {}
    built = {}
    rpath = os.path.join(ROOT, 'content', 'images.json')
    registry = json.load(open(rpath, encoding='utf-8')) if os.path.exists(rpath) else {}
    cpath = os.path.join(ROOT, 'content', 'pages', slug + '.json')
    if os.path.exists(cpath):  # section types chosen by build_pages.py
        for s_ in json.load(open(cpath, encoding='utf-8'))['sections']:
            built.setdefault(s_['doc_section'], []).append(s_['type'])
    for sec in d['sections'][1:]:
        typ = ' + '.join(built[sec['n']]) if sec['n'] in built else guess_type(sec['label'], sec['blocks'])
        tag = 'existing markup/CSS' if typ in EXISTING else ('not a page section' if typ == 'form-config' or (built and sec['n'] not in built) else 'new section template')
        L.append('### %d. %s → `%s` (%s)' % (sec['n'], sec['label'] or '(no label)', typ, tag))
        L.append('')
        key = section_key(sec['label'], typ, sec['n'])
        for b in sec['blocks']:
            t = b['text']
            if t.lower().startswith('image'):
                counters[key] = counters.get(key, 0) + 1
                slot = 'nyc-%s-%s-%02d' % (slug, key, counters[key])
                r = ratio(t)
                full = typ == 'hero' or '16:9' in r
                images.append({'slot': slot, 'section': '%d. %s' % (sec['n'], sec['label']), 'ratio': r,
                               'min_width': 1600 if full else 960, 'sizes': [480, 960, 1600] if full else [480, 960],
                               'alt': getattr(build, 'alts', {}).get(slot, ''),
                               'prompt': re.sub(r'^Image\s*(?:AI Image)?\s*:\s*', '', t)})
                L.append('- 🖼 **Image** (%s)%s' % (r, '' if registry else ' `%s`' % slot))
            else:
                L.append('- `%s` %s' % (b['tag'], b['html']))
        L.append('')
    if registry:  # real slot names from content/images.json (written by build_pages.py)
        images = [dict(m, slot=k, section=m.get('section', '')) for k, m in registry.items() if m.get('page') == route]
    L += ['## Image slots', '', '| Slot | File name (designer) | Section | Ratio | Min width | Sizes | Alt (proposed, needs OK) | Writer prompt |', '|---|---|---|---|---|---|---|---|']
    for im in images:
        L.append('| `%s` | `%s.jpg/.png/.webp` | %s | %s | %d | %s | %s | %s |' % (im['slot'], im['slot'], im['section'], im['ratio'], im['min_width'], '/'.join(map(str, im['sizes'])), im.get('alt') or ('_real photo: alt with caption_' if 'projects' in im['slot'] else '_to draft_'), im['prompt'].replace('|', '/')))
    if not images:
        L.append('| – | – | – | – | – | – | – | No images requested |')
    L += ['', '## Links', '', '| Anchor text | URL in doc | Will render as | Type | Status |', '|---|---|---|---|---|']
    for ln in d['links']:
        m = re.match(r'^https?://(?:www\.)?visiondoesit\.com(/nyc/[^?#]*)', ln['href'], re.I)
        if m:
            path = m.group(1) if m.group(1).endswith('/') else m.group(1) + '/'
            st = 'route exists ✅' if path in routes else ('this page' if path == route else 'route missing ❌')
            live = check(ln['href']) if net else 'not checked'
            L.append('| %s | %s | `%s` | internal | %s; live %s |' % (ln['text'], ln['href'], path, st, live))
        else:
            L.append('| %s | %s | (unchanged) | external | %s |' % (ln['text'], ln['href'], check(ln['href']) if net else 'not checked'))
    if not d['links']:
        L.append('| – | – | – | – | No links in doc |')
    return d, '\n'.join(L) + '\n', images


def main():
    args = sys.argv[1:]
    net = '--no-net' not in args
    issues, alts = {}, {}
    if '--alts' in args:
        alts = json.load(open(args[args.index('--alts') + 1], encoding='utf-8'))
    if '--issues' in args:
        issues = json.load(open(args[args.index('--issues') + 1], encoding='utf-8'))
    slugs = [a for a in args if not a.startswith('--') and not a.endswith('.json')]
    build.alts = alts
    routes = set(manifest())
    for slug in slugs:
        d, md, images = build(slug, routes, net)
        md += '\n## Issues\n\n' + ('\n'.join('- ' + i for i in issues.get(slug, [])) or '- None found.') + '\n'
        with open(os.path.join(REPORTS, slug + '-map.md'), 'w', encoding='utf-8') as f:
            f.write(md)
        print('%s: %d image slots -> inbox/reports/%s-map.md' % (slug, len(images), slug))


if __name__ == '__main__':
    main()
