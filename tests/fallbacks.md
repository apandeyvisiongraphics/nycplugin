None. Every page with the shared hero + article-layout structure (65) round-trips byte for byte through `templates/service-page.php` (checked by `scripts/roundtrip.php` with WordPress loaded) and passes compare. `scripts/raw-overrides.txt` is empty.

The 13 `raw` pages are raw by design, not fallbacks: their layouts are unique (home, contact, projects, 3 project stories, resources, gallery + 5 gallery categories).

One root-cause fix was needed during extraction: `wp_kses_post` drops `srcset`/`sizes` on `<img>` and strips inline `<svg>` icons, which broke the 11 pages with `extra_sections`. `vgnyc_kses()` now uses the post allowlist plus exactly those attributes/elements, so the original markup is kept.
