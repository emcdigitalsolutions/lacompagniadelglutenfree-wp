<?php
/**
 * Import "Novità" — Nuovo catalogo per i rivenditori (27/11/2026)
 * Crea 1 voce lcgf_evento di tipo NOVITÀ (senza data evento) in it/en/de/fr.
 * USO (sul server, nella cartella con questo file + 2026-11-catalogo-rivenditori.jpg):
 *   wp eval-file import-evento-catalogo-rivenditori.php --path=~/public_html
 * Idempotente (meta _lcgf_import_key).
 */
if (!defined('ABSPATH')) { fwrite(STDERR, "Eseguire con: wp eval-file\n"); exit(1); }
if (!function_exists('pll_set_post_language')) { fwrite(STDERR, "Polylang non attivo.\n"); exit(1); }
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$KEY = 'catalogo-rivenditori-2026';
$IMG = __DIR__ . '/2026-11-catalogo-rivenditori.jpg';

$T = [
  'it' => [
    'title' => 'Nuovo catalogo per i rivenditori: prezzi a cartoni',
    'slug'  => 'nuovo-catalogo-rivenditori',
    'excerpt' => 'Da oggi bar, ristoranti, pizzerie, negozi e farmacie possono acquistare i nostri prodotti senza glutine e senza lattosio a cartoni, con prezzi a scaglioni e spedizione gratuita.',
    'lead' => 'È online il <strong>nuovo catalogo dedicato ai rivenditori</strong>.',
    'body' => 'Bar, ristoranti, pizzerie, negozi e farmacie possono ora acquistare direttamente dal sito i nostri prodotti <strong>100% senza glutine e senza lattosio</strong> in <strong>cartoni</strong>: base pizza, pinsa romana, pane rosetta, pane filoncino, focaccia rotonda, cornetto e molto altro.',
    'list' => ['<strong>Prezzi a scaglioni</strong>: più cartoni ordini, meno paghi (1–3, 4–6, 7–9 cartoni).', '<strong>Spedizione gratuita</strong> sugli ordini a cartoni.', 'Acquisto semplice online: scegli i cartoni, aggiungi al carrello e indica la tua Partita IVA al pagamento.', 'Servono 10 o più cartoni o un assortimento su misura? Scrivici: ti prepariamo un preventivo dedicato.'],
    'cta' => 'Vai al catalogo rivenditori',
    'seo_title' => 'Nuovo catalogo rivenditori | Prodotti senza glutine a cartoni',
    'seo_desc' => 'Online il catalogo per i rivenditori: prodotti senza glutine e senza lattosio a cartoni, prezzi a scaglioni e spedizione gratuita. Per bar, ristoranti, pizzerie e negozi.',
    'focuskw' => 'catalogo rivenditori senza glutine',
  ],
  'en' => [
    'title' => 'New reseller catalog: prices by the carton',
    'slug'  => 'new-reseller-catalog',
    'excerpt' => 'Bars, restaurants, pizzerias, shops and pharmacies can now buy our gluten-free and lactose-free products by the carton, with tiered prices and free shipping.',
    'lead' => 'The <strong>new catalog for resellers</strong> is now online.',
    'body' => 'Bars, restaurants, pizzerias, shops and pharmacies can now buy our <strong>100% gluten-free and lactose-free</strong> products directly on the website by the <strong>carton</strong>: pizza bases, Roman pinsa, rosetta rolls, filoncino bread, round focaccia, croissants and much more.',
    'list' => ['<strong>Tiered prices</strong>: the more cartons you order, the less you pay (1–3, 4–6, 7–9 cartons).', '<strong>Free shipping</strong> on carton orders.', 'Easy online ordering: choose the cartons, add them to the cart and enter your VAT number at checkout.', 'Need 10 or more cartons or a custom assortment? Write to us: we will prepare a dedicated quote.'],
    'cta' => 'Go to the reseller catalog',
    'seo_title' => 'New reseller catalog | Gluten-free products by the carton',
    'seo_desc' => 'The reseller catalog is online: gluten-free and lactose-free products by the carton, tiered prices and free shipping. For bars, restaurants, pizzerias and shops.',
    'focuskw' => 'gluten-free reseller catalog',
  ],
  'de' => [
    'title' => 'Neuer Händlerkatalog: Preise pro Karton',
    'slug'  => 'neuer-haendlerkatalog',
    'excerpt' => 'Bars, Restaurants, Pizzerien, Geschäfte und Apotheken können unsere gluten- und laktosefreien Produkte jetzt im Karton kaufen – mit Staffelpreisen und kostenlosem Versand.',
    'lead' => 'Der <strong>neue Katalog für Händler</strong> ist online.',
    'body' => 'Bars, Restaurants, Pizzerien, Geschäfte und Apotheken können unsere <strong>100 % glutenfreien und laktosefreien</strong> Produkte jetzt direkt auf der Website im <strong>Karton</strong> kaufen: Pizzaboden, Pinsa Romana, Rosetta-Brötchen, Filoncino-Brot, runde Focaccia, Croissants und vieles mehr.',
    'list' => ['<strong>Staffelpreise</strong>: Je mehr Kartons, desto günstiger (1–3, 4–6, 7–9 Kartons).', '<strong>Kostenloser Versand</strong> bei Karton-Bestellungen.', 'Einfach online bestellen: Kartons wählen, in den Warenkorb legen und an der Kasse die USt-IdNr. angeben.', 'Sie brauchen 10 oder mehr Kartons oder ein individuelles Sortiment? Schreiben Sie uns: Wir erstellen Ihnen ein individuelles Angebot.'],
    'cta' => 'Zum Händlerkatalog',
    'seo_title' => 'Neuer Händlerkatalog | Glutenfreie Produkte im Karton',
    'seo_desc' => 'Der Händlerkatalog ist online: glutenfreie und laktosefreie Produkte im Karton, Staffelpreise und kostenloser Versand. Für Bars, Restaurants, Pizzerien und Geschäfte.',
    'focuskw' => 'glutenfreier Händlerkatalog',
  ],
  'fr' => [
    'title' => 'Nouveau catalogue revendeurs : prix au carton',
    'slug'  => 'nouveau-catalogue-revendeurs',
    'excerpt' => 'Bars, restaurants, pizzerias, commerces et pharmacies peuvent désormais acheter nos produits sans gluten et sans lactose au carton, avec prix dégressifs et livraison gratuite.',
    'lead' => 'Le <strong>nouveau catalogue dédié aux revendeurs</strong> est en ligne.',
    'body' => 'Bars, restaurants, pizzerias, commerces et pharmacies peuvent désormais acheter directement sur le site nos produits <strong>100 % sans gluten et sans lactose</strong> au <strong>carton</strong> : bases de pizza, pinsa romana, pain rosetta, pain filoncino, focaccia ronde, croissants et bien plus encore.',
    'list' => ['<strong>Prix dégressifs</strong> : plus vous commandez de cartons, moins vous payez (1–3, 4–6, 7–9 cartons).', '<strong>Livraison gratuite</strong> sur les commandes au carton.', 'Commande en ligne simple : choisissez les cartons, ajoutez-les au panier et indiquez votre numéro de TVA au paiement.', 'Besoin de 10 cartons ou plus, ou d’un assortiment sur mesure ? Écrivez-nous : nous vous préparerons un devis dédié.'],
    'cta' => 'Voir le catalogue revendeurs',
    'seo_title' => 'Nouveau catalogue revendeurs | Produits sans gluten au carton',
    'seo_desc' => 'Le catalogue revendeurs est en ligne : produits sans gluten et sans lactose au carton, prix dégressifs et livraison gratuite. Pour bars, restaurants, pizzerias et commerces.',
    'focuskw' => 'catalogue revendeurs sans gluten',
  ],
];

