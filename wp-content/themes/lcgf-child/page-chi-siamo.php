<?php
/**
 * Template Name: Chi Siamo (LCGF)
 * Pagina chi-siamo con storia + valori + team.
 * Stringhe multilingua via helper locale $L(it, en, de, fr).
 */
get_header();
$logo = get_stylesheet_directory_uri() . '/assets/img/logo.webp';
$__lang = function_exists('pll_current_language') ? pll_current_language('slug') : 'it';
$L = function ($it, $en, $de, $fr) use ($__lang) {
  $m = ['it' => $it, 'en' => $en, 'de' => $de, 'fr' => $fr];
  return $m[$__lang] ?? $it;
};
?>

<section class="lcgf-hero" style="padding: 90px 0 80px">
  <div class="container">
    <div style="max-width:760px;margin:0 auto;text-align:center;position:relative;z-index:1">
      <span class="eyebrow"><?php echo $L('La nostra storia', 'Our story', 'Unsere Geschichte', 'Notre histoire'); ?></span>
      <h1 style="color: var(--c-olive-deep) !important">Mangia con Gusto.</h1>
      <p style="font-size:1.15rem;color:var(--c-ink-soft);margin-top:18px">
        <?php echo $L(
          'Una bottega di prodotti senza glutine e senza lattosio. Pane, pinse, focacce, basi pizza e dolci sfornati in laboratorio dedicato e privo di contaminazioni.',
          'A workshop of gluten-free and lactose-free products. Bread, pinsa, focaccia, pizza bases and sweets baked in a dedicated, contamination-free laboratory.',
          'Eine Manufaktur für glutenfreie und laktosefreie Produkte. Brot, Pinsa, Focaccia, Pizzaböden und Süßes, gebacken in einem dedizierten, kontaminationsfreien Labor.',
          'Un atelier de produits sans gluten et sans lactose. Pain, pinsa, focaccia, bases à pizza et douceurs cuits dans un laboratoire dédié et sans contamination.'
        ); ?>
      </p>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="lcgf-split">
      <div class="lcgf-split-content">
        <span class="eyebrow"><?php echo $L('Come è nata', 'How it began', 'Wie alles begann', 'Comment elle est née'); ?></span>
        <h2><?php echo $L("Da un'esigenza, una passione.", 'From a need, a passion.', 'Aus einem Bedürfnis, eine Leidenschaft.', "D'un besoin, une passion."); ?></h2>
        <p><?php echo $L(
          'Da una esigenza personale, un\'esperienza ventennale e un gruppo di amici a cui piace sognare nasce <strong>"Mangia con Gusto — La Compagnia del Gluten Free"</strong>.',
          'From a personal need, twenty years of experience and a group of friends who love to dream, <strong>"Mangia con Gusto — La Compagnia del Gluten Free"</strong> was born.',
          'Aus einem persönlichen Bedürfnis, zwanzig Jahren Erfahrung und einer Gruppe von Freunden, die gerne träumen, entstand <strong>„Mangia con Gusto — La Compagnia del Gluten Free"</strong>.',
          'D\'un besoin personnel, d\'une expérience de vingt ans et d\'un groupe d\'amis qui aiment rêver est née <strong>« Mangia con Gusto — La Compagnia del Gluten Free »</strong>.'
        ); ?></p>
        <p><?php echo $L(
          'Quotidianamente ci impegniamo ad offrirvi prodotti gustosi e con materia prima di qualità, creati in un laboratorio dedicato e privo di contaminazioni, per offrire ai nostri clienti — anche fuori casa — un\'alimentazione buona e sana.',
          'Every day we strive to offer you tasty products made with quality ingredients, created in a dedicated, contamination-free laboratory, to give our customers — even away from home — good and healthy food.',
          'Täglich setzen wir uns dafür ein, Ihnen schmackhafte Produkte aus hochwertigen Zutaten zu bieten, hergestellt in einem dedizierten, kontaminationsfreien Labor, um unseren Kunden — auch unterwegs — eine gute und gesunde Ernährung zu bieten.',
          'Chaque jour, nous nous engageons à vous offrir des produits savoureux à base d\'ingrédients de qualité, créés dans un laboratoire dédié et sans contamination, pour offrir à nos clients — même hors de chez eux — une alimentation bonne et saine.'
        ); ?></p>
        <p style="font-family: var(--f-display); font-style: italic; font-size: 1.4rem; color: var(--c-wheat-dark); margin-top: 24px">
          <?php echo $L(
            '"Mangia senza glutine, ma con gusto!"',
            '"Eat gluten-free, but with taste!"',
            '„Iss glutenfrei, aber mit Genuss!"',
            '« Mangez sans gluten, mais avec goût ! »'
          ); ?>
        </p>
      </div>
      <div class="lcgf-split-visual">
        <img src="<?php echo esc_url($logo); ?>" alt="Logo La Compagnia del Gluten Free" style="width:75%;height:auto;object-fit:contain;filter:drop-shadow(0 16px 36px rgba(54,78,37,.3))" />
      </div>
    </div>
  </div>
