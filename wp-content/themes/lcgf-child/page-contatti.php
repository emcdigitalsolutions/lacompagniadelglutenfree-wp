<?php
/**
 * Template Name: Contatti (LCGF)
 */
get_header();
?>

<section class="lcgf-hero" style="padding: 80px 0 60px">
  <div class="container">
    <div style="max-width:760px;position:relative;z-index:1">
      <nav style="font-size:.82rem;color:var(--c-muted);margin-bottom:14px">
        <a href="<?php echo esc_url(home_url('/')); ?>" style="color:var(--c-muted)">Home</a>
        <span style="margin:0 8px;opacity:.5">/</span>
        <span style="color:var(--c-ink)">Contatti</span>
      </nav>
      <span class="eyebrow">Parliamo</span>
      <h1 style="color: var(--c-olive-deep) !important">Scrivici o chiamaci.</h1>
      <p style="font-size:1.1rem;color:var(--c-ink-soft);max-width:580px">Hai una domanda sui prodotti, sulla spedizione o vuoi fare una sorpresa con una gift card? Rispondiamo entro 24h.</p>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div style="display:grid;grid-template-columns:1.3fr 1fr;gap:60px;align-items:start" class="lcgf-contact-grid">
      <div>
        <h3 style="font-size:1.4rem !important;margin-bottom:24px">Scrivici un messaggio</h3>
        <?php
        if (shortcode_exists('wpforms')) {
            // Cerca il primo form WPForms disponibile
            $forms = get_posts(['post_type' => 'wpforms', 'posts_per_page' => 1]);
            if (!empty($forms)) {
                echo do_shortcode('[wpforms id="' . $forms[0]->ID . '"]');
            } else {
                echo '<p style="background:var(--c-cream-2);padding:16px;border-radius:var(--r-md);font-size:.92rem;color:var(--c-muted)">Form contatti in configurazione. Nel frattempo usa WhatsApp o telefono qui a fianco.</p>';
            }
        }
        ?>

        <!-- Fallback form se WPForms non ha form configurato -->
        <?php if (!shortcode_exists('wpforms') || empty(get_posts(['post_type' => 'wpforms', 'posts_per_page' => 1]))): ?>
          <form id="lcgf-contact-form" style="display:grid;gap:16px;margin-top:8px" onsubmit="event.preventDefault();this.innerHTML='<div style=\'padding:40px;text-align:center;background:var(--c-cream-2);border-radius:var(--r-lg)\'><div style=\'width:64px;height:64px;border-radius:50%;background:var(--c-olive);display:grid;place-items:center;margin:0 auto 18px\'><svg width=30 height=30 viewBox=\'0 0 24 24\' fill=none stroke=white stroke-width=3 stroke-linecap=round><polyline points=\'20 6 9 17 4 12\'/></svg></div><h2 style=\'color:var(--c-olive-deep);margin:0\'>Messaggio inviato!</h2><p style=\'color:var(--c-muted);margin-top:8px\'>Ti risponderemo entro 24h.</p></div>';">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
              <label style="display:flex;flex-direction:column;gap:6px;font-size:.85rem;color:var(--c-ink-soft);font-weight:500">
                Nome
                <input type="text" required style="padding:13px 16px;background:#fff;border:1.5px solid var(--c-line);border-radius:var(--r-md);font-size:.95rem;outline:none;transition:border-color .2s">
              </label>
              <label style="display:flex;flex-direction:column;gap:6px;font-size:.85rem;color:var(--c-ink-soft);font-weight:500">
                Email
                <input type="email" required style="padding:13px 16px;background:#fff;border:1.5px solid var(--c-line);border-radius:var(--r-md);font-size:.95rem;outline:none">
              </label>
            </div>
            <label style="display:flex;flex-direction:column;gap:6px;font-size:.85rem;color:var(--c-ink-soft);font-weight:500">
              Oggetto
              <select style="padding:13px 16px;background:#fff;border:1.5px solid var(--c-line);border-radius:var(--r-md);font-size:.95rem;outline:none">
                <option>Domanda su un ordine</option>
                <option>Informazioni su un prodotto</option>
                <option>Spedizione e resi</option>
                <option>Gift card e regali</option>
                <option>Collaborazioni B2B</option>
                <option>Altro</option>
              </select>
            </label>
            <label style="display:flex;flex-direction:column;gap:6px;font-size:.85rem;color:var(--c-ink-soft);font-weight:500">
              Messaggio
              <textarea rows="6" required style="padding:13px 16px;background:#fff;border:1.5px solid var(--c-line);border-radius:var(--r-md);font-size:.95rem;outline:none;font-family:inherit;resize:vertical"></textarea>
            </label>
            <label style="display:flex;gap:10px;align-items:flex-start;font-size:.88rem">
              <input type="checkbox" required style="width:18px;height:18px;accent-color:var(--c-olive);margin-top:2px">
              <span>Acconsento al trattamento dei dati per rispondere alla mia richiesta.</span>
            </label>
            <button type="submit" class="btn btn-lg" style="justify-self:start">
              Invia messaggio
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </button>
          </form>
        <?php endif; ?>
      </div>

      <aside style="background:var(--c-cream-2);border-radius:var(--r-xl);padding:32px;position:sticky;top:calc(var(--header-h) + 20px)">
        <h3 style="font-size:1.2rem !important;margin:0 0 22px">Chiamaci direttamente</h3>

        <?php
        $contacts = [
          ['name' => 'Carmelo',  'role' => 'CEO',         'phone' => '+39 327 699 9897', 'tel' => '393276999897', 'initial' => 'C'],
          ['name' => 'Gianluca', 'role' => 'Co-Founder',  'phone' => '+39 349 565 8876', 'tel' => '393495658876', 'initial' => 'G'],
          ['name' => 'Gaetano',  'role' => 'Co-Founder',  'phone' => '+39 351 358 2074', 'tel' => '393513582074', 'initial' => 'G'],
        ];
        foreach ($contacts as $i => $c) :
          $border = $i < 2 ? 'border-bottom:1px solid var(--c-line)' : '';
        ?>
          <div style="display:flex;gap:14px;align-items:center;padding:16px 0;<?php echo $border; ?>">
            <div style="width:44px;height:44px;border-radius:50%;background:var(--c-olive);color:#fff;display:grid;place-items:center;flex-shrink:0;font-family:var(--f-display);font-weight:700;font-size:1.05rem"><?php echo esc_html($c['initial']); ?></div>
            <div style="flex:1;min-width:0">
              <strong style="display:block;font-size:.95rem"><?php echo esc_html($c['name']); ?></strong>
              <a href="tel:+<?php echo esc_attr($c['tel']); ?>" style="display:block;color:var(--c-olive-deep);font-size:.88rem;font-weight:500;margin-top:2px;text-decoration:none"><?php echo esc_html($c['phone']); ?></a>
              <span style="font-size:.72rem;color:var(--c-muted);text-transform:uppercase;letter-spacing:.08em"><?php echo esc_html($c['role']); ?></span>
            </div>
            <a href="https://wa.me/<?php echo esc_attr($c['tel']); ?>" target="_blank" rel="noopener" aria-label="WhatsApp <?php echo esc_attr($c['name']); ?>" style="width:38px;height:38px;border-radius:50%;background:var(--g-cta);color:var(--c-cream);display:grid;place-items:center;flex-shrink:0;text-decoration:none;box-shadow:0 4px 12px rgba(54,78,37,.25)">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.5 14.4c-.3-.1-1.7-.8-2-.9-.3-.1-.5-.2-.7.2-.2.3-.8.9-1 1.1-.2.2-.4.2-.7.1-1.9-.8-3.2-1.8-4.4-3.7-.3-.5.3-.5.9-1.5.1-.2 0-.4-.1-.5-.1-.1-.7-1.6-.9-2.2-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1.1 1.1-1.1 2.6 0 1.6 1.1 3.1 1.3 3.3.2.2 2.3 3.6 5.7 4.9 3.4 1.3 3.4.9 4 .8.6-.1 1.7-.7 2-1.4.2-.7.2-1.3.2-1.4-.1-.1-.2-.2-.5-.3ZM12 2C6.5 2 2 6.5 2 12c0 1.8.5 3.4 1.3 4.9L2 22l5.2-1.4c1.4.8 3 1.2 4.7 1.2 5.5 0 10-4.5 10-10S17.5 2 12 2Z"/></svg>
            </a>
          </div>
        <?php endforeach; ?>

        <div style="margin-top:18px;padding-top:18px;border-top:1px dashed var(--c-line)">
          <div style="display:flex;gap:14px;align-items:center">
            <div style="width:44px;height:44px;border-radius:50%;background:var(--c-wheat);color:var(--c-ink);display:grid;place-items:center;flex-shrink:0">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            </div>
            <div>
              <strong style="display:block;font-size:.95rem">lacompagniadelglutenfree.it</strong>
              <span style="font-size:.78rem;color:var(--c-muted)">Ordini online 24/7</span>
            </div>
          </div>
        </div>
      </aside>
    </div>
  </div>
</section>

<style>
@media (max-width: 880px) {
  .lcgf-contact-grid { grid-template-columns: 1fr !important; }
}
</style>

<?php get_footer();
