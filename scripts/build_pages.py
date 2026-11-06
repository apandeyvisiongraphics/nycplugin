#!/usr/bin/env python3
"""Build landing-page JSON from parsed docs (Phase 2, Step 5). Dev-only, stdlib only.

Usage:
    python3 -I scripts/build_pages.py <slug> [<slug> ...] [--config inbox/reports/batch-XXXX-config.json]

Reads inbox/reports/{slug}-parsed.json (from parse_doc.py) and writes:
  - content/pages/{slug}.json      ordered list of sections for templates/landing-page.php
  - content/images.json            image slot registry (existing slots are never renamed)
  - data/manifest.php              NEW route: "status": "draft"; existing route: "draft_*" keys

Copy is taken verbatim from the doc. Only these mechanical changes are made:
  - writer labels ("Eyebrow:", "H1:", "Intro:", "Header:", ...) and notes in (parentheses) are dropped
  - absolute https://www.visiondoesit.com/nyc/... links become /nyc/... paths
  - links to /nyc/contact/ become #inquiry (every page here has its own form section)
  - <strong> that only wraps a link (Google's link styling) is removed
  - links listed in the batch config ("link_additions") are added to the given text
The batch config can also set image slot names per doc section ("slot_keys") and alt text ("alts").
Form-field lists, button labels and confirmation messages belong to Contact Form 7 and are skipped.
"""
import html
import json
import os
import re
import subprocess
import sys

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
REPORTS = os.path.join(ROOT, 'inbox', 'reports')
STOP = {'the', 'and', 'in', 'nyc', 'of', 'a', 'for', 'to', 'what', 'whats', 'how', 'who', 'we', 'you', 'your', 'our',
        'new', 'york', 'city', 'it', 'is', 'h2', 'an', 'on', 'every', 'or'}
NOTE = re.compile(r'^\(.*\)\.?$', re.S)
LABEL = re.compile(r'^\s*(?:<(?:strong|em)>)+\s*([A-Za-z][A-Za-z0-9 ]{0,40}?):\s*(?:</(?:strong|em)>)+\s*', re.S)


# ------------------------------------------------------------------ helpers
def text_of(h):
    return re.sub(r'\s+', ' ', html.unescape(re.sub(r'<[^>]+>', '', h))).strip()


def label_of(b):
    m = LABEL.match(b['html'])
    return m.group(1).strip().lower() if m else None


def strip_label(h):
    return LABEL.sub('', h, count=1).strip()


def is_image(b):
    return bool(re.match(r'^image\b', b['text'], re.I))


def is_note(b):
    return bool(NOTE.match(b['text']))


class Ctx:
    def __init__(self, slug, route, config):
        self.slug, self.route, self.config = slug, route, config
        self.slots, self.counters, self.added = [], {}, []
        self.section_n = None

    def links(self, h):
        """Mechanical link fixes (see module docstring)."""
        def absolute(m):
            path = m.group(1) or '/'
            return 'href="%s"' % path
        h = re.sub(r'href="https?://(?:www\.)?visiondoesit\.com(/nyc/[^"#?]*)?"', absolute, h)
        h = re.sub(r'href="/nyc/contact/"', 'href="#inquiry"', h)
        h = re.sub(r'<strong>\s*(<a href="[^"]*">.*?</a>)\s*(\.?)\s*</strong>', r'\1\2', h, flags=re.S)
        for add in self.config.get('link_additions', {}).get(self.slug, []):
            pat = r'(?:<strong>)?%s(?:</strong>)?' % re.escape(html.escape(add['text'], quote=False))
            new, n = re.subn(pat, '<a href="%s">%s</a>' % (add['href'], html.escape(add['text'], quote=False)), h, count=1)
            if n and new != h:
                h = new
                self.added.append(add)
        return h

    def slot(self, key, b, kind='content', note=''):
        if kind not in ('hero', 'gallery'):
            key = self.config.get('slot_keys', {}).get(self.slug, {}).get(str(self.section_n), key)
        self.counters[key] = self.counters.get(key, 0) + 1
        sid = 'nyc-%s-%s-%02d' % (self.slug, key, self.counters[key])
        prompt = re.sub(r'^Image\s*(?:AI Image)?\s*:\s*', '', b['text']) if b else note
        m = re.search(r'\b(\d+):(\d+)\b', prompt)
        ratio = '%s:%s' % m.groups() if m else '3:2'
        full = kind == 'hero' or ratio == '16:9'
        self.slots.append({'id': sid, 'page': self.route, 'section': key, 'ratio': ratio,
                           'min_width': 1600 if full else 960, 'sizes': [480, 960, 1600] if full else [480, 960],
                           'alt': self.config.get('alts', {}).get(sid) or auto_alt(prompt, kind),
                           'prompt': prompt, 'notes': note})
        return sid


