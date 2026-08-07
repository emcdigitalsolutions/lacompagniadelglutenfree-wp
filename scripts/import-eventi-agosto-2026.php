<?php
/**
 * Import eventi "Novità & Eventi" — Agosto 2026 (La Compagnia del Gluten Free)
 * ---------------------------------------------------------------------------
 * Crea 3 eventi (CPT lcgf_evento) nelle 4 lingue Polylang (it/en/de/fr),
 * con immagine in evidenza (flyer), meta evento, dati geo, Yoast SEO.
 * LCGF partecipa con stand/prodotti senza glutine e senza lattosio.
 *
 * USO (sul server, dalla cartella che contiene questo file + i 3 .jpg):
 *   wp eval-file import-eventi-agosto-2026.php --path=~/public_html
 *
 * Idempotente: se un evento (chiave) esiste già (meta _lcgf_import_key), lo salta.
 * Le immagini attese nella stessa cartella dello script:
 *   2026-08-12.jpg  2026-08-13.jpg  2026-08-20-21.jpg
 */

if (!defined('ABSPATH')) { fwrite(STDERR, "Eseguire con: wp eval-file\n"); exit(1); }
if (!function_exists('pll_set_post_language')) { fwrite(STDERR, "Polylang non attivo.\n"); exit(1); }

require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$IMG_DIR = __DIR__;
$LANGS   = ['it', 'en', 'de', 'fr'];

/* ---------------------------------------------------------------------------
 * DATI EVENTI
 * ------------------------------------------------------------------------- */
