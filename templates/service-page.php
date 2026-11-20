<?php
/**
 * Shared layout for service pages (content/services/{slug}.json).
 * Returns a callable that turns the decoded JSON into the page's <main> element.
 *
 * Output is written without whitespace between tags so it matches the
 * original markup byte for byte. Plain-text fields are escaped with esc_html /
 * esc_attr / esc_url; *_html fields (and "html" / extra_sections) go through
 * vgnyc_kses(), which keeps the original markup including the inline SVG icons.
 */
if (!defined('ABSPATH')) { http_response_code(404); exit; }

return function (array $c) {
    $t = function ($v) { return esc_html((string)$v); };
    $a = function ($v) { return esc_attr((string)$v); };
    $u = function ($v) { return esc_url((string)$v); };

    // Hero
    $img = $c['hero_image'];
    $sizes = array_map('intval', $img['sizes']);
    $src = '/nyc/assets/' . $img['base'] . '-' . max($sizes) . '.webp';
    $srcset = implode(', ', array_map(function ($w) use ($img) { return '/nyc/assets/' . $img['base'] . '-' . $w . '.webp ' . $w . 'w'; }, $sizes));

    $h = '<main><section class="wrap product-hero"><div>';
    $h .= '<p class="breadcrumb"><a href="/nyc/">New York</a> / ' . $t($c['breadcrumb']) . '</p>';
    $h .= '<p class="eyebrow">' . $t($c['eyebrow']) . '</p>';
    $h .= '<h1>' . $t($c['h1']) . '</h1>';
    $h .= '<p class="lead">' . $t($c['lead']) . '</p>';
    if (isset($c['review_note'])) { $h .= '<p class="review-note">' . $t($c['review_note']) . '</p>'; }
    $h .= '<div class="hero-actions">';
    $h .= '<a class="btn primary" href="' . $u($c['primary_cta']['href']) . '">' . $t($c['primary_cta']['label']) . ' ' . vgnyc_icon('arrow-up-right', 17) . '</a>';
    $h .= '<a class="text-link" href="' . $u($c['secondary_cta']['href']) . '">' . $t($c['secondary_cta']['label']) . ' ' . vgnyc_icon('arrow-right', 16) . '</a>';
    $h .= '</div></div>';
    $h .= '<figure class="project-image product-photo"><img src="' . $a($src) . '" srcset="' . $a($srcset) . '" sizes="(max-width: 700px) 100vw, 50vw" alt="' . $a($img['alt']) . '" loading="lazy"><figcaption>' . $t($img['caption']) . '</figcaption></figure>';
    $h .= '</section>';

    // Optional product family grid (hub pages)
    if (!empty($c['product_family'])) {
        $h .= '<section class="wrap product-family"><h2>' . $t($c['product_family']['h2']) . '</h2><div class="product-links">';
        foreach ($c['product_family']['links'] as $l) { $h .= '<a href="' . $u($l['href']) . '">' . $t($l['label']) . vgnyc_icon('arrow-up-right', 16) . '</a>'; }
        $h .= '</div></section>';
    }

    // Article
    $h .= '<section class="wrap section article-layout"><article>';
    foreach ($c['sections'] as $s) {
        $id = empty($s['id']) ? '' : ' id="' . $a($s['id']) . '"';
        $h .= '<section' . $id . '>';
        if (!empty($s['eyebrow'])) { $h .= '<p class="eyebrow">' . $t($s['eyebrow']) . '</p>'; }
        $h .= '<h2>' . $t($s['h2']) . '</h2>';
        if ($s['type'] === 'text') {
            $h .= vgnyc_kses($s['html']);
        } elseif ($s['type'] === 'options') {
            $h .= '<div class="options-grid">';
            foreach ($s['items'] as $i) { $h .= '<div><h3>' . $t($i['h3']) . '</h3><p>' . $t($i['p']) . '</p></div>'; }
            $h .= '</div>';
        } elseif ($s['type'] === 'checklist') {
            $h .= '<ul class="check-list">';
            foreach ($s['items'] as $i) { $h .= '<li>' . vgnyc_icon('check', 18) . $t($i) . '</li>'; }
            $h .= '</ul>';
        }
        $h .= '</section>';
    }
    if (!empty($c['faqs'])) {
        $h .= '<section class="faqs"><h2>' . $t($c['faqs']['h2']) . '</h2>';
        foreach ($c['faqs']['items'] as $f) { $h .= '<details><summary>' . $t($f['q']) . '<span>+</span></summary>' . vgnyc_kses($f['a_html']) . '</details>'; }
        $h .= '</section>';
    }
    $h .= '</article>';

    // Sidebar
    $h .= '<aside class="product-aside"><p class="eyebrow">LET’S START WITH THE DETAILS</p><h3>Have a space in mind?</h3><p>A photo, a few measurements and your project ZIP help us ask the right questions.</p>';
    $h .= '<a href="/nyc/contact/?photo=1" class="btn dark">' . vgnyc_icon('camera', 17) . ' Send a project photo</a><hr><h3>Related possibilities</h3>';
    foreach ($c['related'] as $l) { $h .= '<a href="' . $u($l['href']) . '">' . $t($l['label']) . vgnyc_icon('arrow-up-right', 15) . '</a>'; }
    $h .= '</aside></section>';

    // Closing call to action
    if (!empty($c['cta_band'])) {
        $h .= '<section class="cta-band wrap"><div><p class="eyebrow">START WITH YOUR SPACE</p><h2>A photo is a great place to start.</h2><p>Send your idea, approximate dimensions and project ZIP. We’ll use them to shape the conversation.</p></div>';
        $h .= '<a class="btn primary" href="/nyc/contact/?photo=1">' . vgnyc_icon('camera', 18) . ' Send a project photo ' . vgnyc_icon('arrow-up-right', 18) . '</a></section>';
    }

    // Page-specific blocks kept as HTML (soft-section, portfolio-section, landing-conversion)
    foreach ($c['extra_sections'] ?? array() as $x) { $h .= vgnyc_kses($x); }

    return $h . '</main>';
};
