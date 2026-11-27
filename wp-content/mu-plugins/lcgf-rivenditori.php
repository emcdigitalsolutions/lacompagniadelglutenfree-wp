<?php
/**
 * Plugin Name: LCGF — Area Rivenditori (listino a cartoni)
 * Description: Sezione per i rivenditori affiancata al negozio al dettaglio
 *   (richiesta Vito, listino TABELLE_SITO.xlsx, 23/11/2026).
 *   - Pagina /rivenditori/ (IT/EN/DE/FR) con le tabelle prezzi per cartone.
 *   - Si acquista lo STESSO prodotto del catalogo, ma a cartoni: la riga di
 *     carrello porta il marcatore `lcgf_rv` e il prezzo è calcolato dal listino
 *     a fasce in base al numero di cartoni di quel prodotto (1–3 / 4–6 / 7–9).
 *     10+ cartoni → richiesta di preventivo (max 9 a riga).
 *   - Prezzi del listino IVA ESCLUSA: in carrello si aggiunge l'IVA del prodotto
 *     (il negozio lavora con prezzi IVA inclusa); con P.IVA UE valida VIES
 *     l'esenzione di lcgf-eu-vat.php riporta il prezzo al netto.
 *   - Spedizione gratuita se nel carrello c'è almeno un cartone.
 *   - Partita IVA obbligatoria al checkout per gli ordini a cartoni.
 * Listino modificabile qui sotto in lcgf_rv_listino().
 *
 * @author EMC Digital Solutions
 */

if (!defined('ABSPATH')) exit;

/* =========================================================================
 * Listino (prezzi per pezzo / confezione, IVA esclusa)
 * ====================================================================== */
function lcgf_rv_listino() {
    return array(
        'base-pizza'       => array('pcs' => 12, 'unit' => 'pz',   'prices' => array(4.15, 4.00, 3.85)),
        'pinsa-romana'     => array('pcs' => 15, 'unit' => 'pz',   'prices' => array(4.15, 4.00, 3.85)),
        'cornetto-vuoto'   => array('pcs' => 10, 'unit' => 'pz',   'prices' => array(2.80, 2.65, 2.50)),
        'pane-rosetta'     => array('pcs' => 12, 'unit' => 'conf', 'prices' => array(3.40, 3.10, 2.80)),
        'pane-filoncino'   => array('pcs' => 12, 'unit' => 'conf', 'prices' => array(3.40, 3.10, 2.80)),
        'focaccia-rotonda' => array('pcs' => 15, 'unit' => 'pz',   'prices' => array(3.10, 2.80, 2.60)),
    );
}
define('LCGF_RV_MAX_CARTONI', 9);   // oltre → preventivo
define('LCGF_RV_PAGE_SLUG', 'rivenditori');

/** Fascia (0,1,2) in base al numero di cartoni. */
function lcgf_rv_tier($cartoni) {
    $c = (int) $cartoni;
    if ($c <= 3) return 0;
    if ($c <= 6) return 1;
    return 2;
}
function lcgf_rv_tier_label($i) {
    $m = array('1 – 3', '4 – 6', '7 – 9');
    return $m[$i];
}

/** Slug IT del prodotto (gestisce le traduzioni Polylang). */
function lcgf_rv_it_slug($product_id) {
    $id = (int) $product_id;
    if (function_exists('pll_get_post')) {
        $it = pll_get_post($id, 'it');
        if ($it) $id = (int) $it;
    }
    $p = get_post($id);
    return $p ? $p->post_name : '';
}

/** Riga listino per un prodotto (o null se non è a listino rivenditori). */
function lcgf_rv_row($product_id) {
    $l = lcgf_rv_listino();
    $s = lcgf_rv_it_slug($product_id);
    return isset($l[$s]) ? $l[$s] : null;
}

