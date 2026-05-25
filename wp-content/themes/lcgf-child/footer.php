<?php
/**
 * Footer LCGF — custom su Astra.
 */
?>
</main><!-- #main -->

<footer class="lcgf-footer">
  <div class="container lcgf-footer-grid">
    <div>
      <a href="<?php echo esc_url(home_url('/')); ?>" class="lcgf-brand">
        <span class="lcgf-brand-mark">
          <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/img/logo.webp'); ?>" alt="" />
        </span>
        <span class="brand-text">
          <strong style="font-family:var(--f-display);font-size:1.05rem;color:var(--c-cream);display:block">La Compagnia</strong>
          <small style="font-size:.68rem;letter-spacing:.18em;text-transform:uppercase;color:rgba(251,247,238,.5)">del Gluten Free</small>
        </span>
      </a>
      <p class="lcgf-footer-blurb">Mangia con Gusto — Prodotti senza glutine e senza lattosio. Pane, basi pizza, focacce, dolci e cheesecake prodotti in laboratorio dedicato.</p>
      <div class="lcgf-social">
        <a href="https://www.instagram.com/" target="_blank" rel="noopener" aria-label="Instagram">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
        </a>
        <a href="https://www.facebook.com/" target="_blank" rel="noopener" aria-label="Facebook">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
        </a>
        <a href="https://www.tiktok.com/" target="_blank" rel="noopener" aria-label="TikTok">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19.6 6.32a4.85 4.85 0 0 1-3.77-4.32h-3.27v13.4a2.65 2.65 0 1 1-2.65-2.65c.27 0 .53.04.78.12V9.5a5.91 5.91 0 0 0-.78-.05 5.92 5.92 0 1 0 5.92 5.92V8.66a8.06 8.06 0 0 0 4.66 1.49V6.86c-.3 0-.6-.18-.89-.54Z"/></svg>
        </a>
        <a href="https://wa.me/393276999897" target="_blank" rel="noopener" aria-label="WhatsApp">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.5 14.4c-.3-.1-1.7-.8-2-.9-.3-.1-.5-.2-.7.2-.2.3-.8.9-1 1.1-.2.2-.4.2-.7.1-1.9-.8-3.2-1.8-4.4-3.7-.3-.5.3-.5.9-1.5.1-.2 0-.4-.1-.5-.1-.1-.7-1.6-.9-2.2-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1.1 1.1-1.1 2.6 0 1.6 1.1 3.1 1.3 3.3.2.2 2.3 3.6 5.7 4.9 3.4 1.3 3.4.9 4 .8.6-.1 1.7-.7 2-1.4.2-.7.2-1.3.2-1.4-.1-.1-.2-.2-.5-.3ZM12 2C6.5 2 2 6.5 2 12c0 1.8.5 3.4 1.3 4.9L2 22l5.2-1.4c1.4.8 3 1.2 4.7 1.2 5.5 0 10-4.5 10-10S17.5 2 12 2Z"/></svg>
        </a>
      </div>
    </div>

    <div>
      <h4>Shop</h4>
      <ul>
        <li><a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>">Catalogo completo</a></li>
        <?php $cats = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
          foreach ($cats as $cat) {
            echo '<li><a href="' . esc_url(get_term_link($cat)) . '">' . esc_html($cat->name) . '</a></li>';
          } ?>
      </ul>
    </div>

    <div>
      <h4>Informazioni</h4>
      <ul>
        <li><a href="<?php echo esc_url(home_url('/chi-siamo/')); ?>">Chi siamo</a></li>
        <li><a href="<?php echo esc_url(home_url('/faq/')); ?>">FAQ</a></li>
        <li><a href="<?php echo esc_url(home_url('/spedizioni/')); ?>">Spedizioni e resi</a></li>
        <li><a href="<?php echo esc_url(home_url('/condizioni/')); ?>">Condizioni di vendita</a></li>
        <li><a href="<?php echo esc_url(home_url('/recesso/')); ?>">Diritto di recesso</a></li>
        <li><a href="<?php echo esc_url(home_url('/privacy/')); ?>">Privacy</a></li>
        <li><a href="<?php echo esc_url(home_url('/cookie/')); ?>">Cookie</a></li>
      </ul>
    </div>

    <div>
      <h4>Contatti</h4>
      <ul>
        <li><a href="<?php echo esc_url(home_url('/contatti/')); ?>">Form contatti</a></li>
        <li>Carmelo · <a href="tel:+393276999897">+39 327 699 9897</a></li>
        <li>Gianluca · <a href="tel:+393495658876">+39 349 565 8876</a></li>
        <li>Gaetano · <a href="tel:+393513582074">+39 351 358 2074</a></li>
        <li><a href="https://wa.me/393276999897" target="_blank" rel="noopener">WhatsApp</a></li>
      </ul>
    </div>
  </div>

  <div class="container lcgf-footer-bottom">
    <p>&copy; <?php echo date('Y'); ?> La Compagnia del Gluten Free. Tutti i diritti riservati.</p>
    <a href="https://www.emcdigitalsolutions.it" target="_blank" rel="noopener noreferrer" class="emc-credit">
      <span>Progettato e Sviluppato da</span>
      <svg xmlns="http://www.w3.org/2000/svg" width="70" height="20" viewBox="0 0 200 50">
        <defs>
          <linearGradient id="emcBars" x1="0%" y1="0%" x2="100%" y2="0%"><stop offset="0%" stop-color="#7a9a5a"/><stop offset="100%" stop-color="#c9a96e"/></linearGradient>
          <linearGradient id="emcText" x1="0%" y1="0%" x2="100%" y2="0%"><stop offset="0%" stop-color="#5a7a3a"/><stop offset="50%" stop-color="#8a9a5a"/><stop offset="100%" stop-color="#c9a96e"/></linearGradient>
        </defs>
        <rect x="5" y="10" width="30" height="6" rx="2" fill="url(#emcBars)"/>
        <rect x="5" y="22" width="20" height="6" rx="2" fill="url(#emcBars)"/>
        <rect x="5" y="34" width="30" height="6" rx="2" fill="url(#emcBars)"/>
        <text x="48" y="34" font-family="Arial, sans-serif" font-size="20" font-weight="700" letter-spacing="3" fill="url(#emcText)">EMC</text>
      </svg>
    </a>
  </div>
