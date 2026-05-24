<?php
/**
 * La Compagnia del Gluten Free — child theme functions
 * Parent: Astra (https://wordpress.org/themes/astra/)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ---------- Enqueue parent + child styles ---------- */
add_action( 'wp_enqueue_scripts', function () {
    $parent = 'astra-theme-css';
    wp_enqueue_style( 'astra-parent', get_template_directory_uri() . '/style.css', [], wp_get_theme( get_template() )->get( 'Version' ) );
    wp_enqueue_style( 'lcgf-child',  get_stylesheet_directory_uri() . '/style.css', [ 'astra-parent' ], wp_get_theme()->get( 'Version' ) );

    // Google Fonts (Fraunces + Inter) — coerenti col prototipo Node.js
    wp_enqueue_style(
        'lcgf-fonts',
        'https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap',
        [],
        null
    );
} );

/* ---------- Supporti tema ---------- */
add_action( 'after_setup_theme', function () {
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
    add_theme_support( 'woocommerce' );
} );

/* ---------- Footer EMC credit (regola assoluta) ---------- */
add_action( 'wp_footer', function () {
    $year = date( 'Y' );
    ?>
    <div style="text-align:center;padding:14px;background:#1F1B14;color:rgba(251,247,238,.55);font-size:.82rem">
        <span>&copy; <?php echo $year; ?> La Compagnia del Gluten Free — Mangia con Gusto. Tutti i diritti riservati.</span>
        &nbsp;·&nbsp;
        <a href="https://www.emcdigitalsolutions.it" target="_blank" rel="noopener noreferrer" class="emc-credit" style="color:rgba(251,247,238,.8)">
            <span>Progettato e Sviluppato da</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="70" height="20" viewBox="0 0 200 50" aria-hidden="true" style="vertical-align:middle">
                <defs>
                    <linearGradient id="emcBars" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#7a9a5a" />
                        <stop offset="100%" stop-color="#c9a96e" />
                    </linearGradient>
                    <linearGradient id="emcText" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#5a7a3a" />
                        <stop offset="50%" stop-color="#8a9a5a" />
                        <stop offset="100%" stop-color="#c9a96e" />
                    </linearGradient>
                </defs>
                <rect x="5" y="10" width="30" height="6" rx="2" fill="url(#emcBars)" />
                <rect x="5" y="22" width="20" height="6" rx="2" fill="url(#emcBars)" />
                <rect x="5" y="34" width="30" height="6" rx="2" fill="url(#emcBars)" />
                <text x="48" y="33" font-family="Arial, sans-serif" font-size="20" font-weight="700" letter-spacing="3" fill="url(#emcText)">EMC</text>
            </svg>
        </a>
    </div>
    <?php
}, 99 );

/* ---------- Badge "senza glutine / senza lattosio" sulle card prodotto ---------- */
add_action( 'woocommerce_before_shop_loop_item_title', function () {
    echo '<div class="lcgf-badges" style="position:absolute;top:10px;left:10px;z-index:2">';
    echo '<span class="lcgf-badge gf">Senza glutine</span>';
    echo '<span class="lcgf-badge lf">Senza lattosio</span>';
    echo '</div>';
}, 5 );

/* ---------- WhatsApp floating button (numero Carmelo) ---------- */
add_action( 'wp_footer', function () {
    $wa_number = '393276999897'; // Carmelo Lo Porto
    ?>
    <a href="https://wa.me/<?php echo esc_attr( $wa_number ); ?>" target="_blank" rel="noopener" aria-label="Scrivici su WhatsApp"
       style="position:fixed;bottom:24px;right:24px;width:56px;height:56px;border-radius:50%;background:#25D366;color:#fff;display:grid;place-items:center;box-shadow:0 8px 26px rgba(37,211,102,.4);z-index:90;transition:transform .3s">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor"><path d="M17.5 14.4c-.3-.1-1.7-.8-2-.9-.3-.1-.5-.2-.7.2-.2.3-.8.9-1 1.1-.2.2-.4.2-.7.1-1.9-.8-3.2-1.8-4.4-3.7-.3-.5.3-.5.9-1.5.1-.2 0-.4-.1-.5-.1-.1-.7-1.6-.9-2.2-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1.1 1.1-1.1 2.6 0 1.6 1.1 3.1 1.3 3.3.2.2 2.3 3.6 5.7 4.9 3.4 1.3 3.4.9 4 .8.6-.1 1.7-.7 2-1.4.2-.7.2-1.3.2-1.4-.1-.1-.2-.2-.5-.3ZM12 2C6.5 2 2 6.5 2 12c0 1.8.5 3.4 1.3 4.9L2 22l5.2-1.4c1.4.8 3 1.2 4.7 1.2 5.5 0 10-4.5 10-10S17.5 2 12 2Z"/></svg>
    </a>
    <?php
} );

/* ---------- Disabilita commenti su pagine/prodotti ---------- */
add_filter( 'comments_open', '__return_false', 20, 2 );

/* ---------- Numero colonne shop ---------- */
add_filter( 'loop_shop_columns', fn() => 3 );
add_filter( 'loop_shop_per_page', fn() => 12 );
