<?php
/** Section: process-steps. Fields: h2, intro_html?, steps [html], after_html?, image? (slot id). */
if (!defined('ABSPATH')) { http_response_code(404); exit; }

return function (array $s, array $ctx) {
    $img = !empty($s['image']);
    $h = '<section class="' . vgnyc_section_class('wrap section vg-steps', $s, $img ? 'vg-steps--media' : '') . '"><div class="vg-steps__body">';
    if (!empty($s['h2'])) { $h .= '<h2>' . esc_html($s['h2']) . '</h2>'; }
    if (!empty($s['intro_html'])) { $h .= '<div class="vg-prose vg-intro">' . vgnyc_kses($s['intro_html']) . '</div>'; }
    $h .= '<ol class="vg-steps__list">';
    foreach ($s['steps'] ?? array() as $step) { $h .= '<li>' . vgnyc_kses($step) . '</li>'; }
    $h .= '</ol>';
    if (!empty($s['after_html'])) { $h .= '<div class="vg-prose vg-steps__after">' . vgnyc_kses($s['after_html']) . '</div>'; }
    $h .= '</div>';
    if ($img) { $h .= '<figure class="vg-media">' . vgnyc_image($s['image']) . '</figure>'; }
    return $h . '</section>';
};