/** ID del prodotto nella lingua corrente, dato lo slug IT. */
function lcgf_rv_product_id($it_slug) {
    $p = get_page_by_path($it_slug, OBJECT, 'product');
    if (!$p) return 0;
    $id = (int) $p->ID;
    if (function_exists('pll_get_post')) {
        $tr = pll_get_post($id);
        if ($tr) $id = (int) $tr;
    }
    return $id;
}

/** Aliquota IVA (%) della classe fiscale del prodotto, sede del negozio. */
function lcgf_rv_vat_rate($product) {
    if (!wc_tax_enabled() || !class_exists('WC_Tax')) return 0.0;
    $rates = WC_Tax::get_base_tax_rates($product->get_tax_class('unfiltered'));
    $tot = 0.0;
    foreach ($rates as $r) $tot += (float) $r['rate'];
    return $tot;
}

function lcgf_rv_money($v) {
    return number_format((float) $v, 2, ',', '.') . ' €';
}

/* =========================================================================
 * Testi IT/EN/DE/FR
 * ====================================================================== */
function lcgf_rv_lang() {
    $l = function_exists('pll_current_language') ? pll_current_language('slug') : 'it';
    return in_array($l, array('it', 'en', 'de', 'fr'), true) ? $l : 'it';
}
function lcgf_rv_s($key) {
    static $S = null;
    if ($S === null) $S = array(
        'nav'        => array('it' => 'Rivenditori', 'en' => 'Resellers', 'de' => 'Händler', 'fr' => 'Revendeurs'),
        'eyebrow'    => array('it' => 'Area Rivenditori', 'en' => 'Reseller area', 'de' => 'Händlerbereich', 'fr' => 'Espace revendeurs'),
        'title'      => array('it' => 'Listino rivenditori a cartoni', 'en' => 'Reseller price list by the carton', 'de' => 'Händlerpreisliste pro Karton', 'fr' => 'Tarifs revendeurs au carton'),
        'intro'      => array(
            'it' => 'Bar, ristoranti, pizzerie, negozi e farmacie: acquista i nostri prodotti senza glutine e senza lattosio a cartoni. Più cartoni ordini, meno paghi.',
            'en' => 'Bars, restaurants, pizzerias, shops and pharmacies: buy our gluten-free and lactose-free products by the carton. The more cartons you order, the less you pay.',
            'de' => 'Bars, Restaurants, Pizzerien, Geschäfte und Apotheken: Kaufen Sie unsere gluten- und laktosefreien Produkte im Karton. Je mehr Kartons, desto günstiger.',
            'fr' => 'Bars, restaurants, pizzerias, commerces et pharmacies : achetez nos produits sans gluten et sans lactose au carton. Plus vous commandez de cartons, moins vous payez.'),
        'b_vat'      => array('it' => 'Prezzi IVA esclusa', 'en' => 'Prices excluding VAT', 'de' => 'Preise zzgl. MwSt.', 'fr' => 'Prix hors TVA'),
        'b_ship'     => array('it' => 'Spedizione gratuita', 'en' => 'Free shipping', 'de' => 'Kostenloser Versand', 'fr' => 'Livraison gratuite'),
        'b_piva'     => array('it' => 'Riservato a titolari di Partita IVA', 'en' => 'For VAT-registered businesses', 'de' => 'Nur für Unternehmen mit USt-IdNr.', 'fr' => 'Réservé aux professionnels avec n° TVA'),
        'b_tier'     => array('it' => 'Prezzo a scaglioni per numero di cartoni', 'en' => 'Tiered price by number of cartons', 'de' => 'Staffelpreis nach Anzahl Kartons', 'fr' => 'Prix dégressif selon le nombre de cartons'),
        'carton'     => array('it' => 'Cartone da %s', 'en' => 'Carton of %s', 'de' => 'Karton mit %s', 'fr' => 'Carton de %s'),
        'u_pz'       => array('it' => '%d pezzi', 'en' => '%d pieces', 'de' => '%d Stück', 'fr' => '%d pièces'),
        'u_conf'     => array('it' => '%d confezioni da 2', 'en' => '%d packs of 2', 'de' => '%d Packungen à 2', 'fr' => '%d paquets de 2'),
        'th_cart'    => array('it' => 'N° cartoni', 'en' => 'Cartons', 'de' => 'Kartons', 'fr' => 'Cartons'),
        'th_pz'      => array('it' => 'Prezzo / pezzo', 'en' => 'Price / piece', 'de' => 'Preis / Stück', 'fr' => 'Prix / pièce'),
        'th_conf'    => array('it' => 'Prezzo / confezione', 'en' => 'Price / pack', 'de' => 'Preis / Packung', 'fr' => 'Prix / paquet'),
        'th_tot'     => array('it' => 'Prezzo / cartone', 'en' => 'Price / carton', 'de' => 'Preis / Karton', 'fr' => 'Prix / carton'),
        'popular'    => array('it' => 'Più richiesto', 'en' => 'Most popular', 'de' => 'Beliebteste', 'fr' => 'Le plus demandé'),
        'quote'      => array('it' => 'Richiedi un preventivo', 'en' => 'Request a quote', 'de' => 'Angebot anfordern', 'fr' => 'Demander un devis'),
        'plusvat'    => array('it' => '+ IVA', 'en' => '+ VAT', 'de' => 'zzgl. MwSt.', 'fr' => 'HT'),
        'qty'        => array('it' => 'Cartoni', 'en' => 'Cartons', 'de' => 'Kartons', 'fr' => 'Cartons'),
        'add'        => array('it' => 'Aggiungi al carrello', 'en' => 'Add to cart', 'de' => 'In den Warenkorb', 'fr' => 'Ajouter au panier'),
        'total'      => array('it' => 'Totale', 'en' => 'Total', 'de' => 'Gesamt', 'fr' => 'Total'),
        'soon'       => array('it' => 'Disponibile a breve', 'en' => 'Available soon', 'de' => 'Bald verfügbar', 'fr' => 'Bientôt disponible'),
        'more_q'     => array('it' => 'Ti servono 10 o più cartoni, o un assortimento su misura?', 'en' => 'Need 10 or more cartons, or a custom assortment?', 'de' => 'Sie brauchen 10 oder mehr Kartons oder ein individuelles Sortiment?', 'fr' => 'Besoin de 10 cartons ou plus, ou d’un assortiment sur mesure ?'),
        'more_a'     => array('it' => 'Scrivici: ti prepariamo un preventivo dedicato.', 'en' => 'Write to us: we will prepare a dedicated quote.', 'de' => 'Schreiben Sie uns: Wir erstellen Ihnen ein individuelles Angebot.', 'fr' => 'Écrivez-nous : nous vous préparerons un devis dédié.'),
        'retail'     => array('it' => 'Sei un privato? Vai al catalogo', 'en' => 'Private customer? Go to the catalog', 'de' => 'Privatkunde? Zum Katalog', 'fr' => 'Particulier ? Voir le catalogue'),
        'how'        => array(
            'it' => 'Scegli il numero di cartoni e aggiungili al carrello: il prezzo si aggiorna in automatico in base allo scaglione. Al pagamento indica la tua Partita IVA. L’IVA viene aggiunta in fattura (per le aziende UE con Partita IVA valida si applica il reverse charge).',
            'en' => 'Choose the number of cartons and add them to the cart: the price updates automatically according to the tier. Enter your VAT number at checkout. VAT is added on the invoice (reverse charge applies to EU businesses with a valid VAT number).',
            'de' => 'Wählen Sie die Anzahl der Kartons und legen Sie sie in den Warenkorb: Der Preis passt sich automatisch der Staffel an. Geben Sie an der Kasse Ihre USt-IdNr. an. Die MwSt. wird auf der Rechnung ausgewiesen (Reverse Charge für EU-Unternehmen mit gültiger USt-IdNr.).',
            'fr' => 'Choisissez le nombre de cartons et ajoutez-les au panier : le prix s’adapte automatiquement au palier. Indiquez votre n° de TVA lors du paiement. La TVA est ajoutée sur la facture (autoliquidation pour les entreprises UE avec n° de TVA valide).'),
        // Carrello / checkout
        'ci_format'  => array('it' => 'Formato', 'en' => 'Format', 'de' => 'Format', 'fr' => 'Format'),
        'ci_list'    => array('it' => 'Listino rivenditori', 'en' => 'Reseller price', 'de' => 'Händlerpreis', 'fr' => 'Tarif revendeur'),
        'ci_list_v'  => array('it' => '%1$s + IVA %2$s (fascia %3$s cartoni)', 'en' => '%1$s + VAT %2$s (%3$s cartons tier)', 'de' => '%1$s zzgl. MwSt. %2$s (Staffel %3$s Kartons)', 'fr' => '%1$s HT %2$s (palier %3$s cartons)'),
        'per_pz'     => array('it' => 'al pezzo', 'en' => 'per piece', 'de' => 'pro Stück', 'fr' => 'la pièce'),
        'per_conf'   => array('it' => 'a confezione', 'en' => 'per pack', 'de' => 'pro Packung', 'fr' => 'le paquet'),
        'ship_label' => array('it' => 'Spedizione gratuita (ordine rivenditori)', 'en' => 'Free shipping (reseller order)', 'de' => 'Kostenloser Versand (Händlerbestellung)', 'fr' => 'Livraison gratuite (commande revendeur)'),
        'err_piva'   => array('it' => 'Per gli ordini a cartoni (listino rivenditori) è obbligatoria la Partita IVA.', 'en' => 'A VAT number is required for orders by the carton (reseller price list).', 'de' => 'Für Bestellungen im Karton (Händlerpreise) ist die USt-IdNr. erforderlich.', 'fr' => 'Un n° de TVA est obligatoire pour les commandes au carton (tarif revendeur).'),
        'err_max'    => array('it' => 'Massimo %d cartoni per prodotto: per quantità maggiori richiedi un preventivo.', 'en' => 'Maximum %d cartons per product: for larger quantities please request a quote.', 'de' => 'Maximal %d Kartons pro Produkt: Für größere Mengen fordern Sie bitte ein Angebot an.', 'fr' => 'Maximum %d cartons par produit : pour des quantités supérieures, demandez un devis.'),
        'err_na'     => array('it' => 'Questo prodotto non è al momento ordinabile a cartoni.', 'en' => 'This product cannot currently be ordered by the carton.', 'de' => 'Dieses Produkt ist derzeit nicht im Karton bestellbar.', 'fr' => 'Ce produit n’est pas commandable au carton pour le moment.'),
        'added'      => array('it' => 'Cartoni aggiunti al carrello.', 'en' => 'Cartons added to the cart.', 'de' => 'Kartons in den Warenkorb gelegt.', 'fr' => 'Cartons ajoutés au panier.'),
        'cart_note'  => array('it' => 'Nel carrello ci sono prodotti a cartoni (listino rivenditori): al pagamento inserisci la Partita IVA. Spedizione gratuita.', 'en' => 'Your cart contains cartons (reseller price list): please enter your VAT number at checkout. Free shipping.', 'de' => 'Ihr Warenkorb enthält Kartons (Händlerpreise): Bitte geben Sie an der Kasse Ihre USt-IdNr. ein. Kostenloser Versand.', 'fr' => 'Votre panier contient des cartons (tarif revendeur) : indiquez votre n° de TVA lors du paiement. Livraison gratuite.'),
    );
    $l = lcgf_rv_lang();
    return isset($S[$key][$l]) ? $S[$key][$l] : (isset($S[$key]['it']) ? $S[$key]['it'] : $key);
}

