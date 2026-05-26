<?php
/**
 * Archive Fiere ed Eventi.
 */
get_header();

$now = current_time('Y-m-d');

$args_prossimi = [
    'post_type'      => 'lcgf_evento',
    'posts_per_page' => -1,
    'meta_key'       => '_lcgf_evento_data_inizio',
    'orderby'        => 'meta_value',
    'order'          => 'ASC',
    'meta_query'     => [
        [
            'key'     => '_lcgf_evento_data_inizio',
            'value'   => $now,
            'compare' => '>=',
            'type'    => 'DATE',
        ],
    ],
];
$args_passati = [
    'post_type'      => 'lcgf_evento',
    'posts_per_page' => -1,
    'meta_key'       => '_lcgf_evento_data_inizio',
    'orderby'        => 'meta_value',
    'order'          => 'DESC',
    'meta_query'     => [
        [
            'key'     => '_lcgf_evento_data_inizio',
            'value'   => $now,
            'compare' => '<',
            'type'    => 'DATE',
        ],
    ],
];

$prossimi = new WP_Query($args_prossimi);
$passati  = new WP_Query($args_passati);

function lcgf_render_evento_card($post_id, $passato = false) {
    $start  = get_post_meta($post_id, '_lcgf_evento_data_inizio', true);
    $end    = get_post_meta($post_id, '_lcgf_evento_data_fine', true);
    $time   = get_post_meta($post_id, '_lcgf_evento_ora_inizio', true);
    $luogo  = get_post_meta($post_id, '_lcgf_evento_luogo', true);
    $citta  = get_post_meta($post_id, '_lcgf_evento_citta', true);
    $prezzo = get_post_meta($post_id, '_lcgf_evento_prezzo', true);

    $img = get_the_post_thumbnail_url($post_id, 'large');
    $url = get_permalink($post_id);

    $month_short = $start ? date_i18n('M', strtotime($start)) : '';
    $day         = $start ? date_i18n('d',  strtotime($start)) : '';
    $year        = $start ? date_i18n('Y', strtotime($start)) : '';

    $date_label = '';
    if ($start) {
        $date_label = date_i18n('d F Y', strtotime($start));
        if ($end && $end !== $start) {
            $date_label .= ' — ' . date_i18n('d F Y', strtotime($end));
        }
        if ($time) {
            $date_label .= ' · ore ' . $time;
        }
    }
    ?>
    <article class="lcgf-evento-card<?php echo $passato ? ' is-passato' : ''; ?>">
      <a href="<?php echo esc_url($url); ?>" class="lcgf-evento-cover">
        <?php if ($img) : ?>
          <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr(get_the_title($post_id)); ?>" loading="lazy" />
        <?php else : ?>
          <div class="lcgf-evento-cover-empty">📅</div>
        <?php endif; ?>
        <?php if ($start) : ?>
          <div class="lcgf-evento-datebadge">
            <span class="d"><?php echo esc_html($day); ?></span>
            <span class="m"><?php echo esc_html(strtoupper($month_short)); ?></span>
            <span class="y"><?php echo esc_html($year); ?></span>
          </div>
        <?php endif; ?>
        <?php if ($passato) : ?>
          <span class="lcgf-evento-tag-passato">Evento concluso</span>
        <?php endif; ?>
      </a>
      <div class="lcgf-evento-body">
        <h3><a href="<?php echo esc_url($url); ?>"><?php echo esc_html(get_the_title($post_id)); ?></a></h3>
        <ul class="lcgf-evento-meta">
          <?php if ($date_label) : ?>
            <li>📅 <?php echo esc_html($date_label); ?></li>
          <?php endif; ?>
          <?php if ($luogo) : ?>
            <li>📍 <?php echo esc_html($luogo . ($citta ? ' — ' . $citta : '')); ?></li>
          <?php endif; ?>
          <?php if ($prezzo) : ?>
            <li>🎟️ <?php echo esc_html($prezzo); ?></li>
          <?php endif; ?>
        </ul>
        <?php $excerpt = get_the_excerpt($post_id); ?>
        <?php if ($excerpt) : ?>
          <p class="lcgf-evento-excerpt"><?php echo esc_html(wp_trim_words($excerpt, 24)); ?></p>
        <?php endif; ?>
        <a href="<?php echo esc_url($url); ?>" class="lcgf-evento-link">Scopri di più
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
      </div>
    </article>
    <?php
}
?>

