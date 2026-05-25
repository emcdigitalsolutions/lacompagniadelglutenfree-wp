<?php
/**
 * Template Name: Chi Siamo (LCGF)
 * Pagina chi-siamo con storia + valori + team.
 */
get_header();
$logo = get_stylesheet_directory_uri() . '/assets/img/logo.webp';
?>

<section class="lcgf-hero" style="padding: 90px 0 80px">
  <div class="container">
    <div style="max-width:760px;margin:0 auto;text-align:center;position:relative;z-index:1">
      <span class="eyebrow">La nostra storia</span>
      <h1 style="color: var(--c-olive-deep) !important">Mangia con Gusto.</h1>
      <p style="font-size:1.15rem;color:var(--c-ink-soft);margin-top:18px">
        Una bottega di prodotti senza glutine e senza lattosio. Pane, pinse, focacce, basi pizza e dolci sfornati in laboratorio dedicato e privo di contaminazioni.
      </p>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="lcgf-split">
      <div class="lcgf-split-content">
        <span class="eyebrow">Come è nata</span>
        <h2>Da un'esigenza, una passione.</h2>
        <p>Da una esigenza personale, un'esperienza ventennale e un gruppo di amici a cui piace sognare nasce <strong>"Mangia con Gusto — La Compagnia del Gluten Free"</strong>.</p>
        <p>Quotidianamente ci impegniamo ad offrirvi prodotti gustosi e con materia prima di qualità, creati in un laboratorio dedicato e privo di contaminazioni, per offrire ai nostri clienti — anche fuori casa — un'alimentazione buona e sana.</p>
        <p style="font-family: var(--f-display); font-style: italic; font-size: 1.4rem; color: var(--c-wheat-dark); margin-top: 24px">
          "Mangia senza glutine, ma con gusto!"
        </p>
      </div>
      <div class="lcgf-split-visual">
        <img src="<?php echo esc_url($logo); ?>" alt="Logo La Compagnia del Gluten Free" style="width:75%;height:auto;object-fit:contain;animation: spinSlow 60s linear infinite;filter:drop-shadow(0 16px 36px rgba(54,78,37,.3))" />
      </div>
    </div>
  </div>
</section>

<section class="section" style="background: var(--c-cream-2)">
  <div class="container">
    <div class="lcgf-section-head">
      <span class="eyebrow">I nostri valori</span>
      <h2>Quattro promesse, ogni giorno.</h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:24px">
      <?php
      $values = [
        ['icon' => '🌾', 'title' => 'Senza glutine.',           'body' => 'Laboratorio dedicato e privo di contaminazioni crociate.'],
        ['icon' => '🥛', 'title' => 'Senza lattosio.',          'body' => 'Tutti i nostri prodotti sono anche privi di lattosio.'],
        ['icon' => '👨‍🍳', 'title' => '20 anni di esperienza.', 'body' => 'Una vita passata a perfezionare ogni ricetta gluten free.'],
        ['icon' => '❄️',  'title' => 'Surgelati pronti all\'uso.', 'body' => 'Fragranza appena sfornata in 5 minuti di forno.'],
      ];
      foreach ($values as $v) : ?>
        <div style="background:var(--c-white);padding:32px;border-radius:var(--r-lg);text-align:center;box-shadow:var(--sh-1)">
          <div style="width:64px;height:64px;border-radius:50%;background:var(--c-cream-2);display:grid;place-items:center;margin:0 auto 16px;font-size:32px"><?php echo $v['icon']; ?></div>
          <h3 style="font-size:1.1rem !important;margin:0 0 8px"><?php echo esc_html($v['title']); ?></h3>
          <p style="color:var(--c-muted);font-size:.92rem;margin:0"><?php echo esc_html($v['body']); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="lcgf-section-head">
      <span class="eyebrow">Il team</span>
      <h2>Gli amici dietro la Compagnia</h2>
      <p>Tre persone, una passione condivisa.</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:24px;max-width:900px;margin:0 auto">
      <?php
      $team = [
        ['initial' => 'C', 'name' => 'Carmelo',  'role' => 'CEO & Co-Founder', 'phone' => '+39 327 699 9897', 'tel' => '+393276999897'],
        ['initial' => 'G', 'name' => 'Gianluca', 'role' => 'Co-Founder',       'phone' => '+39 349 565 8876', 'tel' => '+393495658876'],
        ['initial' => 'G', 'name' => 'Gaetano',  'role' => 'Co-Founder',       'phone' => '+39 351 358 2074', 'tel' => '+393513582074'],
      ];
      foreach ($team as $t) : ?>
        <div style="background:var(--c-white);padding:32px 24px;border-radius:var(--r-lg);text-align:center;border:1px solid var(--c-line);box-shadow:var(--sh-1)">
          <div style="width:84px;height:84px;border-radius:50%;background:var(--g-wheat);display:grid;place-items:center;margin:0 auto 16px;font-family:var(--f-display);font-size:2rem;color:var(--c-ink);font-weight:700;box-shadow:var(--sh-2)"><?php echo esc_html($t['initial']); ?></div>
          <h3 style="font-size:1.2rem !important;margin:0"><?php echo esc_html($t['name']); ?></h3>
          <p style="color:var(--c-muted);font-size:.88rem;margin:6px 0 16px"><?php echo esc_html($t['role']); ?></p>
          <a href="tel:<?php echo esc_attr($t['tel']); ?>" style="color:var(--c-olive-deep);font-weight:600;text-decoration:none">📞 <?php echo esc_html($t['phone']); ?></a>
          <br>
          <a href="https://wa.me/<?php echo esc_attr(ltrim($t['tel'],'+')); ?>" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:6px;margin-top:8px;padding:6px 14px;background:#25D366;color:#fff;border-radius:999px;font-size:.82rem;text-decoration:none">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.5 14.4c-.3-.1-1.7-.8-2-.9-.3-.1-.5-.2-.7.2-.2.3-.8.9-1 1.1-.2.2-.4.2-.7.1-1.9-.8-3.2-1.8-4.4-3.7-.3-.5.3-.5.9-1.5.1-.2 0-.4-.1-.5-.1-.1-.7-1.6-.9-2.2-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1.1 1.1-1.1 2.6 0 1.6 1.1 3.1 1.3 3.3.2.2 2.3 3.6 5.7 4.9 3.4 1.3 3.4.9 4 .8.6-.1 1.7-.7 2-1.4.2-.7.2-1.3.2-1.4-.1-.1-.2-.2-.5-.3ZM12 2C6.5 2 2 6.5 2 12c0 1.8.5 3.4 1.3 4.9L2 22l5.2-1.4c1.4.8 3 1.2 4.7 1.2 5.5 0 10-4.5 10-10S17.5 2 12 2Z"/></svg>
            WhatsApp
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section lcgf-testimonials">
  <div class="container" style="text-align:center">
    <span class="eyebrow" style="color:var(--c-wheat) !important">Pronto?</span>
    <h2 style="color:var(--c-cream) !important">Mangia senza glutine. Mangia con Gusto.</h2>
    <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="btn btn-wheat btn-lg" style="margin-top:24px">
      Scopri il catalogo
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
    </a>
  </div>
</section>

<?php get_footer();
