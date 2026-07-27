<?php
/**
 * Plugin Name: LCGF — Soft launch (solo prodotti attivi acquistabili)
 * Description: Fase di lancio graduale (richiesta cliente 27/7/2026): solo un set
 *   ristretto di prodotti è acquistabile; tutti gli altri restano VISIBILI a
 *   catalogo con badge "Disponibile a breve" e non sono ordinabili (mostrano il
 *   form "avvisami quando disponibile"). Per attivare altri prodotti man mano
 *   basta aggiornare l'option `lcgf_launch_active_it_slugs` e rilanciare
 *   /wp-admin/?lcgf_action=launch_reconcile (admin).
 *
 * @author EMC Digital Solutions
 */

if (!defined('ABSPATH')) exit;

define('LCGF_LAUNCH_VER', '2026-07-27-1');

/**
 * Slug (in lingua IT, lingua di default) dei prodotti ATTIVI/acquistabili.
 * Espansi automaticamente a tutte le traduzioni Polylang. Override via option.
 */
function lcgf_launch_active_it_slugs() {
    $default = array(
        'pinsa-romana',     // Base Pinsa Romana
        'base-pizza',       // Base Pizza
        'focaccia-rotonda', // Focaccia Rotonda
        'pane-filoncino',   // Pane Filoncino
        'pane-rosetta',     // Pane Rosetta
    );
    $opt = get_option('lcgf_launch_active_it_slugs');
    if (is_array($opt) && !empty($opt)) return array_values(array_filter(array_map('sanitize_title', $opt)));
    return $default;
}

/** Etichetta "Disponibile a breve" nella lingua corrente. */
function lcgf_launch_soon_label() {
    $l = function_exists('pll_current_language') ? pll_current_language('slug') : 'it';
    $map = array(
        'it' => 'Disponibile a breve',
        'en' => 'Available soon',
        'de' => 'Bald verfügbar',
        'fr' => 'Bientôt disponible',
    );
    return isset($map[$l]) ? $map[$l] : $map['it'];
}

/** Frase estesa per la scheda prodotto. */
function lcgf_launch_soon_notice() {
    $l = function_exists('pll_current_language') ? pll_current_language('slug') : 'it';
    $map = array(
        'it' => 'Questo prodotto sarà presto acquistabile.',
        'en' => 'This product will be purchasable soon.',
        'de' => 'Dieses Produkt ist bald bestellbar.',
        'fr' => 'Ce produit sera bientôt disponible à l’achat.',
    );
    return isset($map[$l]) ? $map[$l] : $map['it'];
}

/** True se il prodotto è marcato "disponibile a breve". */
function lcgf_is_coming_soon($product) {
    $id = is_object($product) ? $product->get_id() : (int) $product;
    if (!$id) return false;
    return get_post_meta($id, '_lcgf_coming_soon', true) === 'yes';
}

/**
 * Applica lo stato di lancio a TUTTO il catalogo (idempotente):
 * attivi → instock + niente flag; tutti gli altri → outofstock + flag coming_soon.
 * Gestisce anche le variazioni dei prodotti variabili.
 */
function lcgf_launch_reconcile() {
    if (!function_exists('wc_get_product')) return array('error' => 'WooCommerce non attivo');

    // Assicura che i prodotti esauriti restino visibili a catalogo.
    if (get_option('woocommerce_hide_out_of_stock_items') !== 'no') {
        update_option('woocommerce_hide_out_of_stock_items', 'no');
    }

    // Costruisci l'insieme degli ID attivi (tutte le lingue).
    $active_ids = array();
    foreach (lcgf_launch_active_it_slugs() as $slug) {
        $p = get_page_by_path($slug, OBJECT, 'product');
        if (!$p) continue;
        $ids = array($p->ID);
        if (function_exists('pll_get_post_translations')) {
            $tr = pll_get_post_translations($p->ID);
            if (is_array($tr)) $ids = array_merge($ids, array_map('intval', array_values($tr)));
        }
        foreach ($ids as $i) $active_ids[(int) $i] = true;
    }

    // Tutti i prodotti pubblicati, in ogni lingua.
    $all = get_posts(array(
        'post_type'   => 'product',
        'post_status' => 'publish',
        'numberposts' => -1,
        'fields'      => 'ids',
        'lang'        => '',
        'suppress_filters' => false,
    ));

    $stats = array('active' => 0, 'soon' => 0, 'skipped' => 0);

    // Sospendi il sync stock tra traduzioni: qui impostiamo esplicitamente ogni post.
    $had_sync = function_exists('lcgf_stock_sync_busy');
    if ($had_sync) lcgf_stock_sync_busy(true);

    foreach ($all as $pid) {
        $prod = wc_get_product($pid);
        if (!$prod) { $stats['skipped']++; continue; }
        $is_active = isset($active_ids[(int) $pid]);
        $target    = $is_active ? 'instock' : 'outofstock';

        if ($is_active) {
            delete_post_meta($pid, '_lcgf_coming_soon');
        } else {
            update_post_meta($pid, '_lcgf_coming_soon', 'yes');
        }

        if ($prod->get_stock_status() !== $target) {
            $prod->set_stock_status($target);
            $prod->save();
        }
        if ($prod->is_type('variable')) {
            foreach ($prod->get_children() as $vid) {
                $v = wc_get_product($vid);
                if ($v && $v->get_stock_status() !== $target) { $v->set_stock_status($target); $v->save(); }
            }
        }
        $stats[$is_active ? 'active' : 'soon']++;
    }

    if ($had_sync) lcgf_stock_sync_busy(false);

    if (function_exists('wc_delete_product_transients')) wc_delete_product_transients();
    update_option('lcgf_launch_ver', LCGF_LAUNCH_VER);
    return $stats;
}