</section>

<section class="section" style="background: var(--c-cream-2)">
  <div class="container">
    <div class="lcgf-section-head">
      <span class="eyebrow"><?php echo $L('I nostri valori', 'Our values', 'Unsere Werte', 'Nos valeurs'); ?></span>
      <h2><?php echo $L('Quattro promesse, ogni giorno.', 'Four promises, every day.', 'Vier Versprechen, jeden Tag.', 'Quatre promesses, chaque jour.'); ?></h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:24px">
      <?php
      $values = [
        ['icon' => '🌾', 'title' => $L('Senza glutine.', 'Gluten-free.', 'Glutenfrei.', 'Sans gluten.'),
          'body' => $L('Laboratorio dedicato e privo di contaminazioni crociate.', 'A dedicated laboratory free from cross-contamination.', 'Ein dediziertes Labor ohne Kreuzkontamination.', 'Un laboratoire dédié et sans contamination croisée.')],
        ['icon' => '🥛', 'title' => $L('Senza lattosio.', 'Lactose-free.', 'Laktosefrei.', 'Sans lactose.'),
          'body' => $L('Tutti i nostri prodotti sono anche privi di lattosio.', 'All our products are also lactose-free.', 'Alle unsere Produkte sind außerdem laktosefrei.', 'Tous nos produits sont également sans lactose.')],
        ['icon' => '👨‍🍳', 'title' => $L('20 anni di esperienza.', '20 years of experience.', '20 Jahre Erfahrung.', "20 ans d'expérience."),
          'body' => $L('Una vita passata a perfezionare ogni ricetta gluten free.', 'A lifetime spent perfecting every gluten-free recipe.', 'Ein Leben lang jedes glutenfreie Rezept perfektioniert.', 'Une vie passée à perfectionner chaque recette sans gluten.')],
        ['icon' => '❄️', 'title' => $L("Surgelati pronti all'uso.", 'Frozen, ready to use.', 'Tiefkühlkost, gebrauchsfertig.', "Surgelés, prêts à l'emploi."),
          'body' => $L('Fragranza appena sfornata in 5 minuti di forno.', 'Freshly-baked fragrance in just 5 minutes in the oven.', 'Ofenfrischer Genuss in nur 5 Minuten im Ofen.', 'Le parfum du tout juste sorti du four en 5 minutes.')],
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
      <span class="eyebrow"><?php echo $L('Il team', 'The team', 'Das Team', "L'équipe"); ?></span>
      <h2><?php echo $L('Gli amici dietro la Compagnia', 'The friends behind the Compagnia', 'Die Freunde hinter der Compagnia', 'Les amis derrière la Compagnia'); ?></h2>
      <p><?php echo $L('Tre persone, una passione condivisa.', 'Three people, one shared passion.', 'Drei Menschen, eine gemeinsame Leidenschaft.', 'Trois personnes, une passion partagée.'); ?></p>
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
          <a href="https://wa.me/<?php echo esc_attr(ltrim($t['tel'],'+')); ?>" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:6px;margin-top:8px;padding:6px 14px;background:var(--g-cta);color:var(--c-cream);border-radius:999px;font-size:.82rem;text-decoration:none;box-shadow:0 4px 14px rgba(54,78,37,.25)">
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
    <span class="eyebrow" style="color:var(--c-wheat) !important"><?php echo $L('Pronto?', 'Ready?', 'Bereit?', 'Prêt ?'); ?></span>
    <h2 style="color:var(--c-cream) !important"><?php echo $L('Mangia senza glutine. Mangia con Gusto.', 'Eat gluten-free. Mangia con Gusto.', 'Iss glutenfrei. Mangia con Gusto.', 'Mangez sans gluten. Mangia con Gusto.'); ?></h2>
    <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="btn btn-wheat btn-lg" style="margin-top:24px">
      <?php echo $L('Scopri il catalogo', 'Browse the catalogue', 'Zum Katalog', 'Découvrir le catalogue'); ?>
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
    </a>
  </div>
</section>

<?php get_footer();
