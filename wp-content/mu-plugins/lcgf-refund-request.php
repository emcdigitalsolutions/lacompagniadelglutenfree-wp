<?php
/**
 * Plugin Name: LCGF — Richiesta rimborso cliente
 * Description: Aggiunge un pulsante "Richiedi un rimborso" nel riepilogo dell'ordine
 *   (pagina "ordine ricevuto" + "Il mio account") e nelle email al cliente, per gli
 *   ordini effettivamente pagati. Il pulsante apre un'email pre-compilata verso la
 *   casella ordini del negozio col numero d'ordine; il rimborso viene poi gestito
 *   manualmente dallo staff dal pannello WooCommerce.
 *
 * @author EMC Digital Solutions
 */

if (!defined('ABSPATH')) exit;

/** Lingua corrente (slug 2 lettere), fallback IT. */
function lcgf_rr_lang() {
    if (function_exists('pll_current_language')) {
        $l = pll_current_language('slug');
        if ($l) return $l;
    }
    return substr(get_locale(), 0, 2) ?: 'it';
}

/** Email del negozio a cui inviare la richiesta (casella ordini, fallback admin). */
function lcgf_rr_shop_email() {
    $no = get_option('woocommerce_new_order_settings');
    $e  = (is_array($no) && !empty($no['recipient'])) ? $no['recipient'] : get_option('admin_email');
    $e  = trim(explode(',', (string) $e)[0]); // se ci sono più destinatari, prendi il primo
    return $e ?: get_option('admin_email');
}

/** Mostra il pulsante solo per ordini pagati e non già rimborsati. */
function lcgf_rr_should_show($order) {
    if (!is_a($order, 'WC_Order')) return false;
    if (in_array($order->get_status(), array('refunded', 'cancelled', 'failed'), true)) return false;
    return (bool) $order->is_paid();
}

/** Stringhe localizzate. */
function lcgf_rr_strings($lang) {
    $all = array(
        'it' => array(
            'title'   => 'Hai bisogno di assistenza?',
            'intro'   => 'Se qualcosa non va con il tuo ordine puoi richiedere un rimborso: ti ricontatteremo al più presto.',
            'button'  => 'Richiedi un rimborso',
            'subject' => 'Richiesta di rimborso – Ordine #%s',
            'body'    => "Salve,\nvorrei richiedere il rimborso per l'ordine #%s.\n\nMotivo della richiesta:\n\n\nGrazie.",
        ),
        'en' => array(
            'title'   => 'Need any help?',
            'intro'   => 'If something is wrong with your order you can request a refund: we will get back to you as soon as possible.',
            'button'  => 'Request a refund',
            'subject' => 'Refund request – Order #%s',
            'body'    => "Hello,\nI would like to request a refund for order #%s.\n\nReason for the request:\n\n\nThank you.",
        ),
        'de' => array(
            'title'   => 'Brauchst du Hilfe?',
            'intro'   => 'Falls mit deiner Bestellung etwas nicht stimmt, kannst du eine Rückerstattung anfordern: Wir melden uns so schnell wie möglich.',
            'button'  => 'Rückerstattung anfordern',
            'subject' => 'Rückerstattungsanfrage – Bestellung #%s',
            'body'    => "Hallo,\nich möchte eine Rückerstattung für die Bestellung #%s anfordern.\n\nGrund der Anfrage:\n\n\nDanke.",
        ),
        'fr' => array(
            'title'   => "Besoin d'aide ?",
            'intro'   => "Si quelque chose ne va pas avec votre commande, vous pouvez demander un remboursement : nous vous recontacterons au plus vite.",
            'button'  => 'Demander un remboursement',
            'subject' => 'Demande de remboursement – Commande #%s',
            'body'    => "Bonjour,\nje souhaite demander le remboursement de la commande #%s.\n\nMotif de la demande :\n\n\nMerci.",
        ),
    );
    return $all[$lang] ?? $all['it'];
}

