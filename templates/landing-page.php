<?php
/**
 * Generic landing page: renders content/pages/{slug}.json section by section.
 * Returns a callable that turns the decoded JSON into the page's <main> element.
 *
 * Band tones follow one pattern on every page: process steps and the closing
 * inquiry section are dark; other sections alternate light / white; a section
 * marked "continues" keeps the tone of the one before it.
 * Only the first "inquiry" section gets the form placeholder (one form per page).
 */
if (!defined('ABSPATH')) { http_response_code(404); exit; }

return function (array $page) {
    $h = '<main class="vg-landing">';
    $form = false;
    $tone = 'white';
    $alt = 'light';
    foreach ($page['sections'] ?? array() as $i => $s) {
        if (!is_array($s)) { continue; }
        $type = (string)($s['type'] ?? '');
        if (in_array($type, array('hero', 'trust-bar'), true)) {
            $tone = 'white';
        } elseif (empty($s['continues'])) {
            if (in_array($type, array('process-steps', 'inquiry'), true)) {
                $tone = 'dark';
            } else {
                $tone = $alt;
                $alt = $alt === 'light' ? 'white' : 'light';
            }
        }
        $ctx = array('index' => $i, 'tone' => $tone, 'form' => $type === 'inquiry' && !$form);
        if ($type === 'inquiry') { $form = true; }
        $h .= vgnyc_section($type, $s, $ctx);
    }
    return $h . '</main>';
};
