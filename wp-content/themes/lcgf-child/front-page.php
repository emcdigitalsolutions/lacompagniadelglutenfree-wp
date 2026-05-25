<?php
/**
 * Front Page LCGF — homepage custom premium.
 */
get_header();

// recupera featured (8 prodotti random pubblicati)
$featured = wc_get_products(['status' => 'publish', 'limit' => 8, 'orderby' => 'rand']);
$cats = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
$logo = get_stylesheet_directory_uri() . '/assets/img/logo.webp';
?>

<!-- HERO -->
<section class="lcgf-hero">
  <div class="container">
    <div class="lcgf-hero-grid">
      <div>
        <span class="eyebrow">Mangia con Gusto · Senza glutine e senza lattosio</span>
        <h1>Senza glutine,<br/><em>ma con gusto.</em></h1>
        <p class="lcgf-hero-lead">Pinsa romana, focacce, basi pizza, cornetti, tiramisù, cheesecake: il nostro panificato e i nostri dolci sono prodotti in un laboratorio dedicato, privi di contaminazioni, completamente senza glutine e senza lattosio.</p>
        <div class="lcgf-hero-ctas">
          <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="btn btn-lg">
            Scopri il catalogo
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
          <a href="<?php echo esc_url(home_url('/chi-siamo/')); ?>" class="btn btn-ghost btn-lg">La nostra storia</a>
        </div>
        <div class="lcgf-hero-stats">
          <div class="lcgf-hero-stat"><strong>13+</strong><span>Prodotti artigianali</span></div>
          <div class="lcgf-hero-stat"><strong>0%</strong><span>Glutine · 0% lattosio</span></div>
          <div class="lcgf-hero-stat"><strong>20+</strong><span>Anni di esperienza</span></div>
        </div>
      </div>

      <div class="lcgf-hero-visual">
        <div class="lcgf-hero-glow"></div>
        <div class="lcgf-hero-logo">
          <img src="<?php echo esc_url($logo); ?>" alt="La Compagnia del Gluten Free" />
        </div>
        <div class="lcgf-pillbadge b1"><span class="dot"></span> Senza glutine</div>
        <div class="lcgf-pillbadge b2"><span class="dot" style="background:#C7613E"></span> Senza lattosio</div>
        <div class="lcgf-pillbadge b3"><span class="dot" style="background:#C9A96E"></span> Laboratorio dedicato</div>
      </div>
    </div>
  </div>
</section>

<!-- MARQUEE -->
<div class="lcgf-marquee" aria-hidden="true">
  <div class="lcgf-marquee-track">
    <span>Senza glutine</span>
    <span>Senza lattosio</span>
    <span>Laboratorio dedicato</span>
    <span>Surgelati pronti all'uso</span>
    <span>20 anni di esperienza</span>
    <span>Mangia con Gusto</span>
    <span>Senza glutine</span>
    <span>Senza lattosio</span>
    <span>Laboratorio dedicato</span>
    <span>Surgelati pronti all'uso</span>
    <span>20 anni di esperienza</span>
    <span>Mangia con Gusto</span>
  </div>
</div>

<!-- CATEGORIES -->
<section class="section">
  <div class="container">
    <div class="lcgf-section-head">
      <span class="eyebrow">Le nostre famiglie</span>
      <h2>Pane, basi e dolci</h2>
      <p>Surgelati pronti all'uso. Senza glutine, senza lattosio, senza compromessi sul gusto.</p>
    </div>
    <div class="lcgf-cat-grid">
      <?php
      $cat_emojis = [
        'pane-basi'       => '🥖',
        'pane-pizza'      => '🥖',
        'dolci-colazione' => '🍰',
        'dolci'           => '🍰',
      ];
      foreach ($cats as $cat) {
        $hue = strpos($cat->slug, 'pane') !== false || strpos($cat->slug, 'pizza') !== false ? 'cat-pane' : 'cat-dolci';
        $emoji = $cat_emojis[$cat->slug] ?? '🌾';
        $url = get_term_link($cat);
        $count = $cat->count;
        ?>
        <a href="<?php echo esc_url($url); ?>" class="lcgf-cat-card <?php echo esc_attr($hue); ?>">
          <div class="lcgf-cat-card-bg"></div>
          <div class="lcgf-cat-card-emoji"><?php echo $emoji; ?></div>
          <div class="lcgf-cat-card-content">
            <h3><?php echo esc_html($cat->name); ?></h3>
            <p><?php echo (int)$count; ?> prodotti</p>
          </div>
        </a>
        <?php
      }
      ?>
    </div>
  </div>
</section>

<!-- FEATURED PRODUCTS -->
<section class="section" style="background: var(--c-cream-2)">
  <div class="container">
    <div class="lcgf-section-head">
      <span class="eyebrow">In evidenza</span>
      <h2>I più amati</h2>
      <p>I prodotti che i nostri clienti riordinano sempre.</p>
    </div>

    <?php if ($featured && count($featured) > 0): ?>
      <div class="woocommerce">
        <ul class="products">
          <?php foreach ($featured as $p) :
            $url = get_permalink($p->get_id());
            $img = wp_get_attachment_image_url($p->get_image_id(), 'medium_large') ?: wc_placeholder_img_src();
            ?>
            <li class="product">
              <div class="lcgf-card-badges">
                <span class="lcgf-card-badge gf">Senza glutine</span>
                <span class="lcgf-card-badge lf">Senza lattosio</span>
              </div>
              <a href="<?php echo esc_url($url); ?>">
                <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($p->get_name()); ?>" />
                <h3 class="woocommerce-loop-product__title"><?php echo esc_html($p->get_name()); ?></h3>
              </a>
              <span class="price"><?php echo $p->get_price_html(); ?></span>
              <a href="<?php echo esc_url('?add-to-cart=' . $p->get_id()); ?>" data-product_id="<?php echo (int)$p->get_id(); ?>" class="button add_to_cart_button ajax_add_to_cart">Aggiungi al carrello</a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div style="text-align:center;margin-top:40px">
      <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="btn btn-ghost btn-lg">
        Vedi tutti i prodotti
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </a>
    </div>
  </div>
