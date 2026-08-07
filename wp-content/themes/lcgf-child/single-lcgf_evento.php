<?php
/**
 * Singolo Novità/Evento (multilingua). Mostra la card "Dettagli evento" solo se
 * la voce ha data/luogo (evento); le novità senza data sono articoli semplici.
 */
get_header();
$__lang = function_exists('pll_current_language') ? (pll_current_language('slug') ?: 'it') : 'it';
$L = function ($it, $en, $de, $fr) use ($__lang) {
  $m = ['it' => $it, 'en' => $en, 'de' => $de, 'fr' => $fr];
  return $m[$__lang] ?? $it;
};

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

  $is_event = (bool) ($start || $luogo);
  $passato  = $start && $start < current_time('Y-m-d');
  $img      = get_the_post_thumbnail_url($post_id, 'full');

  $date_label = '';
  if ($start) {
    $date_label = date_i18n('l d F Y', strtotime($start));
    if ($end && $end !== $start) $date_label .= ' — ' . date_i18n('l d F Y', strtotime($end));
  }
  $maps_q = trim(implode(', ', array_filter([$luogo, $indir, $citta])));
  $archive_url = get_post_type_archive_link('lcgf_evento');
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
  .lcgf-evento-tag-novita-big{display:inline-block;background:var(--g-cta);color:var(--c-cream);padding:4px 14px;border-radius:999px;font-size:.72rem;letter-spacing:1.5px;text-transform:uppercase;font-weight:700;margin-bottom:8px}
  .lcgf-evento-detail-image{border-radius:var(--r-lg);overflow:hidden;background:var(--c-cream-2);box-shadow:var(--sh-2);position:sticky;top:80px;line-height:0}
  .lcgf-evento-detail-image img{width:100%;height:auto;display:block}
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
      <a href="<?php echo esc_url($archive_url); ?>"><?php echo esc_html($L('Novità & Eventi', 'News & Events', 'Neuigkeiten & Events', 'Actualités & Événements')); ?></a><span>/</span>
      <?php the_title(); ?>
    </p>

    <div class="lcgf-evento-detail-grid">
      <div>
        <?php if ($passato) : ?>
          <span class="lcgf-evento-tag-passato-big"><?php echo esc_html($L('Evento concluso', 'Past event', 'Vergangenes Event', 'Événement terminé')); ?></span>
        <?php elseif ($start && strtotime($start) - time() < 14 * DAY_IN_SECONDS) : ?>
          <span class="lcgf-evento-stato-imminente"><?php echo esc_html($L('Imminente', 'Coming soon', 'Demnächst', 'Bientôt')); ?></span>
        <?php elseif (!$is_event) : ?>
          <span class="lcgf-evento-tag-novita-big"><?php echo esc_html($L('Novità', 'News', 'Neuigkeit', 'Actualité')); ?></span>
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

        <?php if ($is_event) : ?>
        <div class="lcgf-evento-info-card">
          <h3><?php echo esc_html($L('Dettagli evento', 'Event details', 'Event-Details', 'Détails de l\'événement')); ?></h3>

          <?php if ($date_label) : ?>
          <div class="lcgf-evento-info-row">
            <span class="ico">📅</span>
            <div class="txt">
              <div class="lbl"><?php echo esc_html($L('Quando', 'When', 'Wann', 'Quand')); ?></div>
              <div class="val"><?php echo esc_html($date_label); ?><?php if ($time) echo '<br><small>' . esc_html($time) . '</small>'; ?></div>
            </div>
          </div>
          <?php endif; ?>

          <?php if ($luogo) : ?>
          <div class="lcgf-evento-info-row">
            <span class="ico">📍</span>
            <div class="txt">
              <div class="lbl"><?php echo esc_html($L('Dove', 'Where', 'Wo', 'Où')); ?></div>
              <div class="val"><?php echo esc_html($luogo); ?><?php if ($indir) echo '<br><small>' . esc_html($indir) . '</small>'; ?><?php if ($citta) echo '<br><small>' . esc_html($citta) . '</small>'; ?></div>
            </div>
          </div>
          <?php endif; ?>

          <?php if ($prezzo) : ?>
          <div class="lcgf-evento-info-row">
            <span class="ico">🎟️</span>
            <div class="txt">
              <div class="lbl"><?php echo esc_html($L('Ingresso', 'Entry', 'Eintritt', 'Entrée')); ?></div>
              <div class="val"><?php echo esc_html($prezzo); ?></div>
            </div>
          </div>
          <?php endif; ?>

          <div class="lcgf-evento-cta-row">
            <?php if ($maps_q) : ?>
              <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($maps_q); ?>" target="_blank" rel="noopener" class="btn btn-olive"><?php echo esc_html($L('Apri in Maps', 'Open in Maps', 'In Maps öffnen', 'Ouvrir dans Maps')); ?></a>
            <?php endif; ?>
            <?php if ($link) : ?>
              <a href="<?php echo esc_url($link); ?>" target="_blank" rel="noopener" class="btn btn-wheat"><?php echo esc_html($L('Sito ufficiale', 'Official website', 'Offizielle Website', 'Site officiel')); ?></a>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
      </aside>
    </div>
  </div>
