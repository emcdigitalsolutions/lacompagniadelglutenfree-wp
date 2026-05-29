<?php
/**
 * Traduzione automatica IT -> EN/DE/FR via Gemini 2.5 Flash.
 * Usa Polylang API per creare e linkare le traduzioni.
 *
 * Idempotente: salta i post gia tradotti. Imposta lcgf_translation_done dopo successo.
 *
 * Esecuzione: wp eval-file scripts/translate-lcgf.php
 */

$is_cli = (php_sapi_name() === 'cli');
$is_web_admin = !empty($GLOBALS['lcgf_force_cli']);
if (!$is_cli && !$is_web_admin) {
    echo "Solo CLI o admin web (con lcgf_force_cli)\n"; return;
}

if (!function_exists('pll_save_post_translations')) {
    echo "[FATAL] Polylang non attivo o non caricato\n"; return;
}

$GEMINI_KEY = getenv('GEMINI_API_KEY');
if (!$GEMINI_KEY) {
    echo "[FATAL] GEMINI_API_KEY non impostata. Configurala come env var (Coolify), mai nel codice.\n";
    return;
}
$MODEL = 'gemini-2.5-flash';
$TARGET_LANGS = ['en', 'de', 'fr'];
$LANG_LABELS = [
    'en' => 'English',
    'de' => 'German (Deutsch)',
    'fr' => 'French (Français)',
];

@set_time_limit(0);
ignore_user_abort(true);

