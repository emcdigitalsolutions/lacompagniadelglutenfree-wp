<?php
global $wpdb;
$wpdb->delete($wpdb->prefix . 'cmplz_cookiebanners', ['ID' => 2]);
delete_transient('cmplz_default_banner_id');
wp_cache_flush();

// Re-verifica
$rows = $wpdb->get_results("SELECT ID, title, `default` FROM {$wpdb->prefix}cmplz_cookiebanners");
echo "Banner rimasti:\n";
foreach ($rows as $r) {
    echo "  ID={$r->ID} title=\"{$r->title}\" default=" . ($r->{'default'} ? 'YES' : 'no') . "\n";
}

// Forza un site_needs_cookie_warning check
if (class_exists('cmplz_banner_loader') && cmplz_banner_loader::this()) {
    $loader = cmplz_banner_loader::this();
    echo "site_needs_cookie_warning: " . ($loader->site_needs_cookie_warning() ? 'YES' : 'NO') . "\n";
}
echo "wizard_completed_once: " . (get_option('cmplz_wizard_completed_once') ? 'YES' : 'NO') . "\n";
echo "default_banner_id: " . cmplz_get_default_banner_id() . "\n";
