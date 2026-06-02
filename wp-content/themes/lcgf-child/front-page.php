<?php
/**
 * Front Page LCGF — homepage custom premium.
 */
get_header();

// recupera featured (8 prodotti random pubblicati)
$featured = wc_get_products(['status' => 'publish', 'limit' => 8, 'orderby' => 'rand']);
$default_cat = (int) get_option('default_product_cat');
$cats = get_terms([
    'taxonomy'   => 'product_cat',
    'hide_empty' => true,
    'exclude'    => array_filter([$default_cat]),
]);
$cats = array_filter($cats, function($c) {
    return strtolower($c->slug) !== 'uncategorized' && strtolower($c->name) !== 'senza categoria';
});
$logo = get_stylesheet_directory_uri() . '/assets/img/logo.webp';
?>

<!-- HERO -->
<section class="lcgf-hero">
  <div class="container">
    <div class="lcgf-hero-grid">
      <div>
        <span class="eyebrow"><?php echo lcgf_t('hero_eyebrow'); ?></span>
        <h1><?php echo lcgf_t('hero_h1'); ?></h1>
        <p class="lcgf-hero-lead"><?php echo lcgf_t('hero_lead'); ?></p>
        <div class="lcgf-hero-ctas">
          <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="btn btn-lg">
            <?php echo lcgf_t('hero_cta1'); ?>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
          <a href="<?php echo esc_url(home_url('/chi-siamo/')); ?>" class="btn btn-ghost btn-lg"><?php echo lcgf_t('our_story'); ?></a>
        </div>
        <div class="lcgf-hero-stats">
          <div class="lcgf-hero-stat"><strong>13+</strong><span><?php echo lcgf_t('stat_products'); ?></span></div>
          <div class="lcgf-hero-stat"><strong>0%</strong><span><?php echo lcgf_t('stat_gluten'); ?></span></div>
          <div class="lcgf-hero-stat"><strong>20+</strong><span><?php echo lcgf_t('stat_years'); ?></span></div>
        </div>
      </div>

      <div class="lcgf-hero-visual">
        <div class="lcgf-hero-glow"></div>
        <div class="lcgf-hero-logo">
          <img src="<?php echo esc_url($logo); ?>" alt="La Compagnia del Gluten Free" />
        </div>
        <div class="lcgf-pillbadge b1"><span class="dot"></span> <?php echo lcgf_t('gluten_free'); ?></div>
        <div class="lcgf-pillbadge b2"><span class="dot" style="background:#C7613E"></span> <?php echo lcgf_t('lactose_free'); ?></div>
        <div class="lcgf-pillbadge b3"><span class="dot" style="background:#C9A96E"></span> <?php echo lcgf_t('dedicated_lab'); ?></div>
      </div>
    </div>
  </div>
</section>

<!-- MARQUEE -->
<div class="lcgf-marquee" aria-hidden="true">
  <div class="lcgf-marquee-track">
    <?php for ($mq = 0; $mq < 2; $mq++) : ?>
    <span><?php echo lcgf_t('gluten_free'); ?></span>
    <span><?php echo lcgf_t('lactose_free'); ?></span>
    <span><?php echo lcgf_t('dedicated_lab'); ?></span>
    <span><?php echo lcgf_t('frozen_ready'); ?></span>
    <span><?php echo lcgf_t('exp_20y'); ?></span>
    <span>Mangia con Gusto</span>
    <?php endfor; ?>
  </div>
</div>

<!-- CATEGORIES -->
<section class="section">
  <div class="container">
    <div class="lcgf-section-head">
      <span class="eyebrow"><?php echo lcgf_t('cat_eyebrow'); ?></span>
      <h2><?php echo lcgf_t('cat_h2'); ?></h2>
      <p><?php echo lcgf_t('cat_sub'); ?></p>
    </div>
    <div class="lcgf-cat-grid">
      <?php
      $cat_emojis = [
        'pane-basi'       => '🥖',
        'pane-pizza'      => '🥖',
        'dolci-colazione' => '🍰',
        'dolci'           => '🍰',
      ];
      foreach ($cats as $cat) {
        $hue = strpos($cat->slug, 'pane') !== false || strpos($cat->slug, 'pizza') !== false ? 'cat-pane' : 'cat-dolci';
        $emoji = $cat_emojis[$cat->slug] ?? '🌾';
        $url = get_term_link($cat);
        $count = $cat->count;
        ?>
        <a href="<?php echo esc_url($url); ?>" class="lcgf-cat-card <?php echo esc_attr($hue); ?>">
          <div class="lcgf-cat-card-bg"></div>
          <div class="lcgf-cat-card-emoji"><?php echo $emoji; ?></div>
          <div class="lcgf-cat-card-content">
            <h3><?php echo esc_html($cat->name); ?></h3>
            <p><?php echo (int)$count; ?> <?php echo lcgf_t('products'); ?></p>
          </div>
        </a>
        <?php
      }
      ?>
    </div>
  </div>
