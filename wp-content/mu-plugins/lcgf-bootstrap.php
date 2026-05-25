<?php
/**
 * Plugin Name: LCGF Bootstrap
 * Description: Installa automaticamente tema, plugin e prodotti al primo accesso admin.
 * Author: EMC Digital Solutions
 * Version: 1.0.0
 *
 * Esegue una sola volta. Per ri-eseguire: option_delete('lcgf_bootstrap_done').
 */

if (!defined('ABSPATH')) exit;

add_action('admin_notices', 'lcgf_bootstrap_notice');
function lcgf_bootstrap_notice() {
    if (get_option('lcgf_bootstrap_done')) return;
    if (!current_user_can('manage_options')) return;
    $nonce = wp_create_nonce('lcgf_run_bootstrap');
    $url = admin_url('admin.php?lcgf_bootstrap=1&_wpnonce=' . $nonce);
    echo '<div class="notice notice-warning" style="padding:18px;border-left-color:#7a9a5a">';
    echo '<h2 style="margin-top:0">🌾 Setup automatico La Compagnia del Gluten Free</h2>';
    echo '<p>Devo ancora: installare il tema Astra, attivare il child theme, installare WooCommerce + Polylang + Yoast SEO + WPForms + WP Mail SMTP + Complianz, configurare WooCommerce per EUR/Italia e importare i 13 prodotti dal CSV.</p>';
    echo '<p><a href="' . esc_url($url) . '" class="button button-primary button-hero">▶ Esegui setup automatico</a> <span style="margin-left:10px;color:#666">(richiede 1-2 minuti)</span></p>';
    echo '</div>';
}

