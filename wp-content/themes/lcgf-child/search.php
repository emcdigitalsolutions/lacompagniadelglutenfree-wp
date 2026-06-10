<?php
/**
 * Pagina dei risultati di ricerca (LCGF).
 * Campo di ricerca a testo libero + risultati su prodotti, pagine e articoli
 * (descrizioni, info legali/pagamento, ecc.). Stringhe multilingua via $L.
 */
get_header();
$__lang = function_exists('pll_current_language') ? pll_current_language('slug') : 'it';
$L = function ($it, $en, $de, $fr) use ($__lang) {
  $m = ['it' => $it, 'en' => $en, 'de' => $de, 'fr' => $fr];
  return $m[$__lang] ?? $it;
};
$q = get_search_query();
$total = (int) ($GLOBALS['wp_query']->found_posts ?? 0);
$ph = $L('Cerca prodotti, pagine, informazioni…', 'Search products, pages, information…', 'Produkte, Seiten, Informationen suchen…', 'Rechercher produits, pages, informations…');
$badge = function ($type) use ($L) {
  if ($type === 'product') return $L('Prodotto', 'Product', 'Produkt', 'Produit');
  if ($type === 'post')    return $L('Articolo', 'Article', 'Artikel', 'Article');
  return $L('Pagina', 'Page', 'Seite', 'Page');
};
?>

<section class="lcgf-hero" style="padding: 80px 0 40px">
  <div class="container">
    <div style="max-width:720px;margin:0 auto;text-align:center;position:relative;z-index:1">
      <span class="eyebrow"><?php echo $L('Ricerca', 'Search', 'Suche', 'Recherche'); ?></span>
      <h1 style="color: var(--c-olive-deep) !important"><?php echo $L('Cosa stai cercando?', 'What are you looking for?', 'Wonach suchst du?', 'Que cherchez-vous ?'); ?></h1>

      <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>"
            style="display:flex;gap:10px;margin-top:26px;max-width:600px;margin-left:auto;margin-right:auto">
        <div style="position:relative;flex:1">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--c-muted)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position:absolute;left:16px;top:50%;transform:translateY(-50%);pointer-events:none"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
          <input type="search" name="s" value="<?php echo esc_attr($q); ?>" placeholder="<?php echo esc_attr($ph); ?>"
                 autofocus required
                 style="width:100%;box-sizing:border-box;padding:15px 18px 15px 46px;border:1.5px solid var(--c-line);border-radius:var(--r-pill);font-size:1rem;font-family:var(--f-sans);background:var(--c-white);color:var(--c-ink);outline:none">
        </div>
        <button type="submit" class="btn btn-lg" style="white-space:nowrap"><?php echo $L('Cerca', 'Search', 'Suchen', 'Rechercher'); ?></button>
      </form>

      <?php if ($q !== '') : ?>
        <p style="margin-top:18px;color:var(--c-ink-soft);font-size:.95rem">
          <?php
          if ($total > 0) {
            printf(esc_html($L('%1$d risultati per «%2$s»', '%1$d results for “%2$s”', '%1$d Ergebnisse für „%2$s"', '%1$d résultats pour « %2$s »')), $total, esc_html($q));
          } else {
            printf(esc_html($L('Nessun risultato per «%s».', 'No results for “%s”.', 'Keine Ergebnisse für „%s".', 'Aucun résultat pour « %s ».')), esc_html($q));
          }
          ?>
        </p>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="section" style="padding-top:20px">
  <div class="container" style="max-width:880px">
    <?php if (have_posts()) : ?>
      <div style="display:flex;flex-direction:column;gap:16px">
        <?php while (have_posts()) : the_post();
          $type = get_post_type();
          $is_product = ($type === 'product');
          $thumb = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail');
          $excerpt = wp_trim_words(wp_strip_all_tags(get_the_excerpt() ?: get_the_content()), 28, '…');
          $price = '';
          if ($is_product && function_exists('wc_get_product')) {
            $prod = wc_get_product(get_the_ID());
            if ($prod) $price = $prod->get_price_html();
          }
        ?>
          <a href="<?php the_permalink(); ?>" style="display:flex;gap:18px;align-items:center;background:var(--c-white);border:1px solid var(--c-line);border-radius:var(--r-lg);padding:16px 18px;box-shadow:var(--sh-1);text-decoration:none;transition:transform .15s ease,box-shadow .2s ease" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='var(--sh-3)'" onmouseout="this.style.transform='';this.style.boxShadow='var(--sh-1)'">
            <?php if ($thumb) : ?>
              <img src="<?php echo esc_url($thumb); ?>" alt="" loading="lazy" style="width:72px;height:72px;object-fit:cover;border-radius:var(--r-md);flex-shrink:0">
            <?php else : ?>
              <div style="width:72px;height:72px;border-radius:var(--r-md);background:var(--c-cream-2);display:grid;place-items:center;flex-shrink:0;color:var(--c-wheat-dark)">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
              </div>
            <?php endif; ?>
            <div style="flex:1;min-width:0">
              <span style="display:inline-block;font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--c-olive);background:var(--c-cream-2);padding:3px 10px;border-radius:999px;margin-bottom:6px"><?php echo esc_html($badge($type)); ?></span>
              <h3 style="font-size:1.12rem !important;margin:0 0 4px;color:var(--c-olive-deep)"><?php the_title(); ?></h3>
              <?php if ($excerpt) : ?><p style="margin:0;color:var(--c-muted);font-size:.9rem;line-height:1.5"><?php echo esc_html($excerpt); ?></p><?php endif; ?>
              <?php if ($price) : ?><div style="margin-top:6px;color:var(--c-wheat-dark);font-weight:700;font-size:.95rem"><?php echo wp_kses_post($price); ?></div><?php endif; ?>
            </div>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--c-muted)" stroke-width="2" stroke-linecap="round" style="flex-shrink:0"><polyline points="9 18 15 12 9 6"/></svg>
          </a>
        <?php endwhile; ?>
      </div>

      <div style="margin-top:34px;text-align:center">
        <?php echo paginate_links(['mid_size' => 1, 'prev_text' => '‹', 'next_text' => '›']); ?>
      </div>

    <?php elseif ($q !== '') : ?>
      <div style="text-align:center;padding:30px 0 10px;max-width:520px;margin:0 auto">
        <p style="color:var(--c-ink-soft);font-size:1.02rem"><?php echo $L(
          'Prova con un altro termine, oppure sfoglia il catalogo o scrivici: ti aiutiamo a trovare quello che cerchi.',
          'Try another term, or browse the catalogue or write to us: we will help you find what you need.',
          'Versuche einen anderen Begriff, durchstöbere den Katalog oder schreib uns: Wir helfen dir, das Richtige zu finden.',
          'Essayez un autre terme, parcourez le catalogue ou écrivez-nous : nous vous aiderons à trouver ce que vous cherchez.'
        ); ?></p>
        <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="btn btn-lg" style="margin-top:18px">
          <?php echo $L('Sfoglia il catalogo', 'Browse the catalogue', 'Zum Katalog', 'Parcourir le catalogue'); ?>
        </a>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php get_footer();
