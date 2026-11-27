<?php
/**
 * Template della pagina "Rivenditori" (NON in radice mu-plugins: caricato da lcgf-rivenditori.php via template_include).
 */
if (!defined('ABSPATH')) exit;
get_header();

$contact_url = function_exists('lcgf_page_url') ? lcgf_page_url('contatti') : home_url('/contatti/');
$shop_url    = get_permalink(wc_get_page_id('shop'));
?>

<section class="lcgf-hero lcgf-rv-hero">
  <div class="container">
    <div class="lcgf-rv-hero-in">
      <span class="eyebrow"><?php echo esc_html(lcgf_rv_s('eyebrow')); ?></span>
      <h1><?php echo esc_html(lcgf_rv_s('title')); ?></h1>
      <p class="lcgf-rv-lead"><?php echo esc_html(lcgf_rv_s('intro')); ?></p>
      <ul class="lcgf-rv-badges">
        <li><?php echo esc_html(lcgf_rv_s('b_tier')); ?></li>
        <li><?php echo esc_html(lcgf_rv_s('b_ship')); ?></li>
        <li><?php echo esc_html(lcgf_rv_s('b_vat')); ?></li>
        <li><?php echo esc_html(lcgf_rv_s('b_piva')); ?></li>
      </ul>
    </div>
  </div>
</section>

<section class="section lcgf-rv">
  <div class="container">
    <?php if (function_exists('wc_print_notices')) wc_print_notices(); ?>
    <p class="lcgf-rv-how"><?php echo esc_html(lcgf_rv_s('how')); ?></p>

    <div class="lcgf-rv-grid">
    <?php foreach (lcgf_rv_listino() as $slug => $row) :
      $pid = lcgf_rv_product_id($slug);
      $product = $pid ? wc_get_product($pid) : null;
      if (!$product || $product->get_status() !== 'publish') continue;
      $buyable = $product->is_purchasable() && $product->is_in_stock()
        && !(function_exists('lcgf_is_coming_soon') && lcgf_is_coming_soon($product));
      $per = lcgf_rv_s($row['unit'] === 'conf' ? 'th_conf' : 'th_pz');
      $cartons = array();
      foreach ($row['prices'] as $i => $u) $cartons[$i] = round($u * $row['pcs'], 2);
    ?>
      <article class="lcgf-rv-card<?php echo $buyable ? '' : ' is-soon'; ?>">
        <a class="lcgf-rv-img" href="<?php echo esc_url(get_permalink($pid)); ?>">
          <?php echo $product->get_image('woocommerce_thumbnail'); ?>
          <?php if (!$buyable) : ?><span class="lcgf-soon-badge"><?php echo esc_html(lcgf_rv_s('soon')); ?></span><?php endif; ?>
        </a>
        <div class="lcgf-rv-body">
          <h2 class="lcgf-rv-name"><?php echo esc_html($product->get_name()); ?></h2>
          <p class="lcgf-rv-carton"><?php echo esc_html(sprintf(lcgf_rv_s('carton'), lcgf_rv_content_label($row))); ?></p>

          <table class="lcgf-rv-table">
            <thead><tr>
              <th><?php echo esc_html(lcgf_rv_s('th_cart')); ?></th>
              <th><?php echo esc_html($per); ?></th>
              <th><?php echo esc_html(lcgf_rv_s('th_tot')); ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ($row['prices'] as $i => $u) : ?>
              <tr class="<?php echo $i === 1 ? 'is-popular' : ''; ?>" data-tier="<?php echo (int) $i; ?>">
                <td><?php echo esc_html(lcgf_rv_tier_label($i)); ?>
                  <?php if ($i === 1) : ?><span class="lcgf-rv-pop"><?php echo esc_html(lcgf_rv_s('popular')); ?></span><?php endif; ?></td>
                <td><strong><?php echo esc_html(lcgf_rv_money($u)); ?></strong> <small><?php echo esc_html(lcgf_rv_s('plusvat')); ?></small></td>
                <td><?php echo esc_html(lcgf_rv_money($cartons[$i])); ?> <small><?php echo esc_html(lcgf_rv_s('plusvat')); ?></small></td>
              </tr>
            <?php endforeach; ?>
              <tr class="is-quote">
                <td>10+</td>
                <td colspan="2"><a href="<?php echo esc_url($contact_url); ?>"><?php echo esc_html(lcgf_rv_s('quote')); ?> &rarr;</a></td>
              </tr>
            </tbody>
          </table>

          <?php if ($buyable) : ?>
          <form class="lcgf-rv-form" method="post" action="<?php echo esc_url(lcgf_rv_page_url()); ?>"
                data-cartons='<?php echo esc_attr(wp_json_encode(array_values($cartons))); ?>'>
            <input type="hidden" name="lcgf_rv_add" value="<?php echo (int) $pid; ?>">
            <label>
              <span><?php echo esc_html(lcgf_rv_s('qty')); ?></span>
              <select name="lcgf_rv_qty">
                <?php for ($q = 1; $q <= LCGF_RV_MAX_CARTONI; $q++) : ?>
                  <option value="<?php echo $q; ?>"><?php echo $q; ?></option>
                <?php endfor; ?>
              </select>
            </label>
            <span class="lcgf-rv-total"><?php echo esc_html(lcgf_rv_s('total')); ?>: <strong data-total><?php echo esc_html(lcgf_rv_money($cartons[0])); ?></strong> <small><?php echo esc_html(lcgf_rv_s('plusvat')); ?></small></span>
            <button type="submit" class="btn"><?php echo esc_html(lcgf_rv_s('add')); ?></button>
          </form>
          <?php else : ?>
            <p class="lcgf-rv-soon"><?php echo esc_html(lcgf_rv_s('soon')); ?></p>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
    </div>

    <div class="lcgf-rv-quote">
      <div>
        <strong><?php echo esc_html(lcgf_rv_s('more_q')); ?></strong>
        <p><?php echo esc_html(lcgf_rv_s('more_a')); ?></p>
      </div>
      <a class="btn btn-wheat" href="<?php echo esc_url($contact_url); ?>"><?php echo esc_html(lcgf_rv_s('quote')); ?></a>
    </div>
    <p class="lcgf-rv-retail"><a href="<?php echo esc_url($shop_url); ?>"><?php echo esc_html(lcgf_rv_s('retail')); ?> &rarr;</a></p>
  </div>
</section>

<script>
(function(){
  var fmt = function(v){ return v.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ' €'; };
  document.querySelectorAll('.lcgf-rv-form').forEach(function(f){
    var c = JSON.parse(f.getAttribute('data-cartons') || '[]');
    var sel = f.querySelector('select'), out = f.querySelector('[data-total]');
    var card = f.closest('.lcgf-rv-card');
    function upd(){
      var q = parseInt(sel.value, 10) || 1, t = q <= 3 ? 0 : (q <= 6 ? 1 : 2);
      out.textContent = fmt(c[t] * q);
      card.querySelectorAll('tbody tr[data-tier]').forEach(function(tr){
        tr.classList.toggle('is-active', parseInt(tr.getAttribute('data-tier'), 10) === t);
      });
    }
    sel.addEventListener('change', upd); upd();
  });
})();
</script>

<?php get_footer();
