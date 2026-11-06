<?php
/**
 * Plugin Name: Vision Graphics NYC Website
 * Description: Customer-facing New York signs and graphics website at /nyc/, using your existing WordPress inquiry form.
 * Version: 1.2.0
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Author: Vision Graphics
 */
if (!defined('ABSPATH')) { http_response_code(404); exit; }

function vgnyc_options() {
    // Previous versions saved a private-preview mode. It is deliberately ignored:
    // every customer page is available without a login in this customer-only release.
    $saved = get_option('vgnyc_settings', array());
    $saved = is_array($saved) ? $saved : array();
    return array('form' => isset($saved['form']) ? trim((string)$saved['form']) : '', 'privacy' => isset($saved['privacy']) ? (string)$saved['privacy'] : get_privacy_policy_url());
}
function vgnyc_form_valid($shortcode) {
    return preg_match('/^\[([a-zA-Z0-9_-]+)(?:\s[^\r\n]*)?\]$/D', trim($shortcode), $m) && shortcode_exists($m[1]);
}
function vgnyc_privacy_valid($url) {
    $host = wp_parse_url(home_url('/'), PHP_URL_HOST);
    $path = wp_parse_url($url, PHP_URL_PATH);
    return wp_parse_url($url, PHP_URL_SCHEME) === 'https' && wp_parse_url($url, PHP_URL_HOST) === $host && $path && $path !== '/' && !preg_match('#^/nyc(?:/|$)#i', $path);
}
function vgnyc_sanitize($value) {
    $value = is_array($value) ? $value : array();
    $out = array('form' => trim((string)($value['form'] ?? '')), 'privacy' => esc_url_raw($value['privacy'] ?? ''));
    if (strlen($out['form']) > 1500 || preg_match('/[<>]/', $out['form'])) { $out['form'] = ''; }
    if ($out['form'] && !vgnyc_form_valid($out['form'])) { add_settings_error('vgnyc_settings', 'form', 'The form shortcode was not found. Activate the existing form plugin and check its shortcode.'); }
    if ($out['privacy'] && !vgnyc_privacy_valid($out['privacy'])) { add_settings_error('vgnyc_settings', 'privacy', 'Choose your existing HTTPS privacy-policy URL on this website, outside /nyc/.'); }
    return $out;
}
add_action('admin_init', function () {
    register_setting('vgnyc_settings_group', 'vgnyc_settings', array('sanitize_callback' => 'vgnyc_sanitize', 'default' => array()));
});
add_action('admin_menu', function () {
    add_options_page('Vision Graphics NYC', 'Vision Graphics NYC', 'manage_options', 'vgnyc', 'vgnyc_settings_page');
});
function vgnyc_settings_page() {
    if (!current_user_can('manage_options')) { return; }
    $o = vgnyc_options(); ?>
    <div class="wrap"><h1>Vision Graphics NYC</h1>
    <p>The customer website is available at <a href="<?php echo esc_url(home_url('/nyc/')); ?>" target="_blank" rel="noopener">/nyc/</a>. Visitors do not need an account.</p>
    <?php settings_errors('vgnyc_settings'); ?>
    <form method="post" action="options.php"><?php settings_fields('vgnyc_settings_group'); ?>
    <table class="form-table">
    <tr><th><label for="vgnyc-form">Inquiry form shortcode</label></th><td><input id="vgnyc-form" class="large-text" name="vgnyc_settings[form]" value="<?php echo esc_attr($o['form']); ?>"><p class="description">Paste the shortcode from your existing WordPress form plugin. Configure recipients, saved entries and spam protection in that plugin. See FORM_SETUP.txt.</p></td></tr>
    <tr><th><label for="vgnyc-privacy">Privacy policy</label></th><td><input id="vgnyc-privacy" type="url" class="large-text" name="vgnyc_settings[privacy]" value="<?php echo esc_attr($o['privacy']); ?>"><p class="description">Use the existing HTTPS privacy policy on this website, outside /nyc/.</p></td></tr>
    </table><?php submit_button('Save settings'); ?></form>
    <p><strong>Form:</strong> <?php echo vgnyc_form_valid($o['form']) ? 'Shortcode found. Confirm a saved submission and delivery to both recipients.' : 'Not connected. Customer pages display the business email and phone until it is connected.'; ?></p>
    <p><strong>Privacy:</strong> <?php echo vgnyc_privacy_valid($o['privacy']) ? 'Privacy-policy link configured.' : 'Configure the approved privacy-policy URL.'; ?></p>
    <p><strong>Search engines:</strong> <?php echo (string)get_option('blog_public') === '1' ? 'WordPress permits indexing.' : 'WordPress currently discourages search engines.'; ?> The NYC sitemap is at <code>/nyc/sitemap.xml</code>. It also registers with Yoast or the standard WordPress sitemap.</p>
    <p>Add <code>/nyc/</code> to the main navigation, or use <code>[vg_nyc_link]</code> on relevant VG pages. Existing main-site content is preserved.</p></div>
<?php }

