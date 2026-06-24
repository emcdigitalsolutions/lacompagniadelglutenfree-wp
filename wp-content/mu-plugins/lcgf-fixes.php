<?php
/*
Plugin Name: LCGF Fixes
Description: Fix post-go-live: immagini email (WebP->JPG), causale pagamento, avviso spam sul sito, nome/cognome in registrazione.
Version: 1.0
*/
if (!defined('ABSPATH')) exit;

/* CSS account/email: file dedicato con ver=filemtime (cache-busting, batte le regole globali del tema) */
add_action('wp_enqueue_scripts', function(){
    $f = WPMU_PLUGIN_DIR . '/lcgf-account.css';
    if (file_exists($f)) {
        wp_enqueue_style('lcgf-account', content_url('mu-plugins/lcgf-account.css'), array('lcgf-child'), filemtime($f));
    }
}, 999);

/* =======================================================================
 * #3 — IMMAGINI EMAIL: WebP -> JPG
 * I client email (Gmail, Outlook, Apple Mail) NON supportano WebP -> rotte.
 * Durante il rendering della tabella ordine nelle email sostituiamo .webp con .jpg
 * (i file .jpg gemelli sono stati generati in uploads).
 * ===================================================================== */
add_action('woocommerce_email_before_order_table', function(){ $GLOBALS['lcgf_in_email'] = true; }, 1);
add_action('woocommerce_email_after_order_table',  function(){ $GLOBALS['lcgf_in_email'] = false; }, 99);
add_filter('wp_get_attachment_image_attributes', function($attr){
    if (!empty($GLOBALS['lcgf_in_email'])) {
        foreach (array('src', 'srcset') as $k) {
            if (!empty($attr[$k])) $attr[$k] = str_replace('.webp', '.jpg', $attr[$k]);
        }
    }
    return $attr;
}, 99);
// Mostra le thumbnail prodotto nelle email (rese come JPG dal filtro sopra).
add_filter('woocommerce_email_order_items_args', function($args){
    $args['show_image']  = true;
    $args['image_size']  = array(64, 64);
    return $args;
});
// Rete di sicurezza: sull'HTML finale di OGNI email WC, converte ogni URL immagine .webp -> .jpg
// (i file .jpg gemelli esistono in uploads e nel tema). Cattura eventuali residui (es. srcset).
add_filter('woocommerce_mail_content', function($content){
    return preg_replace('#(https?://[^\s"\'<>]+?)\.webp#i', '$1.jpg', $content);
}, 99);

/* =======================================================================
 * #4 — CAUSALE DI PAGAMENTO nelle EMAIL (cliente)
 * Mostra il riferimento numero ordine da usare come causale.
 * ===================================================================== */
add_action('woocommerce_email_before_order_table', function($order, $sent_to_admin, $plain_text){
    if ($plain_text || $sent_to_admin || !is_a($order, 'WC_Order')) return;
    $num = $order->get_order_number();
    echo '<div style="background:#f4f7ef;border:1px solid #d8e3c8;border-left:4px solid #6b8e4e;border-radius:8px;padding:14px 16px;margin:0 0 22px;font-size:14px;line-height:1.55;color:#3d4a2a;">'
        . '<strong>Causale di pagamento:</strong> indica come causale <strong>Ordine #' . esc_html($num) . '</strong>'
        . ' (es. &laquo;' . esc_html(get_bloginfo('name')) . ' &ndash; Ordine #' . esc_html($num) . '&raquo;), '
        . 'cos&igrave; possiamo abbinare subito il pagamento al tuo ordine.'
        . '</div>';
}, 15, 3);

/* =======================================================================
 * #4 + #1 — CAUSALE + AVVISO SPAM nel RESOCONTO sul SITO (thank-you page)
 * ===================================================================== */
