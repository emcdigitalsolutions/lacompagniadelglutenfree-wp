<?php
/**
 * Plugin Name: LCGF — Impaginazione pagina ordine
 * Description: Cura l'impaginazione della pagina "ordine ricevuto" e della vista
 *   ordine in "Il mio account": titoli alle giuste dimensioni, spaziatura coerente
 *   tra le sezioni, riepilogo ordine a card, dati bancari in pannello, tabella
 *   ordine pulita e indirizzi (fatturazione/spedizione) affiancati a due colonne.
 *   Solo presentazione (CSS), nessuna modifica ai dati.
 *
 * @author EMC Digital Solutions
 */

if (!defined('ABSPATH')) exit;

add_action('wp_head', function () {
    $show = (function_exists('is_order_received_page') && is_order_received_page())
        || (function_exists('is_account_page') && is_account_page());
    if (!$show) return;
    ?>
<style id="lcgf-order-received-css">
/* contenitore */
.woocommerce-order{max-width:860px;margin-left:auto;margin-right:auto}
/* messaggio di conferma */
.woocommerce-order>.woocommerce-notice--success,.woocommerce-thankyou-order-received{font-family:var(--f-display,"Fraunces",Georgia,serif);font-weight:600;font-size:clamp(1.3rem,3.4vw,1.6rem);color:var(--c-ink,#1F1B14);line-height:1.25;margin:0 0 24px;padding:0;border:0;background:none}
/* riepilogo ordine -> card */
.woocommerce-order-overview{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;list-style:none;margin:0 0 8px;padding:0;border:0}
.woocommerce-order-overview li{margin:0;padding:14px 16px;background:var(--c-cream-2,#F4EDDC);border:1px solid var(--c-line,#E6DECB)!important;border-radius:var(--r-md,14px);display:flex;flex-direction:column;gap:5px;font-family:var(--f-sans,"Inter",sans-serif);font-size:.68rem;letter-spacing:.6px;text-transform:uppercase;color:var(--c-muted,#6A6053);word-break:break-word}
.woocommerce-order-overview li strong{font-size:1.02rem;letter-spacing:0;text-transform:none;color:var(--c-ink,#1F1B14);font-weight:600}
/* spaziatura coerente tra sezioni */
.woocommerce-order>p,.woocommerce-bacs-bank-details,.woocommerce-order-details,.woocommerce-customer-details{margin-top:36px}
.woocommerce-order>p{font-family:var(--f-sans,"Inter",sans-serif);color:var(--c-muted,#6A6053);font-size:.92rem;line-height:1.6;margin-bottom:0}
/* titoli sezione */
.wc-bacs-bank-details-heading,.woocommerce-order-details__title,.woocommerce-customer-details .woocommerce-column__title{font-family:var(--f-display,"Fraunces",Georgia,serif)!important;font-weight:600!important;color:var(--c-ink,#1F1B14)!important;font-size:1.35rem!important;line-height:1.2!important;margin:0 0 16px!important}
/* dati bancari -> pannello */
.woocommerce-bacs-bank-details{background:var(--c-cream,#FBF7EE);border:1px solid var(--c-line,#E6DECB);border-radius:var(--r-lg,22px);padding:22px 24px}
.wc-bacs-bank-details-account-name{font-family:var(--f-sans,"Inter",sans-serif);font-size:.95rem;color:var(--c-muted,#6A6053);font-weight:600;margin:0 0 12px}
ul.wc-bacs-bank-details{list-style:none;margin:0;padding:0;display:grid;gap:8px}
ul.wc-bacs-bank-details li{padding:0;margin:0;border:0;font-family:var(--f-sans,"Inter",sans-serif);color:var(--c-muted,#6A6053);font-size:.9rem}
ul.wc-bacs-bank-details li strong{color:var(--c-ink,#1F1B14);font-family:ui-monospace,"SFMono-Regular",Menlo,Consolas,monospace;font-weight:600;letter-spacing:.3px}
/* tabella ordine */
.woocommerce-table--order-details{width:100%;border-collapse:collapse;border:1px solid var(--c-line,#E6DECB);border-radius:var(--r-md,14px);overflow:hidden;margin:0}
.woocommerce-table--order-details th,.woocommerce-table--order-details td{padding:12px 16px;text-align:left;border-bottom:1px solid var(--c-line,#E6DECB);font-family:var(--f-sans,"Inter",sans-serif);font-size:.92rem;color:var(--c-ink-soft,#3D362C)}
.woocommerce-table--order-details thead th{background:var(--c-cream-2,#F4EDDC);color:var(--c-ink,#1F1B14);font-weight:600;text-transform:uppercase;letter-spacing:.5px;font-size:.7rem}
.woocommerce-table--order-details tfoot th{font-weight:600;color:var(--c-ink,#1F1B14)}
.woocommerce-table--order-details td.product-total,.woocommerce-table--order-details tfoot td{text-align:right}
.woocommerce-table--order-details tfoot tr:last-child th,.woocommerce-table--order-details tfoot tr:last-child td{border-bottom:0}
/* indirizzi: due colonne (fatturazione | spedizione) quando entrambi presenti */
.woocommerce-customer-details{margin-bottom:8px}
.woocommerce-customer-details .woocommerce-columns--addresses{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin:0}
.woocommerce-customer-details .woocommerce-column{margin:0;min-width:0}
.woocommerce-customer-details .woocommerce-column__title{font-size:1.08rem!important;margin:0 0 12px!important}
.woocommerce-customer-details address{font-style:normal;font-family:var(--f-sans,"Inter",sans-serif);color:var(--c-ink-soft,#3D362C);font-size:.93rem;line-height:1.85;background:var(--c-cream-2,#F4EDDC);border:1px solid var(--c-line,#E6DECB);border-radius:var(--r-md,14px);padding:16px 18px;margin:0}
.woocommerce-customer-details address .woocommerce-customer-details--phone,.woocommerce-customer-details address .woocommerce-customer-details--email{margin:10px 0 0;font-size:.88rem;color:var(--c-muted,#6A6053)}
@media(max-width:640px){.woocommerce-customer-details .woocommerce-columns--addresses{grid-template-columns:1fr}}
</style>
<?php
}, 21);
