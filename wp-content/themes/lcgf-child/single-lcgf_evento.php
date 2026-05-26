<?php
/**
 * Single evento.
 */
get_header();

while (have_posts()) : the_post();
  $post_id = get_the_ID();
  $start   = get_post_meta($post_id, '_lcgf_evento_data_inizio', true);
  $end     = get_post_meta($post_id, '_lcgf_evento_data_fine', true);
  $time    = get_post_meta($post_id, '_lcgf_evento_ora_inizio', true);
  $luogo   = get_post_meta($post_id, '_lcgf_evento_luogo', true);
  $indir   = get_post_meta($post_id, '_lcgf_evento_indirizzo', true);
  $citta   = get_post_meta($post_id, '_lcgf_evento_citta', true);
  $prezzo  = get_post_meta($post_id, '_lcgf_evento_prezzo', true);
  $link    = get_post_meta($post_id, '_lcgf_evento_link_esterno', true);

  $passato = $start && $start < current_time('Y-m-d');
  $img     = get_the_post_thumbnail_url($post_id, 'full');

  $date_label = '';
  if ($start) {
      $date_label = date_i18n('l d F Y', strtotime($start));
      if ($end && $end !== $start) {
          $date_label .= ' — ' . date_i18n('l d F Y', strtotime($end));
      }
  }
  $maps_q = trim(implode(', ', array_filter([$luogo, $indir, $citta])));
