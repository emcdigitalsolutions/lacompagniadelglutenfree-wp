<?php
/**
 * Single Product wrapper — LCGF.
 */
defined('ABSPATH') || exit;
get_header('shop');
?>

<section style="padding: 36px 0 100px">
  <div class="container">

    <?php
    while (have_posts()) :
        the_post();
        global $product;

        $cat_links = wc_get_product_category_list($product->get_id(), ', ');
    ?>

    <nav style="font-size:.82rem;color:var(--c-muted);margin-bottom:24px">
      <a href="<?php echo esc_url(home_url('/')); ?>" style="color:var(--c-muted)">Home</a>
      <span style="margin:0 8px;opacity:.5">/</span>
      <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" style="color:var(--c-muted)"><?php echo lcgf_t('nav_catalogo'); ?></a>
      <span style="margin:0 8px;opacity:.5">/</span>
      <span style="color:var(--c-ink)"><?php the_title(); ?></span>
    </nav>

    <div class="lcgf-pdp" style="display:grid;grid-template-columns:1.1fr 1fr;gap:60px;align-items:start">

      <!-- GALLERY -->
      <div class="lcgf-pdp-gallery" style="position:relative">
        <div style="background:var(--c-white);border-radius:var(--r-xl);padding:24px;box-shadow:var(--sh-1);position:relative;overflow:hidden;aspect-ratio:1/1;display:grid;place-items:center">
          <?php
          $img_id = $product->get_image_id();
          if ($img_id) {
              echo wp_get_attachment_image($img_id, 'large', false, ['style' => 'max-width:88%;max-height:88%;object-fit:contain;filter:drop-shadow(0 14px 32px rgba(31,27,20,.15))']);
          } else {
              echo '<div style="font-size:180px;opacity:.5">🌾</div>';
          }
          ?>
        </div>
      </div>

      <!-- INFO -->
      <div class="lcgf-pdp-info">
        <?php if ($cat_links): ?>
          <div style="font-size:.78rem;letter-spacing:.14em;text-transform:uppercase;color:var(--c-muted);margin-bottom:10px"><?php echo wp_kses_post($cat_links); ?></div>
        <?php endif; ?>

        <h1 class="product_title" style="font-size:clamp(2rem,3.5vw,2.6rem) !important;margin-bottom:14px"><?php the_title(); ?></h1>

        <p style="color:var(--c-ink-soft);font-size:1.05rem;line-height:1.6;margin-bottom:18px"><?php echo wp_kses_post($product->get_short_description() ?: ''); ?></p>

        <?php if ((int) $product->get_review_count() > 0) : ?>
        <div style="display:flex;align-items:center;gap:10px;margin:18px 0">
          <?php echo wc_get_rating_html($product->get_average_rating()); ?>
          <a href="#recensioni" style="font-size:.85rem;color:var(--c-muted);text-decoration:none"><?php echo esc_html($product->get_average_rating()); ?> · <?php echo (int) $product->get_review_count(); ?> <?php echo lcgf_t('sp_reviews'); ?></a>
        </div>
        <?php endif; ?>

        <div style="font-family:var(--f-display);font-size:2.4rem;color:var(--c-olive-deep);font-weight:600;margin:18px 0 26px">
          <?php echo $product->get_price_html(); ?>
        </div>

        <?php if (function_exists('lcgf_pickup_badge_html')) echo lcgf_pickup_badge_html($product); ?>

        <?php if (!$product->is_in_stock()) : ?>
          <?php if (function_exists('lcgf_is_coming_soon') && lcgf_is_coming_soon($product)) : ?>
            <div class="lcgf-soon-notice">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
              <span><strong><?php echo esc_html(lcgf_launch_soon_label()); ?>.</strong> <?php echo esc_html(lcgf_launch_soon_notice()); ?></span>
            </div>
            <?php echo lcgf_bis_form($product, true); // in arrivo: form "avvisami" senza intestazione "esaurito" ?>
          <?php else : ?>
            <?php echo lcgf_bis_form($product); // esaurito: form "avvisami quando disponibile" ?>
          <?php endif; ?>
        <?php elseif ($product->is_type('variable')) : ?>
          <?php
          // Prodotto variabile: usa il template standard WooCommerce, che rende i
          // selettori di variante + il JS variations_form (necessario per scegliere
          // gusto/formato prima di aggiungere al carrello).
          woocommerce_template_single_add_to_cart();
          ?>
        <?php else : ?>
          <form class="cart" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype='multipart/form-data' style="display:flex;gap:14px;align-items:center;flex-wrap:wrap;margin:24px 0">
            <?php do_action('woocommerce_before_add_to_cart_button'); ?>

            <?php
            if (!$product->is_sold_individually()) {
                woocommerce_quantity_input([
                    'min_value'   => apply_filters('woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product),
                    'max_value'   => apply_filters('woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product),
                    'input_value' => 1,
                ]);
            }
            ?>
            <button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" class="single_add_to_cart_button button alt btn btn-lg">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              <?php echo lcgf_t('add_to_cart'); ?>
            </button>

            <?php do_action('woocommerce_after_add_to_cart_button'); ?>
          </form>
        <?php endif; ?>

        <ul style="list-style:none;padding:0;margin:28px 0 0;border-top:1px solid var(--c-line)">
          <?php
          $lcgf_is_pickup = function_exists('lcgf_pickup_str') && has_term('solo-ritiro', 'product_shipping_class', $product->get_id());
          $meta_rows = [
              lcgf_t('sp_sku')   => $product->get_sku() ?: '—',
              lcgf_t('sp_avail') => $product->is_in_stock()
                  ? '<span style="color:var(--c-success)">' . lcgf_t('sp_instock') . '</span>'
                  : ((function_exists('lcgf_is_coming_soon') && lcgf_is_coming_soon($product))
                      ? '<span style="color:#b07d12;font-weight:600">' . esc_html(lcgf_launch_soon_label()) . '</span>'
                      : '<span style="color:var(--c-error)">' . lcgf_t('sp_outstock') . '</span>'),
          ];
          if ($lcgf_is_pickup) {
              $meta_rows[lcgf_pickup_str('row_label')] = '<strong style="color:#b07d12">' . esc_html(lcgf_pickup_str('row_val')) . '</strong>';
          } else {
              $meta_rows[lcgf_t('sp_ship')] = lcgf_t('sp_ship_val');
          }
          $meta_rows[lcgf_t('sp_cert')] = lcgf_t('sp_cert_val');
          $meta_rows[lcgf_t('sp_lab')]  = lcgf_t('sp_lab_val');
          foreach ($meta_rows as $lbl => $val) : ?>
            <li style="padding:12px 0;border-bottom:1px solid var(--c-line);display:flex;gap:12px;font-size:.92rem">
              <span style="color:var(--c-muted);min-width:140px"><?php echo esc_html($lbl); ?></span>
              <span><?php echo wp_kses_post($val); ?></span>
            </li>
          <?php endforeach; ?>
        </ul>

        <div style="margin-top:24px;padding:16px 20px;background:var(--c-cream-2);border-radius:var(--r-md);display:flex;align-items:center;gap:12px;font-size:.88rem">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--c-olive-deep);flex-shrink:0"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
          <span><?php echo lcgf_t('sp_help'); ?> <a href="https://wa.me/393276999897" target="_blank" rel="noopener" style="color:var(--c-olive-deep);font-weight:600"><?php echo lcgf_t('sp_help_wa'); ?></a></span>
        </div>
      </div>
    </div>

    <!-- DESCRIZIONE LUNGA -->
    <?php if ($product->get_description()): ?>
    <section style="margin-top:70px">
      <h2 style="font-size:1.5rem !important;margin-bottom:18px"><?php echo lcgf_t('sp_desc'); ?></h2>
      <div style="max-width:760px;font-size:1.02rem;line-height:1.75;color:var(--c-ink-soft)">
        <?php echo wp_kses_post(wpautop($product->get_description())); ?>
      </div>
    </section>
    <?php endif; ?>

    <!-- RECENSIONI -->
    <?php if (comments_open() || (int) $product->get_review_count() > 0) : ?>
    <section style="margin-top:70px" id="recensioni">
      <h2 style="font-size:1.5rem !important;margin-bottom:18px"><?php echo lcgf_t('sp_reviews_title'); ?></h2>
      <?php comments_template(); ?>
    </section>
    <?php endif; ?>

    <!-- CORRELATI -->
    <?php
    $upsells_terms = $product->get_category_ids();
    if (!empty($upsells_terms)) {
        $args = [
            'post_type' => 'product',
            'posts_per_page' => 4,
            'post__not_in' => [$product->get_id()],
            'orderby' => 'rand',
            'tax_query' => [['taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => $upsells_terms]],
        ];
        $related = new WP_Query($args);
        if ($related->have_posts()) :
    ?>
        <section style="margin-top:80px">
          <div style="text-align:left;margin-bottom:24px">
            <span class="eyebrow"><?php echo lcgf_t('sp_related_eyebrow'); ?></span>
            <h2 style="font-size:1.8rem !important"><?php echo lcgf_t('sp_related_h2'); ?></h2>
          </div>
          <div class="woocommerce">
            <ul class="products">
              <?php while ($related->have_posts()) : $related->the_post(); $rp = wc_get_product(get_the_ID()); ?>
                <li class="product">
                  <a href="<?php the_permalink(); ?>" style="display:block">
                    <?php echo get_the_post_thumbnail(get_the_ID(), 'woocommerce_thumbnail'); ?>
                    <h3 class="woocommerce-loop-product__title"><?php the_title(); ?></h3>
                  </a>
                  <span class="price"><?php echo $rp->get_price_html(); ?></span>
                  <?php if ($rp->is_type('simple') && $rp->is_purchasable() && $rp->is_in_stock()) : ?>
                    <a href="<?php echo esc_url('?add-to-cart=' . get_the_ID()); ?>" class="button"><?php echo lcgf_t('add_to_cart'); ?></a>
                  <?php else : ?>
                    <a href="<?php the_permalink(); ?>" class="button"><?php echo lcgf_t('sp_select'); ?></a>
                  <?php endif; ?>
                </li>
              <?php endwhile; wp_reset_postdata(); ?>
            </ul>
          </div>
        </section>
    <?php
        endif;
    }
    ?>

    <?php endwhile; ?>
  </div>
</section>

<style>
.lcgf-pdp form.cart .quantity input[type="number"] {
  width: 70px;
  padding: 13px 12px;
  border: 1.5px solid var(--c-line);
  border-radius: var(--r-pill);
  text-align: center;
  font-weight: 600;
  font-family: var(--f-display);
}
@media (max-width: 880px) {
  .lcgf-pdp { grid-template-columns: 1fr !important; gap: 30px !important; }
}
</style>

<?php
get_footer('shop');
