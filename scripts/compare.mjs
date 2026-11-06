// Compare the current local output against tests/baseline.
// Usage: node scripts/compare.mjs [--no-capture] [--no-screens] [--only=slug,slug]
// Writes tests/report.md and diff images to tests/diff/. Exit code 1 on any failure.
import fs from 'node:fs';
import path from 'node:path';
import { execFileSync } from 'node:child_process';
import { ROOT, TESTS, BASE_URL, WIDTHS, targets, load, pool } from './lib.mjs';

const args = process.argv.slice(2);
const noCapture = args.includes('--no-capture');
const noScreens = args.includes('--no-screens');
const onlyArg = args.find((a) => a.startsWith('--only='));
const only = onlyArg ? onlyArg.slice(7).split(',').filter(Boolean) : [];

const B = path.join(TESTS, 'baseline');
const C = path.join(TESTS, 'current');
const DIFF = path.join(TESTS, 'diff');

if (!noCapture) {
  const capArgs = [path.join(ROOT, 'scripts/capture.mjs'), 'current'];
  if (noScreens) capArgs.push('--no-screens');
  if (only.length) capArgs.push('--only=' + only.join(','));
  execFileSync('node', capArgs, { stdio: 'inherit' });
}

const parse5 = await load('parse5');
const { PNG } = await load('pngjs');
const pixelmatch = (await load('pixelmatch')).default;

