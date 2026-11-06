<?php
/**
 * Generic landing page: renders content/pages/{slug}.json section by section.
 * Returns a callable that turns the decoded JSON into the page's <main> element.
 *
 * Only the first "inquiry" section gets the form placeholder (one form per page).
 * When there is one, a popup (<dialog id="vg-inquiry-modal">) is added as well: assets/landing.js
 * moves that same form into the popup when a quote button is clicked and puts it back on close,
 * so the page still has exactly one Contact Form 7 form. Without JavaScript the buttons simply
 * jump to #inquiry.
 */
if (!defined('ABSPATH')) { http_response_code(404); exit; }

return function (array $page) {
    $h = '<main class="vg-landing">';
    $form = null;
    foreach ($page['sections'] ?? array() as $i => $s) {
        if (!is_array($s)) { continue; }
        $type = (string)($s['type'] ?? '');
        $ctx = array('index' => $i, 'form' => $type === 'inquiry' && $form === null);
        if ($ctx['form']) { $form = $s; }
        $h .= vgnyc_section($type, $s, $ctx);
    }
    if ($form !== null) {
        $h .= '<dialog class="vg-modal" id="vg-inquiry-modal" aria-labelledby="vg-modal-title"><div class="vg-modal__panel">'
            . '<button type="button" class="vg-modal__close" aria-label="Close">&times;</button>'
            . '<p class="vg-modal__title" id="vg-modal-title">' . esc_html($form['h2'] ?? '') . '</p>'
            . '<div class="vg-modal__slot"></div></div></dialog>';
    }
    return $h . '</main>';
};