def auto_alt(prompt, kind):
    if kind == 'gallery':
        return ''
    s = re.sub(r'^Photorealistic[^,]*?photograph,\s*\d+:\d+[,.]?\s*', '', prompt)
    s = re.sub(r'^(of|showing)\s+', '', s)
    s = re.split(r'(?<=[a-z])\.\s', s)[0]
    s = re.split(r',\s(?:with|while|soft|warm|cool|shallow|late|early|no |in a|under|side light)', s)[0]
    s = s.strip(' .')
    return (s[:1].upper() + s[1:])[:140]


def key_for(label, typ):
    if typ in ('hero',):
        return 'hero'
    words = [w for w in re.findall(r'[a-z0-9]+', re.sub(r"[’']", '', label.lower())) if w not in STOP]
    return '-'.join(words[:2]) or typ


def ul(items):
    return '<ul>%s</ul>' % ''.join('<li>%s</li>' % i for i in items)


def html_of(blocks, ctx):
    """Render a run of doc blocks (p / li / table) as HTML, grouping consecutive list items."""
    out, lis = [], []
    for b in blocks:
        if b['tag'] == 'li':
            lis.append(ctx.links(b['html']))
            continue
        if lis:
            out.append(ul(lis)); lis = []
        if b['tag'] == 'table':
            out.append(ctx.links(b['html']))
        else:
            out.append('<p>%s</p>' % ctx.links(b['html']))
    if lis:
        out.append(ul(lis))
    return ''.join(out)


def heading(label):
    return text_of(re.sub(r'^\s*H2:\s*', '', label, flags=re.I))


# ------------------------------------------------------------------ section builders
def build_hero(sec, ctx):
    s = {'type': 'hero', 'eyebrow': None, 'h1': None, 'lead_html': '', 'ctas': [], 'image': None}
    lead = []
    for b in sec['blocks']:
        lab = label_of(b)
        if is_note(b):
            continue
        if is_image(b):
            s['image'] = ctx.slot('hero', b, 'hero')
        elif lab == 'eyebrow':
            s['eyebrow'] = text_of(strip_label(b['html']))
        elif lab == 'h1':
            s['h1'] = text_of(strip_label(b['html']))
        elif lab and lab.startswith('cta'):
            for i, (href, label) in enumerate(re.findall(r'<a href="([^"]*)">(.*?)</a>', ctx.links(b['html']))):
                s['ctas'].append({'label': text_of(label), 'href': html.unescape(href), 'style': 'primary' if i == 0 else 'secondary'})
        else:
            lead.append(dict(b, html=strip_label(b['html']) if lab == 'intro' else b['html']))
    s['lead_html'] = html_of(lead, ctx)
    return [prune(s)]


def build_trust(sec, ctx):
    items = []
    for b in sec['blocks']:
        if is_note(b):
            continue
        m = re.match(r'^<strong>(.*?)</strong>\s*\|\s*(.*)$', b['html'], re.S)
        if m:
            items.append({'title': text_of(m.group(1)), 'text': text_of(m.group(2)), 'icon': icon_for(text_of(b['html']))})
    return [{'type': 'trust-bar', 'items': items}]


def icon_for(t):
    t = t.lower()
    for words, icon in ((('year', 'legacy', 'history'), 'award'), (('licensed',), 'shield-check'), (('insurance',), 'clipboard-check'),
                        (('after-hours', 'day', 'reply'), 'clock'), (('borough', 'citywide'), 'map-pin'), (('contact', 'project lead'), 'user'),
                        (('photo',), 'camera'), (('made', 'design to install', 'shipped'), 'layers')):
        if any(w in t for w in words):
            return icon
    return 'check'


def build_faq(sec, ctx):
    items, cur = [], None
    for b in sec['blocks']:
        if is_note(b):
            continue
        whole_bold = re.fullmatch(r'<strong>(.*?)</strong>', b['html'], re.S)
        if whole_bold:
            cur = {'q': text_of(whole_bold.group(1)), 'a_html': ''}
            items.append(cur)
        elif cur is not None:
            cur['a_html'] += '<p>%s</p>' % ctx.links(b['html'])
    return [{'type': 'faq', 'h2': heading(sec['label']), 'items': items}]


