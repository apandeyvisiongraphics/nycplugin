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

/** Decoded landing or blog JSON for a manifest entry, or null. */
function vgnyc_landing_data($page) {
    if (!in_array($page['template'] ?? '', array('landing', 'blog'), true)) { return null; }
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

/**
 * Quote popup: the <dialog> that assets/landing.js moves the page's one form into when a quote
 * button is clicked (and back on close). Used by landing pages and blog posts.
 */
function vgnyc_inquiry_modal(array $form) {
    return '<dialog class="vg-modal" id="vg-inquiry-modal" aria-labelledby="vg-modal-title"><div class="vg-modal__panel">'
        . '<button type="button" class="vg-modal__close" aria-label="Close">&times;</button>'
        . '<p class="vg-modal__title" id="vg-modal-title">' . esc_html($form['h2'] ?? '') . '</p>'
        . '<div class="vg-modal__slot"></div></div></dialog>';
}

/** Anchor id of a blog section (its "id", or a slug of its heading). */
function vgnyc_blog_anchor(array $s) {
    $id = sanitize_title((string)($s['id'] ?? ''));
    return $id !== '' ? $id : sanitize_title((string)($s['h2'] ?? 'section'));
}

/** BlogPosting JSON-LD for a "blog" manifest entry, or null. */
function vgnyc_blog_schema($page, $url, $org) {
    if (($page['template'] ?? '') !== 'blog') { return null; }
    $data = vgnyc_landing_data($page);
    if (!$data) { return null; }
    $node = array('@type' => 'BlogPosting', '@id' => $url . '#article', 'headline' => (string)($data['h1'] ?? ''), 'description' => (string)($page['description'] ?? ''),
        'mainEntityOfPage' => array('@id' => $url . '#webpage'), 'author' => array('@id' => $org), 'publisher' => array('@id' => $org), 'inLanguage' => 'en-US');
    if (!empty($data['date']) && strtotime($data['date'])) {
        $node['datePublished'] = gmdate('Y-m-d', strtotime($data['date']));
        $node['dateModified'] = !empty($data['updated']) && strtotime($data['updated']) ? gmdate('Y-m-d', strtotime($data['updated'])) : $node['datePublished'];
    }
    $image = vgnyc_image_url($data['image'] ?? '');
    if ($image) { $node['image'] = $image; }
    return $node;
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
