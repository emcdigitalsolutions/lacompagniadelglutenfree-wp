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

    <?php
    // Voci di navigazione (riusate per nav desktop + menu mobile)
    $lcgf_nav_items = [];
    $lcgf_nav_items[] = ['url' => get_permalink(wc_get_page_id('shop')), 'label_html' => lcgf_t('nav_catalogo')];
    $default_cat = (int) get_option('default_product_cat');
    $cats = get_terms([
      'taxonomy'   => 'product_cat',
      'hide_empty' => true,
      'exclude'    => array_filter([$default_cat]),
      'slug'       => ['pane-basi', 'dolci-colazione'],
    ]);
    if (empty($cats) || is_wp_error($cats)) {
      $cats = get_terms(['taxonomy'=>'product_cat','hide_empty'=>true,'exclude'=>array_filter([$default_cat])]);
    }
    if (!is_wp_error($cats)) foreach ($cats as $cat) {
      if (strtolower($cat->slug) === 'uncategorized' || strtolower($cat->name) === 'senza categoria') continue;
      $lcgf_nav_items[] = ['url' => get_term_link($cat), 'label_html' => esc_html($cat->name)];
    }
    $lcgf_nav_items[] = ['url' => get_post_type_archive_link('lcgf_evento'), 'label_html' => lcgf_t('nav_fiere')];
    $lcgf_nav_items[] = ['url' => lcgf_page_url('chi-siamo'), 'label_html' => lcgf_t('nav_chisiamo')];
    $lcgf_nav_items[] = ['url' => lcgf_page_url('contatti'), 'label_html' => lcgf_t('nav_contatti')];
    ?>
    <nav class="lcgf-nav" aria-label="Navigazione principale">
      <ul>
        <?php foreach ($lcgf_nav_items as $it) : ?>
          <li><a href="<?php echo esc_url($it['url']); ?>"><?php echo $it['label_html']; ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>

    <div class="lcgf-actions">
      <button class="lcgf-burger" type="button" aria-label="Menu" aria-expanded="false" aria-controls="lcgfMobileNav">
        <span></span><span></span><span></span>
      </button>
      <?php if (function_exists('pll_the_languages')) :
        $langs = pll_the_languages([
          'echo'             => 0,
          'raw'              => 1,
          'hide_if_empty'    => 0,
          'hide_current'     => 0,
        ]);
        if (!empty($langs) && is_array($langs)) :
          $current = null;
          foreach ($langs as $l) { if (!empty($l['current_lang'])) { $current = $l; break; } }
          if (!$current) $current = reset($langs);
      ?>
      <div class="lcgf-lang">
        <button class="lcgf-icon-btn lcgf-lang-btn" type="button" aria-label="<?php echo esc_attr(strtoupper($current['slug']) . ' - ' . $current['name']); ?>" aria-haspopup="true" aria-expanded="false">
          <span class="lcgf-lang-flag"><?php echo esc_html(strtoupper($current['slug'])); ?></span>
        </button>
        <ul class="lcgf-lang-menu" role="menu">
          <?php foreach ($langs as $l) : ?>
            <li role="none">
              <a role="menuitem" href="<?php echo esc_url($l['url']); ?>"<?php echo !empty($l['current_lang']) ? ' class="is-current"' : ''; ?>>
                <span class="lcgf-lang-code"><?php echo esc_html(strtoupper($l['slug'])); ?></span>
                <span class="lcgf-lang-name"><?php echo esc_html($l['name']); ?></span>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; endif; ?>

      <a href="<?php echo esc_url(home_url('/?s=')); ?>" class="lcgf-icon-btn lcgf-hide-mobile" aria-label="<?php echo esc_attr(lcgf_t('aria_search')); ?>">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
      </a>
      <a href="<?php echo esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))); ?>" class="lcgf-icon-btn lcgf-hide-mobile" aria-label="<?php echo esc_attr(lcgf_t('aria_account')); ?>">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-7 8-7s8 3 8 7"/></svg>
      </a>
      <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="lcgf-icon-btn" aria-label="<?php echo esc_attr(lcgf_t('aria_cart')); ?>">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        <?php $count = function_exists('WC') && WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?>
        <?php if ($count > 0) : ?>
          <span class="lcgf-cart-count"><?php echo (int)$count; ?></span>
        <?php endif; ?>
      </a>
    </div>
  </div>
</header>

<div class="lcgf-mobile-overlay" id="lcgfMobileOverlay" hidden></div>
<nav class="lcgf-mobile-nav" id="lcgfMobileNav" aria-label="Menu di navigazione mobile" aria-hidden="true">
  <ul class="lcgf-mobile-links">
    <?php foreach ($lcgf_nav_items as $it) : ?>
      <li><a href="<?php echo esc_url($it['url']); ?>"><?php echo $it['label_html']; ?></a></li>
    <?php endforeach; ?>
  </ul>
  <div class="lcgf-mobile-sub">
    <a href="<?php echo esc_url(home_url('/?s=')); ?>"><?php echo esc_html(lcgf_t('aria_search')); ?></a>
    <a href="<?php echo esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))); ?>"><?php echo esc_html(lcgf_t('aria_account')); ?></a>
    <a href="<?php echo esc_url(wc_get_cart_url()); ?>"><?php echo esc_html(lcgf_t('aria_cart')); ?></a>
  </div>
  <?php if (!empty($langs) && is_array($langs)) : ?>
  <div class="lcgf-mobile-langs">
    <?php foreach ($langs as $l) : ?>
      <a href="<?php echo esc_url($l['url']); ?>" class="<?php echo !empty($l['current_lang']) ? 'is-current' : ''; ?>"><?php echo esc_html(strtoupper($l['slug'])); ?></a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</nav>
<script>
(function(){
  var burger = document.querySelector('.lcgf-burger');
  var panel  = document.getElementById('lcgfMobileNav');
  var overlay= document.getElementById('lcgfMobileOverlay');
  if(!burger || !panel || !overlay) return;
  function open(){ document.body.classList.add('lcgf-mnav-open'); panel.classList.add('is-open'); overlay.hidden=false; overlay.classList.add('is-open'); burger.classList.add('is-active'); burger.setAttribute('aria-expanded','true'); panel.setAttribute('aria-hidden','false'); }
  function close(){ document.body.classList.remove('lcgf-mnav-open'); panel.classList.remove('is-open'); overlay.classList.remove('is-open'); burger.classList.remove('is-active'); burger.setAttribute('aria-expanded','false'); panel.setAttribute('aria-hidden','true'); setTimeout(function(){ if(!panel.classList.contains('is-open')) overlay.hidden=true; },300); }
  burger.addEventListener('click', function(){ panel.classList.contains('is-open') ? close() : open(); });
  overlay.addEventListener('click', close);
  panel.querySelectorAll('a').forEach(function(a){ a.addEventListener('click', close); });
  document.addEventListener('keydown', function(e){ if(e.key==='Escape') close(); });
})();
</script>

<main id="main" class="site-main">
