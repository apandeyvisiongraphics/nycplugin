<?php
/**
 * Section: trust-bar. A slim, continuously scrolling strip (marquee) of short proof points.
 * Fields: items [{title, text, icon?}] (icon: any vgnyc_icon() name).
 * The list is repeated once for a seamless loop; the copy is hidden from screen readers.
 * Hovering pauses it; with "reduce motion" it becomes a static row.
 */
if (!defined('ABSPATH')) { http_response_code(404); exit; }

return function (array $s, array $ctx) {
    $items = '';
    foreach ($s['items'] ?? array() as $item) {
        $items .= '<li><span class="vg-trust__icon">' . vgnyc_icon($item['icon'] ?? 'check', 18) . '</span><span class="vg-trust__text"><strong>' . esc_html($item['title'] ?? '') . '</strong><span>' . esc_html($item['text'] ?? '') . '</span></span></li>';
    }
    return '<section class="' . vgnyc_section_class('vg-trust vg-trust--marquee', $s) . '" data-overflow-ok aria-label="Highlights"><div class="vg-trust__viewport"><div class="vg-trust__track">'
        . '<ul class="vg-trust__list">' . $items . '</ul><ul class="vg-trust__list" aria-hidden="true">' . $items . '</ul>'
        . '</div></div></section>';
};
