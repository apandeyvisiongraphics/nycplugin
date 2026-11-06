// Capture HTML, headers and full-page screenshots for every route.
// Usage: node scripts/capture.mjs <outdir> [--no-screens] [--only=slug,slug]
//   outdir is relative to tests/ (e.g. "baseline" or "current").
import fs from 'node:fs';
import path from 'node:path';
import { TESTS, BASE_URL, WIDTHS, targets, pool, load } from './lib.mjs';

const args = process.argv.slice(2);
const outName = args.find((a) => !a.startsWith('--'));
if (!outName) { console.error('usage: capture.mjs <outdir>'); process.exit(2); }
const noScreens = args.includes('--no-screens');
const only = (args.find((a) => a.startsWith('--only=')) || '').slice(7).split(',').filter(Boolean);
const out = path.join(TESTS, outName);
if (outName === 'baseline' && fs.existsSync(path.join(out, 'headers.json')) && !process.env.VGNYC_ALLOW_BASELINE) {
  console.error('Refusing to overwrite tests/baseline (the baseline is the source of truth).'); process.exit(2);
}
fs.mkdirSync(path.join(out, 'html'), { recursive: true });
fs.mkdirSync(path.join(out, 'screens'), { recursive: true });

let list = targets();
if (only.length) list = list.filter((t) => only.includes(t.slug));

// 1. HTML + headers (no redirect following, so Location is visible).
const headersFile = path.join(out, 'headers.json');
const headers = only.length && fs.existsSync(headersFile) ? JSON.parse(fs.readFileSync(headersFile, 'utf8')) : {};
await pool(list, 6, async (t) => {
  const res = await fetch(BASE_URL + t.route, { redirect: 'manual', headers: { 'Cache-Control': 'no-cache' } });
  const body = await res.text();
  headers[t.slug] = {
    route: t.route,
    status: res.status,
    'content-type': res.headers.get('content-type'),
    'x-robots-tag': res.headers.get('x-robots-tag'),
    location: res.headers.get('location'),
  };
  fs.writeFileSync(path.join(out, 'html', t.slug + (t.slug.endsWith('.xml') ? '' : '.html')), body);
});
const sorted = Object.fromEntries(Object.keys(headers).sort().map((k) => [k, headers[k]]));
fs.writeFileSync(headersFile, JSON.stringify(sorted, null, 2) + '\n');
console.log(`html+headers: ${list.length} targets -> tests/${outName}/`);

// 2. Screenshots of every page route at each width.
if (!noScreens) {
  const { chromium } = await load('playwright');
  const browser = await chromium.launch();
  const jobs = list.filter((t) => t.page).flatMap((t) => WIDTHS.map((w) => ({ ...t, w })));
  await pool(jobs, 4, async (j) => {
    const ctx = await browser.newContext({ viewport: { width: j.w, height: 900 }, deviceScaleFactor: 1, reducedMotion: 'reduce' });
    const page = await ctx.newPage();
    await page.goto(BASE_URL + j.route, { waitUntil: 'networkidle', timeout: 60000 });
    await page.addStyleTag({ content: '*,*::before,*::after{animation:none!important;transition:none!important;caret-color:transparent!important;scroll-behavior:auto!important}' });
    // Trigger lazy images, then wait for every image and font.
    await page.evaluate(async () => {
      document.querySelectorAll('img[loading="lazy"]').forEach((i) => { i.loading = 'eager'; });
      for (let y = 0; y < document.body.scrollHeight; y += 600) { window.scrollTo(0, y); await new Promise((r) => setTimeout(r, 20)); }
      window.scrollTo(0, 0);
      await Promise.all(Array.from(document.images).map((i) => (i.complete ? null : new Promise((r) => { i.onload = i.onerror = r; }))));
      await Promise.all(Array.from(document.images).map((i) => i.decode().catch(() => null)));
      await document.fonts.ready;
    });
    await page.waitForLoadState('networkidle');
    // Let layout settle (the sticky .product-aside can land on a different sub-pixel
    // position right after scrolling), then shoot until two consecutive captures agree.
    const settle = () => page.evaluate(() => new Promise((r) => requestAnimationFrame(() => requestAnimationFrame(() => setTimeout(r, 150)))));
    await settle();
    let prev = null; let shot = null;
    for (let n = 0; n < 6; n++) {
      shot = await page.screenshot({ fullPage: true, animations: 'disabled' });
      if (prev && prev.equals(shot)) break;
      prev = shot; await settle();
    }
    fs.writeFileSync(path.join(out, 'screens', `${j.slug}-${j.w}.png`), shot);
    await ctx.close();
  });
  await browser.close();
  console.log(`screens: ${jobs.length} -> tests/${outName}/screens/`);
}
