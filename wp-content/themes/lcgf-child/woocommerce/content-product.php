<?php
/**
 * Product card — LCGF.
 */
defined('ABSPATH') || exit;
global $product;
if (empty($product) || !$product->is_visible()) return;
?>
<li <?php wc_product_class('', $product); ?>>
  <a href="<?php echo esc_url(get_the_permalink()); ?>" style="display:block;position:relative">
    <?php if (function_exists('lcgf_is_coming_soon') && lcgf_is_coming_soon($product)) : ?>
      <span class="lcgf-soon-badge"><?php echo esc_html(lcgf_launch_soon_label()); ?></span>
    <?php endif; ?>
    <?php
    if (has_post_thumbnail()) {
      the_post_thumbnail('woocommerce_thumbnail');
    } else {
      echo wc_placeholder_img('woocommerce_thumbnail');
    }
    ?>
    <h3 class="woocommerce-loop-product__title"><?php the_title(); ?></h3>
  </a>
  <span class="price"><?php echo $product->get_price_html(); ?></span>
  <?php woocommerce_template_loop_add_to_cart(); ?>
</li>