$EVENTS = [

  // ===================== 1) FUATA FEST =====================
  'fuata-fest-2026' => [
    'image'    => '2026-08-12.jpg',
    'meta' => [
      'data_inizio' => '2026-08-12',
      'data_fine'   => '',
      'ora_inizio'  => '20:30',
      'luogo'       => 'Corso della Repubblica',
      'indirizzo'   => 'Corso della Repubblica',
      'citta'       => 'Ravanusa (AG)',
      'link'        => '',
      'lat'         => '37.26860',
      'lng'         => '13.97090',
      'cap'         => '92029',
      'nazione'     => 'IT',
      'organizer'   => 'Forni del Saraceno — Comune di Ravanusa',
    ],
    'prezzo' => ['it' => 'Ingresso libero', 'en' => 'Free entry', 'de' => 'Freier Eintritt', 'fr' => 'Entrée libre'],
    'i18n' => [
      'it' => [
        'title'   => 'Fuata Fest 2026 — 4ª Edizione a Ravanusa',
        'slug'    => 'fuata-fest-2026-ravanusa',
        'excerpt' => 'Il 12 agosto torna la Fuata Fest a Ravanusa: fuata, musica live, pizzaioli acrobatici e area food. La Compagnia del Gluten Free è presente con pane, pizza e dolci senza glutine e senza lattosio.',
        'content' => '<p>Mercoledì <strong>12 agosto 2026</strong> torna a <strong>Ravanusa</strong> la <strong>Fuata Fest</strong>, giunta alla sua <strong>4ª edizione</strong>. Appuntamento in <strong>Corso della Repubblica</strong> a partire dalle <strong>ore 20:30</strong>, con l\'organizzazione dei Forni del Saraceno e il patrocinio del Comune di Ravanusa.</p>'
                   . '<p>Una serata di festa per tutta la famiglia: la tradizionale <strong>fuata</strong>, <strong>musica live</strong>, spettacolo dei <strong>pizzaioli acrobatici</strong>, un\'ampia <strong>area food attrezzata</strong> e, per i più piccoli, <strong>gonfiabili e personaggi Disney</strong>.</p>'
                   . '<p><strong>Ci trovi al nostro stand.</strong> La Compagnia del Gluten Free porta alla Fuata Fest le sue specialità <strong>100% senza glutine e senza lattosio</strong>: pane, pizza, focacce e dolci pensati anche per chi è celiaco o intollerante, senza rinunciare al gusto. Vieni ad assaggiare le nostre proposte gluten free e vivi la festa senza pensieri.</p>',
        'seo_title' => 'Fuata Fest 2026 a Ravanusa (12 agosto) | Stand Senza Glutine',
        'seo_desc'  => 'La Compagnia del Gluten Free alla Fuata Fest 2026 di Ravanusa, 12 agosto in Corso della Repubblica: pane, pizza e dolci senza glutine e senza lattosio allo stand.',
        'focuskw'   => 'Fuata Fest Ravanusa',
      ],
      'en' => [
        'title'   => 'Fuata Fest 2026 — 4th Edition in Ravanusa',
        'slug'    => 'fuata-fest-2026-ravanusa',
        'excerpt' => 'On 12 August the Fuata Fest returns to Ravanusa: fuata bread, live music, acrobatic pizza makers and a food area. La Compagnia del Gluten Free will be there with gluten-free, lactose-free bread, pizza and desserts.',
        'content' => '<p>On Wednesday <strong>12 August 2026</strong> the <strong>Fuata Fest</strong> returns to <strong>Ravanusa</strong> for its <strong>4th edition</strong>. Join us in <strong>Corso della Repubblica</strong> from <strong>8:30 PM</strong>, organised by Forni del Saraceno with the patronage of the Municipality of Ravanusa.</p>'
                   . '<p>An evening of celebration for the whole family: the traditional <strong>fuata</strong> flatbread, <strong>live music</strong>, a show by <strong>acrobatic pizza makers</strong>, a large <strong>food area</strong> and, for children, <strong>bouncy castles and Disney characters</strong>.</p>'
                   . '<p><strong>Come and find us at our stand.</strong> La Compagnia del Gluten Free brings its <strong>100% gluten-free and lactose-free</strong> specialities to the Fuata Fest: bread, pizza, focaccia and desserts made for those with coeliac disease or intolerances too, with no compromise on taste. Come and taste our gluten-free treats and enjoy the festival with peace of mind.</p>',
        'seo_title' => 'Fuata Fest 2026 in Ravanusa (12 August) | Gluten-Free Stand',
        'seo_desc'  => 'La Compagnia del Gluten Free at the Fuata Fest 2026 in Ravanusa, 12 August in Corso della Repubblica: gluten-free and lactose-free bread, pizza and desserts at our stand.',
        'focuskw'   => 'Fuata Fest Ravanusa',
      ],
      'de' => [
        'title'   => 'Fuata Fest 2026 — 4. Ausgabe in Ravanusa',
        'slug'    => 'fuata-fest-2026-ravanusa',
        'excerpt' => 'Am 12. August kehrt das Fuata Fest nach Ravanusa zurück: Fuata, Live-Musik, akrobatische Pizzabäcker und ein Food-Bereich. La Compagnia del Gluten Free ist mit glutenfreiem und laktosefreiem Brot, Pizza und Süßem dabei.',
        'content' => '<p>Am Mittwoch, den <strong>12. August 2026</strong>, kehrt das <strong>Fuata Fest</strong> in seiner <strong>4. Ausgabe</strong> nach <strong>Ravanusa</strong> zurück. Treffpunkt ist der <strong>Corso della Repubblica</strong> ab <strong>20:30 Uhr</strong>, organisiert von Forni del Saraceno unter der Schirmherrschaft der Gemeinde Ravanusa.</p>'
                   . '<p>Ein Festabend für die ganze Familie: das traditionelle <strong>Fuata</strong>-Fladenbrot, <strong>Live-Musik</strong>, eine Show der <strong>akrobatischen Pizzabäcker</strong>, ein großer <strong>Food-Bereich</strong> und für die Kleinen <strong>Hüpfburgen und Disney-Figuren</strong>.</p>'
                   . '<p><strong>Besuche uns an unserem Stand.</strong> La Compagnia del Gluten Free bringt ihre <strong>100 % glutenfreien und laktosefreien</strong> Spezialitäten zum Fuata Fest: Brot, Pizza, Focaccia und Süßes – auch für Menschen mit Zöliakie oder Unverträglichkeiten, ohne Kompromisse beim Geschmack. Probiere unsere glutenfreien Köstlichkeiten und genieße das Fest ganz unbeschwert.</p>',
        'seo_title' => 'Fuata Fest 2026 in Ravanusa (12. August) | Glutenfreier Stand',
        'seo_desc'  => 'La Compagnia del Gluten Free beim Fuata Fest 2026 in Ravanusa, 12. August am Corso della Repubblica: glutenfreies und laktosefreies Brot, Pizza und Süßes am Stand.',
        'focuskw'   => 'Fuata Fest Ravanusa',
      ],
      'fr' => [
        'title'   => 'Fuata Fest 2026 — 4ᵉ édition à Ravanusa',
        'slug'    => 'fuata-fest-2026-ravanusa',
        'excerpt' => 'Le 12 août, la Fuata Fest revient à Ravanusa : fuata, musique live, pizzaïolos acrobates et espace food. La Compagnia del Gluten Free est présente avec pain, pizza et desserts sans gluten et sans lactose.',
        'content' => '<p>Le mercredi <strong>12 août 2026</strong>, la <strong>Fuata Fest</strong> revient à <strong>Ravanusa</strong> pour sa <strong>4ᵉ édition</strong>. Rendez-vous <strong>Corso della Repubblica</strong> à partir de <strong>20h30</strong>, organisée par les Forni del Saraceno avec le patronage de la commune de Ravanusa.</p>'
                   . '<p>Une soirée de fête pour toute la famille : la <strong>fuata</strong> traditionnelle, de la <strong>musique live</strong>, un spectacle de <strong>pizzaïolos acrobates</strong>, un vaste <strong>espace food</strong> et, pour les enfants, des <strong>structures gonflables et des personnages Disney</strong>.</p>'
                   . '<p><strong>Retrouvez-nous sur notre stand.</strong> La Compagnia del Gluten Free apporte à la Fuata Fest ses spécialités <strong>100 % sans gluten et sans lactose</strong> : pain, pizza, focaccia et desserts pensés aussi pour les personnes cœliaques ou intolérantes, sans renoncer au goût. Venez déguster nos produits sans gluten et profitez de la fête l\'esprit tranquille.</p>',
        'seo_title' => 'Fuata Fest 2026 à Ravanusa (12 août) | Stand Sans Gluten',
        'seo_desc'  => 'La Compagnia del Gluten Free à la Fuata Fest 2026 de Ravanusa, 12 août Corso della Repubblica : pain, pizza et desserts sans gluten et sans lactose au stand.',
        'focuskw'   => 'Fuata Fest Ravanusa',
      ],
    ],
  ],

  // ===================== 2) NAPOLI INCONTRA RAVANUSA =====================
  'napoli-incontra-ravanusa-2026' => [
    'image'    => '2026-08-13.jpg',
    'meta' => [
      'data_inizio' => '2026-08-13',
      'data_fine'   => '',
      'ora_inizio'  => '',
      'luogo'       => 'Piazza XXV Aprile',
      'indirizzo'   => 'Piazza XXV Aprile',
      'citta'       => 'Ravanusa (AG)',
      'link'        => '',
      'lat'         => '37.26990',
      'lng'         => '13.96860',
      'cap'         => '92029',
      'nazione'     => 'IT',
      'organizer'   => 'Pizzeria "A Regina"',
    ],
    'prezzo' => ['it' => 'Ingresso libero', 'en' => 'Free entry', 'de' => 'Freier Eintritt', 'fr' => 'Entrée libre'],
    'i18n' => [
      'it' => [
        'title'   => 'Napoli incontra Ravanusa — Grande Serata (13 agosto)',
        'slug'    => 'napoli-incontra-ravanusa-2026',
        'excerpt' => 'Il 13 agosto in Piazza XXV Aprile a Ravanusa le specialità napoletane incontrano quelle siciliane. La Compagnia del Gluten Free porta pizza e sfizi senza glutine, per tutti.',
        'content' => '<p>Giovedì <strong>13 agosto 2026</strong>, in <strong>Piazza XXV Aprile</strong> a <strong>Ravanusa</strong>, va in scena la grande serata <strong>&laquo;Napoli incontra Ravanusa&raquo;</strong>: un incontro di gusto in cui le <strong>specialità napoletane</strong> — con la Pizzeria &laquo;A Regina&raquo; — dialogano con le <strong>specialità siciliane</strong>, tra musica, tradizione e convivialità.</p>'
                   . '<p>Una festa dedicata alla buona cucina del Sud, dove due grandi tradizioni gastronomiche si incontrano in piazza.</p>'
                   . '<p><strong>Vieni a trovarci al nostro stand.</strong> La Compagnia del Gluten Free è presente con <strong>pizza e sfizi senza glutine e senza lattosio</strong>: così anche chi è celiaco o intollerante può gustare i sapori della serata in totale sicurezza e con lo stesso piacere di sempre.</p>',
        'seo_title' => 'Napoli incontra Ravanusa 2026 (13 agosto) | Pizza Senza Glutine',
        'seo_desc'  => '13 agosto 2026, Piazza XXV Aprile a Ravanusa: le specialità napoletane incontrano le siciliane. La Compagnia del Gluten Free presente con pizza e dolci senza glutine.',
        'focuskw'   => 'Napoli incontra Ravanusa',
      ],
      'en' => [
        'title'   => 'Naples Meets Ravanusa — Grand Evening (13 August)',
        'slug'    => 'naples-meets-ravanusa-2026',
        'excerpt' => 'On 13 August in Piazza XXV Aprile in Ravanusa, Neapolitan specialities meet Sicilian ones. La Compagnia del Gluten Free brings gluten-free pizza and treats, for everyone.',
        'content' => '<p>On Thursday <strong>13 August 2026</strong>, in <strong>Piazza XXV Aprile</strong> in <strong>Ravanusa</strong>, the grand evening <strong>&ldquo;Naples Meets Ravanusa&rdquo;</strong> takes place: a meeting of flavours where <strong>Neapolitan specialities</strong> — with Pizzeria &ldquo;A Regina&rdquo; — come together with <strong>Sicilian specialities</strong>, amid music, tradition and conviviality.</p>'
                   . '<p>A celebration of great Southern Italian cuisine, where two grand culinary traditions meet in the square.</p>'
                   . '<p><strong>Come and find us at our stand.</strong> La Compagnia del Gluten Free will be there with <strong>gluten-free and lactose-free pizza and treats</strong>: so people with coeliac disease or intolerances can enjoy the flavours of the evening in complete safety and with the same pleasure as ever.</p>',
        'seo_title' => 'Naples Meets Ravanusa 2026 (13 August) | Gluten-Free Pizza',
        'seo_desc'  => '13 August 2026, Piazza XXV Aprile in Ravanusa: Neapolitan specialities meet Sicilian ones. La Compagnia del Gluten Free with gluten-free pizza and desserts.',
        'focuskw'   => 'Naples meets Ravanusa',
      ],
      'de' => [
        'title'   => 'Neapel trifft Ravanusa — Großer Abend (13. August)',
        'slug'    => 'neapel-trifft-ravanusa-2026',
        'excerpt' => 'Am 13. August treffen auf der Piazza XXV Aprile in Ravanusa neapolitanische Spezialitäten auf sizilianische. La Compagnia del Gluten Free bringt glutenfreie Pizza und Leckereien – für alle.',
        'content' => '<p>Am Donnerstag, den <strong>13. August 2026</strong>, findet auf der <strong>Piazza XXV Aprile</strong> in <strong>Ravanusa</strong> der große Abend <strong>&bdquo;Neapel trifft Ravanusa&ldquo;</strong> statt: ein Treffen der Genüsse, bei dem <strong>neapolitanische Spezialitäten</strong> — mit der Pizzeria &bdquo;A Regina&ldquo; — auf <strong>sizilianische Spezialitäten</strong> treffen, begleitet von Musik, Tradition und Geselligkeit.</p>'
                   . '<p>Ein Fest der guten süditalienischen Küche, bei dem zwei große kulinarische Traditionen auf dem Platz zusammenkommen.</p>'
                   . '<p><strong>Besuche uns an unserem Stand.</strong> La Compagnia del Gluten Free ist mit <strong>glutenfreier und laktosefreier Pizza und Leckereien</strong> dabei: So können auch Menschen mit Zöliakie oder Unverträglichkeiten die Aromen des Abends ganz sicher und mit demselben Genuss wie immer erleben.</p>',
        'seo_title' => 'Neapel trifft Ravanusa 2026 (13. August) | Glutenfreie Pizza',
        'seo_desc'  => '13. August 2026, Piazza XXV Aprile in Ravanusa: neapolitanische Spezialitäten treffen auf sizilianische. La Compagnia del Gluten Free mit glutenfreier Pizza und Süßem.',
        'focuskw'   => 'Neapel trifft Ravanusa',
      ],
      'fr' => [
        'title'   => 'Naples rencontre Ravanusa — Grande soirée (13 août)',
        'slug'    => 'naples-rencontre-ravanusa-2026',
        'excerpt' => 'Le 13 août, Piazza XXV Aprile à Ravanusa, les spécialités napolitaines rencontrent les siciliennes. La Compagnia del Gluten Free apporte pizza et gourmandises sans gluten, pour tous.',
        'content' => '<p>Le jeudi <strong>13 août 2026</strong>, sur la <strong>Piazza XXV Aprile</strong> à <strong>Ravanusa</strong>, se tient la grande soirée <strong>&laquo;&nbsp;Naples rencontre Ravanusa&nbsp;&raquo;</strong> : une rencontre de saveurs où les <strong>spécialités napolitaines</strong> — avec la Pizzeria &laquo;&nbsp;A Regina&nbsp;&raquo; — dialoguent avec les <strong>spécialités siciliennes</strong>, entre musique, tradition et convivialité.</p>'
                   . '<p>Une fête dédiée à la bonne cuisine du Sud, où deux grandes traditions gastronomiques se rencontrent sur la place.</p>'
                   . '<p><strong>Retrouvez-nous sur notre stand.</strong> La Compagnia del Gluten Free est présente avec <strong>pizza et gourmandises sans gluten et sans lactose</strong> : ainsi, les personnes cœliaques ou intolérantes peuvent savourer les saveurs de la soirée en toute sécurité et avec le même plaisir que toujours.</p>',
        'seo_title' => 'Naples rencontre Ravanusa 2026 (13 août) | Pizza Sans Gluten',
        'seo_desc'  => '13 août 2026, Piazza XXV Aprile à Ravanusa : les spécialités napolitaines rencontrent les siciliennes. La Compagnia del Gluten Free avec pizza et desserts sans gluten.',
        'focuskw'   => 'Naples rencontre Ravanusa',
      ],
    ],
  ],

  // ===================== 3) ST. JULIAN'S INTERNATIONAL PIZZA FESTIVAL =====================
  'st-julians-pizza-festival-2026' => [
    'image'    => '2026-08-20-21.jpg',
    'meta' => [
      'data_inizio' => '2026-08-20',
      'data_fine'   => '2026-08-21',
      'ora_inizio'  => '17:00',
      'luogo'       => 'Love Monument, Spinola Bay',
      'indirizzo'   => 'Xatt is-Sajjieda',
      'citta'       => "St. Julian's (Malta)",
      'link'        => '',
      'lat'         => '35.91860',
      'lng'         => '14.48940',
      'cap'         => 'STJ',
      'nazione'     => 'MT',
      'organizer'   => 'Kunsill Lokali San Ġiljan — Paceville Purple Flag',
    ],
    'prezzo' => ['it' => 'Ingresso libero · dalle 17:00', 'en' => 'Free entry · from 5 PM', 'de' => 'Freier Eintritt · ab 17 Uhr', 'fr' => 'Entrée libre · dès 17h'],
    'i18n' => [
      'it' => [
        'title'   => "St. Julian's International Pizza Festival 2026 (Malta)",
        'slug'    => 'st-julians-international-pizza-festival-2026',
        'excerpt' => 'Il 20 e 21 agosto La Compagnia del Gluten Free vola a Malta per lo St. Julian\'s International Pizza Festival: pizza senza glutine sul lungomare di Spinola Bay.',
        'content' => '<p>Il <strong>20 e 21 agosto 2026</strong>, dalle <strong>ore 17:00</strong>, il lungomare di <strong>Spinola Bay</strong> a <strong>St. Julian\'s (Malta)</strong> ospita lo <strong>St. Julian\'s International Pizza Festival</strong>, nei pressi del celebre <strong>Love Monument</strong> in Xatt is-Sajjieda.</p>'
                   . '<p>Due giorni di festa internazionale dedicata alla pizza, con maestri pizzaioli da tutto il mondo, gusto e spettacolo affacciati sul mare.</p>'
                   . '<p><strong>Ci saremo anche noi.</strong> La Compagnia del Gluten Free partecipa con la sua <strong>pizza senza glutine e senza lattosio</strong>, portando l\'eccellenza artigianale siciliana gluten free fino a Malta. Ti aspettiamo al nostro stand sul lungomare per una pizza buona e sicura, pensata anche per celiaci e intolleranti.</p>',
        'seo_title' => "St. Julian's Pizza Festival 2026 Malta | Pizza Senza Glutine",
        'seo_desc'  => "20-21 agosto 2026 a St. Julian's, Malta: La Compagnia del Gluten Free allo St. Julian's International Pizza Festival con pizza senza glutine e senza lattosio.",
        'focuskw'   => "St. Julian's Pizza Festival",
      ],
      'en' => [
        'title'   => "St. Julian's International Pizza Festival 2026 (Malta)",
        'slug'    => 'st-julians-international-pizza-festival-2026',
        'excerpt' => "On 20 and 21 August, La Compagnia del Gluten Free flies to Malta for the St. Julian's International Pizza Festival: gluten-free pizza on the Spinola Bay seafront.",
        'content' => '<p>On <strong>20 and 21 August 2026</strong>, from <strong>5 PM</strong>, the <strong>Spinola Bay</strong> seafront in <strong>St. Julian\'s (Malta)</strong> hosts the <strong>St. Julian\'s International Pizza Festival</strong>, by the famous <strong>Love Monument</strong> on Xatt is-Sajjieda.</p>'
                   . '<p>Two days of international celebration dedicated to pizza, with master pizza makers from all over the world, great food and entertainment overlooking the sea.</p>'
                   . '<p><strong>We will be there too.</strong> La Compagnia del Gluten Free takes part with its <strong>gluten-free and lactose-free pizza</strong>, bringing Sicilian artisan gluten-free excellence all the way to Malta. Come and find us at our stand on the seafront for a delicious, safe pizza made for coeliacs and people with intolerances too.</p>',
        'seo_title' => "St. Julian's Pizza Festival 2026 Malta | Gluten-Free Pizza",
        'seo_desc'  => "20-21 August 2026 in St. Julian's, Malta: La Compagnia del Gluten Free at the St. Julian's International Pizza Festival with gluten-free and lactose-free pizza.",
        'focuskw'   => "St. Julian's Pizza Festival",
      ],
      'de' => [
        'title'   => "St. Julian's International Pizza Festival 2026 (Malta)",
        'slug'    => 'st-julians-international-pizza-festival-2026',
        'excerpt' => "Am 20. und 21. August fliegt La Compagnia del Gluten Free nach Malta zum St. Julian's International Pizza Festival: glutenfreie Pizza an der Promenade von Spinola Bay.",
        'content' => '<p>Am <strong>20. und 21. August 2026</strong>, ab <strong>17 Uhr</strong>, findet an der Promenade von <strong>Spinola Bay</strong> in <strong>St. Julian\'s (Malta)</strong> das <strong>St. Julian\'s International Pizza Festival</strong> statt, in der Nähe des berühmten <strong>Love Monument</strong> an der Xatt is-Sajjieda.</p>'
                   . '<p>Zwei Tage internationales Fest rund um die Pizza, mit Pizzabäcker-Meistern aus aller Welt, Genuss und Show mit Blick aufs Meer.</p>'
                   . '<p><strong>Auch wir sind dabei.</strong> La Compagnia del Gluten Free nimmt mit ihrer <strong>glutenfreien und laktosefreien Pizza</strong> teil und bringt sizilianische handwerkliche Exzellenz ohne Gluten bis nach Malta. Besuche uns an unserem Stand an der Promenade für eine köstliche und sichere Pizza – auch für Menschen mit Zöliakie und Unverträglichkeiten.</p>',
        'seo_title' => "St. Julian's Pizza Festival 2026 Malta | Glutenfreie Pizza",
        'seo_desc'  => "20.-21. August 2026 in St. Julian's, Malta: La Compagnia del Gluten Free beim St. Julian's International Pizza Festival mit glutenfreier und laktosefreier Pizza.",
        'focuskw'   => "St. Julian's Pizza Festival",
      ],
      'fr' => [
        'title'   => "St. Julian's International Pizza Festival 2026 (Malte)",
        'slug'    => 'st-julians-international-pizza-festival-2026',
        'excerpt' => "Les 20 et 21 août, La Compagnia del Gluten Free s'envole pour Malte au St. Julian's International Pizza Festival : pizza sans gluten sur le front de mer de Spinola Bay.",
        'content' => '<p>Les <strong>20 et 21 août 2026</strong>, dès <strong>17h</strong>, le front de mer de <strong>Spinola Bay</strong> à <strong>St. Julian\'s (Malte)</strong> accueille le <strong>St. Julian\'s International Pizza Festival</strong>, près du célèbre <strong>Love Monument</strong> sur Xatt is-Sajjieda.</p>'
                   . '<p>Deux jours de fête internationale dédiée à la pizza, avec des maîtres pizzaïolos du monde entier, du goût et du spectacle face à la mer.</p>'
                   . '<p><strong>Nous serons présents nous aussi.</strong> La Compagnia del Gluten Free participe avec sa <strong>pizza sans gluten et sans lactose</strong>, apportant l\'excellence artisanale sicilienne sans gluten jusqu\'à Malte. Retrouvez-nous sur notre stand, sur le front de mer, pour une pizza savoureuse et sûre, pensée aussi pour les cœliaques et les personnes intolérantes.</p>',
        'seo_title' => "St. Julian's Pizza Festival 2026 Malte | Pizza Sans Gluten",
        'seo_desc'  => "20-21 août 2026 à St. Julian's, Malte : La Compagnia del Gluten Free au St. Julian's International Pizza Festival avec pizza sans gluten et sans lactose.",
        'focuskw'   => "St. Julian's Pizza Festival",
      ],
    ],
  ],

];