<style>
  .lcgf-evento-hero{padding:90px 0 50px;background:var(--c-cream)}
  .lcgf-evento-hero h1{color:var(--c-olive-deep) !important;text-align:center}
  .lcgf-evento-hero .sub{text-align:center;color:var(--c-ink-soft);font-size:1.1rem;max-width:680px;margin:16px auto 0}
  .lcgf-evento-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:28px}
  .lcgf-evento-card{background:var(--c-white);border-radius:var(--r-lg);overflow:hidden;box-shadow:var(--sh-1);border:1px solid var(--c-line);transition:transform .35s ease,box-shadow .35s ease;display:flex;flex-direction:column}
  .lcgf-evento-card:hover{transform:translateY(-4px);box-shadow:var(--sh-2)}
  .lcgf-evento-card.is-passato{opacity:.85}
  .lcgf-evento-cover{display:block;position:relative;aspect-ratio:16/10;background:var(--c-cream-2);overflow:hidden}
  .lcgf-evento-cover img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .6s ease}
  .lcgf-evento-card:hover .lcgf-evento-cover img{transform:scale(1.04)}
  .lcgf-evento-cover-empty{display:flex;align-items:center;justify-content:center;width:100%;height:100%;font-size:64px;background:linear-gradient(135deg,var(--c-cream-2),var(--c-wheat-light,#F5E6C9))}
  .lcgf-evento-datebadge{
    position:absolute;top:14px;left:14px;background:rgba(255,255,255,.96);
    border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,.18);
    padding:8px 12px;text-align:center;min-width:60px;line-height:1
  }
  .lcgf-evento-datebadge .d{display:block;font-family:var(--f-display);font-weight:700;color:var(--c-olive-deep);font-size:1.5rem}
  .lcgf-evento-datebadge .m{display:block;font-size:.7rem;font-weight:700;letter-spacing:1.5px;color:var(--c-wheat-dark);margin-top:2px}
  .lcgf-evento-datebadge .y{display:block;font-size:.65rem;color:var(--c-muted);margin-top:1px}
  .lcgf-evento-tag-passato{
    position:absolute;top:14px;right:14px;background:rgba(31,19,6,.85);color:#fff;
    padding:4px 10px;border-radius:999px;font-size:.7rem;letter-spacing:1.5px;text-transform:uppercase;font-weight:600
  }
  .lcgf-evento-body{padding:22px 22px 24px;flex:1;display:flex;flex-direction:column}
  .lcgf-evento-body h3{font-size:1.25rem !important;margin:0 0 12px;line-height:1.25}
  .lcgf-evento-body h3 a{color:var(--c-olive-deep);text-decoration:none}
  .lcgf-evento-body h3 a:hover{color:var(--c-wheat-dark)}
  .lcgf-evento-meta{list-style:none;padding:0;margin:0 0 12px;font-size:.9rem;color:var(--c-ink-soft);display:flex;flex-direction:column;gap:5px}
  .lcgf-evento-meta li{display:flex;align-items:center;gap:6px}
  .lcgf-evento-excerpt{color:var(--c-muted);font-size:.92rem;margin:6px 0 14px;line-height:1.5;flex:1}
  .lcgf-evento-link{display:inline-flex;align-items:center;gap:6px;color:var(--c-wheat-dark);font-weight:600;font-size:.92rem;text-decoration:none;align-self:flex-start;border-bottom:1px solid transparent;padding-bottom:2px}
  .lcgf-evento-link:hover{border-bottom-color:var(--c-wheat-dark)}
  .lcgf-evento-section-title{margin:0 0 32px}
  .lcgf-evento-section-title .eyebrow{display:block;margin-bottom:8px}
  .lcgf-evento-section-title h2{font-size:clamp(1.6rem,3vw,2.2rem) !important;color:var(--c-olive-deep)}
  .lcgf-evento-empty{background:var(--c-cream-2);border-radius:var(--r-lg);padding:48px 24px;text-align:center;color:var(--c-muted)}
  .lcgf-evento-empty strong{display:block;font-family:var(--f-display);color:var(--c-olive-deep);font-size:1.3rem;margin-bottom:8px}
</style>

<section class="lcgf-evento-hero">
  <div class="container">
    <span class="eyebrow" style="display:block;text-align:center">Fiere ed Eventi</span>
    <h1>Dove ci trovate.</h1>
    <p class="sub">
      Fiere, sagre, mercati ed eventi dove portiamo i prodotti senza glutine de La Compagnia del Gluten Free. Vieni a trovarci e assaggia di persona pane, focacce, pinse e dolci.
    </p>
  </div>
</section>

<section class="section">
  <div class="container">

    <div class="lcgf-evento-section-title">
      <span class="eyebrow">Prossimi appuntamenti</span>
      <h2>I prossimi eventi</h2>
    </div>

    <?php if ($prossimi->have_posts()) : ?>
      <div class="lcgf-evento-grid">
        <?php while ($prossimi->have_posts()) : $prossimi->the_post();
          lcgf_render_evento_card(get_the_ID(), false);
        endwhile; ?>
      </div>
    <?php else : ?>
      <div class="lcgf-evento-empty">
        <strong>Nessun evento in programma al momento.</strong>
        Stiamo organizzando le prossime date: torna a trovarci presto o seguici sui social per non perdere i nostri appuntamenti.
      </div>
    <?php endif; wp_reset_postdata(); ?>

  </div>
</section>

<?php if ($passati->have_posts()) : ?>
<section class="section" style="background: var(--c-cream-2)">
  <div class="container">
    <div class="lcgf-evento-section-title">
      <span class="eyebrow">Archivio</span>
      <h2>Eventi passati</h2>
    </div>
    <div class="lcgf-evento-grid">
      <?php while ($passati->have_posts()) : $passati->the_post();
        lcgf_render_evento_card(get_the_ID(), true);
      endwhile; wp_reset_postdata(); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section lcgf-testimonials">
  <div class="container" style="text-align:center">
    <span class="eyebrow" style="color:var(--c-wheat) !important">Vuoi invitarci?</span>
    <h2 style="color:var(--c-cream) !important">Organizzi un evento gluten free?</h2>
    <p style="color:var(--c-cream-2);max-width:560px;margin:14px auto 24px">Contattaci per portare i nostri prodotti senza glutine al tuo evento, sagra o mercato.</p>
    <a href="<?php echo esc_url(home_url('/contatti/')); ?>" class="btn btn-wheat btn-lg">
      Scrivici
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
    </a>
  </div>
</section>

<?php get_footer();
