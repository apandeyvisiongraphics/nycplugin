// Shared helpers for the dev-only capture/compare tools.
// Node dependencies live in tests/node_modules (see tests/package.json).
import { createRequire } from 'node:module';
import { execFileSync } from 'node:child_process';
import { fileURLToPath, pathToFileURL } from 'node:url';
import path from 'node:path';

export const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
export const TESTS = path.join(ROOT, 'tests');
export const BASE_URL = process.env.VGNYC_BASE_URL || 'http://pandeystore.local';
export const WIDTHS = [1440, 390];

const req = createRequire(path.join(TESTS, 'package.json'));
export const load = async (name) => {
  try { return req(name); } catch (e) { return import(pathToFileURL(req.resolve(name)).href); }
};

/** Manifest routes, read through PHP so the result matches what the plugin sees. */
export function manifest() {
  const php = `define('ABSPATH', 1); echo json_encode(require ${JSON.stringify(path.join(ROOT, 'data/manifest.php'))});`;
  return JSON.parse(execFileSync('php', ['-r', php], { encoding: 'utf8' }));
}

export function slugFor(route) {
  const s = route.replace(/^\/nyc\/?/, '').replace(/\/$/, '');
  return s === '' ? 'home' : s.replace(/\//g, '--');
}

/** Every URL that is captured: manifest routes plus the special checks. */
export function targets() {
  const pages = manifest();
  const list = Object.keys(pages).map((route) => ({ route, slug: slugFor(route), page: true, template: pages[route].template || 'legacy' }));
  list.push({ route: '/nyc/sitemap.xml', slug: 'sitemap.xml', page: false, template: '-' });
  list.push({ route: '/nyc/this-page-does-not-exist/', slug: 'this-page-does-not-exist', page: false, template: '-' });
  list.push({ route: '/nyc/request-quote/', slug: 'request-quote', page: false, template: '-' });
  return list;
}

export async function pool(items, size, fn) {
  const out = new Array(items.length); let i = 0;
  await Promise.all(Array.from({ length: size }, async () => {
    while (i < items.length) { const n = i++; out[n] = await fn(items[n], n); }
  }));
  return out;
}