/* ---------------------------------------------------------------------------
 * HELPER: carica immagine flyer nella media library (una volta per evento)
 * ------------------------------------------------------------------------- */
function lcgf_sideload_flyer($path, $title) {
  if (!file_exists($path)) { echo "  ! immagine mancante: {$path}\n"; return 0; }
  $tmp = wp_tempnam(basename($path));
  if (!$tmp) { echo "  ! impossibile creare tmp\n"; return 0; }
  copy($path, $tmp);
  $file_array = ['name' => basename($path), 'tmp_name' => $tmp];
  $att_id = media_handle_sideload($file_array, 0, $title);
  if (is_wp_error($att_id)) {
    @unlink($tmp);
    echo "  ! errore sideload: " . $att_id->get_error_message() . "\n";
    return 0;
  }
  return (int) $att_id;
}

/* ---------------------------------------------------------------------------
 * MAIN
 * ------------------------------------------------------------------------- */
echo "\n===== IMPORT EVENTI AGOSTO 2026 — LCGF =====\n";
$created = 0; $skipped = 0;

foreach ($EVENTS as $key => $ev) {
  echo "\n[Evento] {$key}\n";

  // Idempotenza: esiste già la voce IT con questa chiave?
  $existing = get_posts([
    'post_type'   => 'lcgf_evento',
    'post_status' => 'any',
    'numberposts' => 1,
    'fields'      => 'ids',
    'meta_key'    => '_lcgf_import_key',
    'meta_value'  => $key,
    'lang'        => '', // tutte le lingue
  ]);
  if (!empty($existing)) {
    echo "  = già presente (ID " . implode(',', $existing) . ") → skip\n";
    $skipped++;
    continue;
  }

  // Immagine in evidenza (condivisa tra le 4 lingue)
  $att_id = lcgf_sideload_flyer($IMG_DIR . '/' . $ev['image'], $ev['i18n']['it']['title']);
  if ($att_id && function_exists('pll_set_post_language')) {
    // dà una lingua all'allegato per coerenza Polylang media (se attivo)
    @pll_set_post_language($att_id, 'it');
  }

  $ids_by_lang = [];

  foreach ($LANGS as $lang) {
    $t = $ev['i18n'][$lang];

    $post_id = wp_insert_post([
      'post_type'    => 'lcgf_evento',
      'post_status'  => 'publish',
      'post_title'   => $t['title'],
      'post_name'    => $t['slug'],
      'post_content' => $t['content'],
      'post_excerpt' => $t['excerpt'],
    ], true);

    if (is_wp_error($post_id) || !$post_id) {
      $msg = is_wp_error($post_id) ? $post_id->get_error_message() : 'unknown';
      echo "  ! [{$lang}] errore wp_insert_post: {$msg}\n";
      continue;
    }

    // Meta evento
    $m = $ev['meta'];
    update_post_meta($post_id, '_lcgf_evento_data_inizio', $m['data_inizio']);
    update_post_meta($post_id, '_lcgf_evento_data_fine',   $m['data_fine']);
    update_post_meta($post_id, '_lcgf_evento_ora_inizio',  $m['ora_inizio']);
    update_post_meta($post_id, '_lcgf_evento_luogo',       $m['luogo']);
    update_post_meta($post_id, '_lcgf_evento_indirizzo',   $m['indirizzo']);
    update_post_meta($post_id, '_lcgf_evento_citta',       $m['citta']);
    update_post_meta($post_id, '_lcgf_evento_prezzo',      $ev['prezzo'][$lang]);
    update_post_meta($post_id, '_lcgf_evento_link_esterno', $m['link']);
    // Dati geo/SEO/GEO
    update_post_meta($post_id, '_lcgf_evento_lat',       $m['lat']);
    update_post_meta($post_id, '_lcgf_evento_lng',       $m['lng']);
    update_post_meta($post_id, '_lcgf_evento_cap',       $m['cap']);
    update_post_meta($post_id, '_lcgf_evento_nazione',   $m['nazione']);
    update_post_meta($post_id, '_lcgf_evento_organizer', $m['organizer']);
    // Chiave di import (idempotenza)
    update_post_meta($post_id, '_lcgf_import_key', $key);

    // Immagine in evidenza
    if ($att_id) set_post_thumbnail($post_id, $att_id);

    // Yoast SEO
    update_post_meta($post_id, '_yoast_wpseo_title',    $t['seo_title']);
    update_post_meta($post_id, '_yoast_wpseo_metadesc', $t['seo_desc']);
    if (!empty($t['focuskw'])) update_post_meta($post_id, '_yoast_wpseo_focuskw', $t['focuskw']);

    // Polylang: lingua
    pll_set_post_language($post_id, $lang);

    $ids_by_lang[$lang] = $post_id;
    echo "  + [{$lang}] ID {$post_id} — {$t['title']}\n";
  }

  // Collega le traduzioni tra loro
  if (!empty($ids_by_lang)) {
    pll_save_post_translations($ids_by_lang);
    echo "  ↔ traduzioni collegate: " . json_encode($ids_by_lang) . "\n";
    $created++;
  }
}

echo "\n===== FATTO — eventi creati: {$created}, saltati: {$skipped} =====\n";

// Aggiorna sitemap Yoast + pulizia cache
if (function_exists('wp_cache_flush')) wp_cache_flush();
echo "Ricordati: 'wp cache flush' + eventuale rebuild sitemap Yoast.\n";