/** "12 confezioni da 2" / "15 pezzi" nella lingua corrente. */
function lcgf_rv_content_label($row) {
    return sprintf(lcgf_rv_s($row['unit'] === 'conf' ? 'u_conf' : 'u_pz'), $row['pcs']);
}

/* =========================================================================
 * Pagina /rivenditori/ (template dedicato, creata una tantum in 4 lingue)
 * ====================================================================== */
function lcgf_rv_page_url() {
    return function_exists('lcgf_page_url') ? lcgf_page_url(LCGF_RV_PAGE_SLUG) : home_url('/' . LCGF_RV_PAGE_SLUG . '/');
}

add_action('init', function () {
    if (get_option('lcgf_rv_pages_v1')) return;
    if (!function_exists('pll_set_post_language') || !function_exists('pll_save_post_translations')) return;
    if (get_transient('lcgf_rv_pages_lock')) return;
    set_transient('lcgf_rv_pages_lock', 1, 60);
    $defs = array(
        'it' => array('Rivenditori', 'rivenditori'),
        'en' => array('Resellers', 'resellers'),
        'de' => array('Händler', 'haendler'),
        'fr' => array('Revendeurs', 'revendeurs'),
    );
    $ids = array();
    foreach ($defs as $lang => $d) {
        $existing = get_posts(array('post_type' => 'page', 'name' => $d[1], 'post_status' => 'any', 'lang' => '', 'numberposts' => 1, 'fields' => 'ids'));
        $id = $existing ? (int) $existing[0] : wp_insert_post(array(
            'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $d[0], 'post_name' => $d[1], 'post_content' => '',
        ));
        if (!$id || is_wp_error($id)) { delete_transient('lcgf_rv_pages_lock'); return; }
        pll_set_post_language($id, $lang);
        update_post_meta($id, '_lcgf_rv_page', '1');
        $ids[$lang] = $id;
    }
    pll_save_post_translations($ids);
    update_option('lcgf_rv_pages_v1', current_time('mysql') . ' ' . wp_json_encode($ids));
    delete_transient('lcgf_rv_pages_lock');
}, 120);