add_action('woocommerce_thankyou', function($order_id){
    $order = wc_get_order($order_id);
    if (!$order) return;
    $num = $order->get_order_number();
    echo '<div class="lcgf-order-note">'
        . '<p><span class="lcgf-on-ico">&#128179;</span> <strong>Causale di pagamento:</strong> usa come causale <strong>Ordine #' . esc_html($num) . '</strong> '
        . '(&laquo;' . esc_html(get_bloginfo('name')) . ' &ndash; Ordine #' . esc_html($num) . '&raquo;), per un riscontro immediato del pagamento.</p>'
        . '<p><span class="lcgf-on-ico">&#9993;</span> Ti abbiamo inviato una <strong>email di conferma</strong>. Se non la trovi nella posta in arrivo, '
        . 'controlla nella cartella <strong>Spam / Posta indesiderata</strong> e segna il messaggio come &laquo;Non spam&raquo; '
        . 'per ricevere correttamente i prossimi aggiornamenti.</p>'
        . '</div>';
}, 15);

/* =======================================================================
 * #8 — NOME e COGNOME nel form di REGISTRAZIONE
 * GDPR ok: dati minimi necessari per account/fatturazione, privacy gi presente.
 * Stringhe standard WooCommerce -> tradotte automaticamente IT/EN/DE/FR.
 * ===================================================================== */
add_action('woocommerce_register_form_start', function(){
    $fn = isset($_POST['first_name']) ? wc_clean(wp_unslash($_POST['first_name'])) : '';
    $ln = isset($_POST['last_name'])  ? wc_clean(wp_unslash($_POST['last_name']))  : '';
    ?>
    <p class="form-row form-row-first">
        <label for="reg_first_name"><?php esc_html_e('First name', 'woocommerce'); ?>&nbsp;<span class="required">*</span></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="first_name" id="reg_first_name" autocomplete="given-name" value="<?php echo esc_attr($fn); ?>" />
    </p>
    <p class="form-row form-row-last">
        <label for="reg_last_name"><?php esc_html_e('Last name', 'woocommerce'); ?>&nbsp;<span class="required">*</span></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="last_name" id="reg_last_name" autocomplete="family-name" value="<?php echo esc_attr($ln); ?>" />
    </p>
    <div class="clear"></div>
    <?php
});
add_filter('woocommerce_registration_errors', function($errors){
    if (empty($_POST['first_name'])) $errors->add('first_name_error', __('Inserisci il tuo nome.', 'woocommerce'));
    if (empty($_POST['last_name']))  $errors->add('last_name_error',  __('Inserisci il tuo cognome.', 'woocommerce'));
    return $errors;
}, 10, 1);
add_action('woocommerce_created_customer', function($customer_id){
    if (!empty($_POST['first_name'])) update_user_meta($customer_id, 'first_name', wc_clean(wp_unslash($_POST['first_name'])));
    if (!empty($_POST['last_name']))  update_user_meta($customer_id, 'last_name',  wc_clean(wp_unslash($_POST['last_name'])));
});

/* =======================================================================
 * #11 — PRODOTTI "SOLO RITIRO IN SEDE" (Cheesecake + Tiramisù)
 * Prodotti freschi non spedibili: ordinabili online ma con UNICO metodo
 * "Ritiro gratuito in sede" (Ravanusa). La spedizione viene bloccata se il
 * carrello contiene almeno uno di questi prodotti; per gli ordini normali il
 * ritiro resta nascosto. Avviso multilingua sul carrello + badge sulla scheda.
 * ===================================================================== */

/* Lingua corrente (Polylang) con fallback italiano. Prefisso _fx_ per non
 * collidere con lcgf_lang() già definita nel tema. */
function lcgf_fx_lang() {
    if (function_exists('pll_current_language')) {
        $l = pll_current_language('slug');
        if ($l) return $l;
    }
    return 'it';
}