</section>

<!-- FEATURED PRODUCTS -->
<section class="section" style="background: var(--c-cream-2)">
  <div class="container">
    <div class="lcgf-section-head">
      <span class="eyebrow"><?php echo lcgf_t('feat_eyebrow'); ?></span>
      <h2><?php echo lcgf_t('feat_h2'); ?></h2>
      <p><?php echo lcgf_t('feat_sub'); ?></p>
    </div>

    <?php if ($featured && count($featured) > 0): ?>
      <div class="woocommerce">
        <ul class="products">
          <?php foreach ($featured as $p) :
            $url = get_permalink($p->get_id());
            $img = wp_get_attachment_image_url($p->get_image_id(), 'medium_large') ?: wc_placeholder_img_src();
            ?>
            <li class="product">
              <a href="<?php echo esc_url($url); ?>">
                <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($p->get_name()); ?>" />
                <h3 class="woocommerce-loop-product__title"><?php echo esc_html($p->get_name()); ?></h3>
              </a>
              <span class="price"><?php echo $p->get_price_html(); ?></span>
              <a href="<?php echo esc_url('?add-to-cart=' . $p->get_id()); ?>" data-product_id="<?php echo (int)$p->get_id(); ?>" class="button add_to_cart_button ajax_add_to_cart"><?php echo lcgf_t('add_to_cart'); ?></a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div style="text-align:center;margin-top:40px">
      <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="btn btn-ghost btn-lg">
        <?php echo lcgf_t('view_all'); ?>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </a>
    </div>
  </div>
</section>

<!-- STORY SPLIT -->
<section class="section">
  <div class="container">
    <div class="lcgf-split">
      <div class="lcgf-split-content">
        <span class="eyebrow"><?php echo lcgf_t('our_story'); ?></span>
        <h2><?php echo lcgf_t('story_h2'); ?></h2>
        <p><?php echo lcgf_t('story_p'); ?></p>
        <ul class="lcgf-split-list">
          <li><span class="ck">✓</span><span><?php echo lcgf_t('story_li1'); ?></span></li>
          <li><span class="ck">✓</span><span><?php echo lcgf_t('story_li2'); ?></span></li>
          <li><span class="ck">✓</span><span><?php echo lcgf_t('story_li3'); ?></span></li>
          <li><span class="ck">✓</span><span><?php echo lcgf_t('story_li4'); ?></span></li>
        </ul>
        <a href="<?php echo esc_url(home_url('/chi-siamo/')); ?>" class="btn btn-lg" style="margin-top:24px">
          <?php echo lcgf_t('story_cta'); ?>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
      </div>
      <div class="lcgf-split-visual">
        <span class="emoji">🌾</span>
      </div>
    </div>
  </div>
</section>

<!-- CERTIFICAZIONI & GARANZIE CELIACHIA -->
<section class="section lcgf-certs">
  <div class="container">
    <div class="lcgf-section-head">
      <span class="eyebrow"><?php echo lcgf_t('cert_eyebrow'); ?></span>
      <h2><?php echo lcgf_t('cert_h2'); ?></h2>
      <p class="lcgf-certs-sub"><?php echo lcgf_t('cert_sub'); ?></p>
    </div>
    <div class="lcgf-certs-grid">

      <div class="lcgf-cert-card">
        <div class="lcgf-cert-logo"><?php echo lcgf_cert_logo('aic.png', 'AIC · Associazione Italiana Celiachia'); ?></div>
        <strong><?php echo lcgf_t('cert1_t'); ?></strong>
        <span><?php echo lcgf_t('cert1_d'); ?></span>
      </div>

      <div class="lcgf-cert-card">
        <div class="lcgf-cert-logo"><?php echo lcgf_cert_logo('ministero-salute.png', 'Ministero della Salute'); ?></div>
        <strong><?php echo lcgf_t('cert2_t'); ?></strong>
        <span><?php echo lcgf_t('cert2_d'); ?></span>
      </div>

      <div class="lcgf-cert-card">
        <div class="lcgf-cert-logo lcgf-cert-logo--seal">
          <!-- Sigillo brand-safe (disegnato da noi, NON un marchio registrato) -->
          <svg width="86" height="86" viewBox="0 0 100 100" fill="none" aria-hidden="true">
            <circle cx="50" cy="50" r="47" fill="#F4EDDC" stroke="#6B8E4E" stroke-width="2.5"/>
            <circle cx="50" cy="50" r="38" fill="none" stroke="#C9A96E" stroke-width="1" stroke-dasharray="2 3"/>
            <path d="M50 30c-4 6-4 12 0 18 4-6 4-12 0-18z" fill="#6B8E4E"/>
            <path d="M50 38c-5 3-7 8-6 14 5-2 8-7 6-14z" fill="#8AA86A"/>
            <path d="M50 38c5 3 7 8 6 14-5-2-8-7-6-14z" fill="#8AA86A"/>
            <path d="M40 58l7 7 14-15" stroke="#364E25" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
            <text x="50" y="84" text-anchor="middle" font-family="Inter,sans-serif" font-size="9" font-weight="700" letter-spacing="1" fill="#364E25">GLUTEN FREE</text>
          </svg>
        </div>
        <strong><?php echo lcgf_t('cert3_t'); ?></strong>
        <span><?php echo lcgf_t('cert3_d'); ?></span>
      </div>

    </div>
    <p style="text-align:center;margin-top:30px">
      <a href="<?php echo esc_url(home_url('/abc-dieta-senza-glutine/')); ?>" class="btn-ghost" style="display:inline-flex;align-items:center;gap:8px">
        <?php echo lcgf_t('cert_cta'); ?>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </a>
    </p>
  </div>