function lcgf_rv_is_page() {
    return is_page() && get_post_meta(get_queried_object_id(), '_lcgf_rv_page', true) === '1';
}

add_filter('template_include', function ($tpl) {
    if (lcgf_rv_is_page()) {
        $f = __DIR__ . '/lcgf-rivenditori/page.php';
        if (file_exists($f)) return $f;
    }
    return $tpl;
}, 99);

add_action('wp_enqueue_scripts', function () {
    if (!lcgf_rv_is_page()) return;
    $f = __DIR__ . '/lcgf-rivenditori/style.css';
    if (file_exists($f)) wp_enqueue_style('lcgf-rivenditori', plugins_url('lcgf-rivenditori/style.css', __FILE__), array(), (string) filemtime($f));
}, 20);

/* =========================================================================
 * Aggiunta al carrello dalla pagina rivenditori (POST)
 * ====================================================================== */
add_action('wp_loaded', function () {
    if (empty($_POST['lcgf_rv_add']) || !function_exists('WC')) return;
    $pid = absint($_POST['lcgf_rv_add']);
    $qty = isset($_POST['lcgf_rv_qty']) ? absint($_POST['lcgf_rv_qty']) : 1;
    $back = wp_get_referer() ? wp_get_referer() : lcgf_rv_page_url();
    $row = lcgf_rv_row($pid);
    $product = wc_get_product($pid);
    if (!$row || !$product || !$product->is_purchasable() || !$product->is_in_stock()) {
        wc_add_notice(lcgf_rv_s('err_na'), 'error');
        wp_safe_redirect($back); exit;
    }
    if (is_null(WC()->cart) && function_exists('wc_load_cart')) wc_load_cart();
    // Somma ai cartoni già in carrello per lo stesso prodotto, senza superare il massimo.
    $existing = 0;
    foreach (WC()->cart->get_cart() as $k => $ci) {
        if (!empty($ci['lcgf_rv']) && (int) $ci['product_id'] === $pid) $existing += (int) $ci['quantity'];
    }
    $qty = max(1, $qty);
    if ($existing + $qty > LCGF_RV_MAX_CARTONI) {
        wc_add_notice(sprintf(lcgf_rv_s('err_max'), LCGF_RV_MAX_CARTONI), 'error');
        wp_safe_redirect($back); exit;
    }
    $ok = WC()->cart->add_to_cart($pid, $qty, 0, array(), array('lcgf_rv' => array('pcs' => (int) $row['pcs'], 'unit' => $row['unit'])));
    if ($ok) {
        wc_add_notice(lcgf_rv_s('added'), 'success');
        wp_safe_redirect(wc_get_cart_url()); exit;
    }
    wp_safe_redirect($back); exit;
}, 25);

