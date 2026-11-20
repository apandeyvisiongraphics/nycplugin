<?php
/**
 * Blog post: renders content/blog/{slug}.json (manifest "template": "blog").
 * Returns a callable that turns the decoded JSON into the page's <main> element.
 *
 * JSON: eyebrow?, h1, date? (YYYY-MM-DD), author?, lead_html?, image? (slot id),
 *       aside_cta? {label, href} (button under the table of contents; #inquiry opens the quote popup), sections [...]
 *   - {type: "article", id, h2, image?, html, variant?: "checklist"}  one heading of the post
 *   - {type: "faq", h2, items [{q, a_html}]}                          questions (also FAQPage JSON-LD)
 *   - {type: "inquiry", h2, intro_html?}                              closing section with the form
 * Built from the writer's doc by scripts/build_blog.py. Shares the landing-page look
 * (sections.css), quote popup and animations (landing.js); blog-only styling is in assets/blog.css.
 */
if (!defined('ABSPATH')) { http_response_code(404); exit; }

return function (array $post) {
    $sections = array_values(array_filter($post['sections'] ?? array(), 'is_array'));

    // Table of contents + reading time (about 220 words a minute).
    $toc = array();
    $words = str_word_count(wp_strip_all_tags((string)($post['lead_html'] ?? '')));
    foreach ($sections as $s) {
        $type = (string)($s['type'] ?? '');
        $words += str_word_count(wp_strip_all_tags((string)($s['html'] ?? '') . ' ' . ($s['intro_html'] ?? '')));
        foreach ($s['items'] ?? array() as $item) { $words += str_word_count(wp_strip_all_tags(($item['q'] ?? '') . ' ' . ($item['a_html'] ?? ''))); }
        if (in_array($type, array('article', 'faq'), true) && !empty($s['h2'])) {
            $toc[] = array('id' => vgnyc_blog_anchor($s), 'label' => (string)$s['h2']);
        }
    }
    $minutes = max(1, (int)ceil($words / 220));

    $h = '<main class="vg-landing vg-blog">';
    $h .= '<div class="vg-blog__progress" aria-hidden="true"><span></span></div>';

    // ---- Article header ----
    $h .= '<header class="wrap vg-blog__head">';
    $h .= '<nav class="vg-blog__crumbs" aria-label="Breadcrumb"><a href="/nyc/">New York</a><span aria-hidden="true">/</span><a href="/nyc/resources/">Resources</a></nav>';
    if (!empty($post['eyebrow'])) { $h .= '<p class="eyebrow"><span></span>' . esc_html(strtoupper($post['eyebrow'])) . '</p>'; }
    $h .= '<h1>' . esc_html($post['h1'] ?? '') . '</h1>';
    if (!empty($post['lead_html'])) { $h .= '<div class="vg-blog__lead vg-prose">' . vgnyc_kses($post['lead_html']) . '</div>'; }
    $h .= '<div class="vg-blog__meta">';
    $h .= '<span class="vg-blog__avatar" aria-hidden="true">VG</span><span class="vg-blog__author">' . esc_html($post['author'] ?? 'Vision Graphics New York') . '</span>';
    $time = !empty($post['date']) ? strtotime($post['date'] . ' 12:00:00 UTC') : false;
    if ($time) { $h .= '<span class="vg-blog__dot" aria-hidden="true"></span><time datetime="' . esc_attr(gmdate('Y-m-d', $time)) . '">' . esc_html(gmdate('F j, Y', $time)) . '</time>'; }
    $h .= '<span class="vg-blog__dot" aria-hidden="true"></span><span>' . vgnyc_icon('clock', 15) . ' ' . $minutes . ' min read</span>';
    $h .= '</div></header>';
    if (!empty($post['image'])) {
        $h .= '<figure class="wrap vg-blog__hero">' . vgnyc_image($post['image'], array('hero' => true, 'sizes' => '(max-width: 1200px) 100vw, 1180px')) . '</figure>';
    }

    // ---- Body: table of contents + article ----
    $h .= '<div class="wrap vg-blog__layout">';
    $h .= '<aside class="vg-blog__aside">';
    if ($toc) {
        $h .= '<nav class="vg-toc" aria-label="On this page"><p class="vg-toc__title">On this page</p><ol>';
        foreach ($toc as $t) { $h .= '<li><a href="#' . esc_attr($t['id']) . '">' . esc_html($t['label']) . '</a></li>'; }
        $h .= '</ol></nav>';
    }
    if (!empty($post['aside_cta']['label'])) {
        $h .= '<a class="btn primary vg-blog__aside-cta" href="' . esc_url($post['aside_cta']['href'] ?? '#inquiry') . '">' . esc_html($post['aside_cta']['label']) . ' ' . vgnyc_icon('arrow-up-right', 17) . '</a>';
    }
    $h .= '</aside><article class="vg-blog__body">';

    $form = null;
    $closing = '';
    foreach ($sections as $i => $s) {
        $type = (string)($s['type'] ?? '');
        if ($type === 'article') {
            $checklist = ($s['variant'] ?? '') === 'checklist';
            $h .= '<section class="vg-post' . ($checklist ? ' vg-post--checklist' : '') . '" id="' . esc_attr(vgnyc_blog_anchor($s)) . '">';
            $h .= '<h2>' . esc_html($s['h2'] ?? '') . '</h2>';
            if (!empty($s['image'])) { $h .= '<figure class="vg-media vg-post__media">' . vgnyc_image($s['image'], array('sizes' => '(max-width: 980px) 100vw, 760px')) . '</figure>'; }
            // [[image:slot]] paragraphs (images the writer placed inside a section) become figures.
            $prose = preg_replace_callback('#<p>\s*\[\[image:(nyc-[a-z0-9-]+)\]\]\s*</p>#', function ($m) {
                return '<figure class="vg-media vg-post__media vg-post__media--inline">' . vgnyc_image($m[1], array('sizes' => '(max-width: 980px) 100vw, 760px')) . '</figure>';
            }, vgnyc_kses($s['html'] ?? ''));
            $h .= '<div class="vg-prose vg-post__prose">' . $prose . '</div></section>';
        } elseif ($type === 'faq') {
            $h .= '<div class="vg-post vg-post--faq" id="' . esc_attr(vgnyc_blog_anchor($s)) . '">' . vgnyc_section('faq', $s, array('index' => $i)) . '</div>';
        } elseif ($type === 'inquiry' && $form === null) {
            // The closing section sits full width under the article and holds the page's one form.
            $form = $s;
            $closing = vgnyc_section('inquiry', $s, array('index' => $i, 'form' => true));
        }
    }
    $h .= '</article></div>';
    $h .= $closing;
    if ($form !== null) { $h .= vgnyc_inquiry_modal($form); }
    return $h . '</main>';
};