/* Stringhe localizzate del modulo "solo ritiro". */
function lcgf_pickup_str($key) {
    $L = lcgf_fx_lang();
    $map = [
        'label' => [
            'it' => 'Ritiro gratuito in sede',
            'en' => 'Free pickup in store',
            'de' => 'Kostenlose Abholung im Geschäft',
            'fr' => 'Retrait gratuit en magasin',
        ],
        'notice' => [
            'it' => 'Alcuni prodotti nel carrello (Cheesecake, Tiramisù) sono disponibili <strong>solo per il ritiro</strong> presso la nostra sede di Ravanusa (AG) e non possono essere spediti. L’intero ordine sarà quindi da ritirare in sede. Per ricevere a casa gli altri prodotti, effettua un ordine separato.',
            'en' => 'Some products in your cart (Cheesecake, Tiramisù) are available for <strong>in-store pickup only</strong> at our premises in Ravanusa (AG) and cannot be shipped. The whole order will therefore be for in-store pickup. To have the other products delivered, please place a separate order.',
            'de' => 'Einige Produkte in Ihrem Warenkorb (Cheesecake, Tiramisù) sind <strong>nur zur Abholung</strong> in unserem Geschäft in Ravanusa (AG) verfügbar und können nicht versandt werden. Die gesamte Bestellung ist daher nur zur Abholung. Um die anderen Produkte liefern zu lassen, geben Sie bitte eine separate Bestellung auf.',
            'fr' => 'Certains produits de votre panier (Cheesecake, Tiramisù) sont disponibles <strong>uniquement pour le retrait</strong> dans notre établissement à Ravanusa (AG) et ne peuvent pas être expédiés. Toute la commande sera donc à retirer sur place. Pour faire livrer les autres produits, veuillez passer une commande séparée.',
        ],
        'badge_title' => [
            'it' => 'Solo ritiro in sede',
            'en' => 'In-store pickup only',
            'de' => 'Nur Abholung im Geschäft',
            'fr' => 'Retrait en magasin uniquement',
        ],
        'badge_text' => [
            'it' => 'Prodotto fresco disponibile <strong>esclusivamente per il ritiro</strong> presso la nostra sede di Ravanusa (AG). Non viene spedito.',
            'en' => 'Fresh product available <strong>for in-store pickup only</strong> at our premises in Ravanusa (AG). It is not shipped.',
            'de' => 'Frisches Produkt <strong>nur zur Abholung</strong> in unserem Geschäft in Ravanusa (AG) verfügbar. Es wird nicht versendet.',
            'fr' => 'Produit frais disponible <strong>uniquement pour le retrait</strong> dans notre établissement à Ravanusa (AG). Il n’est pas expédié.',
        ],
        'row_label' => [
            'it' => 'Ritiro', 'en' => 'Pickup', 'de' => 'Abholung', 'fr' => 'Retrait',
        ],
        'row_val' => [
            'it' => 'Solo in sede (Ravanusa)',
            'en' => 'In-store only (Ravanusa)',
            'de' => 'Nur im Geschäft (Ravanusa)',
            'fr' => 'En magasin uniquement (Ravanusa)',
        ],
    ];
    return $map[$key][$L] ?? $map[$key]['it'];
}

/* Il carrello contiene almeno un prodotto della classe "solo-ritiro"? */
function lcgf_cart_has_pickup_only() {
    if (!function_exists('WC') || is_null(WC()->cart)) return false;
    foreach (WC()->cart->get_cart() as $item) {
        if (!empty($item['product_id']) && has_term('solo-ritiro', 'product_shipping_class', $item['product_id'])) {
            return true;
        }
    }
    return false;
}

/* Rate di spedizione: ritiro-only se il carrello lo richiede, altrimenti nascondi il ritiro.
 * Priorità 95: gira PRIMA del filtro free_shipping del tema (priorità 100), che poi
 * non trova free_shipping tra i rate residui e li lascia invariati. */
add_filter('woocommerce_package_rates', function ($rates) {
    if (lcgf_cart_has_pickup_only()) {
        $pickup = [];
        foreach ($rates as $id => $r) {
            if ('local_pickup' === $r->get_method_id()) {
                $r->set_label(lcgf_pickup_str('label'));
                $pickup[$id] = $r;
            }
        }
        return !empty($pickup) ? $pickup : $rates;
    }
    // Ordine normale: il ritiro non deve comparire.
    foreach ($rates as $id => $r) {
        if ('local_pickup' === $r->get_method_id()) unset($rates[$id]);
    }
    return $rates;
}, 95);

