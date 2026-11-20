<?php
/**
 * Section: features-grid. Fields: h2, intro_html?, items_heading?, image? (slot id, shown beside the intro),
 * images? (2 slot ids, shown as a pair beside the intro), after_html? (below the cards, e.g. a table), and either items [{title, html?, image?}]
 * or groups [{label, intro_html?, image?, items [...]}] (group label H3 with its image beside it, card titles H4).
 */
if (!defined('ABSPATH')) { http_response_code(404); exit; }

return function (array $s, array $ctx) {
    $grid = function (array $items, $tag) {
        $n = count($items);
        // 4 columns when the count divides by 4, and for 7 (4 + 3 instead of a lone card on a third row).
        $h = '<div class="vg-grid' . ($n === 2 ? ' vg-grid--cols-2' : ($n % 4 === 0 || $n === 7 ? ' vg-grid--cols-4' : '')) . '">';
        foreach ($items as $item) {
            $h .= '<div class="vg-card">';
            if (!empty($item['image'])) { $h .= '<figure class="vg-card__media">' . vgnyc_image($item['image'], array('sizes' => '(max-width: 700px) 100vw, 33vw')) . '</figure>'; }
            $h .= '<' . $tag . '>' . esc_html($item['title'] ?? '') . '</' . $tag . '>';
            if (!empty($item['html'])) { $h .= '<div class="vg-prose">' . vgnyc_kses($item['html']) . '</div>'; }
            $h .= '</div>';
        }
        return $h . '</div>';
    };
    $pair = !empty($s['images']) && is_array($s['images']);
    $img  = !empty($s['image']) || $pair;
    $h = '<section class="' . vgnyc_section_class('wrap section vg-features', $s) . '"><div class="vg-head' . ($img ? ' vg-head--media' : '') . '"><div>';
    if (!empty($s['h2'])) { $h .= '<h2>' . esc_html($s['h2']) . '</h2>'; }
    if (!empty($s['intro_html'])) { $h .= '<div class="vg-prose vg-intro">' . vgnyc_kses($s['intro_html']) . '</div>'; }
    $h .= '</div>';
    if ($pair) {
        $h .= '<div class="vg-media vg-media--pair">';
        foreach ($s['images'] as $slot) { $h .= '<figure>' . vgnyc_image($slot, array('sizes' => '(max-width: 700px) 50vw, 25vw')) . '</figure>'; }
        $h .= '</div>';
    } elseif ($img) {
        $h .= '<figure class="vg-media">' . vgnyc_image($s['image']) . '</figure>';
    }
    $h .= '</div>';
    if (!empty($s['items_heading'])) { $h .= '<p class="vg-subhead">' . esc_html($s['items_heading']) . '</p>'; }
    if (!empty($s['groups'])) {
        foreach ($s['groups'] as $g) {
            $gimg = !empty($g['image']);
            $h .= '<div class="vg-group vg-group--features"><div class="vg-group__head' . ($gimg ? ' vg-head--media' : '') . '"><div>';
            if (!empty($g['label'])) { $h .= '<h3 class="vg-group__title">' . esc_html($g['label']) . '</h3>'; }
            if (!empty($g['intro_html'])) { $h .= '<div class="vg-prose vg-group__intro">' . vgnyc_kses($g['intro_html']) . '</div>'; }
            $h .= '</div>';
            if ($gimg) { $h .= '<figure class="vg-media">' . vgnyc_image($g['image']) . '</figure>'; }
            $h .= '</div>';
            if (!empty($g['items'])) { $h .= $grid($g['items'], empty($g['label']) ? 'h3' : 'h4'); }
            $h .= '</div>';
        }
    } else {
        $h .= $grid($s['items'] ?? array(), 'h3');
    }
    if (!empty($s['after_html'])) { $h .= '<div class="vg-prose vg-features__after">' . vgnyc_kses($s['after_html']) . '</div>'; }
    return $h . '</section>';
};
