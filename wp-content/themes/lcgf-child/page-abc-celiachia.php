<?php
/**
 * Template Name: ABC dieta senza glutine
 * Pagina divulgativa originale sull'ABC della dieta del celiaco. Contenuti
 * scritti da noi (NON copiati); fonte autorevole citata: AIC (celiachia.it).
 * Accordion nativo <details> = accessibile, leggero, non invasivo.
 */
defined('ABSPATH') || exit;
get_header('shop');
?>
<section class="section" style="padding:48px 0 90px">
  <div class="container" style="max-width:860px">

    <div class="lcgf-section-head" style="text-align:left">
      <span class="eyebrow"><?php echo lcgf_t('abc_eyebrow'); ?></span>
      <h2><?php echo lcgf_t('abc_h1'); ?></h2>
      <p style="color:var(--c-ink-soft);font-size:1.05rem;line-height:1.65;max-width:680px">
        <?php echo lcgf_t('abc_intro'); ?>
      </p>
    </div>

    <div class="lcgf-acc" style="margin-top:34px;display:flex;flex-direction:column;gap:12px">

      <details class="lcgf-acc-item" open>
        <summary><?php echo lcgf_t('abc_s1_t'); ?></summary>
        <div class="lcgf-acc-body">
          <p><?php echo lcgf_t('abc_s1_p1'); ?></p>
          <ul>
            <li><?php echo lcgf_t('abc_s1_li1'); ?></li>
            <li><?php echo lcgf_t('abc_s1_li2'); ?></li>
            <li><?php echo lcgf_t('abc_s1_li3'); ?></li>
          </ul>
          <p><?php echo lcgf_t('abc_s1_p2'); ?></p>
        </div>
      </details>

      <details class="lcgf-acc-item">
        <summary><?php echo lcgf_t('abc_s2_t'); ?></summary>
        <div class="lcgf-acc-body">
          <p><?php echo lcgf_t('abc_s2_p1'); ?></p>
          <p><?php echo lcgf_t('abc_s2_p2'); ?></p>
        </div>
      </details>

      <details class="lcgf-acc-item">
        <summary><?php echo lcgf_t('abc_s3_t'); ?></summary>
        <div class="lcgf-acc-body">
          <p><?php echo lcgf_t('abc_s3_p1'); ?></p>
        </div>
      </details>

      <details class="lcgf-acc-item">
        <summary><?php echo lcgf_t('abc_s4_t'); ?></summary>
        <div class="lcgf-acc-body">
          <p><?php echo lcgf_t('abc_s4_p1'); ?></p>
          <p><?php echo lcgf_t('abc_s4_p2'); ?></p>
        </div>
      </details>

      <details class="lcgf-acc-item">
        <summary><?php echo lcgf_t('abc_s5_t'); ?></summary>
        <div class="lcgf-acc-body">
          <p><?php echo lcgf_t('abc_s5_p1'); ?></p>
          <p style="margin-top:10px">
            <a href="https://www.celiachia.it/dieta-senza-glutine/labc-della-dieta-del-celiaco/" target="_blank" rel="noopener" style="color:var(--c-olive-deep);font-weight:600">
              <?php echo lcgf_t('abc_s5_link'); ?>
            </a>
          </p>
        </div>
      </details>

    </div>

    <?php
    // Eventuale contenuto aggiuntivo inserito dall'admin nella pagina.
    while (have_posts()) : the_post();
      $extra = trim(get_the_content());
      if ($extra) { echo '<div style="margin-top:34px;line-height:1.7;color:var(--c-ink-soft)">' . apply_filters('the_content', $extra) . '</div>'; }
    endwhile;
    ?>

    <p style="margin-top:40px;font-size:.84rem;color:var(--c-muted);border-top:1px solid var(--c-line);padding-top:18px">
      <?php echo lcgf_t('abc_disclaimer'); ?> <a href="https://www.celiachia.it/" target="_blank" rel="noopener" style="color:var(--c-muted);text-decoration:underline">Associazione Italiana Celiachia — celiachia.it</a>.
    </p>

  </div>
</section>

<style>
.lcgf-acc-item{border:1px solid var(--c-line);border-radius:var(--r-lg);background:var(--c-white);overflow:hidden;box-shadow:var(--sh-1)}
.lcgf-acc-item summary{cursor:pointer;list-style:none;padding:18px 24px;font-family:var(--f-display);font-size:1.12rem;color:var(--c-olive-deep);display:flex;align-items:center;justify-content:space-between;gap:14px}
.lcgf-acc-item summary::-webkit-details-marker{display:none}
.lcgf-acc-item summary::after{content:"+";font-size:1.5rem;color:var(--c-wheat-dark);transition:transform .2s ease;line-height:1}
.lcgf-acc-item[open] summary::after{content:"\2212"}
.lcgf-acc-item summary:hover{background:var(--c-cream)}
.lcgf-acc-body{padding:2px 24px 22px;color:var(--c-ink-soft);line-height:1.7}
.lcgf-acc-body ul{margin:10px 0 12px;padding-left:18px}
.lcgf-acc-body li{margin:7px 0}
</style>
<?php
get_footer('shop');
