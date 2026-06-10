<?php
/**
 * Template Name: FAQ (LCGF)
 * Pagina Domande frequenti: riusa le chiavi faq_* di lcgf_t (già multilingua),
 * stessa fonte della sezione FAQ in home. Nessun contenuto da tradurre a parte.
 */
get_header();
$__lang = function_exists('pll_current_language') ? pll_current_language('slug') : 'it';
$L = function ($it, $en, $de, $fr) use ($__lang) {
  $m = ['it' => $it, 'en' => $en, 'de' => $de, 'fr' => $fr];
  return $m[$__lang] ?? $it;
};
$intro = $L(
  'Le risposte alle domande più comuni su prodotti, celiachia, spedizioni e conservazione. Non trovi quello che cerchi? Scrivici.',
  'Answers to the most common questions about products, coeliac disease, shipping and storage. Can\'t find what you need? Write to us.',
  'Antworten auf die häufigsten Fragen zu Produkten, Zöliakie, Versand und Aufbewahrung. Nicht gefunden, was du suchst? Schreib uns.',
  'Les réponses aux questions les plus fréquentes sur les produits, la maladie cœliaque, la livraison et la conservation. Vous ne trouvez pas ? Écrivez-nous.'
);
$contact_id = function_exists('pll_get_post') ? pll_get_post(29) : 29; // 29 = contatti IT
$contact_url = $contact_id ? get_permalink($contact_id) : home_url('/');
?>

<section class="lcgf-hero" style="padding: 80px 0 50px">
  <div class="container">
    <div style="max-width:760px;margin:0 auto;text-align:center;position:relative;z-index:1">
      <span class="eyebrow">FAQ</span>
      <h1 style="color: var(--c-olive-deep) !important"><?php echo esc_html(lcgf_t('faq_h2')); ?></h1>
      <p style="font-size:1.1rem;color:var(--c-ink-soft);margin-top:16px"><?php echo esc_html($intro); ?></p>
    </div>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width:820px">
    <div class="lcgf-faq">
      <?php for ($i = 1; $i <= 6; $i++) :
        $q = lcgf_t('faq_q' . $i);
        $a = lcgf_t('faq_a' . $i);
        if (!$q) continue; ?>
        <details class="lcgf-faq-item" style="background:var(--c-white);border:1px solid var(--c-line);border-radius:var(--r-lg);margin-bottom:14px;box-shadow:var(--sh-1);overflow:hidden">
          <summary style="cursor:pointer;list-style:none;padding:20px 24px;font-family:var(--f-display);font-size:1.1rem;color:var(--c-olive-deep);font-weight:600;display:flex;justify-content:space-between;align-items:center;gap:16px">
            <span><?php echo esc_html($q); ?></span>
            <svg class="lcgf-faq-chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" style="flex-shrink:0;transition:transform .25s"><polyline points="6 9 12 15 18 9"/></svg>
          </summary>
          <div style="padding:0 24px 22px;color:var(--c-ink-soft);font-size:.98rem;line-height:1.65"><?php echo esc_html($a); ?></div>
        </details>
      <?php endfor; ?>
    </div>

    <div style="text-align:center;margin-top:40px">
      <a href="<?php echo esc_url($contact_url); ?>" class="btn btn-lg">
        <?php echo esc_html($L('Hai altre domande? Contattaci', 'More questions? Contact us', 'Weitere Fragen? Kontaktiere uns', 'D\'autres questions ? Contactez-nous')); ?>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </a>
    </div>
  </div>
</section>

<style>
.lcgf-faq-item[open] .lcgf-faq-chevron { transform: rotate(180deg); }
.lcgf-faq summary::-webkit-details-marker { display:none; }
</style>

<?php get_footer();