/* Avviso sul carrello/checkout (compatibile coi blocchi via Store API). */
add_action('woocommerce_check_cart_items', function () {
    if (lcgf_cart_has_pickup_only()) {
        $msg = lcgf_pickup_str('notice');
        if (!wc_has_notice($msg, 'notice')) wc_add_notice($msg, 'notice');
    }
});

/* Badge "solo ritiro" per la scheda prodotto (chiamato dal template single-product.php). */
function lcgf_pickup_badge_html($product) {
    if (!$product || !has_term('solo-ritiro', 'product_shipping_class', $product->get_id())) return '';
    return '<div style="margin:18px 0 6px;padding:14px 18px;background:#fff6e6;border:1px solid #f0d79a;border-left:4px solid #d8a32e;border-radius:12px;display:flex;align-items:flex-start;gap:12px;font-size:.92rem;line-height:1.55;color:#6b4e15">'
        . '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:1px"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>'
        . '<span><strong>' . esc_html(lcgf_pickup_str('badge_title')) . '</strong><br>' . wp_kses_post(lcgf_pickup_str('badge_text')) . '</span>'
        . '</div>';
}

/* Once-run: crea la classe di spedizione, la assegna a Cheesecake/Tiramisù (tutte
 * le lingue) e aggiunge "Ritiro gratuito in sede" a ogni zona. Idempotente. */
add_action('init', function () {
    if (get_option('lcgf_pickup_v2')) return;
    if (!class_exists('WC_Shipping_Zones')) return;

    // 1) Classe di spedizione "solo-ritiro"
    $term = get_term_by('slug', 'solo-ritiro', 'product_shipping_class');
    if (!$term) {
        $res = wp_insert_term('Solo ritiro in sede', 'product_shipping_class', ['slug' => 'solo-ritiro']);
        if (is_wp_error($res)) return;
    }

    // 2) Assegna a Cheesecake/Tiramisù in TUTTE le lingue.
    // 'lang' => '' disattiva il filtro lingua di Polylang (altrimenti get_posts
    // tornerebbe solo i prodotti della lingua corrente); in più, per ogni match
    // espandiamo alle traduzioni Polylang per sicurezza.
    $assigned = 0;
    $pids = get_posts(['post_type' => 'product', 'numberposts' => -1, 'fields' => 'ids', 'post_status' => 'any', 'lang' => '']);
    foreach ($pids as $pid) {
        if (!preg_match('/^(cheesecake|tiramis)/i', trim(get_the_title($pid)))) continue;
        $targets = [$pid];
        if (function_exists('pll_get_post_translations')) {
            $targets = array_merge($targets, array_values(pll_get_post_translations($pid)));
        }
        foreach (array_unique($targets) as $tid) {
            wp_set_object_terms($tid, 'solo-ritiro', 'product_shipping_class', false);
            $assigned++;
        }
    }

    // 3) Aggiungi "Ritiro gratuito in sede" a ogni zona (incl. zona 0 "resto del mondo")
    $zone_ids = [0];
    foreach (WC_Shipping_Zones::get_zones() as $z) $zone_ids[] = (int) $z['id'];
    foreach (array_unique($zone_ids) as $zid) {
        $zone = new WC_Shipping_Zone($zid);
        $has_lp = false;
        foreach ($zone->get_shipping_methods() as $m) {
            if ('local_pickup' === $m->id) { $has_lp = true; break; }
        }
        if (!$has_lp) {
            $iid = $zone->add_shipping_method('local_pickup');
            update_option("woocommerce_local_pickup_{$iid}_settings", [
                'title'      => 'Ritiro gratuito in sede',
                'tax_status' => 'none',
                'cost'       => '0',
            ]);
        }
    }

    update_option('lcgf_pickup_v2', current_time('mysql') . " (shipping class solo-ritiro, {$assigned} prodotti tutte lingue, local_pickup su tutte le zone)");
}, 110);
