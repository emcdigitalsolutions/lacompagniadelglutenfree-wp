<?php
/**
 * Crea il banner cookie di default per Complianz.
 * Eseguire come admin: wp eval-file ... --user=1
 */

if (!class_exists('cmplz_cookiebanner')) {
    echo "Class cmplz_cookiebanner non trovata\n";
    return;
}

if (!function_exists('cmplz_user_can_manage')) {
    echo "Complianz non caricato completamente\n";
    return;
}

if (!cmplz_user_can_manage()) {
    echo "L'utente corrente non ha manage_privacy capability\n";
    echo "Run with --user=1 (admin)\n";
    return;
}

// Crea istanza vuota + save() -> chiamerà add() internamente
$banner = new cmplz_cookiebanner(false, true);
$banner->title = 'Banner LCGF';
$banner->save();

echo "Banner creato con ID = " . $banner->ID . "\n";

// Verifica
global $wpdb;
$rows = $wpdb->get_results("SELECT ID, title, `default` FROM {$wpdb->prefix}cmplz_cookiebanners");
echo "\n=== Banner nel DB ===\n";
foreach ($rows as $r) {
    echo "  ID={$r->ID} title=\"{$r->title}\" default=" . ($r->{'default'} ? 'YES' : 'no') . "\n";
}

// Reset transient + cache
delete_transient('cmplz_default_banner_id');
wp_cache_flush();
echo "\nDefault banner ID: " . cmplz_get_default_banner_id() . "\n";