const stripVer = (v) => v.replace(/([?&])ver=[^&"'\s]*/g, '$1ver=*');
const fileFor = (dir, slug) => path.join(dir, 'html', slug + (slug.endsWith('.xml') ? '' : '.html'));

// ---------- a. HTML: DOM normalization into a flat list of lines with CSS paths ----------
function flatten(html) {
  const doc = parse5.parse(html);
  const lines = [];
  const walk = (node, cssPath) => {
    for (const child of node.childNodes || []) {
      if (child.nodeName === '#text') {
        const t = child.value.replace(/\s+/g, ' ');
        if (t.trim() !== '') lines.push({ path: cssPath, text: 'TEXT ' + JSON.stringify(stripVer(t)) });
      } else if (child.nodeName === '#comment') {
        lines.push({ path: cssPath, text: '<!--' + child.data.trim() + '-->' });
      } else if (child.tagName) {
        const attrs = (child.attrs || []).map((a) => [a.name, stripVer(a.value.replace(/\s+/g, ' '))]).sort((x, y) => (x[0] < y[0] ? -1 : 1));
        const idx = (node.childNodes.filter((n) => n.tagName === child.tagName).indexOf(child)) + 1;
        let seg = child.tagName;
        const id = attrs.find((a) => a[0] === 'id');
        const cls = attrs.find((a) => a[0] === 'class');
        if (id) seg += '#' + id[1]; else if (cls && cls[1].trim()) seg += '.' + cls[1].trim().split(' ').join('.');
        seg += `:nth-of-type(${idx})`;
        const p = cssPath ? cssPath + ' > ' + seg : seg;
        lines.push({ path: p, text: '<' + child.tagName + attrs.map((a) => ` ${a[0]}="${a[1]}"`).join('') + '>' });
        walk(child.tagName === 'template' ? child.content : child, p);
        lines.push({ path: p, text: '</' + child.tagName + '>' });
      }
    }
  };
  walk(doc, '');
  return lines;
}
function compareHtml(oldHtml, newHtml) {
  const a = flatten(oldHtml); const b = flatten(newHtml);
  const n = Math.max(a.length, b.length);
  for (let i = 0; i < n; i++) {
    if (!a[i] || !b[i] || a[i].text !== b[i].text) {
      const snip = (L) => L.slice(Math.max(0, i - 1), i + 2).map((l) => '    ' + l.text.slice(0, 220)).join('\n');
      return { ok: false, detail: `first difference at ${(a[i] || b[i]).path}\n  old:\n${snip(a)}\n  new:\n${snip(b)}` };
    }
  }
  return { ok: true };
}

// ---------- b. SEO ----------
function seo(html) {
  const doc = parse5.parse(html);
  const out = { title: null, description: null, robots: null, canonical: null, og: {}, jsonld: [], gtm: [] };
  const walk = (node) => {
    for (const c of node.childNodes || []) {
      if (!c.tagName) continue;
      const at = Object.fromEntries((c.attrs || []).map((a) => [a.name, a.value]));
      const text = () => (c.childNodes || []).map((t) => t.value || '').join('');
      if (c.tagName === 'title') out.title = text();
      if (c.tagName === 'meta' && at.name === 'description') out.description = at.content;
      if (c.tagName === 'meta' && at.name === 'robots') out.robots = at.content;
      if (c.tagName === 'meta' && at.property && at.property.startsWith('og:')) out.og[at.property] = at.content;
      if (c.tagName === 'link' && at.rel === 'canonical') out.canonical = at.href;
      if (c.tagName === 'script' && at.type === 'application/ld+json') { try { out.jsonld.push(JSON.parse(text())); } catch { out.jsonld.push('INVALID:' + text()); } }
      if (c.tagName === 'script' && text().includes('googletagmanager.com/gtm.js')) out.gtm.push(text().trim());
      if (c.tagName === 'noscript') { const s = parse5.serialize(c); if (s.includes('googletagmanager.com/ns.html')) out.gtm.push(s.trim()); }
      walk(c);
    }
  };
  walk(doc);
  return out;
}
const stable = (v) => JSON.stringify(v, (k, val) => (val && typeof val === 'object' && !Array.isArray(val) ? Object.fromEntries(Object.keys(val).sort().map((x) => [x, val[x]])) : val));
function compareSeo(oldHtml, newHtml) {
  const a = seo(oldHtml); const b = seo(newHtml);
  const bad = Object.keys(a).filter((k) => stable(a[k]) !== stable(b[k]));
  return bad.length ? { ok: false, detail: 'SEO mismatch: ' + bad.map((k) => `${k}\n    old: ${stable(a[k]).slice(0, 300)}\n    new: ${stable(b[k]).slice(0, 300)}`).join('\n  ') } : { ok: true };
}

// ---------- e. Assets ----------
const assetCache = new Map();
// The local server drops connections under load: limit concurrency and retry network errors.
let active = 0; const waiting = [];
async function getStatus(u) {
  if (active >= 4) await new Promise((r) => waiting.push(r));
  active++;
  try {
    let last;
    for (let n = 0; n < 4; n++) {
      try { const r = await fetch(u); await r.arrayBuffer(); return r.status; } catch (e) { last = e; await new Promise((r) => setTimeout(r, 300 * (n + 1))); }
    }
    return 'ERR ' + last.message;
  } finally { active--; if (waiting.length) waiting.shift()(); }
}
function assetUrls(html) {
  const doc = parse5.parse(html); const urls = new Set();
  const walk = (node) => {
    for (const c of node.childNodes || []) {
      if (!c.tagName) continue;
      const at = Object.fromEntries((c.attrs || []).map((a) => [a.name, a.value]));
      if ((c.tagName === 'img' || c.tagName === 'source') && at.src) urls.add(at.src);
      if ((c.tagName === 'img' || c.tagName === 'source') && at.srcset) at.srcset.split(',').forEach((s) => urls.add(s.trim().split(/\s+/)[0]));
      if (c.tagName === 'link' && /\bstylesheet\b/.test(at.rel || '') && at.href) urls.add(at.href);
      if (c.tagName === 'script' && at.src) urls.add(at.src);
      walk(c.tagName === 'template' ? c.content : c);
    }
  };
  walk(doc);
  return [...urls].map((u) => new URL(u, BASE_URL).href);
}
async function checkAssets(html) {
  const urls = assetUrls(html).filter((u) => u.startsWith(BASE_URL)); // external hosts (e.g. GTM) are not checked offline
  const bad = [];
  for (const u of urls) {
    if (!assetCache.has(u)) assetCache.set(u, getStatus(u));
    const st = await assetCache.get(u);
    if (st !== 200) bad.push(`${st} ${u}`);
  }
  return bad.length ? { ok: false, detail: 'assets not 200:\n    ' + bad.join('\n    ') } : { ok: true, count: urls.length };
}

// ---------- d. Visual ----------
function compareScreens(slug) {
  const fails = [];
  for (const w of WIDTHS) {
    const name = `${slug}-${w}.png`;
    const pa = path.join(B, 'screens', name); const pb = path.join(C, 'screens', name);
    if (!fs.existsSync(pb)) { fails.push(`${w}: missing screenshot`); continue; }
    const A = PNG.sync.read(fs.readFileSync(pa)); const Bm = PNG.sync.read(fs.readFileSync(pb));
    if (A.width !== Bm.width || A.height !== Bm.height) { fails.push(`${w}: size ${A.width}x${A.height} -> ${Bm.width}x${Bm.height}`); continue; }
    const out = new PNG({ width: A.width, height: A.height });
    const n = pixelmatch(A.data, Bm.data, out.data, A.width, A.height, { threshold: 0 });
    if (n > 0) { fs.mkdirSync(DIFF, { recursive: true }); fs.writeFileSync(path.join(DIFF, name), PNG.sync.write(out)); fails.push(`${w}: ${n} px differ (tests/diff/${name})`); }
  }
  return fails.length ? { ok: false, detail: 'visual: ' + fails.join('; ') } : { ok: true };
}

// ---------- run ----------
fs.rmSync(DIFF, { recursive: true, force: true });
let list = targets();
if (only.length) list = list.filter((t) => only.includes(t.slug));
const hb = JSON.parse(fs.readFileSync(path.join(B, 'headers.json'), 'utf8'));
const hc = JSON.parse(fs.readFileSync(path.join(C, 'headers.json'), 'utf8'));
const locs = (xml) => new Set([...xml.matchAll(/<loc>(.*?)<\/loc>/g)].map((m) => m[1]));

const rows = await pool(list, 6, async (t) => {
  const r = { route: t.route, template: t.template, html: '-', seo: '-', headers: '-', visual: '-', assets: '-', details: [] };
  const set = (k, res) => { r[k] = res.ok ? 'ok' : 'FAIL'; if (!res.ok) r.details.push(`[${k}] ${res.detail}`); };
  const oldHtml = fs.readFileSync(fileFor(B, t.slug), 'utf8');
  const cf = fileFor(C, t.slug);
  if (!fs.existsSync(cf)) { r.html = 'FAIL'; r.details.push('[html] not captured'); return r; }
  const newHtml = fs.readFileSync(cf, 'utf8');
  const fields = ['status', 'content-type', 'x-robots-tag', 'location'];
  const hdiff = fields.filter((f) => (hb[t.slug] || {})[f] !== (hc[t.slug] || {})[f]);
  set('headers', hdiff.length ? { ok: false, detail: hdiff.map((f) => `${f}: ${hb[t.slug]?.[f]} -> ${hc[t.slug]?.[f]}`).join(', ') } : { ok: true });
  if (t.slug === 'sitemap.xml') {
    const a = locs(oldHtml); const b = locs(newHtml);
    const miss = [...a].filter((x) => !b.has(x)); const extra = [...b].filter((x) => !a.has(x));
    set('html', miss.length || extra.length ? { ok: false, detail: `sitemap missing: ${miss.join(' ')} extra: ${extra.join(' ')}` } : { ok: true });
    r.template = 'sitemap';
    return r;
  }
  set('html', compareHtml(oldHtml, newHtml));
  if (t.page) {
    set('seo', compareSeo(oldHtml, newHtml));
    set('assets', await checkAssets(newHtml));
    if (!noScreens) set('visual', compareScreens(t.slug));
  }
  return r;
});

const pass = (r) => !['html', 'seo', 'headers', 'visual', 'assets'].some((k) => r[k] === 'FAIL');
const failed = rows.filter((r) => !pass(r));
const header = '| route | template | html | seo | headers | visual | assets | result |\n|---|---|---|---|---|---|---|---|';
const table = rows.map((r) => `| ${r.route} | ${r.template} | ${r.html} | ${r.seo} | ${r.headers} | ${r.visual} | ${r.assets} | ${pass(r) ? 'PASS' : 'FAIL'} |`).join('\n');
const tally = {};
for (const r of rows) tally[r.template] = (tally[r.template] || 0) + 1;
const fallbackFile = path.join(TESTS, 'fallbacks.md');
const fallbacks = fs.existsSync(fallbackFile) ? fs.readFileSync(fallbackFile, 'utf8').trim() : 'None.';
const report = `# Compare report\n\nGenerated ${new Date().toISOString()} against \`tests/baseline\` (${BASE_URL}).\n\n` +
  `**${rows.length - failed.length}/${rows.length} targets pass** (checks: a html, b seo, c headers, d visual @ ${WIDTHS.join(' & ')}px with 0 px tolerance, e assets, f sitemap)${noScreens ? ' — visual check skipped' : ''}.\n\n` +
  `Templates: ${Object.entries(tally).map(([k, v]) => `${k} ${v}`).join(', ')}\n\n${header}\n${table}\n\n## Fallbacks\n\n${fallbacks}\n` +
  (failed.length ? `\n## Failures\n\n${failed.map((r) => `### ${r.route}\n\n\`\`\`\n${r.details.join('\n')}\n\`\`\``).join('\n\n')}\n` : '');
fs.writeFileSync(path.join(TESTS, 'report.md'), report);
console.log(header + '\n' + table);
for (const r of failed) console.log(`\n### ${r.route}\n${r.details.join('\n')}`);
console.log(`\n${rows.length - failed.length}/${rows.length} pass${noScreens ? ' (visual skipped)' : ''}. Report: tests/report.md`);
process.exit(failed.length ? 1 : 0);