</section>

<section class="section" style="background: var(--c-cream-2)">
  <div class="container" style="text-align:center;max-width:680px">
    <span class="eyebrow"><?php echo esc_html($L('Resta aggiornato', 'Stay updated', 'Bleib informiert', 'Restez informés')); ?></span>
    <h2 style="color:var(--c-olive-deep)"><?php echo esc_html($is_event ? $L('Vieni a trovarci.', 'Come and visit us.', 'Besuch uns.', 'Venez nous voir.') : $L('Scopri i nostri prodotti.', 'Discover our products.', 'Entdecke unsere Produkte.', 'Découvrez nos produits.')); ?></h2>
    <a href="<?php echo esc_url($archive_url); ?>" class="btn btn-olive">← <?php echo esc_html($L('Tutte le novità', 'All news', 'Alle Neuigkeiten', 'Toutes les actualités')); ?></a>
  </div>
</section>

<?php
  // Dati geo/indirizzo aggiuntivi (SEO/GEO/LLM) — impostati dallo script di import, con fallback graceful
  $lat       = get_post_meta($post_id, '_lcgf_evento_lat', true);
  $lng       = get_post_meta($post_id, '_lcgf_evento_lng', true);
  $cap       = get_post_meta($post_id, '_lcgf_evento_cap', true);
  $country   = get_post_meta($post_id, '_lcgf_evento_nazione', true) ?: 'IT';
  $organizer = get_post_meta($post_id, '_lcgf_evento_organizer', true);
  $lang_loc  = function_exists('pll_current_language') ? (pll_current_language('locale') ?: 'it_IT') : 'it_IT';
  $permalink = get_permalink($post_id);
  $descr     = wp_strip_all_tags(get_the_excerpt()) ?: wp_strip_all_tags(get_the_content());

  // "Ravanusa (AG)" -> località + regione/provincia
  $loc_locality = $citta; $loc_region = '';
  if ($citta && preg_match('/^(.*?)\s*\(([^)]+)\)\s*$/u', $citta, $mm)) {
    $loc_locality = trim($mm[1]);
    $loc_region   = trim($mm[2]);
  }

  // Schema.org: Event (se ha data) oppure NewsArticle
  if ($is_event && $start) {
    $start_iso = $start . ($time ? 'T' . substr($time, 0, 5) . ':00' : '');
    $schema = [
      '@context' => 'https://schema.org',
      '@type' => 'Event',
      'name' => get_the_title(),
      'startDate' => $start_iso,
      'eventStatus' => 'https://schema.org/EventScheduled',
      'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
      'description' => $descr,
      'url' => $permalink,
      'inLanguage' => $lang_loc,
      'isAccessibleForFree' => true,
    ];
    if ($end) $schema['endDate'] = $end . ($time ? 'T' . substr($time, 0, 5) . ':00' : '');
    if ($img) $schema['image'] = [$img];

    if ($luogo || $citta) {
      $place = ['@type' => 'Place', 'name' => $luogo ?: $citta];
      $address = ['@type' => 'PostalAddress'];
      if ($indir)        $address['streetAddress']   = $indir;
      if ($loc_locality) $address['addressLocality'] = $loc_locality;
      if ($loc_region)   $address['addressRegion']   = $loc_region;
      if ($cap)          $address['postalCode']      = $cap;
      $address['addressCountry'] = $country;
      $place['address'] = $address;
      if ($lat && $lng) $place['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => (float) $lat, 'longitude' => (float) $lng];
      $schema['location'] = $place;
    }

    // Offerta (ingresso libero) — utile per rich result e motori generativi
    $schema['offers'] = [
      '@type' => 'Offer',
      'price' => '0',
      'priceCurrency' => 'EUR',
      'availability' => 'https://schema.org/InStock',
      'url' => $permalink,
      'validFrom' => get_the_date('c'),
    ];

    // Organizzatore reale dell'evento (se noto) + La Compagnia del Gluten Free come partecipante/espositore
    if ($organizer) {
      $schema['organizer'] = ['@type' => 'Organization', 'name' => $organizer];
    }
    $schema['performer'] = ['@type' => 'Organization', 'name' => 'La Compagnia del Gluten Free', 'url' => home_url('/')];
  } else {
    $schema = ['@context' => 'https://schema.org', '@type' => 'NewsArticle', 'headline' => get_the_title(), 'datePublished' => get_the_date('c'), 'dateModified' => get_the_modified_date('c'), 'description' => $descr, 'inLanguage' => $lang_loc, 'mainEntityOfPage' => $permalink, 'author' => ['@type' => 'Organization', 'name' => 'La Compagnia del Gluten Free'], 'publisher' => ['@type' => 'Organization', 'name' => 'La Compagnia del Gluten Free', 'url' => home_url('/')]];
    if ($img) $schema['image'] = [$img];
  }
  echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
?>

<?php endwhile;
get_footer();
