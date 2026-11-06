// Phase 2 QA for draft landing pages (Step 6). Dev-only.
// Usage: node scripts/qa.mjs <batch> <slug> [<slug> ...]
//   Needs admin cookies: php scripts/admin_cookie.php > tests/.admin-cookies.json (gitignored)
// Writes inbox/reports/qa-{batch}.md and screenshots to inbox/reports/screens/{slug}-{width}.png.
import fs from 'node:fs';
import path from 'node:path';
import { ROOT, BASE_URL, manifest, load } from './lib.mjs';

const [batch, ...slugs] = process.argv.slice(2);
if (!batch || !slugs.length) { console.error('usage: qa.mjs <batch> <slug> [...]'); process.exit(2); }
const REPORTS = path.join(ROOT, 'inbox', 'reports');
const SCREENS = path.join(REPORTS, 'screens');
const WIDTHS = [1440, 1024, 768, 390];
fs.mkdirSync(SCREENS, { recursive: true });
const cookies = JSON.parse(fs.readFileSync(path.join(ROOT, 'tests', '.admin-cookies.json'), 'utf8'));
const cookieHeader = cookies.map((c) => `${c.name}=${c.value}`).join('; ');
const parse5 = await load('parse5');
const pages = manifest();

const norm = (s) => s.replace(/\s+/g, ' ').trim();
const decode = (s) => s.replace(/<[^>]+>/g, '').replace(/&nbsp;/g, ' ').replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').replace(/&#0?39;/g, "'");

function walk(node, fn, ctx = {}) {
  for (const c of node.childNodes || []) { fn(c, ctx); walk(c.tagName === 'template' ? c.content : c, fn, ctx); }
}
const attrs = (n) => Object.fromEntries((n.attrs || []).map((a) => [a.name, a.value]));
const BLOCK = new Set(['p', 'li', 'div', 'section', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'td', 'th', 'tr', 'summary', 'details', 'figcaption', 'blockquote', 'ul', 'ol', 'figure', 'article', 'aside', 'main', 'span', 'strong']);
function textOf(node) {
  // Text nodes are joined as written; block elements (and the separate parts of trust/quote items) are separated by a space.
  const rec = (n) => (n.childNodes || []).map((c) => (c.nodeName === '#text' ? c.value : c.tagName ? (BLOCK.has(c.tagName) && c.tagName !== 'strong' ? ' ' + rec(c) + ' ' : rec(c)) : '')).join('');
  return norm(rec(node));
}
function find(node, pred) { const out = []; walk(node, (c) => { if (c.tagName && pred(c)) out.push(c); }); return out; }

/** Expected visible copy from the parsed doc (labels, notes, prompts and form-field specs removed). */
function expectedCopy(doc) {
  const out = [];
  const skipLabels = /^(url|meta title|meta description|lead form fields|cta button|button|helper text)$/i;
  for (const sec of doc.sections.slice(1)) {
    if (/confirmation/i.test(sec.label)) continue;
    let inForm = false;
    for (const b of sec.blocks) {
      const t = norm(b.text);
      if (/^\(.*\)\.?$/.test(t) || /^image\b/i.test(t) || /image slots|project slots/i.test(t)) continue;
      const lab = t.match(/^([A-Za-z][A-Za-z0-9 ]{0,40}):\s*(.*)$/);
      if (lab && /^(lead form fields)$/i.test(lab[1])) { inForm = true; continue; }
      if (inForm && (b.tag === 'li' || /^cta button/i.test(t))) continue;
      if (b.tag === 'li' && /form heading|lead form/i.test(JSON.stringify(sec.blocks.slice(0, 2)))) continue;
      if (lab && skipLabels.test(lab[1])) continue;
      if (lab && /^cta buttons?$/i.test(lab[1])) { lab[2].split('|').forEach((x) => out.push(norm(x))); continue; }
      if (lab && /^microcopy/i.test(lab[1])) { out.push(norm(lab[2])); continue; }
      if (lab && /^(eyebrow|h1|intro|header|text|form heading)$/i.test(lab[1])) { out.push(norm(lab[2])); continue; }
      if (/^testimonials:?$/i.test(t)) continue;
      if (b.tag === 'table') { for (const m of b.html.matchAll(/<t[hd]>(.*?)<\/t[hd]>/g)) out.push(norm(decode(m[1]))); continue; }
      const pipe = t.match(/^(.+?) \| (.+)$/);
      if (pipe) { out.push(pipe[1], pipe[2]); continue; }
      const quote = t.match(/^(.*[.!?])\s+([A-Z][\w’'.-]+(?:\s+[A-Z][\w’'.-]+)+),\s*([^,]+),\s*(.+?)\.?$/);
      if (quote && /clients say|testimonial|our work/i.test(sec.label)) { out.push(quote[1], quote[2], quote[3], quote[4]); continue; }
      if (sec.blocks.some((x) => /^Form heading:/i.test(x.text)) && (b.tag === 'li' || b.tag === 'h3')) continue;
      out.push(t);
    }
  }
  return out;
}