echo "\n===== IMPORT NOVITÀ CATALOGO RIVENDITORI =====\n";
$ex = get_posts(['post_type' => 'lcgf_evento', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_lcgf_import_key', 'meta_value' => $KEY, 'lang' => '']);
if ($ex) { echo "= già presente (ID " . implode(',', $ex) . ") → skip\n"; return; }

$att = 0;
if (file_exists($IMG)) {
  $tmp = wp_tempnam(basename($IMG)); copy($IMG, $tmp);
  $r = media_handle_sideload(['name' => basename($IMG), 'tmp_name' => $tmp], 0, $T['it']['title']);
  if (is_wp_error($r)) { @unlink($tmp); echo "! sideload: " . $r->get_error_message() . "\n"; } else { $att = (int) $r; @pll_set_post_language($att, 'it'); }
} else echo "! immagine mancante\n";

$ids = [];
foreach (['it', 'en', 'de', 'fr'] as $lang) {
  $t = $T[$lang];
  $rv = '';
  $pg = get_posts(['post_type' => 'page', 'post_status' => 'publish', 'numberposts' => 1, 'meta_key' => '_lcgf_rv_page', 'meta_value' => '1', 'lang' => $lang]);
  if ($pg) $rv = get_permalink($pg[0]);
  $content = '<p>' . $t['lead'] . '</p><p>' . $t['body'] . '</p><ul>';
  foreach ($t['list'] as $li) $content .= '<li>' . $li . '</li>';
  $content .= '</ul>';
  if ($rv) $content .= '<p><a class="btn" href="' . esc_url($rv) . '">' . esc_html($t['cta']) . '</a></p>';

  $id = wp_insert_post(['post_type' => 'lcgf_evento', 'post_status' => 'publish', 'post_title' => $t['title'], 'post_name' => $t['slug'], 'post_content' => $content, 'post_excerpt' => $t['excerpt']], true);
  if (is_wp_error($id) || !$id) { echo "! [$lang] errore\n"; continue; }
  update_post_meta($id, '_lcgf_import_key', $KEY);
  if ($att) set_post_thumbnail($id, $att);
  update_post_meta($id, '_yoast_wpseo_title', $t['seo_title']);
  update_post_meta($id, '_yoast_wpseo_metadesc', $t['seo_desc']);
  update_post_meta($id, '_yoast_wpseo_focuskw', $t['focuskw']);
  pll_set_post_language($id, $lang);
  $ids[$lang] = $id;
  echo "+ [$lang] ID $id — {$t['title']}\n";
}
if ($ids) { pll_save_post_translations($ids); echo "↔ " . json_encode($ids) . "\n"; }
if (function_exists('wp_cache_flush')) wp_cache_flush();
echo "FATTO\n";