/* =========================================================================
 * Prezzo a fasce in carrello
 * ====================================================================== */
add_action('woocommerce_before_calculate_totals', function ($cart) {
    if (!($cart instanceof WC_Cart)) return;
    foreach ($cart->get_cart() as $k => $ci) {
        if (empty($ci['lcgf_rv']) || empty($ci['data'])) continue;
        $row = lcgf_rv_row($ci['product_id']);
        if (!$row) continue;
        $p    = $ci['data'];
        $q    = min((int) $ci['quantity'], LCGF_RV_MAX_CARTONI);
        $unit = $row['prices'][lcgf_rv_tier($q)];
        $net  = round($unit * $row['pcs'], 2);                 // prezzo cartone IVA esclusa
        $price = (wc_prices_include_tax()) ? $net * (1 + lcgf_rv_vat_rate($p) / 100) : $net;
        $p->set_price($price);   // idempotente: ricalcolato da listino a ogni totals
    }
}, 20);

// Righe descrittive nel carrello/checkout (classic + blocchi).
add_filter('woocommerce_get_item_data', function ($data, $ci) {
    if (empty($ci['lcgf_rv'])) return $data;
    $row = lcgf_rv_row($ci['product_id']);
    if (!$row) return $data;
    $t = lcgf_rv_tier((int) $ci['quantity']);
    $data[] = array('key' => lcgf_rv_s('ci_format'), 'value' => sprintf(lcgf_rv_s('carton'), lcgf_rv_content_label($row)));
    $data[] = array('key' => lcgf_rv_s('ci_list'), 'value' => sprintf(lcgf_rv_s('ci_list_v'),
        lcgf_rv_money($row['prices'][$t]), lcgf_rv_s($row['unit'] === 'conf' ? 'per_conf' : 'per_pz'), lcgf_rv_tier_label($t)));
    return $data;
}, 10, 2);