</section>

<!-- STORY SPLIT -->
<section class="section">
  <div class="container">
    <div class="lcgf-split">
      <div class="lcgf-split-content">
        <span class="eyebrow">La nostra storia</span>
        <h2>Da un'esigenza personale, una passione per tutti.</h2>
        <p>Da una esigenza personale, un'esperienza ventennale e un gruppo di amici a cui piace sognare nasce "Mangia con Gusto - La Compagnia del Gluten Free". Quotidianamente ci impegniamo ad offrirvi prodotti gustosi e con materia prima di qualità.</p>
        <ul class="lcgf-split-list">
          <li><span class="ck">✓</span><span>Laboratorio dedicato, privo di contaminazioni da glutine.</span></li>
          <li><span class="ck">✓</span><span>Tutti i prodotti sono anche senza lattosio.</span></li>
          <li><span class="ck">✓</span><span>Surgelati pronti all'uso, fragranza appena sfornata.</span></li>
          <li><span class="ck">✓</span><span>Anche fuori casa puoi mangiare buono e sano.</span></li>
        </ul>
        <a href="<?php echo esc_url(home_url('/chi-siamo/')); ?>" class="btn btn-lg" style="margin-top:24px">
          Conosci la Compagnia
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
      </div>
      <div class="lcgf-split-visual">
        <span class="emoji">🌾</span>
      </div>
    </div>
  </div>
</section>

<!-- USP STRIP -->
<section class="section-tight">
  <div class="container">
    <div class="lcgf-usp">
      <div class="lcgf-usp-item">
        <div class="lcgf-usp-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 12V8a2 2 0 0 0-2-2H8L2 12l6 6h10a2 2 0 0 0 2-2v-4"/><line x1="2" y1="12" x2="20" y2="12"/></svg>
        </div>
        <div><strong>Spedizione veloce</strong><span>24-48h in Italia</span></div>
      </div>
      <div class="lcgf-usp-item">
        <div class="lcgf-usp-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </div>
        <div><strong>Soddisfatti o rimborsati</strong><span>Recesso entro 14 giorni</span></div>
      </div>
      <div class="lcgf-usp-item">
        <div class="lcgf-usp-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>
        <div><strong>Pagamenti sicuri</strong><span>Stripe, PayPal, Klarna</span></div>
      </div>
      <div class="lcgf-usp-item">
        <div class="lcgf-usp-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
        </div>
        <div><strong>Supporto su WhatsApp</strong><span>Rispondiamo entro 1h</span></div>
      </div>
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section class="section lcgf-testimonials">
  <div class="container">
    <div class="lcgf-section-head">
      <span class="eyebrow">Le voci di chi ci sceglie</span>
      <h2>Mille storie senza glutine</h2>
    </div>
    <div class="lcgf-t-grid">
      <div class="lcgf-t-card">
        <div class="lcgf-t-stars">★★★★★</div>
        <p class="lcgf-t-quote">"La pinsa è la cosa più simile alla pizza vera che abbia mangiato in 10 anni di celiachia."</p>
        <div class="lcgf-t-author">
          <div class="lcgf-t-avatar">MS</div>
          <div><strong>Maria Sgarlata</strong><span>Catania</span></div>
        </div>
      </div>
      <div class="lcgf-t-card">
        <div class="lcgf-t-stars">★★★★★</div>
        <p class="lcgf-t-quote">"I cornetti sono perfetti, mio figlio celiaco ha pianto di gioia. Ordinerò di nuovo a Natale."</p>
        <div class="lcgf-t-author">
          <div class="lcgf-t-avatar">GP</div>
          <div><strong>Giulia Patti</strong><span>Milano</span></div>
        </div>
      </div>
      <div class="lcgf-t-card">
        <div class="lcgf-t-stars">★★★★★</div>
        <p class="lcgf-t-quote">"Il tiramisù senza glutine e senza lattosio è straordinario. Si sente l'amore in ogni cucchiaio."</p>
        <div class="lcgf-t-author">
          <div class="lcgf-t-avatar">AR</div>
          <div><strong>Andrea Romano</strong><span>Berlino</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- NEWSLETTER -->
<section class="section">
  <div class="container" style="max-width:880px">
    <div class="lcgf-newsletter">
      <span class="eyebrow">Resta aggiornato</span>
      <h2>Ricette, novità e -10% sul primo ordine</h2>
      <p>Iscriviti alla newsletter: ogni mese ricette gluten free, anteprime sui nuovi arrivi e uno sconto di benvenuto.</p>
      <form class="lcgf-newsletter-form" id="lcgfNewsletter" onsubmit="event.preventDefault();this.querySelector('button').textContent='✓ Iscritto';">
        <input type="email" placeholder="La tua email" required />
        <button type="submit" class="btn">Iscriviti</button>
      </form>
    </div>
  </div>
</section>

<?php get_footer();