function vgnyc_pages() {
    static $pages;
    if ($pages === null) { $pages = require __DIR__ . '/data/manifest.php'; }
    return $pages;
}
function vgnyc_is_path($path) { return (bool)preg_match('#^/nyc(?:/|$)#i', $path); }




// NYC-only Google Tag Manager (vgnyc_render() only ever runs on /nyc/ URLs)
if (!defined('VGNYC_GTM_ID')) { define('VGNYC_GTM_ID', 'GTM-KC2ZRJ69'); }
function vgnyc_gtm_head() { ?>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','<?php echo esc_js(VGNYC_GTM_ID); ?>');</script>
<!-- End Google Tag Manager -->
<?php }
function vgnyc_gtm_body() { ?>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr(VGNYC_GTM_ID); ?>"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<?php }








function vgnyc_indexable() {
    return (string)get_option('blog_public') === '1';
}
function vgnyc_public_routes() {
    // Both sitemap integrations use the exact same public allowlist.
    return array_filter(vgnyc_pages(), function ($page, $route) {
        return preg_match('#^/nyc/(?:[a-z0-9-]+/)*$#D', $route) && !preg_match('#^/nyc/(?:team|api|test|privacy|thank-you|request-quote|meet-ed)(?:/|$)#', $route) && empty($page['noindex']);
    }, ARRAY_FILTER_USE_BOTH);
}
add_filter('wpseo_sitemap_index', function ($xml) {
    if (!vgnyc_indexable()) { return $xml; }
    $url = esc_xml(home_url('/nyc/sitemap.xml'));
    return strpos($xml, $url) === false ? $xml . '<sitemap><loc>' . $url . '</loc></sitemap>' : $xml;
});
add_action('init', function () {
    if (!vgnyc_indexable() || defined('WPSEO_VERSION') || !class_exists('WP_Sitemaps_Provider') || !function_exists('wp_register_sitemap_provider')) { return; }
    $provider = new class extends WP_Sitemaps_Provider {
        public function __construct() { $this->name = 'vgnyc'; $this->object_type = 'vgnyc'; }
        public function get_url_list($page_num, $object_subtype = '') {
            if (!vgnyc_indexable() || (int)$page_num !== 1) { return array(); }
            return array_map(function ($route) { return array('loc' => home_url($route)); }, array_keys(vgnyc_public_routes()));
        }
        public function get_max_num_pages($object_subtype = '') { return vgnyc_indexable() && count(vgnyc_public_routes()) ? 1 : 0; }
        public function get_sitemap_url($page_num, $object_subtype = '') { return home_url('/nyc/sitemap.xml'); }
    };
    wp_register_sitemap_provider('vgnyc', $provider);
}, 20);
add_shortcode('vg_nyc_link', function ($atts) {
    $a = shortcode_atts(array('label' => 'Custom signs & graphics in New York'), $atts, 'vg_nyc_link');
    return '<a class="vg-nyc-link" href="' . esc_url(home_url('/nyc/')) . '">' . esc_html($a['label']) . '</a>';
});
// Mark these virtual routes before common page-cache plugins render the page.
add_action('init', function () {
    $path = wp_parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if ($path && vgnyc_is_path($path)) {
        if (!defined('DONOTCACHEPAGE')) { define('DONOTCACHEPAGE', true); }
        add_filter('show_admin_bar', '__return_false');
    }
}, 0);

function vgnyc_json($data) { return wp_json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES); }
function vgnyc_schema($path, $page) {
    $url = home_url($path); $root = home_url('/nyc/'); $org = home_url('/#organization');
    $name = trim(explode('|', $page['title'])[0]);
    $crumbs = array(array('@type' => 'ListItem', 'position' => 1, 'name' => 'Vision Graphics', 'item' => home_url('/')), array('@type' => 'ListItem', 'position' => 2, 'name' => 'New York', 'item' => $root));
    if ($path !== '/nyc/') {
        $parts = explode('/', trim($path, '/'));
        if (count($parts) > 2) {
            $parent = '/nyc/' . $parts[1] . '/'; $pages = vgnyc_pages();
            if (isset($pages[$parent])) { $crumbs[] = array('@type' => 'ListItem', 'position' => count($crumbs) + 1, 'name' => trim(explode('|', $pages[$parent]['title'])[0]), 'item' => home_url($parent)); }
        }
        $crumbs[] = array('@type' => 'ListItem', 'position' => count($crumbs) + 1, 'name' => $name, 'item' => $url);
    }
    $graph = array(
        array('@type' => 'Organization', '@id' => $org, 'name' => 'Vision Graphics', 'url' => home_url('/'), 'contactPoint' => array('@type' => 'ContactPoint', 'contactType' => 'New York sales', 'telephone' => '+1-949-878-1336', 'email' => 'ekerins@visiondoesit.com', 'areaServed' => array('Brooklyn, New York', 'Manhattan, New York'))),
        array('@type' => 'WebSite', '@id' => $root . '#website', 'name' => 'Vision Graphics | New York', 'url' => $root, 'publisher' => array('@id' => $org), 'inLanguage' => 'en-US'),
        array('@type' => 'WebPage', '@id' => $url . '#webpage', 'url' => $url, 'name' => $page['title'], 'description' => $page['description'], 'isPartOf' => array('@id' => $root . '#website'), 'about' => array('@id' => $org), 'breadcrumb' => array('@id' => $url . '#breadcrumb'), 'inLanguage' => 'en-US'),
        array('@type' => 'BreadcrumbList', '@id' => $url . '#breadcrumb', 'itemListElement' => $crumbs)
    );
    $has_service = array_key_exists('service', $page) ? (bool)$page['service'] : !preg_match('#^/nyc/(?:contact|about|about-us|gallery|resources|projects|privacy|service-areas)(?:/|$)#', $path);
    if ($has_service) {
        $graph[] = array('@type' => 'Service', '@id' => $url . '#service', 'name' => $name, 'description' => $page['description'], 'url' => $url, 'provider' => array('@id' => $org), 'areaServed' => array(array('@type' => 'AdministrativeArea', 'name' => 'Brooklyn, New York'), array('@type' => 'AdministrativeArea', 'name' => 'Manhattan, New York')));
        $graph[2]['mainEntity'] = array('@id' => $url . '#service');
    }
    return array('@context' => 'https://schema.org', '@graph' => $graph);
}
function vgnyc_filter_theme_schema($value, $owned_ids) {
    if (!is_array($value)) { return $value; }
    if (isset($value['@graph'])) {
        $value['@graph'] = array_values(array_filter(array_map(function ($node) use ($owned_ids) { return vgnyc_filter_theme_schema($node, $owned_ids); }, (array)$value['@graph']), function ($node) { return $node !== null; }));
        return $value['@graph'] ? $value : null;
    }
    if (array_keys($value) === range(0, count($value) - 1)) {
        return array_values(array_filter(array_map(function ($node) use ($owned_ids) { return vgnyc_filter_theme_schema($node, $owned_ids); }, $value), function ($node) { return $node !== null; }));
    }
    // Virtual NYC routes have their own page graph. Keep unrelated site-wide
    // entities (e.g. Organization with another ID), remove only page-scoped
    // entities from the underlying WordPress query and IDs replaced above.
    $types = (array)($value['@type'] ?? array());
    if (in_array($value['@id'] ?? '', $owned_ids, true) || array_intersect($types, array('WebPage', 'CollectionPage', 'ItemPage', 'AboutPage', 'ContactPage', 'SearchResultsPage', 'BreadcrumbList', 'Article', 'BlogPosting', 'NewsArticle', 'Product', 'Service', 'FAQPage'))) { return null; }
    return $value;
}
function vgnyc_head_clean($head, $graph = array()) {
    $head = preg_replace('#<title\b[^>]*>.*?</title\s*>#is', '', $head);
    $owned_ids = array_column($graph['@graph'] ?? array(), '@id');
    $head = preg_replace_callback('#<script\b[^>]*type=[\x22\x27]application/ld\+json[\x22\x27][^>]*>(.*?)</script\s*>#is', function ($m) use ($owned_ids) {
        $data = json_decode($m[1], true);
        if (json_last_error() !== JSON_ERROR_NONE) { return $m[0]; }
        $data = vgnyc_filter_theme_schema($data, $owned_ids);
        return $data ? '<script type="application/ld+json">' . vgnyc_json($data) . '</script>' : '';
    }, $head);
    return preg_replace_callback('#<(?:meta|link)\b[^>]*>#i', function ($m) {
        if (preg_match('/\b(?:name|property)\s*=\s*[\x22\x27](?:description|robots|googlebot|bingbot|og:[^\x22\x27]*|twitter:[^\x22\x27]*)[\x22\x27]/i', $m[0]) || preg_match('/\brel\s*=\s*[\x22\x27]canonical[\x22\x27]/i', $m[0])) { return ''; }
        return $m[0];
    }, $head);
}
function vgnyc_render($path, $page, $o) {
    global $wp_query;
    if (isset($wp_query)) { $wp_query->is_404 = false; $wp_query->is_home = false; }
    status_header(200);
    header('Content-Type: text/html; charset=UTF-8');
    $public = (string)get_option('blog_public') === '1';
    header('X-Robots-Tag: ' . ($public ? 'index, follow' : 'noindex, nofollow'));
    $body = require __DIR__ . '/data/' . $page['file'];
    $form = '';
    if (strpos($body, '<!-- VGNYC_FORM -->') !== false) {
        if (vgnyc_form_valid($o['form'])) {
            // Some form plugins only enqueue their assets when their shortcode runs.
            $form = do_shortcode($o['form']);
            if (function_exists('wpcf7_enqueue_scripts')) { wpcf7_enqueue_scripts(); }
            if (function_exists('wpcf7_enqueue_styles')) { wpcf7_enqueue_styles(); }
        }
        if (stripos($form, '<form') === false) {
            $form = '<p>Please email <a href="mailto:ekerins@visiondoesit.com">ekerins@visiondoesit.com</a> or call <a href="tel:+19498781336">(949) 878-1336</a> to discuss your project.</p>';
        }
        $body = str_replace('<!-- VGNYC_FORM -->', '<div class="vgnyc-form">' . $form . '</div>', $body);
    }
    $body = str_replace('<!-- VGNYC_YEAR -->', gmdate('Y'), $body);
    $body = str_replace('/nyc/assets/', esc_url(plugins_url('assets/', __FILE__)), $body);
    $canonical = home_url($path);
    $graph = vgnyc_schema($path, $page);
    ob_start(); wp_head(); $head = vgnyc_head_clean(ob_get_clean(), $graph);
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    vgnyc_gtm_head();
    echo '<meta name="google-site-verification" content="R9wezSQ6II61TIMtdpQVqR7N7JIj7_hpwxM_aSZnvQA">';

    echo $head;
    echo '<title>' . esc_html($page['title']) . '</title><meta name="description" content="' . esc_attr($page['description']) . '">';
    echo '<meta name="robots" content="' . ($public ? 'index, follow' : 'noindex, nofollow') . '"><link rel="canonical" href="' . esc_url($canonical) . '">';
    echo '<meta property="og:type" content="website"><meta property="og:title" content="' . esc_attr($page['title']) . '"><meta property="og:description" content="' . esc_attr($page['description']) . '"><meta property="og:url" content="' . esc_url($canonical) . '">';
    $og_image = !empty($page['image']) && strpos($page['image'], '/nyc/assets/') === 0 ? plugins_url('assets/' . substr($page['image'], strlen('/nyc/assets/')), __FILE__) : plugins_url('assets/mcs-new-home-hero-channel-letters-1600.webp', __FILE__);
    echo '<meta property="og:image" content="' . esc_url($og_image) . '">';
    echo '<script type="application/ld+json" id="vgnyc-schema">' . vgnyc_json($graph) . '</script>';
    echo '<link rel="stylesheet" href="' . esc_url(plugins_url('assets/public.css', __FILE__)) . '?ver=1.2.0"><link rel="stylesheet" href="' . esc_url(plugins_url('assets/wordpress.css', __FILE__)) . '?ver=1.2.0">';
    echo '</head><body class="vgnyc-site">';
    vgnyc_gtm_body();
    wp_body_open();
    echo $body;
    echo '<script src="' . esc_url(plugins_url('assets/website.js', __FILE__)) . '?ver=1.2.0" defer></script>';
    wp_footer();
    echo '</body></html>';
}
add_action('template_redirect', function () {
    $uri = wp_unslash($_SERVER['REQUEST_URI'] ?? '/');
    $raw = wp_parse_url($uri, PHP_URL_PATH);
    if (!$raw || !vgnyc_is_path($raw)) { return; }
    nocache_headers();
    header('X-Robots-Tag: noindex, nofollow');
    $o = vgnyc_options();
    if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', array('GET', 'HEAD', 'POST'), true)) {
        status_header(405); header('Allow: GET, HEAD, POST'); exit;
    }
    $path = strtolower($raw);
    if ($path !== '/nyc/sitemap.xml') { $path = trailingslashit($path); }
    $query = wp_parse_url($uri, PHP_URL_QUERY);
    $suffix = $query ? '?' . $query : '';
    if ($path !== $raw) { wp_safe_redirect(home_url($path) . $suffix, 301); exit; }
    if (in_array($path, array('/nyc/request-quote/', '/nyc/meet-ed/'), true)) { wp_safe_redirect(home_url('/nyc/contact/') . $suffix, 301); exit; }
    if ($path === '/nyc/privacy/') {
        if (vgnyc_privacy_valid($o['privacy'])) { wp_safe_redirect($o['privacy'], 302); exit; }
        status_header(503); echo 'Privacy information is being prepared. Please contact ekerins@visiondoesit.com.'; exit;
    }
    $pages = vgnyc_pages();
    if ($path === '/nyc/sitemap.xml') {
        status_header(200); header('Content-Type: application/xml; charset=UTF-8');
        echo '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        if (vgnyc_indexable()) {
            foreach (vgnyc_public_routes() as $route => $page) { echo '<url><loc>' . esc_xml(home_url($route)) . '</loc></url>'; }
        }
        echo '</urlset>'; exit;
    }
    if (!isset($pages[$path])) { status_header(404); echo 'Not found.'; exit; }
    vgnyc_render($path, $pages[$path], $o); exit;
}, -100);