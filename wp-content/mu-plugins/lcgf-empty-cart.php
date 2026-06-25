<?php
/**
 * Plugin Name: LCGF — Carrello vuoto brandizzato
 * Description: Trasforma la schermata "Il tuo carrello è vuoto" (blocco WooCommerce)
 *   in una pagina curata e coerente col brand: icona su misura, titolo serif,
 *   sottotitolo amichevole multilingua, call-to-action verso il catalogo e i
 *   prodotti consigliati impaginati a card. Solo lato presentazione, nessun dato.
 *
 * @author EMC Digital Solutions
 */

if (!defined('ABSPATH')) exit;

/** Lingua corrente (slug a 2 lettere), con fallback IT. */
function lcgf_ec_lang() {
    if (function_exists('pll_current_language')) {
        $l = pll_current_language('slug');
        if ($l) return $l;
    }
    return substr(get_locale(), 0, 2) ?: 'it';
}

/**
 * Inietta sottotitolo + CTA dentro al blocco "empty-cart", subito dopo il titolo.
 * Lato server (render_block) così è presente già al primo caricamento.
 */
add_filter('render_block', function ($content, $block) {
    if (($block['blockName'] ?? '') !== 'woocommerce/empty-cart-block') return $content;

    $lang = lcgf_ec_lang();

    $subtitles = array(
        'it' => 'Non hai ancora aggiunto nulla. Scopri le nostre specialità senza glutine e riempi il carrello di bontà.',
        'en' => "You haven't added anything yet. Discover our gluten-free specialities and fill your cart with goodness.",
        'de' => 'Du hast noch nichts hinzugefügt. Entdecke unsere glutenfreien Spezialitäten und fülle deinen Warenkorb.',
        'fr' => "Vous n'avez encore rien ajouté. Découvrez nos spécialités sans gluten et remplissez votre panier.",
    );
    $ctas = array(
        'it' => 'Scopri il catalogo',
        'en' => 'Browse the shop',
        'de' => 'Zum Shop',
        'fr' => 'Voir la boutique',
    );

    $subtitle = $subtitles[$lang] ?? $subtitles['it'];
    $cta      = $ctas[$lang] ?? $ctas['it'];

    // URL del catalogo nella lingua corrente: parte dalla pagina shop di WooCommerce
    // e ne prende la traduzione Polylang, così in EN/DE/FR non rimanda alla home IT.
    $shop_id  = function_exists('wc_get_page_id') ? wc_get_page_id('shop') : 0;
    if ($shop_id > 0 && function_exists('pll_get_post')) {
        $tr = pll_get_post($shop_id, $lang);
        if ($tr) $shop_id = $tr;
    }
    $shop_url = $shop_id > 0 ? get_permalink($shop_id) : home_url('/');

    $inject = '<p class="lcgf-empty-sub">' . esc_html($subtitle) . '</p>'
        . '<div class="lcgf-empty-cta"><a class="lcgf-empty-btn" href="' . esc_url($shop_url) . '">' . esc_html($cta) . '</a></div>';

    // Inserisci dopo la chiusura del titolo del carrello vuoto.
    $anchor = 'wc-block-cart__empty-cart__title';
    $pos = strpos($content, $anchor);
    if ($pos !== false) {
        $end = strpos($content, '</h2>', $pos);
        if ($end !== false) {
            $end += 5; // lunghezza di "</h2>"
            return substr($content, 0, $end) . $inject . substr($content, $end);
        }
    }
    return $content;
}, 10, 2);