def build_testimonials(blocks, h2, ctx):
    items = []
    for b in blocks:
        if is_note(b) or is_image(b):
            continue
        m = re.match(r'^(.*[.!?])\s+([A-Z][\w’\'.-]+(?:\s+[A-Z][\w’\'.-]+)+),\s*([^,]+),\s*(.+?)\.?$', b['text'], re.S)
        if m:
            items.append({'quote': m.group(1), 'name': m.group(2), 'role': m.group(3).strip(), 'company': m.group(4).strip()})
        else:
            items.append({'quote': b['text'], 'name': '', 'role': '', 'company': ''})
    s = {'type': 'testimonials', 'items': items}
    if h2:
        s['h2'] = h2
    return [s]


def build_inquiry(sec, ctx):
    s = {'type': 'inquiry', 'h2': None, 'intro_html': '', 'note_html': ''}
    intro, skip = [], False
    for b in sec['blocks']:
        lab = label_of(b)
        if lab in ('header', 'form heading'):
            s['h2'] = text_of(strip_label(b['html']))
            skip = False
        elif lab == 'text':
            intro.append(dict(b, html=strip_label(b['html'])))
            skip = False
        elif lab and lab.startswith('microcopy'):
            s['note_html'] = '<p>%s</p>' % ctx.links(strip_label(b['html']))
        elif lab in ('lead form fields', 'button', 'cta button') or b['tag'] in ('li', 'h3'):
            skip = True  # form fields / button: configured in Contact Form 7
        elif not skip and b['tag'] == 'p' and not is_note(b):
            intro.append(b)
    s['intro_html'] = html_of(intro, ctx)
    return [prune(s)]


def build_steps(sec, ctx):
    intro, steps, after, img = [], [], [], None
    for b in sec['blocks']:
        if is_note(b):
            continue
        if is_image(b):
            img = ctx.slot(key_for(sec['label'], 'process'), b)
        elif b['tag'] == 'li':
            steps.append(ctx.links(b['html']))
        elif steps:
            after.append(b)
        else:
            intro.append(b)
    return [prune({'type': 'process-steps', 'h2': heading(sec['label']), 'intro_html': html_of(intro, ctx), 'steps': steps,
                   'after_html': html_of(after, ctx), 'image': img})]


