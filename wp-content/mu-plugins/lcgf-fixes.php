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
