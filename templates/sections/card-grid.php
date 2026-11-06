<?php
/**
 * Section: card-grid (image cards). Fields: h2, intro_html?, and either
 * items [{title, html?, image}] or groups [{label, intro_html?, items [...]}].
 * Card titles are H3, or H4 inside a labeled group (the group label is the H3).
 */
if (!defined('ABSPATH')) { http_response_code(404); exit; }

return function (array $s, array $ctx) {
    $cards = function (array $items, $tag) {
        $n = count($items);
        $h = '<div class="vg-grid vg-grid--cards' . ($n === 2 ? ' vg-grid--cols-2' : ($n % 4 === 0 ? ' vg-grid--cols-4' : '')) . '">';
        foreach ($items as $item) {
            $h .= '<article class="vg-card vg-card--media">';
            if (!empty($item['image'])) { $h .= '<figure class="vg-card__media">' . vgnyc_image($item['image'], array('sizes' => '(max-width: 700px) 100vw, (max-width: 1100px) 50vw, 33vw')) . '</figure>'; }
            $h .= '<div class="vg-card__body"><' . $tag . '>' . esc_html($item['title'] ?? '') . '</' . $tag . '>';
            if (!empty($item['html'])) { $h .= '<div class="vg-prose">' . vgnyc_kses($item['html']) . '</div>'; }
            $h .= '</div></article>';
        }
        return $h . '</div>';
    };
    $h = vgnyc_section_open('cards', $s, $ctx) . '<div class="vg-head"><div>';
    if (!empty($s['h2'])) { $h .= '<h2>' . esc_html($s['h2']) . '</h2>'; }
    if (!empty($s['intro_html'])) { $h .= '<div class="vg-prose vg-intro">' . vgnyc_kses($s['intro_html']) . '</div>'; }
    $h .= '</div></div>';
    if (!empty($s['groups'])) {
        foreach ($s['groups'] as $g) {
            $h .= '<div class="vg-group">';
            if (!empty($g['label'])) { $h .= '<h3 class="vg-group__title vg-diamond">' . esc_html($g['label']) . '</h3>'; }
            if (!empty($g['intro_html'])) { $h .= '<div class="vg-prose vg-group__intro">' . vgnyc_kses($g['intro_html']) . '</div>'; }
            $h .= $cards($g['items'] ?? array(), empty($g['label']) ? 'h3' : 'h4') . '</div>';
        }
    } else {
        $h .= $cards($s['items'] ?? array(), 'h3');
    }
    return $h . vgnyc_section_close();
};
