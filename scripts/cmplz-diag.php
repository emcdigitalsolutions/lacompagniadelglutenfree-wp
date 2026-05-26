<?php
/**
 * Diagnostica perche il banner Complianz non viene renderizzato al frontend.
 */
echo "is_admin(): " . (is_admin() ? 'YES' : 'NO') . "\n";
echo "wp_doing_cron(): " . (wp_doing_cron() ? 'YES' : 'NO') . "\n";
echo "is CLI: " . (defined('WP_CLI') ? 'YES' : 'NO') . "\n";
echo "---\n";

echo "wizard_completed_once option: ";
var_dump(get_option('cmplz_wizard_completed_once'));

echo "default_banner_id: " . cmplz_get_default_banner_id() . "\n";

// Banner_loader stato
if (class_exists('cmplz_banner_loader')) {
    $loader = cmplz_banner_loader::this();
    if ($loader) {
        echo "Loader istanziato: YES\n";
        echo "site_needs_cookie_warning: " . ($loader->site_needs_cookie_warning() ? 'YES' : 'NO') . "\n";
        echo "site_shares_data: " . ($loader->site_shares_data() ? 'YES' : 'NO') . "\n";
        if (method_exists($loader, 'uses_google_tagmanager')) {
            echo "uses_google_tagmanager: " . ($loader->uses_google_tagmanager() ? 'YES' : 'NO') . "\n";
        }
        echo "uses_marketing_cookies: " . (function_exists('cmplz_uses_marketing_cookies') && cmplz_uses_marketing_cookies() ? 'YES' : 'NO') . "\n";

        // Verifica le option direttamente
        echo "\n--- options-relevant ---\n";
        echo "uses_social_media: " . cmplz_get_option('uses_social_media') . "\n";
        echo "uses_thirdparty_services: " . cmplz_get_option('uses_thirdparty_services') . "\n";
        echo "uses_ad_cookies: " . cmplz_get_option('uses_ad_cookies') . "\n";
    } else {
        echo "Loader::this() ritorna NULL — problema di istanziazione\n";
    }
}

// Forza la chiamata di cookiebanner_html() e vedi cosa stampa
if (isset($loader) && $loader) {
    echo "\n--- forced cookiebanner_html() output ---\n";
    ob_start();
    $loader->cookiebanner_html();
    $out = ob_get_clean();
    echo "length: " . strlen($out) . "\n";
    if (strlen($out)) echo substr($out, 0, 600) . "...\n";
}