?>
<style>
  .lcgf-evento-detail-hero{position:relative;padding:90px 0 70px;background:var(--c-cream);overflow:hidden}
  .lcgf-evento-detail-hero .container{position:relative;z-index:1}
  .lcgf-evento-bread{font-size:.85rem;color:var(--c-muted);margin-bottom:18px}
  .lcgf-evento-bread a{color:var(--c-olive-deep);text-decoration:none;font-weight:600}
  .lcgf-evento-bread a:hover{text-decoration:underline}
  .lcgf-evento-bread span{margin:0 8px;opacity:.5}
  .lcgf-evento-detail-grid{display:grid;grid-template-columns:1.3fr 1fr;gap:48px;align-items:start}
  @media (max-width:880px){.lcgf-evento-detail-grid{grid-template-columns:1fr;gap:32px}}
  .lcgf-evento-detail-title{font-family:var(--f-display);font-size:clamp(2rem,4.5vw,3rem);line-height:1.1;color:var(--c-olive-deep);margin:6px 0 18px}
  .lcgf-evento-tag-passato-big{display:inline-block;background:rgba(31,19,6,.85);color:#fff;padding:4px 12px;border-radius:999px;font-size:.72rem;letter-spacing:1.5px;text-transform:uppercase;font-weight:600;margin-bottom:8px}
  .lcgf-evento-stato-imminente{display:inline-block;background:linear-gradient(135deg,var(--c-wheat),var(--c-wheat-dark));color:var(--c-ink);padding:4px 12px;border-radius:999px;font-size:.72rem;letter-spacing:1.5px;text-transform:uppercase;font-weight:700;margin-bottom:8px}
  .lcgf-evento-detail-image{aspect-ratio:4/3;border-radius:var(--r-lg);overflow:hidden;background:var(--c-cream-2);box-shadow:var(--sh-2);position:sticky;top:80px}
  .lcgf-evento-detail-image img{width:100%;height:100%;object-fit:cover;display:block}
  .lcgf-evento-info-card{background:var(--c-white);border-radius:var(--r-lg);padding:24px;box-shadow:var(--sh-1);border:1px solid var(--c-line);margin-top:24px}
  .lcgf-evento-info-card h3{font-size:.78rem !important;letter-spacing:2px;text-transform:uppercase;color:var(--c-wheat-dark);margin:0 0 14px;font-weight:700}
  .lcgf-evento-info-row{display:flex;gap:14px;padding:10px 0;border-bottom:1px solid var(--c-line)}
  .lcgf-evento-info-row:last-child{border-bottom:none}
  .lcgf-evento-info-row .ico{flex-shrink:0;width:32px;height:32px;border-radius:50%;background:var(--c-cream-2);display:grid;place-items:center;font-size:14px}
  .lcgf-evento-info-row .txt{flex:1}
  .lcgf-evento-info-row .lbl{font-size:.72rem;letter-spacing:1.5px;text-transform:uppercase;color:var(--c-muted);font-weight:600;margin-bottom:2px}
  .lcgf-evento-info-row .val{color:var(--c-ink);font-weight:500;line-height:1.4}
  .lcgf-evento-cta-row{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px}
  .lcgf-evento-cta-row .btn{flex:1;min-width:140px;justify-content:center}
  .lcgf-evento-body-content{margin-top:36px;font-size:1.05rem;line-height:1.7;color:var(--c-ink)}
  .lcgf-evento-body-content p{margin-bottom:1em}
</style>

<section class="lcgf-evento-detail-hero">
  <div class="container">
    <p class="lcgf-evento-bread">
      <a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span>/</span>
      <a href="<?php echo esc_url(get_post_type_archive_link('lcgf_evento')); ?>">Fiere &amp; Eventi</a><span>/</span>
      <?php the_title(); ?>
    </p>

    <div class="lcgf-evento-detail-grid">
      <div>
        <?php if ($passato) : ?>
          <span class="lcgf-evento-tag-passato-big">Evento concluso</span>
        <?php elseif ($start && strtotime($start) - time() < 14 * DAY_IN_SECONDS) : ?>
          <span class="lcgf-evento-stato-imminente">Imminente</span>
        <?php endif; ?>

        <h1 class="lcgf-evento-detail-title"><?php the_title(); ?></h1>

        <div class="lcgf-evento-body-content">
          <?php the_content(); ?>
        </div>
      </div>

      <aside>
        <?php if ($img) : ?>
          <div class="lcgf-evento-detail-image">
            <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
          </div>
        <?php endif; ?>

        <div class="lcgf-evento-info-card">
          <h3>Dettagli evento</h3>

          <?php if ($date_label) : ?>
          <div class="lcgf-evento-info-row">
            <span class="ico">📅</span>
            <div class="txt">
              <div class="lbl">Quando</div>
              <div class="val">
                <?php echo esc_html($date_label); ?>
                <?php if ($time) echo '<br><small>ore ' . esc_html($time) . '</small>'; ?>
              </div>
            </div>
          </div>
          <?php endif; ?>

          <?php if ($luogo) : ?>
          <div class="lcgf-evento-info-row">
            <span class="ico">📍</span>
            <div class="txt">
              <div class="lbl">Dove</div>
              <div class="val">
                <?php echo esc_html($luogo); ?>
                <?php if ($indir) echo '<br><small>' . esc_html($indir) . '</small>'; ?>
                <?php if ($citta) echo '<br><small>' . esc_html($citta) . '</small>'; ?>
              </div>
            </div>
          </div>
          <?php endif; ?>

          <?php if ($prezzo) : ?>
          <div class="lcgf-evento-info-row">
            <span class="ico">🎟️</span>
            <div class="txt">
              <div class="lbl">Ingresso</div>
              <div class="val"><?php echo esc_html($prezzo); ?></div>
            </div>
          </div>
          <?php endif; ?>

          <div class="lcgf-evento-cta-row">
            <?php if ($maps_q) : ?>
              <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($maps_q); ?>"
                 target="_blank" rel="noopener" class="btn btn-olive">
                Apri in Maps
              </a>
            <?php endif; ?>
            <?php if ($link) : ?>
              <a href="<?php echo esc_url($link); ?>" target="_blank" rel="noopener" class="btn btn-wheat">
                Sito ufficiale
              </a>
            <?php endif; ?>
          </div>
        </div>
      </aside>
    </div>
  </div>
</section>

<section class="section" style="background: var(--c-cream-2)">
  <div class="container" style="text-align:center;max-width:680px">
    <span class="eyebrow">Ti aspettiamo</span>
    <h2 style="color:var(--c-olive-deep)">Vieni a trovarci.</h2>
    <p style="color:var(--c-ink-soft);margin:14px 0 24px">
      Assaggi gratuiti, vendita diretta dei nostri prodotti senza glutine e senza lattosio. Una bottega in trasferta per scoprirci di persona.
    </p>
    <a href="<?php echo esc_url(get_post_type_archive_link('lcgf_evento')); ?>" class="btn btn-olive">
      ← Tutti gli eventi
    </a>
  </div>
</section>

<?php endwhile;
get_footer();
