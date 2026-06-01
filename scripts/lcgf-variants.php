<?php
/**
 * lcgf-variants.php — converte i prodotti con gusti/formati da SEMPLICI a
 * VARIABILI (attributi prodotto custom + varianti), in tutte le lingue Polylang.
 *
 * Eseguire via:  wp eval-file scripts/lcgf-variants.php
 *
 * Idempotente: salta i prodotti già variabili con varianti. Sicuro da ri-eseguire.
 *
 * Usa ATTRIBUTI CUSTOM (non tassonomie globali pa_*): così ogni traduzione ha le
 * proprie etichette senza dover tradurre tassonomie globali (Polylang free non
 * integra le tassonomie WooCommerce).
 *
 * ⚠️ PREZZI STIMA per le varianti con formati diversi (tiramisù kg, crostate
 * Ø20/Ø25): da rifinire col listino reale di Carmelo. Vedi memoria
 * lacompagniadelglutenfree-todo.md.
 */

if (!defined('ABSPATH')) { echo "Eseguire via wp eval-file\n"; return; }
if (!class_exists('WC_Product_Variable')) { echo "WooCommerce non attivo\n"; return; }

/**
 * Config: per ogni prodotto base (slug IT), l'etichetta attributo per lingua e
 * le varianti (label per lingua + prezzo). Le varianti sono allineate per indice
 * tra le lingue. La chiave 'it' è quella di riferimento per il numero di varianti.
 */
$LCGF_VARIANTS = [
    // CHEESECAKE — 5 gusti, stesso prezzo (stima 4,80)
    'cheesecake' => [
        'attr' => ['it' => 'Gusto', 'en' => 'Flavour', 'de' => 'Geschmack', 'fr' => 'Goût'],
        'variants' => [
            ['price' => '4.80', 'label' => ['it' => 'Pistacchio',      'en' => 'Pistachio',     'de' => 'Pistazie',     'fr' => 'Pistache']],
            ['price' => '4.80', 'label' => ['it' => 'Frutti di bosco', 'en' => 'Mixed berries', 'de' => 'Waldbeeren',   'fr' => 'Fruits des bois']],
            ['price' => '4.80', 'label' => ['it' => 'Fragola',         'en' => 'Strawberry',    'de' => 'Erdbeere',     'fr' => 'Fraise']],
            ['price' => '4.80', 'label' => ['it' => 'Limone',          'en' => 'Lemon',         'de' => 'Zitrone',      'fr' => 'Citron']],
            ['price' => '4.80', 'label' => ['it' => 'Pan di stelle',   'en' => 'Pan di stelle', 'de' => 'Pan di stelle','fr' => 'Pan di stelle']],
        ],
    ],
    // TIRAMISU — 2 formati (mono 5,50 reale · torta 1kg PREZZO STIMA 30,00)
    'tiramisu' => [
        'attr' => ['it' => 'Formato', 'en' => 'Size', 'de' => 'Größe', 'fr' => 'Format'],
        'variants' => [
            ['price' => '5.50',  'label' => ['it' => 'Monoporzione 100g', 'en' => 'Single portion 100g', 'de' => 'Einzelportion 100g', 'fr' => 'Portion individuelle 100g']],
            ['price' => '30.00', 'label' => ['it' => 'Torta 1 kg',        'en' => 'Cake 1 kg',           'de' => 'Torte 1 kg',         'fr' => 'Gâteau 1 kg']],
        ],
    ],
    // CROSTATE — 3 formati (mono 3,50 reale · Ø20 e Ø25 PREZZI STIMA)
    'crostate' => [
        'attr' => ['it' => 'Formato', 'en' => 'Size', 'de' => 'Größe', 'fr' => 'Format'],
        'variants' => [
            ['price' => '3.50',  'label' => ['it' => 'Monoporzione 100g', 'en' => 'Single portion 100g', 'de' => 'Einzelportion 100g', 'fr' => 'Portion individuelle 100g']],
            ['price' => '18.00', 'label' => ['it' => 'Ø 20 cm', 'en' => 'Ø 20 cm', 'de' => 'Ø 20 cm', 'fr' => 'Ø 20 cm']],
            ['price' => '24.00', 'label' => ['it' => 'Ø 25 cm', 'en' => 'Ø 25 cm', 'de' => 'Ø 25 cm', 'fr' => 'Ø 25 cm']],
        ],
    ],
];

