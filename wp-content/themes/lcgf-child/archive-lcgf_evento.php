<?php
/**
 * Archivio Novità & Eventi (multilingua).
 * Eventi = voci con data; Novità = voci senza data. Polylang filtra per lingua.
 */
get_header();
$__lang = function_exists('pll_current_language') ? (pll_current_language('slug') ?: 'it') : 'it';
$L = function ($it, $en, $de, $fr) use ($__lang) {
  $m = ['it' => $it, 'en' => $en, 'de' => $de, 'fr' => $fr];
  return $m[$__lang] ?? $it;
};

$now = current_time('Y-m-d');
$all = get_posts(['post_type' => 'lcgf_evento', 'numberposts' => -1, 'post_status' => 'publish']); // Polylang filtra per lingua
$upcoming = [];
$rest = [];
foreach ($all as $p) {
  $d = get_post_meta($p->ID, '_lcgf_evento_data_inizio', true);
  if ($d && $d >= $now) $upcoming[] = $p;
  else $rest[] = $p;
}
usort($upcoming, function ($a, $b) {
  return strcmp(get_post_meta($a->ID, '_lcgf_evento_data_inizio', true), get_post_meta($b->ID, '_lcgf_evento_data_inizio', true));
});
usort($rest, function ($a, $b) {
  $da = get_post_meta($a->ID, '_lcgf_evento_data_inizio', true) ?: $a->post_date;
  $db = get_post_meta($b->ID, '_lcgf_evento_data_inizio', true) ?: $b->post_date;
  return strcmp($db, $da);
});

function lcgf_render_evento_card($post_id, $L, $passato = false) {
  $start  = get_post_meta($post_id, '_lcgf_evento_data_inizio', true);
  $end    = get_post_meta($post_id, '_lcgf_evento_data_fine', true);
  $time   = get_post_meta($post_id, '_lcgf_evento_ora_inizio', true);
  $luogo  = get_post_meta($post_id, '_lcgf_evento_luogo', true);
  $citta  = get_post_meta($post_id, '_lcgf_evento_citta', true);
  $prezzo = get_post_meta($post_id, '_lcgf_evento_prezzo', true);
  $img = get_the_post_thumbnail_url($post_id, 'large');
  $url = get_permalink($post_id);
  $is_news = !$start;

  $date_label = '';
  if ($start) {
    $date_label = date_i18n('d F Y', strtotime($start));
    if ($end && $end !== $start) $date_label .= ' — ' . date_i18n('d F Y', strtotime($end));
    if ($time) $date_label .= ' · ' . esc_html($time);
  }
  ?>
  <article class="lcgf-evento-card<?php echo $passato ? ' is-passato' : ''; ?>">
    <a href="<?php echo esc_url($url); ?>" class="lcgf-evento-cover">
      <?php if ($img) : ?>
        <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr(get_the_title($post_id)); ?>" loading="lazy" />
      <?php else : ?>
        <div class="lcgf-evento-cover-empty"><?php echo $is_news ? '📰' : '📅'; ?></div>
      <?php endif; ?>
      <?php if ($start) : ?>
        <div class="lcgf-evento-datebadge">
          <span class="d"><?php echo esc_html(date_i18n('d', strtotime($start))); ?></span>
          <span class="m"><?php echo esc_html(strtoupper(date_i18n('M', strtotime($start)))); ?></span>
          <span class="y"><?php echo esc_html(date_i18n('Y', strtotime($start))); ?></span>
        </div>
      <?php else : ?>
        <span class="lcgf-evento-tag-novita"><?php echo esc_html($L('Novità', 'News', 'Neuigkeit', 'Actualité')); ?></span>
      <?php endif; ?>
      <?php if ($passato) : ?>
        <span class="lcgf-evento-tag-passato"><?php echo esc_html($L('Concluso', 'Past', 'Vorbei', 'Terminé')); ?></span>
      <?php endif; ?>
    </a>
    <div class="lcgf-evento-body">
      <h3><a href="<?php echo esc_url($url); ?>"><?php echo esc_html(get_the_title($post_id)); ?></a></h3>
      <ul class="lcgf-evento-meta">
        <?php if ($date_label) : ?><li>📅 <?php echo esc_html($date_label); ?></li><?php endif; ?>
        <?php if ($luogo) : ?><li>📍 <?php echo esc_html($luogo . ($citta ? ' — ' . $citta : '')); ?></li><?php endif; ?>
        <?php if ($prezzo) : ?><li>🎟️ <?php echo esc_html($prezzo); ?></li><?php endif; ?>
      </ul>
      <?php $excerpt = get_the_excerpt($post_id); ?>
      <?php if ($excerpt) : ?><p class="lcgf-evento-excerpt"><?php echo esc_html(wp_trim_words($excerpt, 24)); ?></p><?php endif; ?>
      <a href="<?php echo esc_url($url); ?>" class="lcgf-evento-link"><?php echo esc_html($L('Scopri di più', 'Read more', 'Mehr erfahren', 'En savoir plus')); ?>
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
  .lcgf-evento-datebadge{position:absolute;top:14px;left:14px;background:rgba(255,255,255,.96);border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,.18);padding:8px 12px;text-align:center;min-width:60px;line-height:1}
  .lcgf-evento-datebadge .d{display:block;font-family:var(--f-display);font-weight:700;color:var(--c-olive-deep);font-size:1.5rem}
  .lcgf-evento-datebadge .m{display:block;font-size:.7rem;font-weight:700;letter-spacing:1.5px;color:var(--c-wheat-dark);margin-top:2px}
  .lcgf-evento-datebadge .y{display:block;font-size:.65rem;color:var(--c-muted);margin-top:1px}
  .lcgf-evento-tag-novita{position:absolute;top:14px;left:14px;background:var(--g-cta);color:var(--c-cream);padding:5px 13px;border-radius:999px;font-size:.7rem;letter-spacing:1.2px;text-transform:uppercase;font-weight:700}
  .lcgf-evento-tag-passato{position:absolute;top:14px;right:14px;background:rgba(31,19,6,.85);color:#fff;padding:4px 10px;border-radius:999px;font-size:.7rem;letter-spacing:1.5px;text-transform:uppercase;font-weight:600}
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
    <span class="eyebrow" style="display:block;text-align:center"><?php echo esc_html($L('Resta aggiornato', 'Stay updated', 'Bleib informiert', 'Restez informés')); ?></span>
    <h1><?php echo esc_html($L('Novità & Eventi', 'News & Events', 'Neuigkeiten & Events', 'Actualités & Événements')); ?></h1>
    <p class="sub"><?php echo esc_html($L(
      'Le ultime novità, i nuovi prodotti e gli eventi dove incontrarci di persona. Resta aggiornato sul mondo La Compagnia del Gluten Free.',
      'The latest news, new products and the events where you can meet us. Stay up to date with the world of La Compagnia del Gluten Free.',
      'Die neuesten Nachrichten, neue Produkte und die Events, bei denen du uns triffst. Bleib auf dem Laufenden über die Welt von La Compagnia del Gluten Free.',
      'Les dernières actualités, les nouveaux produits et les événements où nous rencontrer. Restez informés sur le monde de La Compagnia del Gluten Free.'
    )); ?></p>
  </div>
</section>

<?php if (!empty($upcoming)) : ?>
<section class="section">
  <div class="container">
    <div class="lcgf-evento-section-title">
      <span class="eyebrow"><?php echo esc_html($L('In programma', 'Coming up', 'Demnächst', 'À venir')); ?></span>
      <h2><?php echo esc_html($L('Prossimi eventi', 'Upcoming events', 'Kommende Events', 'Prochains événements')); ?></h2>
    </div>
    <div class="lcgf-evento-grid">
      <?php foreach ($upcoming as $p) { $GLOBALS['post'] = $p; setup_postdata($p); lcgf_render_evento_card($p->ID, $L, false); } wp_reset_postdata(); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($rest)) : ?>
