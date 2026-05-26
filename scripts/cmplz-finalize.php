<?php
/**
 * Finalizza il wizard Complianz attivando le condizioni che fanno
 * site_needs_cookie_warning() ritornare true e quindi mostrano il banner.
 *
 * Esegui via: wp eval-file scripts/cmplz-finalize.php
 */

if (!function_exists('cmplz_update_option')) {
    echo "Complianz non caricato\n";
    return;
}

$updates = [
    // ESSENZIALE: questi triggera site_shares_data() = true -> banner mostrato
    ['wizard', 'uses_social_media',         'yes'],
    ['wizard', 'uses_thirdparty_services',  'yes'],
    ['wizard', 'uses_ad_cookies_personalized', 'no'],

    // Region target
    ['wizard', 'regions',              ['eu' => '1']],
    ['wizard', 'eu_consent_regions',   'yes'],

    // Company info
    ['wizard', 'company_name',         'La Compagnia del Gluten Free'],
    ['wizard', 'organisation_type',    'company'],
    ['wizard', 'country_company',      'IT'],

    // Policy URLs
    ['wizard', 'privacy_policy_url',   home_url('/privacy/')],
    ['wizard', 'cookie_policy_url',    home_url('/cookie/')],

    // Banner appearance
    ['wizard', 'use_categories',       'visible'],
    ['wizard', 'soft_cookiewall',      'no'],
    ['wizard', 'position',             'bottom'],
    ['wizard', 'banner_position',      'bottom-right'],

    // Mark wizard done
    ['wizard', 'wizard_completed',     'completed'],
];

foreach ($updates as [$page, $key, $val]) {
    cmplz_update_option($page, $key, $val);
}

update_option('cmplz_wizard_completed_once', true);
update_option('cmplz_run_wizard_in_admin', false);
update_option('cmplz_documents_update_date', time());

// Forza rebuild della cookie policy + banner
if (function_exists('cmplz_create_cookiepolicy_snapshot')) {
    do_action('cmplz_generate_cookie_policy');
}

// Verifica risultato finale
echo "=== STATO FINALE COMPLIANZ ===\n";
echo "wizard_completed_once: " . (get_option('cmplz_wizard_completed_once') ? 'YES' : 'NO') . "\n";
echo "uses_social_media:     " . cmplz_get_option('uses_social_media') . "\n";
echo "uses_thirdparty:       " . cmplz_get_option('uses_thirdparty_services') . "\n";
echo "company_name:          " . cmplz_get_option('company_name') . "\n";
echo "privacy_policy_url:    " . cmplz_get_option('privacy_policy_url') . "\n";
echo "cookie_policy_url:     " . cmplz_get_option('cookie_policy_url') . "\n";

if (class_exists('cmplz_banner_loader')) {
    $loader = cmplz_banner_loader::this();
    if ($loader && method_exists($loader, 'site_needs_cookie_warning')) {
        $need = $loader->site_needs_cookie_warning();
        echo "site_needs_cookie_warning: " . ($need ? 'YES (banner verra mostrato)' : 'NO (banner nascosto)') . "\n";
    }
}
