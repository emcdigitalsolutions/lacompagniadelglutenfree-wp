<?php
global $wpdb;
$banners = $wpdb->get_results("SELECT ID, title, `default`, status FROM {$wpdb->prefix}cmplz_cookiebanners");
echo "Banner totali nel DB: " . count($banners) . "\n";
foreach ($banners as $b) {
    echo "  ID={$b->ID} title=\"{$b->title}\" default=" . ($b->{'default'} ? 'YES' : 'no') . " status={$b->status}\n";
}

if (function_exists('cmplz_get_default_banner_id')) {
    echo "default_banner_id: " . cmplz_get_default_banner_id() . "\n";
}

// Se non ci sono banner attivi, prova a installare il default
if (count($banners) === 0) {
    echo "\n=> NESSUN BANNER, creo il default...\n";
    if (class_exists('COMPLIANZ\COMPLIANZ\cookiebanner\Cookie_Banner')) {
        echo "Class Cookie_Banner trovata\n";
    }
    // Forza l'installazione default
    if (function_exists('cmplz_install')) {
        cmplz_install();
        echo "cmplz_install() chiamato\n";
    }
    // Controllo class che gestisce installazione
    if (class_exists('cmplz_admin')) {
        if (method_exists('cmplz_admin', 'install_default_banner')) {
            (new cmplz_admin())->install_default_banner();
            echo "install_default_banner() chiamato\n";
        }
    }
}