const results = [];
const { chromium } = await load('playwright');
const browser = await chromium.launch();
for (const slug of slugs) {
  const doc = JSON.parse(fs.readFileSync(path.join(REPORTS, `${slug}-parsed.json`), 'utf8'));
  const route = doc.route;
  const entry = pages[route];
  const isNew = entry && entry.status === 'draft';
  const url = `${BASE_URL}${route}${isNew ? '' : '?vgnyc_preview=1'}`;
  const r = { slug, route, url, checks: [], issues: [] };
  const check = (name, ok, detail = '') => { r.checks.push({ name, ok, detail }); };

  const res = await fetch(url, { headers: { Cookie: cookieHeader }, redirect: 'manual' });
  const html = await res.text();
  const anon = await fetch(`${BASE_URL}${route}`, { redirect: 'manual' });
  check('Renders for admins (HTTP 200)', res.status === 200, `HTTP ${res.status}`);
  check('Draft hidden from the public', isNew ? anon.status === 404 : anon.status === 200 && !(await anon.clone().text()).includes('vgnyc-draft-bar'), isNew ? `anonymous: HTTP ${anon.status}` : 'anonymous visitors still get the current live page');
  check('Not indexable while draft', /noindex/.test(res.headers.get('x-robots-tag') || '') && /<meta name="robots" content="noindex/.test(html), res.headers.get('x-robots-tag'));
  const php = html.match(/<b>(Warning|Notice|Deprecated|Fatal error)<\/b>|PHP (Warning|Notice|Deprecated)/g);
  check('No PHP warnings or notices in output', !php, php ? php.join(', ') : '');

  const dom = parse5.parse(html);
  const main = find(dom, (n) => n.tagName === 'main')[0];
  const pageText = textOf(main);
  const expected = expectedCopy(doc);
  const missing = expected.filter((t) => !pageText.includes(norm(t)));
  check('Copy matches the doc', missing.length === 0, missing.length ? `${expected.length - missing.length}/${expected.length} doc text blocks found verbatim` : `${expected.length}/${expected.length} doc text blocks found verbatim`);
  missing.forEach((m) => r.issues.push(`Copy not found verbatim: “${m.slice(0, 160)}”`));

  // Links
  const links = find(main, (n) => n.tagName === 'a').map((a) => ({ href: attrs(a).href || '', text: textOf(a), cls: attrs(a).class || '' }));
  const badInternal = links.filter((l) => l.href.startsWith('/nyc/') && !pages[l.href.split(/[?#]/)[0]] && !['/nyc/privacy/'].includes(l.href.split(/[?#]/)[0]));
  check('Internal links point to existing routes', badInternal.length === 0, badInternal.map((l) => `${l.text} → ${l.href}`).join('; '));
  const drafts = links.filter((l) => l.href.startsWith('/nyc/') && pages[l.href.split(/[?#]/)[0]]?.status === 'draft');
  drafts.forEach((l) => r.issues.push(`Links to a page that is still a draft: “${l.text}” → ${l.href} (fine once it is published together)`));
  const external = links.filter((l) => /^https?:/.test(l.href));
  for (const l of external) { try { const s = (await fetch(l.href, { redirect: 'follow' })).status; if (s !== 200) r.issues.push(`External link ${l.href} returned ${s}`); } catch (e) { r.issues.push(`External link ${l.href} failed: ${e.message}`); } }
  check('External links return 200', !r.issues.some((i) => i.startsWith('External link')), `${external.length} external links`);
  const buttons = links.filter((l) => /\bbtn\b/.test(l.cls));
  const badCta = buttons.filter((l) => !(l.href === '#inquiry' || l.href.startsWith('/nyc/contact/')));
  check('Every CTA leads to the inquiry form', badCta.length === 0, badCta.length ? badCta.map((l) => `${l.text} → ${l.href}`).join('; ') : `${buttons.length} buttons → #inquiry or /nyc/contact/`);
  const forms = (html.match(/class="vgnyc-form"/g) || []).length;
  const inquiry = (html.match(/id="inquiry"/g) || []).length;
  check('At most one form, inside #inquiry', forms <= 1 && inquiry === 1, `${forms} form block, ${inquiry} #inquiry section`);
  const realForm = /<form\b[^>]*wpcf7/i.test(html);
  check('Form renders (or the email/phone fallback shows)', realForm || html.includes('mailto:ekerins@visiondoesit.com'), realForm ? 'Contact Form 7 form rendered' : 'fallback shown: form shortcode not configured on this site');

  // SEO
  const title = (html.match(/<title>(.*?)<\/title>/) || [])[1] || '';
  const desc = (html.match(/<meta name="description" content="([^"]*)"/) || [])[1] || '';
  const t = decode(title); const d = decode(desc);
  check('Title ≤ 60 characters', t.length <= 60, `${t.length}: ${t}`);
  check('Description ≤ 160 characters', d.length <= 160, `${d.length} characters`);
  const heads = find(main, (n) => /^h[1-6]$/.test(n.tagName)).map((n) => +n.tagName[1]);
  const skips = heads.filter((h, i) => i > 0 && h > heads[i - 1] + 1);
  check('One H1, headings in order', heads.filter((h) => h === 1).length === 1 && heads[0] === 1 && skips.length === 0, `${heads.length} headings, ${skips.length} skipped levels`);
  let ld = 'ok';
  for (const m of html.matchAll(/<script type="application\/ld\+json"[^>]*>(.*?)<\/script>/gs)) { try { JSON.parse(m[1]); } catch (e) { ld = 'invalid JSON-LD'; } }
  const faqLd = /"@type":"FAQPage"/.test(html);
  check('JSON-LD valid (with FAQPage when the page has FAQs)', ld === 'ok' && (faqLd || !find(main, (n) => n.tagName === 'details').length), faqLd ? 'FAQPage present' : 'no FAQ section');

  // Visual
  const ctx = await browser.newContext({ deviceScaleFactor: 1 });
  await ctx.addCookies(cookies.map((c) => ({ name: c.name, value: c.value, url: BASE_URL })));
  const overflow = []; let broken = 0; let placeholders = 0;
  for (const w of WIDTHS) {
    const page = await ctx.newPage();
    await page.setViewportSize({ width: w, height: 900 });
    await page.goto(url, { waitUntil: 'networkidle', timeout: 60000 });
    const info = await page.evaluate(async () => {
      document.querySelectorAll('img[loading="lazy"]').forEach((i) => { i.loading = 'eager'; });
      for (let y = 0; y < document.body.scrollHeight; y += 700) { window.scrollTo(0, y); await new Promise((r) => setTimeout(r, 15)); }
      window.scrollTo(0, 0);
      await Promise.all(Array.from(document.images).map((i) => (i.complete ? null : new Promise((r) => { i.onload = i.onerror = r; }))));
      const wide = Array.from(document.querySelectorAll('main *')).filter((e) => { const r = e.getBoundingClientRect(); return r.right > document.documentElement.clientWidth + 1 && getComputedStyle(e).position !== 'fixed' && !e.closest('table'); }).slice(0, 3).map((e) => e.tagName.toLowerCase() + (e.className ? '.' + String(e.className).split(' ')[0] : ''));
      return { scroll: document.documentElement.scrollWidth > document.documentElement.clientWidth + 1, wide, broken: Array.from(document.images).filter((i) => i.complete && i.naturalWidth === 0).length, placeholders: document.querySelectorAll('.vgnyc-img-placeholder').length };
    });
    if (info.scroll || info.wide.length) overflow.push(`${w}px: ${info.wide.join(', ') || 'page scrolls sideways'}`);
    broken = Math.max(broken, info.broken); placeholders = info.placeholders;
    await page.screenshot({ path: path.join(SCREENS, `${slug}-${w}.png`), fullPage: true });
    await page.close();
  }
  await ctx.close();
  check('No horizontal overflow at 1440 / 1024 / 768 / 390', overflow.length === 0, overflow.join('; '));
  check('Images: real or labeled placeholder, none broken', broken === 0, `${placeholders} placeholders, ${broken} broken`);
  results.push(r);
  console.log(`${slug}: ${r.checks.filter((c) => c.ok).length}/${r.checks.length} checks pass`);
}
await browser.close();

let md = `# QA report: batch ${batch}\n\nGenerated ${new Date().toISOString()} on ${BASE_URL} (as an administrator).\n\n`;
md += '| Page | Preview URL | Checks |\n|---|---|---|\n' + results.map((r) => `| ${r.slug} | ${r.url} | ${r.checks.filter((c) => c.ok).length}/${r.checks.length} |`).join('\n') + '\n';
for (const r of results) {
  md += `\n## ${r.route}\n\n${r.url}\n\n` + r.checks.map((c) => `- [${c.ok ? 'x' : ' '}] ${c.name}${c.detail ? ` — ${c.detail}` : ''}`).join('\n') + '\n';
  if (r.issues.length) md += '\n**Notes:**\n\n' + r.issues.map((i) => `- ${i}`).join('\n') + '\n';
  md += `\nScreenshots: ${WIDTHS.map((w) => `\`inbox/reports/screens/${r.slug}-${w}.png\``).join(', ')}\n`;
}
fs.writeFileSync(path.join(REPORTS, `qa-${batch}.md`), md);
console.log(`Report: inbox/reports/qa-${batch}.md`);
process.exit(results.every((r) => r.checks.every((c) => c.ok)) ? 0 : 1);
