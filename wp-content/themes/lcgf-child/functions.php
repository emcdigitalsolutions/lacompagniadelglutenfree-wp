<?php
/**
 * La Compagnia del Gluten Free — child theme functions
 * Parent: Astra
 */
if (!defined('ABSPATH')) exit;

/* ---------- Enqueue ---------- */
add_action('wp_enqueue_scripts', function () {
    // parent
    wp_enqueue_style('astra-parent', get_template_directory_uri() . '/style.css', [], wp_get_theme(get_template())->get('Version'));
    // child (cache busting via filemtime)
    $child_css = get_stylesheet_directory() . '/style.css';
    $ver = file_exists($child_css) ? filemtime($child_css) : '0.2.0';
    wp_enqueue_style('lcgf-child', get_stylesheet_directory_uri() . '/style.css', ['astra-parent'], $ver);

    // Google Fonts
    wp_enqueue_style(
        'lcgf-fonts',
        'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500&family=Inter:wght@300;400;500;600;700&display=swap',
        [],
        null
    );
}, 20);

/* ---------- Supporti tema ---------- */
add_action('after_setup_theme', function () {
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    add_theme_support('woocommerce');
    add_theme_support('title-tag');
});

/* ---------- Nascondi Astra default header/footer (li riscrive il child) ---------- */
add_filter('astra_main_header_display', '__return_false');
add_filter('ast_footer_section_display', '__return_false');
add_filter('astra_page_layout', function () { return 'no-sidebar'; });
add_filter('astra_the_title_enabled', '__return_false');
add_filter('astra_content_layout', function () { return 'page-builder'; });

/* ---------- WooCommerce wrapper override ---------- */
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content',  'woocommerce_output_content_wrapper_end', 10);
add_action('woocommerce_before_main_content', function () { echo '<div class="container" style="padding-top:40px;padding-bottom:40px">'; }, 10);
add_action('woocommerce_after_main_content',  function () { echo '</div>'; }, 10);

/* ---------- Disable sidebar ---------- */
add_filter('woocommerce_show_page_title', '__return_false');
remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);

/* ---------- Numero colonne shop ---------- */
add_filter('loop_shop_columns', fn() => 3);
add_filter('loop_shop_per_page', fn() => 12);

/* ---------- Disabilita commenti su pagine/prodotti ---------- */
add_filter('comments_open', '__return_false', 20, 2);

/* ---------- Allunga thumb shop ---------- */
add_action('after_setup_theme', function () {
    add_image_size('lcgf-card', 600, 600, true);
});