// Riporta i dati sulle righe d'ordine (admin, email, fattura).
add_action('woocommerce_checkout_create_order_line_item', function ($item, $key, $values) {
    if (empty($values['lcgf_rv'])) return;
    $row = lcgf_rv_row($values['product_id']);
    if (!$row) return;
    $t = lcgf_rv_tier((int) $values['quantity']);
    $item->add_meta_data('Formato', 'Cartone da ' . sprintf($row['unit'] === 'conf' ? '%d confezioni da 2' : '%d pezzi', $row['pcs']), true);
    $item->add_meta_data('Listino rivenditori', lcgf_rv_money($row['prices'][$t]) . ' + IVA ' . ($row['unit'] === 'conf' ? 'a confezione' : 'al pezzo') . ' (fascia ' . lcgf_rv_tier_label($t) . ' cartoni)', true);
    $item->add_meta_data('_lcgf_rv', 'yes', true);
}, 10, 3);

/* Massimo cartoni per riga (blocchi Store API + classic). */
add_filter('woocommerce_store_api_product_quantity_maximum', function ($max, $product, $ci = null) {
    return (is_array($ci) && !empty($ci['lcgf_rv'])) ? min((int) $max ?: LCGF_RV_MAX_CARTONI, LCGF_RV_MAX_CARTONI) : $max;
}, 10, 3);
add_filter('woocommerce_quantity_input_args', function ($args, $product) {
    return $args; // le righe a cartoni del carrello classic sono limitate dal controllo sotto
}, 10, 2);

