<?php
/**
 * Landing pages (Phase 2): drafts, previews, section rendering and FAQ schema.
 * Page content lives in content/pages/{slug}.json as an ordered list of sections;
 * each section type has a template in templates/sections/{type}.php.
 */
if (!defined('ABSPATH')) { http_response_code(404); exit; }

/** True for a manifest entry that has not been published yet. */
function vgnyc_is_draft($page) {
    return is_array($page) && ($page['status'] ?? '') === 'draft';
}

/** Only administrators may see drafts and previews. */
function vgnyc_can_preview() {
    return is_user_logged_in() && current_user_can('manage_options');
}

/** The draft version of a replaced route (draft_* keys of the manifest entry). */
function vgnyc_draft_version($page) {
    $draft = $page;
    $draft['template'] = $page['draft_template'] ?? 'landing';
    $draft['content'] = $page['draft_content'];
    foreach (array('title', 'description', 'image', 'service') as $key) {
        if (array_key_exists('draft_' . $key, $page)) { $draft[$key] = $page['draft_' . $key]; }
    }
    $draft['preview'] = true;
    return $draft;
}

/** Decoded landing JSON for a manifest entry, or null. */
function vgnyc_landing_data($page) {
    if (($page['template'] ?? '') !== 'landing') { return null; }
    $file = vgnyc_content_file($page);
    $data = $file ? json_decode((string)file_get_contents($file), true) : null;
    return is_array($data) ? $data : null;
}

/** Section types that have a template in templates/sections/. */
function vgnyc_section_types() {
    return array('hero', 'trust-bar', 'rich-text', 'image-text', 'features-grid', 'card-grid', 'process-steps', 'testimonials', 'gallery', 'faq', 'inquiry', 'cta-band');
}

/** Renders one section; unknown types render nothing. */
function vgnyc_section($type, array $section, array $ctx = array()) {
    if (!in_array($type, vgnyc_section_types(), true)) { return ''; }
    static $cache = array();
    if (!isset($cache[$type])) { $cache[$type] = require dirname(__DIR__) . '/templates/sections/' . $type . '.php'; }
    return call_user_func($cache[$type], $section, $ctx);
}

/** Opening <section> classes shared by section templates. */
function vgnyc_section_class($base, array $s, $extra = '') {
    return trim($base . (empty($s['continues']) ? '' : ' vg-continues') . ($extra ? ' ' . $extra : ''));
}

/** FAQPage JSON-LD built from a landing page's FAQ sections. */
function vgnyc_faq_schema($page, $url) {
    $data = vgnyc_landing_data($page);
    if (!$data) { return null; }
    $questions = array();
    foreach ($data['sections'] ?? array() as $s) {
        if (($s['type'] ?? '') !== 'faq') { continue; }
        foreach ($s['items'] ?? array() as $item) {
            $answer = trim(preg_replace('/\s+/', ' ', wp_strip_all_tags((string)($item['a_html'] ?? ''))));
            if (!empty($item['q']) && $answer !== '') {
                $questions[] = array('@type' => 'Question', 'name' => (string)$item['q'], 'acceptedAnswer' => array('@type' => 'Answer', 'text' => $answer));
            }
        }
    }
    return $questions ? array('@type' => 'FAQPage', '@id' => $url . '#faq', 'mainEntity' => $questions) : null;
}