add_action('admin_init', 'lcgf_run_bootstrap');
function lcgf_run_bootstrap() {
    if (!isset($_GET['lcgf_bootstrap'])) return;
    if (!current_user_can('manage_options')) return;
    if (!wp_verify_nonce($_GET['_wpnonce'] ?? '', 'lcgf_run_bootstrap')) wp_die('Nonce invalido.');
    if (get_option('lcgf_bootstrap_done')) wp_die('Setup già eseguito.');

    @set_time_limit(300);
    @ini_set('memory_limit', '512M');

    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>LCGF Setup</title>';
    echo '<style>body{font-family:system-ui;max-width:780px;margin:40px auto;padding:20px;background:#fbf7ee;color:#1F1B14}';
    echo 'h1{color:#364E25}.ok{color:#4D8B5A}.err{color:#B14545}.step{padding:8px 0;border-bottom:1px solid #e6decb}</style>';
    echo '</head><body>';
    echo '<h1>🌾 Setup automatico</h1>';
    echo '<p>Procedura in corso. Non chiudere questa pagina.</p>';

    // Step 1: install + activate theme Astra
    lcgf_step('Tema Astra (parent)', function() {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/misc.php';
        require_once ABSPATH . 'wp-admin/includes/theme.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/class-theme-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php';
        require_once ABSPATH . 'wp-admin/includes/class-automatic-upgrader-skin.php';

        if (wp_get_theme('astra')->exists()) return 'già installato';
        $api = themes_api('theme_information', ['slug' => 'astra']);
        if (is_wp_error($api)) throw new Exception($api->get_error_message());
        $upgrader = new Theme_Upgrader(new Automatic_Upgrader_Skin());
        $result = $upgrader->install($api->download_link);
        if (is_wp_error($result) || !$result) throw new Exception('install fallita');
        return 'installato';
    });

    lcgf_step('Attiva child theme La Compagnia del Gluten Free', function() {
        $current = wp_get_theme();
        if ($current->get('Name') === 'La Compagnia del Gluten Free') return 'già attivo';
        switch_theme('lcgf-child');
        return 'attivato';
    });

    // Step 2: install + activate plugins
    $plugins = [
        'woocommerce'                    => 'woocommerce/woocommerce.php',
        'polylang'                       => 'polylang/polylang.php',
        'woocommerce-paypal-payments'    => 'woocommerce-paypal-payments/woocommerce-paypal-payments.php',
        'woocommerce-gateway-stripe'     => 'woocommerce-gateway-stripe/woocommerce-gateway-stripe.php',
        'wordpress-seo'                  => 'wordpress-seo/wp-seo.php',
        'complianz-gdpr'                 => 'complianz-gdpr/complianz-gpdr.php',
        'wpforms-lite'                   => 'wpforms-lite/wpforms.php',
        'wp-mail-smtp'                   => 'wp-mail-smtp/wp_mail_smtp.php',
    ];

    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/misc.php';
    require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';
    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php';
    require_once ABSPATH . 'wp-admin/includes/class-automatic-upgrader-skin.php';

    foreach ($plugins as $slug => $main_guess) {
        lcgf_step("Plugin: {$slug}", function() use ($slug, $main_guess) {
            $installed = get_plugins();
            $main_file = null;
            foreach ($installed as $path => $info) {
                if (strpos($path, $slug . '/') === 0) { $main_file = $path; break; }
            }
            if (!$main_file) {
                $api = plugins_api('plugin_information', ['slug' => $slug, 'fields' => ['sections' => false]]);
                if (is_wp_error($api)) throw new Exception($api->get_error_message());
                $upgrader = new Plugin_Upgrader(new Automatic_Upgrader_Skin());
                $result = $upgrader->install($api->download_link);
                if (is_wp_error($result) || !$result) throw new Exception('install fallita');
                $installed = get_plugins();
                foreach ($installed as $path => $info) {
                    if (strpos($path, $slug . '/') === 0) { $main_file = $path; break; }
                }
            }
            if (!$main_file) $main_file = $main_guess;
            if (is_plugin_active($main_file)) return 'già attivo';
            $ok = activate_plugin($main_file);
            if (is_wp_error($ok)) throw new Exception($ok->get_error_message());
            return 'installato + attivato (' . $main_file . ')';
        });
    }

    // Step 3: configura WooCommerce
    lcgf_step('WooCommerce: EUR + Italia', function() {
        update_option('woocommerce_currency', 'EUR');
        update_option('woocommerce_default_country', 'IT:AG');
        update_option('woocommerce_store_address', 'Via da definire');
        update_option('woocommerce_store_city', 'Campobello di Licata');
        update_option('woocommerce_store_postcode', '92023');
        update_option('woocommerce_calc_taxes', 'yes');
        update_option('woocommerce_prices_include_tax', 'yes');
        update_option('woocommerce_weight_unit', 'g');
        update_option('woocommerce_dimension_unit', 'cm');
        update_option('woocommerce_currency_pos', 'left_space');
        update_option('woocommerce_price_thousand_sep', '.');
        update_option('woocommerce_price_decimal_sep', ',');
        update_option('woocommerce_price_num_decimals', 2);
        return 'configurato';
    });

    // Step 4: importa prodotti
    lcgf_step('Importa 13 prodotti reali', function() {
        require_once ABSPATH . 'wp-admin/includes/post.php';
        if (!class_exists('WC_Product')) throw new Exception('WooCommerce non attivo, riprova fra qualche secondo');
        return lcgf_import_products();
    });

    // Step 5: pagine
    lcgf_step('Crea pagine principali', function() {
        $pages = [
            'chi-siamo' => 'Chi siamo',
            'contatti'  => 'Contatti',
            'spedizioni'=> 'Spedizioni e resi',
            'privacy'   => 'Privacy Policy',
            'cookie'    => 'Cookie Policy',
            'recesso'   => 'Diritto di recesso',
            'condizioni'=> 'Condizioni di vendita',
            'faq'       => 'Domande frequenti',
        ];
        $created = 0;
        foreach ($pages as $slug => $title) {
            if (get_page_by_path($slug)) continue;
            wp_insert_post([
                'post_title'   => $title,
                'post_name'    => $slug,
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => '<p>Pagina ' . $title . ' — da personalizzare con i contenuti definitivi.</p>',
            ]);
            $created++;
        }
        return "$created pagine create";
    });

    // Step 6: site title/tagline
    lcgf_step('Title + tagline del sito', function() {
        update_option('blogname', 'La Compagnia del Gluten Free');
        update_option('blogdescription', 'Mangia con Gusto — Prodotti senza glutine e senza lattosio');
        update_option('timezone_string', 'Europe/Rome');
        update_option('date_format', 'j F Y');
        update_option('time_format', 'H:i');
        return 'ok';
    });

    update_option('lcgf_bootstrap_done', current_time('mysql'));

    echo '<h2 class="ok">✅ Setup completato!</h2>';
    echo '<p><a href="' . admin_url() . '" class="button button-primary">Torna alla dashboard</a> ';
    echo '<a href="' . home_url('/') . '" class="button" target="_blank">Vedi il sito →</a></p>';
    echo '</body></html>';
    exit;
}

