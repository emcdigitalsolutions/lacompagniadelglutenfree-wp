<?php
/**
 * Pagina 404 (LCGF) — brandizzata e multilingua.
 * Hero coerente col tema, messaggio amichevole, ricerca a testo libero
 * e CTA verso home e catalogo. Stringhe via helper $L.
 */
get_header();
$__lang = function_exists('pll_current_language') ? pll_current_language('slug') : 'it';
$L = function ($it, $en, $de, $fr) use ($__lang) {
  $m = ['it' => $it, 'en' => $en, 'de' => $de, 'fr' => $fr];
  return $m[$__lang] ?? $it;
};
$ph        = $L('Cerca prodotti, pagine, informazioni…', 'Search products, pages, information…', 'Produkte, Seiten, Informationen suchen…', 'Rechercher produits, pages, informations…');
$home_url  = function_exists('pll_home_url') ? pll_home_url() : home_url('/');
$shop_url  = get_permalink(wc_get_page_id('shop'));
?>

<section class="lcgf-hero" style="padding: 90px 0 50px">
  <div class="container">
    <div style="max-width:720px;margin:0 auto;text-align:center;position:relative;z-index:1">
      <span class="eyebrow"><?php echo $L('Pagina non trovata', 'Page not found', 'Seite nicht gefunden', 'Page introuvable'); ?></span>

      <div style="font-family:var(--f-serif,serif);font-size:clamp(4.5rem,16vw,8rem);line-height:1;font-weight:700;color:var(--c-wheat-dark);letter-spacing:.02em;margin:6px 0 4px">404</div>

      <h1 style="color: var(--c-olive-deep) !important;margin-top:0">
        <?php echo $L('Ops! Questa pagina è andata… a lievitare altrove.', 'Oops! This page has gone… to rise somewhere else.', 'Ups! Diese Seite ist… woanders aufgegangen.', 'Oups ! Cette page est partie… lever ailleurs.'); ?>
      </h1>

      <p style="color:var(--c-ink-soft);font-size:1.08rem;max-width:560px;margin:14px auto 0">
        <?php echo $L(
          'Il link che hai seguito non esiste o è stato spostato. Nessun problema: cerca quello che ti serve o torna al catalogo dei nostri prodotti senza glutine e senza lattosio.',
          'The link you followed doesn\'t exist or has been moved. No worries: search for what you need or go back to our gluten-free and lactose-free catalogue.',
          'Der Link, dem du gefolgt bist, existiert nicht oder wurde verschoben. Kein Problem: Suche, was du brauchst, oder kehre zu unserem glutenfreien und laktosefreien Katalog zurück.',
          'Le lien que vous avez suivi n\'existe pas ou a été déplacé. Pas d\'inquiétude : cherchez ce dont vous avez besoin ou revenez à notre catalogue sans gluten et sans lactose.'
        ); ?>
      </p>

      <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>"
            style="display:flex;gap:10px;margin-top:28px;max-width:600px;margin-left:auto;margin-right:auto">
        <div style="position:relative;flex:1">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--c-muted)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position:absolute;left:16px;top:50%;transform:translateY(-50%);pointer-events:none"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
          <input type="search" name="s" value="" placeholder="<?php echo esc_attr($ph); ?>"
                 style="width:100%;box-sizing:border-box;padding:15px 18px 15px 46px;border:1.5px solid var(--c-line);border-radius:var(--r-pill);font-size:1rem;font-family:var(--f-sans);background:var(--c-white);color:var(--c-ink);outline:none">
        </div>
        <button type="submit" class="btn btn-lg" style="white-space:nowrap"><?php echo $L('Cerca', 'Search', 'Suchen', 'Rechercher'); ?></button>
      </form>

      <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;margin-top:24px">
        <a href="<?php echo esc_url($shop_url); ?>" class="btn btn-lg">
          <?php echo $L('Sfoglia il catalogo', 'Browse the catalogue', 'Zum Katalog', 'Parcourir le catalogue'); ?>
        </a>
        <a href="<?php echo esc_url($home_url); ?>" class="btn btn-lg btn-ghost" style="background:transparent;border:1.5px solid var(--c-line);color:var(--c-olive-deep)">
          <?php echo $L('Torna alla home', 'Back to home', 'Zur Startseite', 'Retour à l\'accueil'); ?>
        </a>
      </div>
    </div>
  </div>
</section>

<?php get_footer();