// ---------- Gemini batch translate ----------
function gemini_translate(array $texts, string $target_lang_label, string $api_key, string $model = 'gemini-2.5-flash'): array {
    if (empty($texts)) return [];

    $system = "You are a professional translator for an Italian artisan gluten-free, lactose-free bakery e-commerce called \"La Compagnia del Gluten Free\". Translate from Italian to {$target_lang_label}.\n\nRULES:\n- Use a warm, artisanal, welcoming tone\n- KEEP UNTRANSLATED these brand terms (do NOT translate): \"Mangia con Gusto\", \"La Compagnia del Gluten Free\", \"Pinsa Romana\", \"Box Family\"\n- Use correct food/allergen terminology in the target language (e.g. gluten-free / glutenfrei / sans gluten; lactose-free / laktosefrei / sans lactose)\n- Preserve ALL HTML tags, paragraph structure, formatting, and special characters EXACTLY as in the source\n- If a text is empty or whitespace only, return an empty string in its place\n- Return ONLY the translations as a JSON array of strings, in the SAME ORDER as input. No prose, no markdown fences.";

    $user = "Translate the following Italian texts (preserve HTML):\n\n" . json_encode($texts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $payload = [
        'system_instruction' => ['parts' => [['text' => $system]]],
        'contents' => [
            ['role' => 'user', 'parts' => [['text' => $user]]],
        ],
        'generationConfig' => [
            'temperature' => 0.3,
            'responseMimeType' => 'application/json',
            'responseSchema' => [
                'type' => 'ARRAY',
                'items' => ['type' => 'STRING']
            ]
        ]
    ];

    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_CONNECTTIMEOUT => 15,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($resp === false) throw new Exception("Gemini curl error: {$err}");
    if ($code !== 200) throw new Exception("Gemini HTTP {$code}: " . substr($resp, 0, 600));

    $data = json_decode($resp, true);
    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    $result = json_decode($text, true);
    if (!is_array($result)) {
        throw new Exception("Risposta Gemini non parsable: " . substr($text, 0, 300));
    }
    // Ensure length matches input
    while (count($result) < count($texts)) $result[] = '';
    return array_slice($result, 0, count($texts));
}

// ---------- Translate one post into target lang ----------
function translate_post_to_lang(WP_Post $post, string $target_lang, string $lang_label, string $api_key): ?int {
    // Skip if translation already exists
    $map = pll_get_post_translations($post->ID);
    if (!empty($map[$target_lang])) {
        $exid = (int)$map[$target_lang];
        echo "  - skip [{$target_lang}]: gia esiste ID {$exid}\n";
        return $exid;
    }

    $batch = [
        (string)$post->post_title,
        (string)$post->post_content,
        (string)$post->post_excerpt,
    ];

    echo "  - Gemini -> {$target_lang} ... ";
    $t = gemini_translate($batch, $lang_label, $api_key);
    echo "ok\n";

    $new_id = wp_insert_post([
        'post_title'   => $t[0] !== '' ? $t[0] : $post->post_title,
        'post_content' => $t[1] !== '' ? $t[1] : $post->post_content,
        'post_excerpt' => $t[2] ?? '',
        'post_status'  => $post->post_status,
        'post_type'    => $post->post_type,
        'post_author'  => $post->post_author,
        'menu_order'   => $post->menu_order,
        'post_parent'  => $post->post_parent,
        'comment_status' => $post->comment_status,
        'ping_status'  => $post->ping_status,
    ], true);

    if (is_wp_error($new_id) || !$new_id) {
        $msg = is_wp_error($new_id) ? $new_id->get_error_message() : 'unknown';
        echo "  ! errore wp_insert_post: {$msg}\n";
        return null;
    }

    // Copy critical post meta (featured image, custom fields, woo, page template)
    $skip_meta = ['_edit_lock', '_edit_last'];
    $meta = get_post_meta($post->ID);
    foreach ($meta as $key => $vals) {
        if (in_array($key, $skip_meta, true)) continue;
        foreach ($vals as $v) {
            add_post_meta($new_id, $key, maybe_unserialize($v));
        }
    }

    // Set Polylang language and link translations
    pll_set_post_language($new_id, $target_lang);

    $orig_lang = pll_get_post_language($post->ID) ?: 'it';
    $updated_map = pll_get_post_translations($post->ID);
    $updated_map[$orig_lang] = $post->ID;
    $updated_map[$target_lang] = $new_id;
    pll_save_post_translations($updated_map);

    // Copy taxonomies (cats already translated separately if Polylang-aware)
    $taxonomies = get_object_taxonomies($post->post_type);
    foreach ($taxonomies as $tax) {
        $orig_term_ids = wp_get_object_terms($post->ID, $tax, ['fields' => 'ids']);
        if (empty($orig_term_ids) || is_wp_error($orig_term_ids)) continue;
        // Try to map IT term to target lang term
        $target_term_ids = [];
        foreach ($orig_term_ids as $tid) {
            if (function_exists('pll_get_term') && function_exists('pll_get_term_translations')) {
                $tmap = pll_get_term_translations($tid);
                if (!empty($tmap[$target_lang])) {
                    $target_term_ids[] = (int)$tmap[$target_lang];
                } else {
                    $target_term_ids[] = (int)$tid;
                }
            } else {
                $target_term_ids[] = (int)$tid;
            }
        }
        wp_set_object_terms($new_id, $target_term_ids, $tax);
    }

    return (int)$new_id;
}

// ---------- Translate taxonomy term ----------
function translate_term_to_lang(WP_Term $term, string $target_lang, string $lang_label, string $api_key): ?int {
    if (!function_exists('pll_get_term_translations')) return null;
    $map = pll_get_term_translations($term->term_id);
    if (!empty($map[$target_lang])) {
        echo "  - skip term [{$target_lang}]: gia esiste ID {$map[$target_lang]}\n";
        return (int)$map[$target_lang];
    }

    echo "  - Gemini term -> {$target_lang} ... ";
    $t = gemini_translate([$term->name, (string)$term->description], $lang_label, $api_key);
    echo "ok\n";

    $name = $t[0] ?: $term->name;
    $desc = $t[1] ?? '';
    $result = wp_insert_term($name, $term->taxonomy, [
        'description' => $desc,
        'parent'      => $term->parent,
    ]);
    if (is_wp_error($result)) {
        // Term already exists? Reuse ID
        $existing = $result->get_error_data('term_exists');
        if ($existing) {
            $new_term_id = (int)$existing;
        } else {
            echo "  ! errore wp_insert_term: " . $result->get_error_message() . "\n";
            return null;
        }
    } else {
        $new_term_id = (int)$result['term_id'];
    }

    pll_set_term_language($new_term_id, $target_lang);
    $orig_lang = pll_get_term_language($term->term_id) ?: 'it';
    $tmap = pll_get_term_translations($term->term_id);
    $tmap[$orig_lang] = $term->term_id;
    $tmap[$target_lang] = $new_term_id;
    pll_save_term_translations($tmap);

    return $new_term_id;
}

// ---------- MAIN ----------

echo "\n========= TRADUZIONE LCGF =========\n";
echo "Modello: {$MODEL}\n";
echo "Lingue target: " . implode(', ', $TARGET_LANGS) . "\n\n";

$total = ['post_done' => 0, 'post_skip' => 0, 'term_done' => 0, 'term_skip' => 0, 'errors' => 0];

// 1) Categorie prodotti
$taxonomies_to_translate = ['product_cat'];
foreach ($taxonomies_to_translate as $tax) {
    if (!taxonomy_exists($tax)) continue;
    echo "\n--- TAXONOMY: {$tax} ---\n";
    $terms = get_terms(['taxonomy' => $tax, 'hide_empty' => false, 'lang' => 'it']);
    foreach ($terms as $term) {
        if (!($term instanceof WP_Term)) continue;
        // Salta term non IT (sicurezza extra)
        $cur_lang = function_exists('pll_get_term_language') ? pll_get_term_language($term->term_id) : 'it';
        if ($cur_lang && $cur_lang !== 'it') continue;
        echo "\n[term #{$term->term_id}] {$term->name}\n";
        foreach ($TARGET_LANGS as $lang) {
            try {
                $r = translate_term_to_lang($term, $lang, $LANG_LABELS[$lang], $GEMINI_KEY);
                $r !== null ? $total['term_done']++ : $total['term_skip']++;
            } catch (Exception $e) {
                $total['errors']++;
                echo "  ! ERR: " . $e->getMessage() . "\n";
            }
            usleep(400000);
        }
    }
}

// 2) Post types
$post_types = ['page', 'product', 'lcgf_evento'];
foreach ($post_types as $pt) {
    if (!post_type_exists($pt)) {
        echo "\n[skip post type {$pt}: non esiste]\n";
        continue;
    }
    echo "\n--- POST TYPE: {$pt} ---\n";
    $posts = get_posts([
        'post_type'   => $pt,
        'numberposts' => -1,
        'post_status' => 'publish',
        'lang'        => 'it',
    ]);
    echo "Trovati " . count($posts) . " post IT\n";
    foreach ($posts as $post) {
        // Salta i post non IT (sicurezza extra: Polylang query var puo' fallire in CLI)
        $cur_lang = function_exists('pll_get_post_language') ? pll_get_post_language($post->ID) : 'it';
        if ($cur_lang && $cur_lang !== 'it') continue;
        echo "\n[{$pt} #{$post->ID}] " . substr($post->post_title, 0, 60) . "\n";
        foreach ($TARGET_LANGS as $lang) {
            try {
                $r = translate_post_to_lang($post, $lang, $LANG_LABELS[$lang], $GEMINI_KEY);
                $r !== null ? $total['post_done']++ : $total['post_skip']++;
            } catch (Exception $e) {
                $total['errors']++;
                echo "  ! ERR: " . $e->getMessage() . "\n";
            }
            usleep(400000);
        }
    }
}

// Imposta il marker SOLO se non ci sono stati errori (o pochi)
if ($total['errors'] === 0) {
    update_option('lcgf_translation_done', time(), false);
} else {
    delete_option('lcgf_translation_done');
}

echo "\n========= RIEPILOGO =========\n";
echo "Post tradotti:   {$total['post_done']}\n";
echo "Post skippati:   {$total['post_skip']}\n";
echo "Term tradotti:   {$total['term_done']}\n";
echo "Term skippati:   {$total['term_skip']}\n";
echo "Errori:          {$total['errors']}\n";
echo "lcgf_translation_done = " . (get_option('lcgf_translation_done') ?: '(non settato per errori)') . "\n";
