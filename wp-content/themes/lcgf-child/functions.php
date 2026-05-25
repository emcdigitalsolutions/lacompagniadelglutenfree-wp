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

/* ---------- Auto-import immagini prodotti se mancanti ---------- */
add_action('admin_init', function () {
    if (get_option('lcgf_images_imported_v2') === 'done') return;
    if (!current_user_can('manage_options')) return;
    if (!class_exists('WooCommerce')) return;

    $mapping = [
        'box-family'        => 'box-family.png',
        'pinsa-romana'      => 'pinsa-romana.png',
        'pan-focaccia'      => 'pan-focaccia.png',
        'focaccia-rotonda'  => 'focaccia-rotonda.png',
        'base-pizza'        => 'base-pizza.png',
        'pane-filoncino'    => 'pane-filoncino.png',
        'pane-rosetta'      => 'pane-rosetta.png',
        'brioche'           => 'brioche.png',
        'cornetto-vuoto'    => 'cornetto.png',
        'crostate'          => 'crostate.png',
        'biscotti'          => 'biscotti.png',
        'tiramisu'          => 'tiramisu.png',
        'cheesecake'        => 'cheesecake.png',
    ];

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $imported = 0;
    foreach ($mapping as $slug => $filename) {
        $page = get_page_by_path($slug, OBJECT, 'product');
        if (!$page) continue;
        $product_id = $page->ID;
        if (has_post_thumbnail($product_id)) continue;

        $src = get_stylesheet_directory() . '/assets/products/' . $filename;
        if (!file_exists($src)) continue;

        $upload = wp_upload_dir();
        $new_filename = wp_unique_filename($upload['path'], $filename);
        $dest = trailingslashit($upload['path']) . $new_filename;
        if (!@copy($src, $dest)) continue;

        $wp_filetype = wp_check_filetype($new_filename, null);
        $att_id = wp_insert_attachment([
            'guid'           => trailingslashit($upload['url']) . $new_filename,
            'post_mime_type' => $wp_filetype['type'],
            'post_title'     => $page->post_title,
            'post_content'   => '',
            'post_status'    => 'inherit',
        ], $dest, $product_id);
        if (is_wp_error($att_id)) continue;
        $meta = wp_generate_attachment_metadata($att_id, $dest);
        wp_update_attachment_metadata($att_id, $meta);
        set_post_thumbnail($product_id, $att_id);
        $imported++;
    }

    if ($imported > 0) {
        set_transient('lcgf_images_notice', $imported, 30);
    }
    update_option('lcgf_images_imported_v2', 'done');
});

add_action('admin_notices', function () {
    $imported = get_transient('lcgf_images_notice');
    if (!$imported) return;
    delete_transient('lcgf_images_notice');
    echo '<div class="notice notice-success is-dismissible"><p>🌾 LCGF: ' . (int)$imported . ' immagini prodotto importate automaticamente.</p></div>';
});