function lcgf_cart_has_rv() {
    if (!function_exists('WC') || !WC()->cart) return false;
    foreach (WC()->cart->get_cart() as $ci) if (!empty($ci['lcgf_rv'])) return true;
    return false;
}

add_action('woocommerce_check_cart_items', function () {
    if (!lcgf_cart_has_rv()) return;
    foreach (WC()->cart->get_cart() as $ci) {
        if (!empty($ci['lcgf_rv']) && (int) $ci['quantity'] > LCGF_RV_MAX_CARTONI) {
            wc_add_notice(sprintf(lcgf_rv_s('err_max'), LCGF_RV_MAX_CARTONI), 'error');
            return;
        }
    }
    $msg = lcgf_rv_s('cart_note');
    if (!wc_has_notice($msg, 'notice')) wc_add_notice($msg, 'notice');
});

/* =========================================================================
 * Spedizione gratuita per gli ordini con cartoni
 * Prio 120: dopo fasce peso (90), pickup (95) e free (100).
 * ====================================================================== */
add_filter('woocommerce_package_rates', function ($rates) {
    if (!lcgf_cart_has_rv()) return $rates;
    if (function_exists('lcgf_cart_has_pickup_only') && lcgf_cart_has_pickup_only()) return $rates; // ordine a ritiro
    $keep = null;
    foreach ($rates as $id => $r) {
        if ('free_shipping' === $r->get_method_id()) { $keep = $id; break; }
    }
    if ($keep === null) {
        foreach ($rates as $id => $r) {
            if ('flat_rate' === $r->get_method_id()) { $keep = $id; break; }
        }
    }
    if ($keep === null) return $rates;
    $r = $rates[$keep];
    $r->set_cost(0);
    $r->set_taxes(array());
    $r->set_label(lcgf_rv_s('ship_label'));
    return array($keep => $r);
}, 120);

/* =========================================================================
 * Partita IVA obbligatoria per gli ordini a cartoni
 * ====================================================================== */
add_action('woocommerce_store_api_checkout_update_order_from_request', function ($order, $request) {
    if (!lcgf_cart_has_rv()) return;
    $ba = $request['billing_address'];
    $v = (is_array($ba) && isset($ba['lcgf/piva'])) ? trim((string) $ba['lcgf/piva']) : '';
    if ($v === '' && function_exists('lcgf_euvat_cart_vat')) $v = trim((string) lcgf_euvat_cart_vat());
    if ($v === '') {
        throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException('lcgf_rv_piva_required', lcgf_rv_s('err_piva'), 400);
    }
}, 10, 2);
// Checkout classico (fallback)
add_action('woocommerce_after_checkout_validation', function ($data, $errors) {
    if (!lcgf_cart_has_rv()) return;
    $v = isset($_POST['lcgf/piva']) ? trim((string) $_POST['lcgf/piva']) : '';
    if ($v === '' && function_exists('lcgf_euvat_cart_vat')) $v = trim((string) lcgf_euvat_cart_vat());
    if ($v === '') $errors->add('lcgf_rv_piva', lcgf_rv_s('err_piva'));
}, 10, 2);

/* =========================================================================
 * Prezzo al dettaglio del pane: indica che è la confezione da 2
 * ====================================================================== */
add_filter('woocommerce_get_price_suffix', function ($suffix, $product) {
    if (!$product) return $suffix;
    $s = lcgf_rv_it_slug($product->get_id());
    if (in_array($s, array('pane-filoncino', 'pane-rosetta'), true)) {
        $m = array('it' => 'conf. da 2', 'en' => 'pack of 2', 'de' => '2er-Packung', 'fr' => 'paquet de 2');
        $suffix .= ' <small class="lcgf-price-unit" style="font-size:.72em;opacity:.75;font-weight:500">/ ' . esc_html($m[lcgf_rv_lang()]) . '</small>';
    }
    return $suffix;
}, 10, 2);