/** Costruisce il link mailto pre-compilato. */
function lcgf_rr_mailto($order) {
    $s   = lcgf_rr_strings(lcgf_rr_lang());
    $num = $order->get_order_number();
    $subject = sprintf($s['subject'], $num);
    $body    = sprintf($s['body'], $num);
    return 'mailto:' . rawurlencode(lcgf_rr_shop_email())
        . '?subject=' . rawurlencode($subject)
        . '&body=' . rawurlencode($body);
}

/* ---- 1) Riepilogo ordine sul SITO (ordine ricevuto + Il mio account) ---- */
add_action('woocommerce_order_details_after_order_table', function ($order) {
    if (!lcgf_rr_should_show($order)) return;
    $s = lcgf_rr_strings(lcgf_rr_lang());
    echo '<div class="lcgf-refund-req">'
        . '<p class="lcgf-rr-title">' . esc_html($s['title']) . '</p>'
        . '<p class="lcgf-rr-intro">' . esc_html($s['intro']) . '</p>'
        . '<a class="lcgf-rr-btn" href="' . esc_url(lcgf_rr_mailto($order)) . '">' . esc_html($s['button']) . '</a>'
        . '</div>';
});

/* ---- CSS (solo pagine ordine ricevuto / account) ---- */
add_action('wp_head', function () {
    $show = (function_exists('is_order_received_page') && is_order_received_page())
        || (function_exists('is_account_page') && is_account_page());
    if (!$show) return;
    ?>
<style id="lcgf-refund-req-css">
.lcgf-refund-req{margin:28px 0 0;padding:20px 22px;background:var(--c-cream-2,#F4EDDC);border:1px solid var(--c-line,#E6DECB);border-radius:var(--r-md,14px)}
.lcgf-rr-title{font-family:var(--f-display,"Fraunces",Georgia,serif);font-weight:600;color:var(--c-ink,#1F1B14);font-size:1.08rem;margin:0 0 4px}
.lcgf-rr-intro{font-family:var(--f-sans,"Inter",sans-serif);color:var(--c-muted,#6A6053);font-size:.95rem;line-height:1.55;margin:0 0 14px}
.lcgf-rr-btn{display:inline-block;background:transparent;color:var(--c-olive-dark,#4F6E37)!important;border:1.5px solid var(--c-olive,#6B8E4E);text-decoration:none;font-family:var(--f-sans,"Inter",sans-serif);font-weight:600;font-size:.95rem;padding:10px 24px;border-radius:var(--r-pill,999px);transition:background .2s ease,color .2s ease}
.lcgf-rr-btn:hover{background:var(--c-olive,#6B8E4E);color:#fff!important}
</style>
<?php
}, 20);

/* ---- 2) Email al CLIENTE (HTML, ordini pagati) ---- */
add_action('woocommerce_email_after_order_table', function ($order, $sent_to_admin, $plain_text) {
    if ($sent_to_admin || $plain_text || !lcgf_rr_should_show($order)) return;
    $s = lcgf_rr_strings(lcgf_rr_lang());
    $href = esc_url(lcgf_rr_mailto($order));
    echo '<div style="margin:18px 0 8px;padding:16px 18px;background:#F4EDDC;border:1px solid #E6DECB;border-radius:10px;font-family:Inter,Arial,sans-serif;">'
        . '<p style="margin:0 0 4px;font-size:15px;font-weight:600;color:#1F1B14;">' . esc_html($s['title']) . '</p>'
        . '<p style="margin:0 0 12px;font-size:13.5px;line-height:1.5;color:#6A6053;">' . esc_html($s['intro']) . '</p>'
        . '<a href="' . $href . '" style="display:inline-block;background:#4F6E37;color:#ffffff;text-decoration:none;font-size:14px;font-weight:600;padding:11px 24px;border-radius:999px;">' . esc_html($s['button']) . '</a>'
        . '</div>';
}, 25, 3);