def build_content(sec, ctx):
    """Generic H2 section: intro, optional groups (H3) of items (H3/H4) with optional images, gallery slots, testimonials."""
    h2 = heading(sec['label'])
    blocks = [b for b in sec['blocks'] if not is_note(b)]
    key = key_for(sec['label'], 'content')
    has_h4 = any(b['tag'] == 'h4' for b in blocks)
    intro, groups, section_img, extra = [], [], None, []
    cur_group, cur_item, items_heading = None, None, None
    gallery_note, testimonials = None, None
    i = 0
    while i < len(blocks):
        b = blocks[i]
        lab = label_of(b)
        nxt = blocks[i + 1] if i + 1 < len(blocks) else None
        if testimonials is not None:
            testimonials.append(b); i += 1; continue
        if lab == 'testimonials':
            testimonials = []; i += 1; continue
        if re.search(r'image slots|project slots', b['text'], re.I):
            gallery_note = b['text']; i += 1; continue
        if is_image(b):
            if cur_item is not None and cur_item.get('image') is None and cur_item['_body']:
                cur_item['image'] = ctx.slot(key, b)
            else:
                section_img = ctx.slot(key, b)
            i += 1; continue
        if b['tag'] == 'h3' and cur_item is not None and not cur_item['_body'] and len(b['text']) > 60:
            cur_item['_body'].append(dict(b, tag='p'))          # body text styled as a heading in the doc
        elif b['tag'] == 'h3' and has_h4:
            cur_group = {'label': text_of(b['html']), '_intro': [], 'items': []}
            groups.append(cur_group); cur_item = None
        elif b['tag'] == 'h3' and '<strong>' not in b['html'] and nxt is not None and nxt['tag'] == 'h3' and '<strong>' in nxt['html']:
            cur_group = {'label': text_of(b['html']), '_intro': [], 'items': []}
            groups.append(cur_group); cur_item = None
        elif b['tag'] in ('h3', 'h4'):
            if cur_group is None:
                cur_group = {'label': None, '_intro': [], 'items': []}
                groups.append(cur_group)
            cur_item = {'title': text_of(b['html']), '_body': [], 'image': None}
            cur_group['items'].append(cur_item)
        elif re.fullmatch(r'<strong>[^<]*</strong>', b['html']) and nxt is not None and nxt['tag'] == 'h3':
            items_heading = text_of(b['html'])
        elif cur_item is not None:
            cur_item['_body'].append(b)
        elif cur_group is not None:
            cur_group['_intro'].append(b)
        else:
            intro.append(b)
        i += 1

    for g in groups:
        g['intro_html'] = html_of(g.pop('_intro'), ctx)
        for it in g['items']:
            it['html'] = html_of(it.pop('_body'), ctx)
    items = [it for g in groups for it in g['items']]
    if items and not all(it['image'] for it in items):
        # Images that follow only some items belong to the section (e.g. one image after the last item).
        imgs = [it['image'] for it in items if it['image']]
        if len(imgs) == 1 and section_img is None:
            section_img = imgs[0]
            for it in items:
                it['image'] = None
    out = []
    if items and all(it['image'] for it in items):
        out.append(prune({'type': 'card-grid', 'h2': h2, 'intro_html': html_of(intro, ctx),
                          'groups': [prune(g) for g in groups] if any(g['label'] for g in groups) else None,
                          'items': None if any(g['label'] for g in groups) else [prune(it) for it in items]}))
    elif items:
        out.append(prune({'type': 'features-grid', 'h2': h2, 'intro_html': html_of(intro, ctx), 'items_heading': items_heading,
                          'items': [prune(it) for it in items], 'image': section_img}))
    elif section_img:
        out.append({'type': 'image-text', 'h2': h2, 'html': html_of(intro, ctx), 'image': section_img})
    elif gallery_note is None or intro:
        cta = None
        if intro and re.fullmatch(r'\s*<strong>\s*<a href="[^"]*">[^<]*</a>\s*</strong>\s*|\s*<a href="[^"]*">[^<]*</a>\s*', intro[-1]['html']):
            m = re.search(r'<a href="([^"]*)">([^<]*)</a>', ctx.links(intro.pop()['html']))
            cta = {'label': text_of(m.group(2)), 'href': html.unescape(m.group(1))}
        if gallery_note is None:
            out.append(prune({'type': 'rich-text', 'h2': h2, 'html': html_of(intro, ctx), 'cta': cta}))
    if gallery_note is not None:
        slots = [ctx.slot('projects', None, 'gallery', note=gallery_note) for _ in range(4)]
        g = {'type': 'gallery', 'items': [{'image': s, 'caption': ''} for s in slots]}
        if out:
            g['continues'] = True
        else:
            g.update({'h2': h2, 'intro_html': html_of(intro, ctx)})
        out.append(prune(g))
    if testimonials:
        t = build_testimonials([dict(b, text=text_of(b['html'])) for b in testimonials], None, ctx)
        t[0]['continues'] = True
        out.extend(t)
    return out


def prune(d):
    return {k: v for k, v in d.items() if v not in (None, '', [], {})}


def build_section(sec, ctx):
    l = sec['label'].lower()
    blocks = sec['blocks']
    if 'hero' in l:
        return build_hero(sec, ctx)
    if 'proof' in l or 'trust' in l:
        return build_trust(sec, ctx)
    if 'confirmation' in l:
        return []  # Contact Form 7 setting, not page content
    if any(label_of(b) in ('header', 'form heading', 'lead form fields') for b in blocks):
        return build_inquiry(sec, ctx)
    if any(k in l for k in ('question', 'faq', 'answers')):
        return build_faq(sec, ctx)
    if 'testimonial' in l or 'clients say' in l:
        return build_testimonials(blocks, heading(sec['label']) if 'h2' in l else None, ctx)
    if any(k in l for k in ('how it works', 'happens next', 'project works')) or (blocks and sum(b['tag'] == 'li' for b in blocks) >= 3 and all('<strong>' in b['html'] for b in blocks if b['tag'] == 'li') and not any(b['tag'] in ('h3', 'h4') for b in blocks)):
        return build_steps(sec, ctx)
    return build_content(sec, ctx)


# ------------------------------------------------------------------ manifest / registry
def manifest():
    php = "define('ABSPATH', 1); echo json_encode(require %s);" % json.dumps(os.path.join(ROOT, 'data', 'manifest.php'))
    return json.loads(subprocess.check_output(['php', '-r', php]))