<section class="section"<?php echo !empty($upcoming) ? ' style="background: var(--c-cream-2)"' : ''; ?>>
  <div class="container">
    <div class="lcgf-evento-section-title">
      <span class="eyebrow"><?php echo esc_html($L('Dal nostro mondo', 'From our world', 'Aus unserer Welt', 'De notre univers')); ?></span>
      <h2><?php echo esc_html($L('Novità e appuntamenti passati', 'News & past events', 'Neuigkeiten & vergangene Events', 'Actualités & événements passés')); ?></h2>
    </div>
    <div class="lcgf-evento-grid">
      <?php foreach ($rest as $p) {
        $GLOBALS['post'] = $p; setup_postdata($p);
        $d = get_post_meta($p->ID, '_lcgf_evento_data_inizio', true);
        lcgf_render_evento_card($p->ID, $L, ($d && $d < $now));
      } wp_reset_postdata(); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (empty($upcoming) && empty($rest)) : ?>
<section class="section">
  <div class="container">
    <div class="lcgf-evento-empty">
      <strong><?php echo esc_html($L('Presto nuove novità.', 'New updates coming soon.', 'Bald gibt es Neuigkeiten.', 'De nouvelles actualités bientôt.')); ?></strong>
      <?php echo esc_html($L('Torna a trovarci o seguici sui social per non perdere i nostri appuntamenti.', 'Check back soon or follow us on social media so you don\'t miss our events.', 'Schau bald wieder vorbei oder folge uns in den sozialen Medien.', 'Revenez bientôt ou suivez-nous sur les réseaux sociaux.')); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section lcgf-testimonials">
  <div class="container" style="text-align:center">
    <span class="eyebrow" style="color:var(--c-wheat) !important"><?php echo esc_html($L('Vuoi invitarci?', 'Want to invite us?', 'Lade uns ein', 'Envie de nous inviter ?')); ?></span>
    <h2 style="color:var(--c-cream) !important"><?php echo esc_html($L('Organizzi un evento gluten free?', 'Organising a gluten-free event?', 'Organisierst du ein glutenfreies Event?', 'Vous organisez un événement sans gluten ?')); ?></h2>
    <p style="color:var(--c-cream-2);max-width:560px;margin:14px auto 24px"><?php echo esc_html($L('Contattaci per portare i nostri prodotti senza glutine al tuo evento, sagra o mercato.', 'Contact us to bring our gluten-free products to your event, fair or market.', 'Kontaktiere uns, um unsere glutenfreien Produkte zu deinem Event zu bringen.', 'Contactez-nous pour amener nos produits sans gluten à votre événement.')); ?></p>
    <a href="<?php echo esc_url(function_exists('lcgf_page_url') ? lcgf_page_url('contatti') : home_url('/contatti/')); ?>" class="btn btn-wheat btn-lg">
      <?php echo esc_html($L('Scrivici', 'Write to us', 'Schreib uns', 'Écrivez-nous')); ?>
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
    </a>
  </div>
</section>

<?php get_footer();