</footer>

<a class="lcgf-wa" href="https://wa.me/393276999897" target="_blank" rel="noopener" aria-label="WhatsApp">
  <svg viewBox="0 0 24 24"><path d="M17.5 14.4c-.3-.1-1.7-.8-2-.9-.3-.1-.5-.2-.7.2-.2.3-.8.9-1 1.1-.2.2-.4.2-.7.1-1.9-.8-3.2-1.8-4.4-3.7-.3-.5.3-.5.9-1.5.1-.2 0-.4-.1-.5-.1-.1-.7-1.6-.9-2.2-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1.1 1.1-1.1 2.6 0 1.6 1.1 3.1 1.3 3.3.2.2 2.3 3.6 5.7 4.9 3.4 1.3 3.4.9 4 .8.6-.1 1.7-.7 2-1.4.2-.7.2-1.3.2-1.4-.1-.1-.2-.2-.5-.3ZM12 2C6.5 2 2 6.5 2 12c0 1.8.5 3.4 1.3 4.9L2 22l5.2-1.4c1.4.8 3 1.2 4.7 1.2 5.5 0 10-4.5 10-10S17.5 2 12 2Z"/></svg>
</a>

<script>
(function(){
  var h = document.getElementById('lcgfHeader');
  if (!h) return;
  function onScroll(){ h.classList.toggle('scrolled', window.scrollY > 12); }
  window.addEventListener('scroll', onScroll, {passive:true});
  onScroll();
})();
</script>

<?php wp_footer(); ?>
</body>
</html>