</section>

<!-- USP STRIP -->
<section class="section-tight">
  <div class="container">
    <div class="lcgf-usp">
      <div class="lcgf-usp-item">
        <div class="lcgf-usp-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 12V8a2 2 0 0 0-2-2H8L2 12l6 6h10a2 2 0 0 0 2-2v-4"/><line x1="2" y1="12" x2="20" y2="12"/></svg>
        </div>
        <div><strong><?php echo lcgf_t('usp1_t'); ?></strong><span><?php echo lcgf_t('usp1_s'); ?></span></div>
      </div>
      <div class="lcgf-usp-item">
        <div class="lcgf-usp-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </div>
        <div><strong><?php echo lcgf_t('usp2_t'); ?></strong><span><?php echo lcgf_t('usp2_s'); ?></span></div>
      </div>
      <div class="lcgf-usp-item">
        <div class="lcgf-usp-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>
        <div><strong><?php echo lcgf_t('usp3_t'); ?></strong><span>Stripe, PayPal, Klarna</span></div>
      </div>
      <div class="lcgf-usp-item">
        <div class="lcgf-usp-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
        </div>
        <div><strong><?php echo lcgf_t('usp4_t'); ?></strong><span><?php echo lcgf_t('usp4_s'); ?></span></div>
      </div>
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section class="section lcgf-testimonials">
  <div class="container">
    <div class="lcgf-section-head">
      <span class="eyebrow"><?php echo lcgf_t('test_eyebrow'); ?></span>
      <h2><?php echo lcgf_t('test_h2'); ?></h2>
    </div>
    <div class="lcgf-t-grid">
      <div class="lcgf-t-card">
        <div class="lcgf-t-stars">★★★★★</div>
        <p class="lcgf-t-quote"><?php echo lcgf_t('test_q1'); ?></p>
        <div class="lcgf-t-author">
          <div class="lcgf-t-avatar">MS</div>
          <div><strong>Maria Sgarlata</strong><span>Catania</span></div>
        </div>
      </div>
      <div class="lcgf-t-card">
        <div class="lcgf-t-stars">★★★★★</div>
        <p class="lcgf-t-quote"><?php echo lcgf_t('test_q2'); ?></p>
        <div class="lcgf-t-author">
          <div class="lcgf-t-avatar">GP</div>
          <div><strong>Giulia Patti</strong><span>Milano</span></div>
        </div>
      </div>
      <div class="lcgf-t-card">
        <div class="lcgf-t-stars">★★★★★</div>
        <p class="lcgf-t-quote"><?php echo lcgf_t('test_q3'); ?></p>
        <div class="lcgf-t-author">
          <div class="lcgf-t-avatar">AR</div>
          <div><strong>Andrea Romano</strong><span>Berlino</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- NEWSLETTER -->
<section class="section">
  <div class="container" style="max-width:880px">
    <div class="lcgf-newsletter">
      <span class="eyebrow"><?php echo lcgf_t('nl_eyebrow'); ?></span>
      <h2><?php echo lcgf_t('nl_h2'); ?></h2>
      <p><?php echo lcgf_t('nl_p'); ?></p>
      <form class="lcgf-newsletter-form" id="lcgfNewsletter" onsubmit="event.preventDefault();this.querySelector('button').textContent='<?php echo esc_js(lcgf_t('nl_done')); ?>';">
        <input type="email" placeholder="<?php echo esc_attr(lcgf_t('nl_email')); ?>" required />
        <button type="submit" class="btn"><?php echo lcgf_t('nl_btn'); ?></button>
      </form>
    </div>
  </div>
</section>

<?php get_footer();