def jstr(v):
    return json.dumps(v, ensure_ascii=False)


def update_manifest(route, fields, new, remove=()):
    path = os.path.join(ROOT, 'data', 'manifest.php')
    src = open(path, encoding='utf-8').read()
    lines = src.split('\n')
    start = next((i for i, l in enumerate(lines) if l == '  %s: {' % jstr(route)), None)
    if new and start is None:
        keys = [(i, json.loads(l.strip()[:-3])) for i, l in enumerate(lines) if re.match(r'^  "/nyc/.*": \{$', l)]
        after = next((i for i, k in keys if k > route), None)
        body = ['  %s: {' % jstr(route)] + ['    %s: %s,' % (jstr(k), jstr(v)) for k, v in fields.items()]
        body[-1] = body[-1][:-1]
        if after is None:  # append as last entry
            end = max(i for i, l in enumerate(lines) if l == '  }')
            lines[end] = '  },'
            lines[end + 1:end + 1] = body + ['  }']
        else:
            lines[after:after] = body + ['  },']
    else:
        end = next(i for i in range(start, len(lines)) if lines[i] in ('  }', '  },'))
        block = [l for l in lines[start + 1:end] if not any(l.strip().startswith(jstr(k) + ':') for k in list(fields) + list(remove))]
        block = [l if l.endswith(',') else l + ',' for l in block]
        block += ['    %s: %s,' % (jstr(k), jstr(v)) for k, v in fields.items()]
        block[-1] = block[-1][:-1]
        lines[start + 1:end] = block
    with open(path, 'w', encoding='utf-8') as f:
        f.write('\n'.join(lines))


def main():
    args = sys.argv[1:]
    config = {}
    if '--config' in args:
        config = json.load(open(args[args.index('--config') + 1], encoding='utf-8'))
    slugs = [a for a in args if not a.startswith('--') and not a.endswith('.json')]
    reg_path = os.path.join(ROOT, 'content', 'images.json')
    registry = json.load(open(reg_path, encoding='utf-8')) if os.path.exists(reg_path) else {}
    pages = manifest()
    os.makedirs(os.path.join(ROOT, 'content', 'pages'), exist_ok=True)
    for slug in slugs:
        doc = json.load(open(os.path.join(REPORTS, slug + '-parsed.json'), encoding='utf-8'))
        ctx = Ctx(slug, doc['route'], config)
        sections = []
        for sec in doc['sections'][1:]:
            ctx.section_n = sec['n']
            for s in build_section(sec, ctx):
                s['doc_section'] = sec['n']
                sections.append(s)
        content = 'content/pages/%s.json' % slug
        with open(os.path.join(ROOT, content), 'w', encoding='utf-8') as f:
            json.dump({'route': doc['route'], 'source': doc['source'], 'sections': sections}, f, ensure_ascii=False, indent=2)
            f.write('\n')
        for sl in ctx.slots:
            sid = sl.pop('id')
            old = registry.get(sid, {})
            registry[sid] = dict(sl, alt=old.get('alt') or sl['alt'], caption=old.get('caption', ''))
        hero = next((s.get('image') for s in sections if s['type'] == 'hero'), None)
        existing = pages.get(doc['route'])
        if existing is None or existing.get('status') == 'draft':
            update_manifest(doc['route'], {'title': doc['meta_title'], 'description': doc['meta_description'],
                                           'template': 'landing', 'content': content, 'service': True,
                                           'image': hero or '/nyc/assets/mcs-new-home-hero-channel-letters-1600.webp',
                                           'status': 'draft'}, new=existing is None,
                            remove=('draft_template', 'draft_content', 'draft_title', 'draft_description', 'draft_image'))
            state = 'NEW (draft)'
        else:
            fields = {'draft_template': 'landing', 'draft_content': content, 'draft_title': doc['meta_title'],
                      'draft_description': doc['meta_description']}
            if hero:
                fields['draft_image'] = hero
            update_manifest(doc['route'], fields, new=False)
            state = 'REPLACEMENT (draft_content)'
        types = ', '.join(s['type'] for s in sections)
        print('%-20s %-28s %2d sections, %2d image slots, +%d links  [%s]' % (slug, state, len(sections), len(ctx.slots), len(ctx.added), types))
    with open(reg_path, 'w', encoding='utf-8') as f:
        json.dump(dict(sorted(registry.items())), f, ensure_ascii=False, indent=2)
        f.write('\n')


if __name__ == '__main__':
    main()