/** CSS di stile del carrello vuoto: caricato solo sulla pagina carrello. */
add_action('wp_head', function () {
    if (!function_exists('is_cart') || !is_cart()) return;

    // Icona "shopping bag" disegnata su misura (sorride), colorata col gradiente brand via mask.
    $svg = "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64' fill='none' stroke='#000' stroke-width='3.2' stroke-linecap='round' stroke-linejoin='round'>"
        . "<path d='M15 23h34a2 2 0 0 1 2 2.2l-2.6 27.6a6 6 0 0 1-6 5.4H21.6a6 6 0 0 1-6-5.4L13 25.2A2 2 0 0 1 15 23Z'/>"
        . "<path d='M24 25v-3.5a8 8 0 0 1 16 0V25'/>"
        . "<path d='M26 39q6 5 12 0'/>"
        . "</svg>";
    $icon = 'data:image/svg+xml,' . rawurlencode($svg);
    ?>
<style id="lcgf-empty-cart-css">
.wp-block-woocommerce-empty-cart-block{max-width:760px;margin:8px auto 0;text-align:center;padding:44px 22px 8px}
.wc-block-cart__empty-cart__title.with-empty-cart-icon{font-family:var(--f-display,"Fraunces",Georgia,serif);font-weight:600;font-size:clamp(1.65rem,4.2vw,2.3rem);color:var(--c-ink,#1F1B14);line-height:1.15;margin:0 0 8px}
.wc-block-cart__empty-cart__title.with-empty-cart-icon::before{content:"";display:block;width:108px;height:108px;margin:0 auto 22px;background:var(--g-cta,linear-gradient(135deg,#6B8E4E,#364E25));-webkit-mask:url("<?php echo $icon; ?>") center/contain no-repeat;mask:url("<?php echo $icon; ?>") center/contain no-repeat}
.lcgf-empty-sub{font-family:var(--f-sans,"Inter",sans-serif);color:var(--c-muted,#6A6053);font-size:1.04rem;line-height:1.6;max-width:480px;margin:0 auto 28px}
.lcgf-empty-cta{margin:0 0 6px}
.lcgf-empty-btn{display:inline-flex;align-items:center;gap:.55em;background:var(--g-cta,linear-gradient(135deg,#6B8E4E,#364E25));color:#fff!important;text-decoration:none;font-family:var(--f-sans,"Inter",sans-serif);font-weight:600;font-size:1.02rem;letter-spacing:.2px;padding:14px 32px;border-radius:var(--r-pill,999px);box-shadow:var(--sh-2,0 4px 12px rgba(31,27,20,.12));transition:transform .2s ease,box-shadow .2s ease}
.lcgf-empty-btn::after{content:"\2192";font-size:1.12em;transition:transform .2s ease}
.lcgf-empty-btn:hover{transform:translateY(-2px);box-shadow:var(--sh-3,0 10px 30px rgba(31,27,20,.14))}
.lcgf-empty-btn:hover::after{transform:translateX(4px)}
/* heading "Novità in negozio" */
.wp-block-woocommerce-empty-cart-block>h2.wp-block-heading:not(.with-empty-cart-icon){font-family:var(--f-display,"Fraunces",Georgia,serif);font-weight:600;color:var(--c-ink,#1F1B14);font-size:1.32rem;margin:54px 0 22px;padding-top:30px;position:relative}
.wp-block-woocommerce-empty-cart-block>h2.wp-block-heading:not(.with-empty-cart-icon)::before{content:"";position:absolute;top:0;left:50%;transform:translateX(-50%);width:58px;height:3px;border-radius:2px;background:var(--c-wheat,#C9A96E)}
/* prodotti consigliati a card */
.wp-block-woocommerce-product-new .wc-block-grid__products{gap:18px}
.wp-block-woocommerce-product-new .wc-block-grid__product{background:var(--c-white,#fff);border:1px solid var(--c-line,#E6DECB);border-radius:var(--r-md,14px);padding:14px 14px 18px;box-shadow:var(--sh-1,0 1px 3px rgba(31,27,20,.08));transition:transform .2s ease,box-shadow .2s ease}
.wp-block-woocommerce-product-new .wc-block-grid__product:hover{transform:translateY(-3px);box-shadow:var(--sh-2,0 4px 12px rgba(31,27,20,.12))}
.wp-block-woocommerce-product-new .wc-block-grid__product-title{font-family:var(--f-sans,"Inter",sans-serif);font-weight:600;color:var(--c-ink,#1F1B14);font-size:.98rem;margin-top:10px}
.wp-block-woocommerce-product-new .wc-block-grid__product-price{color:var(--c-olive-dark,#4F6E37);font-weight:600}
.wp-block-woocommerce-product-new .wp-block-button__link,.wp-block-woocommerce-product-new .add_to_cart_button{background:var(--c-olive,#6B8E4E)!important;color:#fff!important;border-radius:var(--r-pill,999px)!important;font-family:var(--f-sans,"Inter",sans-serif);font-weight:600}
.wp-block-woocommerce-product-new .wp-block-button__link:hover,.wp-block-woocommerce-product-new .add_to_cart_button:hover{background:var(--c-olive-dark,#4F6E37)!important}
@media(max-width:600px){.wp-block-woocommerce-empty-cart-block{padding:32px 16px 4px}.wc-block-cart__empty-cart__title.with-empty-cart-icon::before{width:92px;height:92px}}
</style>
<?php
}, 20);
