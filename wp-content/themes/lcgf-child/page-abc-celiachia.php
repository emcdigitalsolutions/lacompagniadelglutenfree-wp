<?php
/**
 * Template Name: ABC dieta senza glutine
 * Pagina divulgativa originale sull'ABC della dieta del celiaco. Contenuti
 * scritti da noi (NON copiati); fonte autorevole citata: AIC (celiachia.it).
 * Accordion nativo <details> = accessibile, leggero, non invasivo.
 */
defined('ABSPATH') || exit;
get_header('shop');
?>
<section class="section" style="padding:48px 0 90px">
  <div class="container" style="max-width:860px">

    <div class="lcgf-section-head" style="text-align:left">
      <span class="eyebrow">Guida pratica</span>
      <h2>L'ABC della dieta senza glutine</h2>
      <p style="color:var(--c-ink-soft);font-size:1.05rem;line-height:1.65;max-width:680px">
        Poche nozioni chiare per vivere serenamente la celiachia, ogni giorno. Una piccola guida
        per chi inizia e per i genitori dei bambini celiaci. Per approfondimenti completi e sempre
        aggiornati fai riferimento all'<strong>Associazione Italiana Celiachia (AIC)</strong>.
      </p>
    </div>

    <div class="lcgf-acc" style="margin-top:34px;display:flex;flex-direction:column;gap:12px">

      <details class="lcgf-acc-item" open>
        <summary>Le tre categorie di alimenti</summary>
        <div class="lcgf-acc-body">
          <p>Per orientarsi, gli alimenti si dividono in tre gruppi:</p>
          <ul>
            <li><strong>Permessi</strong> — naturalmente privi di glutine: riso, mais, patate, legumi, carne, pesce, uova, frutta, verdura, latte e formaggi naturali.</li>
            <li><strong>Vietati</strong> — contengono glutine: frumento, orzo, segale, farro, kamut, spelta, triticale e tutti i loro derivati (pane, pasta, pizza, dolci comuni).</li>
            <li><strong>A rischio</strong> — potrebbero contenere glutine per ingredienti o lavorazione (alcuni salumi, salse, preparati, avena non certificata): vanno sempre verificati.</li>
          </ul>
          <p>I nostri prodotti appartengono alla categoria dei <strong>sostitutivi senza glutine</strong>: pane, basi pizza, focacce e dolci pensati per sostituire in sicurezza quelli vietati.</p>
        </div>
      </details>

      <details class="lcgf-acc-item">
        <summary>La contaminazione crociata</summary>
        <div class="lcgf-acc-body">
          <p>Per chi è celiaco anche piccole tracce di glutine sono dannose. La contaminazione avviene
          quando un alimento sicuro entra in contatto con uno che contiene glutine: stesse superfici,
          utensili, friggitrici o acqua di cottura condivisi.</p>
          <p>Per questo il nostro <strong>laboratorio è esclusivamente senza glutine</strong>: nessuna
          lavorazione con farine contenenti glutine, quindi nessun rischio di contaminazione crociata.
          A casa e fuori, usa utensili puliti e dedicati.</p>
        </div>
      </details>

      <details class="lcgf-acc-item">
        <summary>Come leggere le etichette</summary>
        <div class="lcgf-acc-body">
          <p>Controlla sempre l'elenco ingredienti e la sezione allergeni: il glutine e i cereali che
          lo contengono vanno evidenziati per legge. Diffida dei prodotti senza etichetta chiara e,
          nel dubbio, scegli prodotti pensati e certificati per i celiaci.</p>
        </div>
      </details>

      <details class="lcgf-acc-item">
        <summary>Prodotti mutuabili e buono celiachia</summary>
        <div class="lcgf-acc-body">
          <p>Le persone con diagnosi di celiachia hanno diritto a un <strong>contributo mensile</strong>
          del Servizio Sanitario Nazionale per l'acquisto di prodotti sostitutivi senza glutine,
          registrati nell'apposito Registro del <strong>Ministero della Salute</strong>. Il buono si
          utilizza nelle farmacie e nei punti vendita accreditati.</p>
          <p>I nostri <strong>prodotti mutuabili</strong> rientrano tra quelli erogabili: chiedici quali
          e come utilizzare il tuo buono.</p>
        </div>
      </details>

      <details class="lcgf-acc-item">
        <summary>Gli strumenti utili di AIC</summary>
        <div class="lcgf-acc-body">
          <p>L'<strong>Associazione Italiana Celiachia</strong> mette a disposizione strumenti preziosi:
          il <em>Prontuario degli Alimenti</em>, l'app per consultarlo, e il marchio della spiga barrata
          che identifica i prodotti idonei. Noi siamo <strong>accreditati AIC</strong>.</p>
          <p style="margin-top:10px">
            <a href="https://www.celiachia.it/dieta-senza-glutine/labc-della-dieta-del-celiaco/" target="_blank" rel="noopener" style="color:var(--c-olive-deep);font-weight:600">
              Approfondisci sul sito ufficiale di AIC →
            </a>
          </p>
        </div>
      </details>

    </div>

    <?php
    // Eventuale contenuto aggiuntivo inserito dall'admin nella pagina.
    while (have_posts()) : the_post();
      $extra = trim(get_the_content());
      if ($extra) { echo '<div style="margin-top:34px;line-height:1.7;color:var(--c-ink-soft)">' . apply_filters('the_content', $extra) . '</div>'; }
    endwhile;
    ?>

    <p style="margin-top:40px;font-size:.84rem;color:var(--c-muted);border-top:1px solid var(--c-line);padding-top:18px">
      Contenuto a scopo divulgativo, non sostituisce le indicazioni del medico o di AIC.
      Fonte autorevole: <a href="https://www.celiachia.it/" target="_blank" rel="noopener" style="color:var(--c-muted);text-decoration:underline">Associazione Italiana Celiachia — celiachia.it</a>.
    </p>

  </div>
</section>

<style>
.lcgf-acc-item{border:1px solid var(--c-line);border-radius:var(--r-lg);background:var(--c-white);overflow:hidden;box-shadow:var(--sh-1)}
.lcgf-acc-item summary{cursor:pointer;list-style:none;padding:18px 24px;font-family:var(--f-display);font-size:1.12rem;color:var(--c-olive-deep);display:flex;align-items:center;justify-content:space-between;gap:14px}
.lcgf-acc-item summary::-webkit-details-marker{display:none}
.lcgf-acc-item summary::after{content:"+";font-size:1.5rem;color:var(--c-wheat-dark);transition:transform .2s ease;line-height:1}
.lcgf-acc-item[open] summary::after{content:"\2212"}
.lcgf-acc-item summary:hover{background:var(--c-cream)}
.lcgf-acc-body{padding:2px 24px 22px;color:var(--c-ink-soft);line-height:1.7}
.lcgf-acc-body ul{margin:10px 0 12px;padding-left:18px}
.lcgf-acc-body li{margin:7px 0}
</style>
<?php
get_footer('shop');
