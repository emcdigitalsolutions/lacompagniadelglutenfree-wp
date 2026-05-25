<?php
/**
 * Header LCGF — custom su Astra.
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="profile" href="https://gmpg.org/xfn/11">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="lcgf-header" id="lcgfHeader">
  <div class="lcgf-header-inner">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="lcgf-brand">
      <span class="lcgf-brand-mark">
        <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/img/logo.webp'); ?>" alt="" />
      </span>
      <span class="lcgf-brand-text">
        <strong>La Compagnia</strong>
        <small>del Gluten Free</small>
      </span>
    </a>

    <nav class="lcgf-nav" aria-label="Navigazione principale">
      <ul>
        <li><a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>">Catalogo</a></li>
        <?php
        $default_cat = (int) get_option('default_product_cat');
        $cats = get_terms([
          'taxonomy'   => 'product_cat',
          'hide_empty' => true,
          'exclude'    => array_filter([$default_cat]),
          'slug'       => ['pane-basi', 'dolci-colazione'], // mostra solo queste due
        ]);
        if (empty($cats)) {
          // fallback: tutte tranne default e vuote
          $cats = get_terms([
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'exclude'    => array_filter([$default_cat]),
          ]);
        }
        foreach ($cats as $cat) {
          if (strtolower($cat->slug) === 'uncategorized' || strtolower($cat->name) === 'senza categoria') continue;
          $url = get_term_link($cat);
          echo '<li><a href="' . esc_url($url) . '">' . esc_html($cat->name) . '</a></li>';
        }
        ?>
        <li><a href="<?php echo esc_url(home_url('/chi-siamo/')); ?>">Chi siamo</a></li>
        <li><a href="<?php echo esc_url(home_url('/contatti/')); ?>">Contatti</a></li>
      </ul>
    </nav>

    <div class="lcgf-actions">
      <a href="<?php echo esc_url(home_url('/?s=&post_type=product')); ?>" class="lcgf-icon-btn" aria-label="Cerca">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
      </a>
      <a href="<?php echo esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))); ?>" class="lcgf-icon-btn" aria-label="Account">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-7 8-7s8 3 8 7"/></svg>
      </a>
      <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="lcgf-icon-btn" aria-label="Carrello">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        <?php $count = function_exists('WC') && WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?>
        <?php if ($count > 0) : ?>
          <span class="lcgf-cart-count"><?php echo (int)$count; ?></span>
        <?php endif; ?>
      </a>
    </div>
  </div>
</header>

<main id="main" class="site-main">