/* -------------------------------------------------------------------------
 * Auto-applicazione una tantum (self-healing dopo un eventuale redeploy).
 * ---------------------------------------------------------------------- */
add_action('init', function () {
    if (!function_exists('wc_get_product')) return;
    if (get_option('lcgf_launch_ver') === LCGF_LAUNCH_VER) return;
    if (get_transient('lcgf_launch_lock')) return;
    set_transient('lcgf_launch_lock', 1, 120);
    lcgf_launch_reconcile();
    delete_transient('lcgf_launch_lock');
}, 30);

/* -------------------------------------------------------------------------
 * Presentazione front-end.
 * ---------------------------------------------------------------------- */

// Testo disponibilità WooCommerce → "Disponibile a breve".
add_filter('woocommerce_get_availability_text', function ($text, $product) {
    return lcgf_is_coming_soon($product) ? lcgf_launch_soon_label() : $text;
}, 20, 2);

// Classe disponibilità → stile dedicato (oro, non rosso "esaurito").
add_filter('woocommerce_get_availability_class', function ($class, $product) {
    return lcgf_is_coming_soon($product) ? 'lcgf-coming-soon' : $class;
}, 20, 2);

// Testo del bottone nel loop catalogo per i "coming soon" (che sono out-of-stock
// → di default "Leggi tutto"): diventa "Disponibile a breve".
add_filter('woocommerce_product_add_to_cart_text', function ($text, $product) {
    return lcgf_is_coming_soon($product) ? lcgf_launch_soon_label() : $text;
}, 20, 2);

// Badge "Disponibile a breve" sull'immagine nelle card del catalogo.
add_action('woocommerce_before_shop_loop_item_title', function () {
    global $product;
    if ($product && lcgf_is_coming_soon($product)) {
        echo '<span class="lcgf-soon-badge">' . esc_html(lcgf_launch_soon_label()) . '</span>';
    }
}, 8);

// CSS dedicato (cache-busting via filemtime).
add_action('wp_enqueue_scripts', function () {
    $f = __DIR__ . '/lcgf-launch.css';
    if (file_exists($f)) {
        wp_enqueue_style('lcgf-launch', plugins_url('lcgf-launch.css', __FILE__), array(), (string) filemtime($f));
    }
}, 20);

/* -------------------------------------------------------------------------
 * Endpoint admin: riconciliazione manuale e stato.
 * ---------------------------------------------------------------------- */
add_action('init', function () {
    $act = isset($_GET['lcgf_action']) ? $_GET['lcgf_action'] : '';
    if ($act === 'launch_reconcile') {
        if (!current_user_can('manage_options')) wp_die('Non autorizzato');
        header('Content-Type: text/plain; charset=utf-8');
        $r = lcgf_launch_reconcile();
        echo "OK launch_reconcile\n" . print_r($r, true);
        echo "\nAttivi (slug IT): " . implode(', ', lcgf_launch_active_it_slugs()) . "\n";
        exit;
    }
    if ($act === 'launch_status') {
        header('Content-Type: text/plain; charset=utf-8');
        echo "Versione: " . LCGF_LAUNCH_VER . " | applied: " . get_option('lcgf_launch_ver') . "\n";
        echo "hide_out_of_stock_items: " . get_option('woocommerce_hide_out_of_stock_items') . "\n";
        echo "Attivi (slug IT): " . implode(', ', lcgf_launch_active_it_slugs()) . "\n";
        if (function_exists('wc_get_product')) {
            $all = get_posts(array('post_type' => 'product', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids', 'lang' => ''));
            $a = 0; $s = 0;
            foreach ($all as $pid) { lcgf_is_coming_soon($pid) ? $s++ : $a++; }
            echo "Prodotti acquistabili: $a | disponibili a breve: $s | totale: " . count($all) . "\n";
        }
        exit;
    }
}, 5);