/** Lingua di un post: usa Polylang se presente, altrimenti 'it'. */
function lcgf_post_lang($post_id) {
    if (function_exists('pll_get_post_language')) {
        $l = pll_get_post_language($post_id, 'slug');
        if ($l) return $l;
    }
    return 'it';
}

/** Tutte le versioni-lingua di un post (incluso sé stesso). */
function lcgf_post_translations($post_id) {
    if (function_exists('pll_get_post_translations')) {
        $tr = pll_get_post_translations($post_id);
        if (!empty($tr)) return array_values($tr);
    }
    return [$post_id];
}

/** Converte un singolo post-prodotto in variabile con attributo custom. */
function lcgf_make_variable($post_id, $config) {
    $lang = lcgf_post_lang($post_id);
    $attr_label = $config['attr'][$lang] ?? $config['attr']['it'];

    $existing = wc_get_product($post_id);
    if ($existing && $existing->is_type('variable') && count($existing->get_children()) > 0) {
        return "[$lang] id $post_id già variabile (" . count($existing->get_children()) . " varianti) — skip";
    }

    // Valori attributo (etichette tradotte) + prezzi allineati per indice.
    $options = [];
    foreach ($config['variants'] as $v) {
        $options[] = $v['label'][$lang] ?? $v['label']['it'];
    }

    // Trasforma in variabile.
    wp_set_object_terms($post_id, 'variable', 'product_type');
    $product = new WC_Product_Variable($post_id);

    $attribute = new WC_Product_Attribute();
    $attribute->set_id(0); // 0 = attributo custom (non tassonomia globale)
    $attribute->set_name($attr_label);
    $attribute->set_options($options);
    $attribute->set_position(0);
    $attribute->set_visible(true);
    $attribute->set_variation(true);
    $product->set_attributes([$attribute]);
    $product->set_manage_stock(false);
    $product->set_stock_status('instock');
    $product->save();

    // Rimuovi eventuali varianti orfane preesistenti.
    foreach ($product->get_children() as $child_id) {
        wp_delete_post($child_id, true);
    }

    $attr_key = sanitize_title($attr_label); // es. 'gusto', 'formato', 'gout'
    $created = 0;
    foreach ($config['variants'] as $i => $v) {
        $val = $v['label'][$lang] ?? $v['label']['it'];
        $variation = new WC_Product_Variation();
        $variation->set_parent_id($post_id);
        $variation->set_attributes([$attr_key => $val]);
        $variation->set_regular_price($v['price']);
        $variation->set_manage_stock(false);
        $variation->set_stock_status('instock');
        $variation->save();
        $created++;
    }

    WC_Product_Variable::sync($post_id);
    wc_delete_product_transients($post_id);

    return "[$lang] id $post_id → variabile con $created varianti (attr: $attr_label)";
}

echo "== LCGF: conversione prodotti in variabili (" . current_time('mysql') . ") ==\n";
$lcgf_converted = 0;
foreach ($LCGF_VARIANTS as $slug => $config) {
    $base = get_page_by_path($slug, OBJECT, 'product');
    if (!$base) {
        echo "- '$slug': prodotto base IT non trovato — skip\n";
        continue;
    }
    $ids = lcgf_post_translations($base->ID);
    echo "- '$slug': " . count($ids) . " versioni-lingua (" . implode(',', $ids) . ")\n";
    foreach ($ids as $pid) {
        try {
            $msg = lcgf_make_variable($pid, $config);
            if (strpos($msg, '→ variabile') !== false) $lcgf_converted++;
            echo "    " . $msg . "\n";
        } catch (Throwable $e) {
            echo "    [ERR] id $pid: " . $e->getMessage() . "\n";
        }
    }
}
// Conta i variabili effettivi a posteriori (verifica reale, non solo l'option)
$lcgf_var_total = (int) (new WP_Query([
    'post_type' => 'product', 'posts_per_page' => -1, 'fields' => 'ids',
    'tax_query' => [['taxonomy' => 'product_type', 'field' => 'slug', 'terms' => 'variable']],
]))->found_posts;
echo "== fatto: $lcgf_converted convertiti in questa run · $lcgf_var_total prodotti variabili totali ==\n";
// Marca come completato SOLO se ci sono davvero prodotti variabili (self-healing:
// se la run fallisce e ne restano 0, l'option non viene settata e si riprova).
if ($lcgf_var_total > 0) {
    update_option('lcgf_variants_v2', current_time('mysql'));
}