function lcgf_step($name, $callback) {
    echo '<div class="step"><strong>' . esc_html($name) . '</strong>: ';
    flush(); @ob_flush();
    try {
        $res = $callback();
        echo '<span class="ok">' . esc_html($res ?: 'OK') . '</span>';
    } catch (Throwable $e) {
        echo '<span class="err">FAIL — ' . esc_html($e->getMessage()) . '</span>';
    }
    echo '</div>';
    flush(); @ob_flush();
}

function lcgf_import_products() {
    $csv_path = WP_CONTENT_DIR . '/themes/lcgf-child/imports/products.csv';
    if (!file_exists($csv_path)) {
        // fallback hardcoded import
        return lcgf_import_hardcoded();
    }
    return 'csv import non implementato, usare hardcoded';
}

function lcgf_import_hardcoded() {
    $cats = [
        'pane-basi'        => 'Pane & Basi',
        'dolci-colazione'  => 'Dolci & Colazione',
    ];
    $cat_ids = [];
    foreach ($cats as $slug => $name) {
        $term = get_term_by('slug', $slug, 'product_cat');
        if (!$term) {
            $res = wp_insert_term($name, 'product_cat', ['slug' => $slug]);
            $cat_ids[$slug] = is_wp_error($res) ? null : $res['term_id'];
        } else {
            $cat_ids[$slug] = $term->term_id;
        }
    }

    $products = [
        ['slug' => 'box-family',        'name' => 'Box Family — Misto Pane & Basi', 'price' => 59.90, 'cat' => 'pane-basi',       'img' => 'box-family.png',        'desc' => '26 pezzi: 4 pinse, 4 focacce, 6 pane rosetta, 6 base pizza, 6 pane filoncino. La scorta perfetta per la famiglia.'],
        ['slug' => 'pinsa-romana',      'name' => 'Base Pinsa Romana',              'price' => 4.50,  'cat' => 'pane-basi',       'img' => 'pinsa-romana.png',      'desc' => 'Pinsa romana 30×20 cm, 260 g. Croccante fuori, alveolata dentro.'],
        ['slug' => 'pan-focaccia',      'name' => 'Pan Focaccia',                   'price' => 3.90,  'cat' => 'pane-basi',       'img' => 'pan-focaccia.png',      'desc' => 'Focaccia rettangolare 22 cm, 230 g. Soffice e profumata.'],
        ['slug' => 'focaccia-rotonda',  'name' => 'Focaccia Rotonda',               'price' => 2.80,  'cat' => 'pane-basi',       'img' => 'focaccia-rotonda.png',  'desc' => 'Focaccia rotonda Ø 20 cm, 120 g. Monoporzione perfetta.'],
        ['slug' => 'base-pizza',        'name' => 'Base Pizza',                     'price' => 4.20,  'cat' => 'pane-basi',       'img' => 'base-pizza.png',        'desc' => 'Base pizza Ø 33 cm, 300 g. Cottura veloce, gusto pieno.'],
        ['slug' => 'pane-filoncino',    'name' => 'Pane Filoncino',                 'price' => 4.50,  'cat' => 'pane-basi',       'img' => 'pane-filoncino.png',    'desc' => 'Filoncino 120 g, crosta dorata. Confezione da 2 pezzi.'],
        ['slug' => 'pane-rosetta',      'name' => 'Pane Rosetta',                   'price' => 4.20,  'cat' => 'pane-basi',       'img' => 'pane-rosetta.png',      'desc' => 'Rosette 120 g, classiche e con sesamo. Confezione da 2 pezzi.'],
        ['slug' => 'brioche',           'name' => 'Brioche col Tuppo',              'price' => 2.90,  'cat' => 'dolci-colazione', 'img' => 'brioche.png',           'desc' => 'Brioche siciliana 130 g, col classico tuppo.'],
        ['slug' => 'cornetto-vuoto',    'name' => 'Cornetto Vuoto',                 'price' => 2.50,  'cat' => 'dolci-colazione', 'img' => 'cornetto.png',          'desc' => 'Cornetto vuoto 130 g, sfogliato e dorato.'],
        ['slug' => 'crostate',          'name' => 'Crostata di Frutta',             'price' => 3.50,  'cat' => 'dolci-colazione', 'img' => 'crostate.png',          'desc' => 'Crostata monoporzione 100 g, pasta frolla burrosa.'],
        ['slug' => 'biscotti',          'name' => 'Biscotti con gocce di cioccolato','price'=> 7.50,  'cat' => 'dolci-colazione', 'img' => 'biscotti.png',          'desc' => 'Sacchetto 250 g di biscotti friabili con gocce di cioccolato.'],
        ['slug' => 'tiramisu',          'name' => 'Tiramisù',                       'price' => 5.50,  'cat' => 'dolci-colazione', 'img' => 'tiramisu.png',          'desc' => 'Tiramisù monoporzione 100 g. Crema, caffè, savoiardi gluten free.'],
        ['slug' => 'cheesecake',        'name' => 'Cheesecake',                     'price' => 4.80,  'cat' => 'dolci-colazione', 'img' => 'cheesecake.png',        'desc' => 'Cheesecake 130 g in 5 gusti: pistacchio, frutti di bosco, fragola, limone, pan di stelle.'],
    ];

    $imported = 0;
    foreach ($products as $p) {
        $existing = get_page_by_path($p['slug'], OBJECT, 'product');
        if ($existing) continue;

        $product = new WC_Product_Simple();
        $product->set_name($p['name']);
        $product->set_slug($p['slug']);
        $product->set_status('publish');
        $product->set_catalog_visibility('visible');
        $product->set_short_description($p['desc']);
        $product->set_description($p['desc'] . ' Senza glutine e senza lattosio, prodotto in laboratorio dedicato.');
        $product->set_regular_price((string)$p['price']);
        $product->set_manage_stock(false);
        $product->set_stock_status('instock');

        if (isset($cat_ids[$p['cat']]) && $cat_ids[$p['cat']]) {
            $product->set_category_ids([$cat_ids[$p['cat']]]);
        }

        $product_id = $product->save();

        // Import immagine dal tema child
        $img_src = WP_CONTENT_DIR . '/themes/lcgf-child/assets/products/' . $p['img'];
        if (file_exists($img_src)) {
            $att_id = lcgf_sideload_image($img_src, $product_id, $p['name']);
            if ($att_id) {
                set_post_thumbnail($product_id, $att_id);
            }
        }

        $imported++;
    }

    return "$imported prodotti importati";
}

function lcgf_sideload_image($file_path, $post_id, $title) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $upload = wp_upload_dir();
    $filename = wp_unique_filename($upload['path'], basename($file_path));
    $dest = trailingslashit($upload['path']) . $filename;
    if (!copy($file_path, $dest)) return false;

    $wp_filetype = wp_check_filetype($filename, null);
    $attachment = [
        'guid'           => trailingslashit($upload['url']) . $filename,
        'post_mime_type' => $wp_filetype['type'],
        'post_title'     => $title,
        'post_content'   => '',
        'post_status'    => 'inherit',
    ];
    $att_id = wp_insert_attachment($attachment, $dest, $post_id);
    $meta = wp_generate_attachment_metadata($att_id, $dest);
    wp_update_attachment_metadata($att_id, $meta);
    return $att_id;
}
