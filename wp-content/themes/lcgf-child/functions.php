<?php
/**
 * La Compagnia del Gluten Free — child theme functions
 * Parent: Astra
 */
if (!defined('ABSPATH')) exit;

/* ---------- Enqueue ---------- */
add_action('wp_enqueue_scripts', function () {
    // parent
    wp_enqueue_style('astra-parent', get_template_directory_uri() . '/style.css', [], wp_get_theme(get_template())->get('Version'));
    // child (cache busting via filemtime)
    $child_css = get_stylesheet_directory() . '/style.css';
    $ver = file_exists($child_css) ? filemtime($child_css) : '0.2.0';
    wp_enqueue_style('lcgf-child', get_stylesheet_directory_uri() . '/style.css', ['astra-parent'], $ver);

    // Google Fonts
    wp_enqueue_style(
        'lcgf-fonts',
        'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500&family=Inter:wght@300;400;500;600;700&display=swap',
        [],
        null
    );
}, 20);

/* ---------- Supporti tema ---------- */
add_action('after_setup_theme', function () {
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    add_theme_support('woocommerce');
    add_theme_support('title-tag');
});

/* ---------- Nascondi Astra default header/footer (li riscrive il child) ---------- */
add_filter('astra_main_header_display', '__return_false');
add_filter('ast_footer_section_display', '__return_false');
add_filter('astra_page_layout', function () { return 'no-sidebar'; });
add_filter('astra_the_title_enabled', '__return_false');
add_filter('astra_content_layout', function () { return 'page-builder'; });

/* ---------- WooCommerce wrapper override ---------- */
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content',  'woocommerce_output_content_wrapper_end', 10);
add_action('woocommerce_before_main_content', function () { echo '<div class="container" style="padding-top:40px;padding-bottom:40px">'; }, 10);
add_action('woocommerce_after_main_content',  function () { echo '</div>'; }, 10);

/* ---------- Disable sidebar ---------- */
add_filter('woocommerce_show_page_title', '__return_false');
remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);

/* ---------- Numero colonne shop ---------- */
add_filter('loop_shop_columns', fn() => 3);
add_filter('loop_shop_per_page', fn() => 12);

/* ---------- Disabilita commenti su pagine/prodotti ---------- */
add_filter('comments_open', '__return_false', 20, 2);

/* ---------- Allunga thumb shop ---------- */
add_action('after_setup_theme', function () {
    add_image_size('lcgf-card', 600, 600, true);
});

/* ---------- Auto-import immagini prodotti se mancanti ---------- */
add_action('admin_init', function () {
    if (get_option('lcgf_images_imported_v2') === 'done') return;
    if (!current_user_can('manage_options')) return;
    if (!class_exists('WooCommerce')) return;

    $mapping = [
        'box-family'        => 'box-family.png',
        'pinsa-romana'      => 'pinsa-romana.png',
        'pan-focaccia'      => 'pan-focaccia.png',
        'focaccia-rotonda'  => 'focaccia-rotonda.png',
        'base-pizza'        => 'base-pizza.png',
        'pane-filoncino'    => 'pane-filoncino.png',
        'pane-rosetta'      => 'pane-rosetta.png',
        'brioche'           => 'brioche.png',
        'cornetto-vuoto'    => 'cornetto.png',
        'crostate'          => 'crostate.png',
        'biscotti'          => 'biscotti.png',
        'tiramisu'          => 'tiramisu.png',
        'cheesecake'        => 'cheesecake.png',
    ];

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $imported = 0;
    foreach ($mapping as $slug => $filename) {
        $page = get_page_by_path($slug, OBJECT, 'product');
        if (!$page) continue;
        $product_id = $page->ID;
        if (has_post_thumbnail($product_id)) continue;

        $src = get_stylesheet_directory() . '/assets/products/' . $filename;
        if (!file_exists($src)) continue;

        $upload = wp_upload_dir();
        $new_filename = wp_unique_filename($upload['path'], $filename);
        $dest = trailingslashit($upload['path']) . $new_filename;
        if (!@copy($src, $dest)) continue;

        $wp_filetype = wp_check_filetype($new_filename, null);
        $att_id = wp_insert_attachment([
            'guid'           => trailingslashit($upload['url']) . $new_filename,
            'post_mime_type' => $wp_filetype['type'],
            'post_title'     => $page->post_title,
            'post_content'   => '',
            'post_status'    => 'inherit',
        ], $dest, $product_id);
        if (is_wp_error($att_id)) continue;
        $meta = wp_generate_attachment_metadata($att_id, $dest);
        wp_update_attachment_metadata($att_id, $meta);
        set_post_thumbnail($product_id, $att_id);
        $imported++;
    }

    if ($imported > 0) {
        set_transient('lcgf_images_notice', $imported, 30);
    }
    update_option('lcgf_images_imported_v2', 'done');
});

add_action('admin_notices', function () {
    $imported = get_transient('lcgf_images_notice');
    if (!$imported) return;
    delete_transient('lcgf_images_notice');
    echo '<div class="notice notice-success is-dismissible"><p>🌾 LCGF: ' . (int)$imported . ' immagini prodotto importate automaticamente.</p></div>';
});

/* ====================================================================== */
/* ===========  Complianz — fix hook banner sul frontend  ============== */
/* ====================================================================== */
// Per qualche ragione (race condition al boot) i hook wp_footer/wp_head/
// wp_enqueue_scripts del banner Complianz non vengono registrati sul
// frontend. Li ri-registriamo manualmente quando le condizioni sono OK.
add_action('init', function () {
    if (is_admin() || defined('DOING_CRON') || (defined('WP_CLI') && WP_CLI)) return;
    if (!class_exists('cmplz_banner_loader')) return;
    $loader = cmplz_banner_loader::this();
    if (!$loader) return;
    if (!get_option('cmplz_wizard_completed_once')) return;
    if (!method_exists($loader, 'site_needs_cookie_warning')) return;
    if (!$loader->site_needs_cookie_warning()) return;

    // Re-registra gli hook frontend del banner
    if (!has_action('wp_enqueue_scripts', [$loader, 'enqueue_assets'])) {
        add_action('wp_enqueue_scripts', [$loader, 'enqueue_assets'], PHP_INT_MAX - 50);
    }
    if (!has_action('wp_head', [$loader, 'cookiebanner_css'])) {
        add_action('wp_head', [$loader, 'cookiebanner_css']);
    }
    if (!has_action('wp_footer', [$loader, 'cookiebanner_html'])) {
        add_action('wp_footer', [$loader, 'cookiebanner_html']);
    }
    if (method_exists($loader, 'dynamic_gtm_enqueue')) {
        $loader->dynamic_gtm_enqueue();
    }
}, 30);

/* ====================================================================== */
/* ===========  WPForms — Form contatti default LCGF  =================== */
/* ====================================================================== */

add_action('init', function () {
    if (!is_admin() && !defined('WP_CLI')) return;
    if (get_option('lcgf_wpforms_seeded_v1') === 'done') return;
    if (!post_type_exists('wpforms')) return;

    // Verifica se esiste già un form WPForms
    $existing = get_posts([
        'post_type'   => 'wpforms',
        'numberposts' => 1,
        'post_status' => ['publish', 'draft'],
    ]);
    if (!empty($existing)) {
        update_option('lcgf_wpforms_seeded_v1', 'done');
        update_option('lcgf_contact_form_id', $existing[0]->ID);
        return;
    }

    $admin_email = get_option('admin_email');

    // NB: WPForms richiede fields/notifications/confirmations come associative array
    // con KEY STRING ('1','2',...). Le keys sequenziali int causerebbero JSON array
    // (e WPForms non lo riconosce). Field id parte da 1, non 0.
    $form_data = [
        'id'       => 0,
        'field_id' => 7,
        'fields'   => [
            '1' => [
                'id'       => '1',
                'type'     => 'name',
                'label'    => 'Nome e cognome',
                'format'   => 'simple',
                'required' => '1',
                'size'     => 'medium',
            ],
            '2' => [
                'id'       => '2',
                'type'     => 'email',
                'label'    => 'Email',
                'required' => '1',
                'size'     => 'medium',
            ],
            '3' => [
                'id'       => '3',
                'type'     => 'phone',
                'label'    => 'Telefono (opzionale)',
                'format'   => 'international',
                'size'     => 'medium',
            ],
            '4' => [
                'id'       => '4',
                'type'     => 'select',
                'label'    => 'Oggetto',
                'choices'  => [
                    '1' => ['label' => 'Domanda su un ordine', 'value' => ''],
                    '2' => ['label' => 'Informazioni su un prodotto', 'value' => ''],
                    '3' => ['label' => 'Spedizione e resi', 'value' => ''],
                    '4' => ['label' => 'Gift card e regali', 'value' => ''],
                    '5' => ['label' => 'Collaborazioni B2B', 'value' => ''],
                    '6' => ['label' => 'Altro', 'value' => ''],
                ],
                'required' => '1',
                'size'     => 'medium',
            ],
            '5' => [
                'id'       => '5',
                'type'     => 'textarea',
                'label'    => 'Messaggio',
                'required' => '1',
                'size'     => 'medium',
            ],
            '6' => [
                'id'        => '6',
                'type'      => 'gdpr-checkbox',
                'label'     => 'Privacy',
                'choices'   => [
                    '1' => ['label' => 'Acconsento al trattamento dei dati personali ai sensi della <a href="/privacy/">Privacy Policy</a> per essere ricontattato.', 'value' => ''],
                ],
                'required'  => '1',
            ],
        ],
        'settings' => [
            'form_title'             => 'Contatti LCGF',
            'form_desc'              => '',
            'submit_text'            => 'Invia messaggio',
            'submit_text_processing' => 'Invio in corso...',
            'honeypot'               => '1',
            'antispam_v3'            => '1',
            'ajax_submit'            => '1',
            'notification_enable'    => '1',
            'notifications'          => [
                '1' => [
                    'enable'            => '1',
                    'notification_name' => 'Notifica admin',
                    'email'             => '{admin_email}',
                    'subject'           => '[LCGF] Nuovo messaggio da {field_id="1"}',
                    'sender_name'       => 'La Compagnia del Gluten Free',
                    'sender_address'    => '{admin_email}',
                    'replyto'           => '{field_id="2"}',
                    'message'           => "Hai ricevuto un nuovo messaggio dal form contatti.\n\n{all_fields}\n\n---\nInviato da {site_name} il {date format=\"d/m/Y H:i\"}",
                ],
            ],
            'confirmations'          => [
                '1' => [
                    'type'           => 'message',
                    'message'        => '<div style="text-align:center;padding:30px 20px"><h2 style="color:#2f4823">Grazie! Messaggio inviato.</h2><p>Abbiamo ricevuto il tuo messaggio e ti risponderemo entro <strong>24 ore lavorative</strong>. Nel frattempo puoi anche scriverci su WhatsApp al <strong>+39 327 699 9897</strong>.</p></div>',
                    'message_scroll' => '1',
                ],
            ],
        ],
        'meta'     => [
            'template' => 'simple-contact-form-template',
        ],
    ];

    // 1) Crea il post (post_content vuoto, lo settiamo via $wpdb dopo per evitare
    //    il doppio wp_unslash di wp_insert_post + wp_update_post che corrompe il JSON
    //    quando contiene HTML con virgolette).
    $post_id = wp_insert_post([
        'post_type'    => 'wpforms',
        'post_status'  => 'publish',
        'post_title'   => 'Contatti LCGF',
        'post_excerpt' => 'Form contatti principale del sito',
        'post_content' => '',
    ]);

    if ($post_id && !is_wp_error($post_id)) {
        global $wpdb;
        // Assegna l'id reale al form_data poi salva il JSON puro (senza wp_slash)
        // direttamente nel DB. WPForms userà wpforms_decode() che fa json_decode().
        $form_data['id'] = (string)$post_id;
        $wpdb->update(
            $wpdb->posts,
            ['post_content' => wp_json_encode($form_data)],
            ['ID' => $post_id]
        );
        clean_post_cache($post_id);
        update_option('lcgf_contact_form_id', $post_id);
        set_transient('lcgf_wpforms_notice', $post_id, 30);
    }
    update_option('lcgf_wpforms_seeded_v1', 'done');
});

add_action('admin_notices', function () {
    $fid = get_transient('lcgf_wpforms_notice');
    if (!$fid) return;
    delete_transient('lcgf_wpforms_notice');
    echo '<div class="notice notice-success is-dismissible"><p>📧 LCGF: form contatti WPForms creato (ID ' . (int)$fid . ').</p></div>';
});

/* ====================================================================== */
/* ===========  CPT "Evento" — Fiere ed Eventi  ========================== */
/* ====================================================================== */

/* Registra CPT */
add_action('init', function () {
    register_post_type('lcgf_evento', [
        'labels' => [
            'name'               => 'Fiere ed Eventi',
            'singular_name'      => 'Evento',
            'menu_name'          => 'Fiere ed Eventi',
            'add_new'            => 'Aggiungi evento',
            'add_new_item'       => 'Nuovo evento',
            'edit_item'          => 'Modifica evento',
            'new_item'           => 'Nuovo evento',
            'view_item'          => 'Vedi evento',
            'search_items'       => 'Cerca eventi',
            'not_found'          => 'Nessun evento trovato',
            'not_found_in_trash' => 'Nessun evento nel cestino',
            'all_items'          => 'Tutti gli eventi',
            'archives'           => 'Archivio eventi',
        ],
        'public'              => true,
        'show_in_rest'        => true,
        'has_archive'         => 'fiere-eventi',
        'rewrite'             => ['slug' => 'evento', 'with_front' => false],
        'supports'            => ['title', 'editor', 'thumbnail', 'excerpt'],
        'menu_icon'           => 'dashicons-calendar-alt',
        'menu_position'       => 22,
        'hierarchical'        => false,
        'taxonomies'          => [],
    ]);
});

/* Flush rewrite rules una sola volta dopo il deploy del nuovo CPT */
add_action('init', function () {
    if (get_option('lcgf_evento_rewrite_flushed_v1') === 'done') return;
    flush_rewrite_rules(false);
    update_option('lcgf_evento_rewrite_flushed_v1', 'done');
}, 99);

/**
 * Rende i PRODOTTI (e le loro tassonomie) traducibili/filtrabili da Polylang.
 * Senza questo, il post type WooCommerce 'product' non è gestito da Polylang e
 * il catalogo mostra TUTTE le lingue insieme (52 prodotti) invece dei 13 della
 * lingua corrente. (Equivale a "Polylang for WooCommerce" per il solo filtro lingua.)
 */
add_filter('pll_get_post_types', function ($post_types, $is_settings = false) {
    $post_types['product'] = 'product';
    return $post_types;
}, 10, 2);
add_filter('pll_get_taxonomies', function ($taxonomies, $is_settings = false) {
    $taxonomies['product_cat'] = 'product_cat';
    $taxonomies['product_tag'] = 'product_tag';
    return $taxonomies;
}, 10, 2);

/* Meta box dettagli evento */
add_action('add_meta_boxes', function () {
    add_meta_box(
        'lcgf_evento_dettagli',
        '📅 Dettagli evento',
        'lcgf_evento_render_meta_box',
        'lcgf_evento',
        'normal',
        'high'
    );
});

function lcgf_evento_render_meta_box($post) {
    wp_nonce_field('lcgf_evento_save', 'lcgf_evento_nonce');
    $f = [
        'data_inizio'    => get_post_meta($post->ID, '_lcgf_evento_data_inizio', true),
        'data_fine'      => get_post_meta($post->ID, '_lcgf_evento_data_fine', true),
        'ora_inizio'     => get_post_meta($post->ID, '_lcgf_evento_ora_inizio', true),
        'luogo'          => get_post_meta($post->ID, '_lcgf_evento_luogo', true),
        'indirizzo'      => get_post_meta($post->ID, '_lcgf_evento_indirizzo', true),
        'citta'          => get_post_meta($post->ID, '_lcgf_evento_citta', true),
        'prezzo'         => get_post_meta($post->ID, '_lcgf_evento_prezzo', true),
        'link_esterno'   => get_post_meta($post->ID, '_lcgf_evento_link_esterno', true),
    ];
    ?>
    <style>
      .lcgf-meta-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px 20px}
      .lcgf-meta-grid label{display:block;font-weight:600;margin-bottom:4px;font-size:13px;color:#333}
      .lcgf-meta-grid input[type=text],.lcgf-meta-grid input[type=date],.lcgf-meta-grid input[type=time],.lcgf-meta-grid input[type=url]{width:100%;padding:6px 8px;font-size:13px}
      .lcgf-meta-grid .full{grid-column:1/-1}
      .lcgf-meta-hint{color:#666;font-size:12px;font-style:italic;margin-top:2px}
    </style>
    <div class="lcgf-meta-grid">
      <div>
        <label>Data inizio *</label>
        <input type="date" name="lcgf_evento[data_inizio]" value="<?php echo esc_attr($f['data_inizio']); ?>" required />
        <p class="lcgf-meta-hint">Es. 2026-05-15</p>
      </div>
      <div>
        <label>Data fine (opzionale)</label>
        <input type="date" name="lcgf_evento[data_fine]" value="<?php echo esc_attr($f['data_fine']); ?>" />
        <p class="lcgf-meta-hint">Lascia vuoto per eventi di una sola giornata</p>
      </div>
      <div>
        <label>Ora inizio</label>
        <input type="time" name="lcgf_evento[ora_inizio]" value="<?php echo esc_attr($f['ora_inizio']); ?>" />
      </div>
      <div>
        <label>Prezzo / Ingresso</label>
        <input type="text" name="lcgf_evento[prezzo]" value="<?php echo esc_attr($f['prezzo']); ?>" placeholder="Es. Ingresso libero" />
      </div>
      <div class="full">
        <label>Luogo (nome) *</label>
        <input type="text" name="lcgf_evento[luogo]" value="<?php echo esc_attr($f['luogo']); ?>" placeholder="Es. Piazza Garibaldi, Sagra del Pane" required />
      </div>
      <div>
        <label>Indirizzo</label>
        <input type="text" name="lcgf_evento[indirizzo]" value="<?php echo esc_attr($f['indirizzo']); ?>" placeholder="Es. Via Roma 1" />
      </div>
      <div>
        <label>Città</label>
        <input type="text" name="lcgf_evento[citta]" value="<?php echo esc_attr($f['citta']); ?>" placeholder="Es. Ravanusa (AG)" />
      </div>
      <div class="full">
        <label>Link esterno (sito ufficiale evento)</label>
        <input type="url" name="lcgf_evento[link_esterno]" value="<?php echo esc_attr($f['link_esterno']); ?>" placeholder="https://..." />
      </div>
    </div>
    <?php
}

add_action('save_post_lcgf_evento', function ($post_id) {
    if (!isset($_POST['lcgf_evento_nonce']) || !wp_verify_nonce($_POST['lcgf_evento_nonce'], 'lcgf_evento_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $data = $_POST['lcgf_evento'] ?? [];
    $fields = ['data_inizio', 'data_fine', 'ora_inizio', 'luogo', 'indirizzo', 'citta', 'prezzo', 'link_esterno'];
    foreach ($fields as $f) {
        $val = isset($data[$f]) ? sanitize_text_field($data[$f]) : '';
        if ($f === 'link_esterno') $val = esc_url_raw($val);
        update_post_meta($post_id, '_lcgf_evento_' . $f, $val);
    }
});

/* Colonne admin custom */
add_filter('manage_lcgf_evento_posts_columns', function ($cols) {
    $new = [];
    foreach ($cols as $k => $v) {
        $new[$k] = $v;
        if ($k === 'title') {
            $new['lcgf_data']  = 'Data';
            $new['lcgf_luogo'] = 'Luogo';
        }
    }
    return $new;
});
add_action('manage_lcgf_evento_posts_custom_column', function ($col, $post_id) {
    if ($col === 'lcgf_data') {
        $d = get_post_meta($post_id, '_lcgf_evento_data_inizio', true);
        echo $d ? esc_html(date_i18n('d M Y', strtotime($d))) : '—';
    }
    if ($col === 'lcgf_luogo') {
        $l = get_post_meta($post_id, '_lcgf_evento_luogo', true);
        $c = get_post_meta($post_id, '_lcgf_evento_citta', true);
        echo esc_html(trim($l . ($c ? ' · ' . $c : '')) ?: '—');
    }
}, 10, 2);

/* Ordina archivio per data evento crescente */
add_action('pre_get_posts', function ($q) {
    if (is_admin() || !$q->is_main_query()) return;
    if ($q->is_post_type_archive('lcgf_evento')) {
        $q->set('meta_key', '_lcgf_evento_data_inizio');
        $q->set('orderby', 'meta_value');
        $q->set('order', 'ASC');
        $q->set('posts_per_page', 24);
    }
});

/* JSON-LD schema.org Event sulla single */
add_action('wp_head', function () {
    if (!is_singular('lcgf_evento')) return;
    $post_id = get_queried_object_id();
    $title   = get_the_title($post_id);
    $desc    = wp_strip_all_tags(get_the_excerpt($post_id) ?: get_the_content(null, false, $post_id));
    $img     = get_the_post_thumbnail_url($post_id, 'large');
    $url     = get_permalink($post_id);

    $start   = get_post_meta($post_id, '_lcgf_evento_data_inizio', true);
    $end     = get_post_meta($post_id, '_lcgf_evento_data_fine', true);
    $time    = get_post_meta($post_id, '_lcgf_evento_ora_inizio', true);
    $luogo   = get_post_meta($post_id, '_lcgf_evento_luogo', true);
    $indir   = get_post_meta($post_id, '_lcgf_evento_indirizzo', true);
    $citta   = get_post_meta($post_id, '_lcgf_evento_citta', true);
    $prezzo  = get_post_meta($post_id, '_lcgf_evento_prezzo', true);

    if (!$start) return;
    $start_iso = $start . ($time ? 'T' . $time . ':00' : '');
    $end_iso   = $end ? ($end . ($time ? 'T' . $time . ':00' : '')) : $start_iso;

    $schema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Event',
        'name'        => $title,
        'description' => mb_substr($desc, 0, 600),
        'startDate'   => $start_iso,
        'endDate'     => $end_iso,
        'eventStatus' => 'https://schema.org/EventScheduled',
        'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
        'url'         => $url,
    ];
    if ($img) $schema['image'] = [$img];
    if ($luogo) {
        $schema['location'] = [
            '@type' => 'Place',
            'name'  => $luogo,
            'address' => [
                '@type'           => 'PostalAddress',
                'streetAddress'   => $indir,
                'addressLocality' => $citta,
                'addressCountry'  => 'IT',
            ],
        ];
    }
    $schema['organizer'] = [
        '@type' => 'Organization',
        'name'  => 'La Compagnia del Gluten Free',
        'url'   => home_url('/'),
    ];
    if ($prezzo) {
        $schema['offers'] = [
            '@type' => 'Offer',
            'price' => preg_match('/[0-9]/', $prezzo) ? preg_replace('/[^0-9.]/', '', $prezzo) : '0',
            'priceCurrency' => 'EUR',
            'availability'  => 'https://schema.org/InStock',
            'url'           => $url,
            'description'   => $prezzo,
        ];
    }
    echo "\n" . '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
});

/* Seed primo evento al boot se nessuno presente */
add_action('admin_init', function () {
    if (get_option('lcgf_evento_seeded_v1') === 'done') return;
    if (!current_user_can('manage_options')) return;
    $count = wp_count_posts('lcgf_evento');
    $total = $count ? (int)$count->publish + (int)$count->draft : 0;
    if ($total > 0) {
        update_option('lcgf_evento_seeded_v1', 'done');
        return;
    }
    $post_id = wp_insert_post([
        'post_type'    => 'lcgf_evento',
        'post_status'  => 'draft',
        'post_title'   => 'Sagra del Pane — Edizione 2026',
        'post_content' => "Saremo presenti con il nostro stand alla Sagra del Pane, con assaggi gratuiti di pane, focacce, pinse e dolci senza glutine e senza lattosio. Vieni a scoprire i nostri prodotti e a parlare con il nostro team!",
        'post_excerpt' => 'Stand La Compagnia del Gluten Free con assaggi e prodotti in vendita.',
    ]);
    if ($post_id && !is_wp_error($post_id)) {
        update_post_meta($post_id, '_lcgf_evento_data_inizio', date('Y-m-d', strtotime('+45 days')));
        update_post_meta($post_id, '_lcgf_evento_ora_inizio',  '10:00');
        update_post_meta($post_id, '_lcgf_evento_luogo',       'Piazza centrale, Sagra del Pane');
        update_post_meta($post_id, '_lcgf_evento_citta',       'Ravanusa (AG)');
        update_post_meta($post_id, '_lcgf_evento_prezzo',      'Ingresso libero');
    }
    update_option('lcgf_evento_seeded_v1', 'done');
});

/**
 * Trigger manuale traduzione automatica IT -> EN/DE/FR via Gemini.
 * Accessibile da admin loggato: /wp-admin/?lcgf_action=translate
 * Streaming output (nessun timeout client). Idempotente.
 */
add_action('admin_init', function () {
    if (!isset($_GET['lcgf_action']) || $_GET['lcgf_action'] !== 'translate') return;
    if (!current_user_can('manage_options')) wp_die('Non autorizzato');

    @set_time_limit(0);
    @ignore_user_abort(true);
    while (ob_get_level()) @ob_end_flush();
    header('Content-Type: text/plain; charset=utf-8');
    header('X-Accel-Buffering: no');
    echo "Avvio traduzione LCGF (può richiedere 3-4 minuti)...\n\n";
    @flush();

    $script = WP_CONTENT_DIR . '/themes/lcgf-child/lib/translate-lcgf.php';
    if (!file_exists($script)) {
        $alt = '/var/www/html/lcgf-scripts/translate-lcgf.php';
        if (file_exists($alt)) $script = $alt;
    }
    if (!file_exists($script)) {
        echo "ERROR: script non trovato\n"; exit;
    }

    // Emula CLI per il check interno dello script
    if (!defined('STDIN')) define('STDIN', fopen('php://memory', 'r'));
    if (php_sapi_name() !== 'cli') {
        // Force the script to run anche se non in CLI
        $GLOBALS['lcgf_force_cli'] = true;
    }

    include $script;
    echo "\nFatto.\n";
    exit;
});

/**
 * Conversione prodotti -> variabili (cheesecake/tiramisu/crostate).
 * Auto-run UNA volta, in modo affidabile: functions.php è sempre caricato e
 * sincronizzato col tema, a differenza del blocco background del docker-compose
 * (il cui stdout non è catturato dai log Coolify). Guardia su option v2 +
 * self-healing nello script (setta l'option solo se restano prodotti variabili).
 * L'output viene catturato in un'option leggibile via ?lcgf_action=variants_status.
 */
add_action('init', function () {
    if (get_option('lcgf_variants_v2')) return;
    if (!class_exists('WC_Product_Variable')) return; // WooCommerce non ancora pronto
    $script = get_stylesheet_directory() . '/lib/lcgf-variants.php';
    if (!file_exists($script)) {
        $alt = '/var/www/html/lcgf-scripts/lcgf-variants.php';
        if (file_exists($alt)) $script = $alt; else return;
    }
    @set_time_limit(120);
    ob_start();
    try { include $script; } catch (Throwable $e) { echo "\n[FATAL] " . $e->getMessage() . "\n"; }
    $out = ob_get_clean();
    update_option('lcgf_variants_result', current_time('mysql') . "\n" . $out);
}, 99);

/**
 * Lettura sola dell'esito dell'ultima conversione varianti (no auth: read-only,
 * nessun dato sensibile). /?lcgf_action=variants_status
 */
add_action('init', function () {
    if (($_GET['lcgf_action'] ?? '') !== 'variants_status') return;
    header('Content-Type: text/plain; charset=utf-8');
    echo "lcgf_variants_v2 = " . (get_option('lcgf_variants_v2') ?: '(non settata)') . "\n\n";
    echo get_option('lcgf_variants_result') ?: '(nessun risultato registrato)';
    exit;
});

/**
 * Impostazioni account/checkout WooCommerce — applicate UNA volta (guardia option).
 * Abilita registrazione self-service + guest checkout + login/registrazione dal
 * checkout, e rinomina la pagina account in italiano. Pattern affidabile (init).
 */
add_action('init', function () {
    if (get_option('lcgf_acct_v1')) return;
    if (!function_exists('WC')) return; // WooCommerce non ancora pronto
    update_option('woocommerce_enable_myaccount_registration', 'yes');     // registrazione da /my-account/
    update_option('woocommerce_registration_generate_username', 'yes');    // username automatico dall'email
    update_option('woocommerce_registration_generate_password', 'no');     // l'utente sceglie la password
    update_option('woocommerce_enable_signup_and_login_from_checkout', 'yes'); // crea account al checkout
    update_option('woocommerce_enable_checkout_login_reminder', 'yes');     // promemoria login al checkout
    update_option('woocommerce_enable_guest_checkout', 'yes');             // consenti acquisto come ospite

    // Rinomina la pagina "My account" -> "Il mio account" (slug invariato).
    $acc_id = function_exists('wc_get_page_id') ? wc_get_page_id('myaccount') : 0;
    if ($acc_id > 0) {
        $p = get_post($acc_id);
        if ($p && $p->post_title !== 'Il mio account') {
            wp_update_post(['ID' => $acc_id, 'post_title' => 'Il mio account']);
        }
    }
    update_option('lcgf_acct_v1', current_time('mysql'));
}, 100);

/**
 * Importa le BOZZE legali nelle pagine IT (condizioni, cookie, privacy, recesso,
 * spedizioni) leggendo i file `legal/*.html` del tema (blocchi Gutenberg). Le
 * pagine erano vuote. Testo di prova, da far revisionare al commercialista.
 * Idempotente: riempie solo se la pagina è vuota (non sovrascrive contenuto reale).
 */
add_action('init', function () {
    if (get_option('lcgf_legal_import_v1')) return;
    $map = [
        'condizioni' => 'condizioni.html',
        'cookie'     => 'cookie.html',
        'privacy'    => 'privacy.html',
        'recesso'    => 'recesso.html',
        'spedizioni' => 'spedizioni.html',
    ];
    $dir = get_stylesheet_directory() . '/legal/';
    $filled = 0;
    foreach ($map as $slug => $file) {
        $q = get_posts(['post_type'=>'page','name'=>$slug,'post_status'=>'publish','numberposts'=>1,'suppress_filters'=>true]);
        if (!$q) continue;
        $p = $q[0];
        if (strlen(trim(wp_strip_all_tags($p->post_content))) > 60) continue; // ha già contenuto reale
        $path = $dir . $file;
        if (!file_exists($path)) continue;
        $html = file_get_contents($path);
        if (!$html) continue;
        wp_update_post(['ID' => $p->ID, 'post_content' => $html]);
        $filled++;
    }
    update_option('lcgf_legal_import_v1', current_time('mysql') . " ({$filled} pagine)");
}, 105);

/**
 * Ripara i collegamenti Polylang delle pagine tradotte. Una run di traduzione
 * precedente aveva creato le pagine EN/DE/FR (slug tradotti) ma le aveva
 * lasciate etichettate "it" e SCOLLEGATE dall'originale, rompendo language
 * switcher e hreflang (solo la pagina ABC risultava collegata). Qui assegniamo
 * la lingua corretta in base allo slug e ricostruiamo i gruppi di traduzione.
 * Idempotente, guardato da option. Pattern affidabile su init (vale anche su
 * Keliweb dopo la migrazione).
 */
add_action('init', function () {
    if (get_option('lcgf_pll_relink_v1')) return;
    if (!function_exists('pll_set_post_language') || !function_exists('pll_save_post_translations')) return;
    if (!function_exists('pll_languages_list') || count(pll_languages_list()) < 2) return;

    // slug IT (master) => [lingua => slug tradotto]
    $groups = [
        'chi-siamo'   => ['en' => 'about-us',                  'de' => 'uber-uns',                  'fr' => 'qui-sommes-nous'],
        'contatti'    => ['en' => 'contacts',                  'de' => 'kontakt',                   'fr' => 'contacts-2'],
        'faq'         => ['en' => 'frequently-asked-questions','de' => 'haufig-gestellte-fragen',   'fr' => 'foire-aux-questions'],
        'spedizioni'  => ['en' => 'shipping-and-returns',      'de' => 'versand-und-rucksendungen', 'fr' => 'livraisons-et-retours'],
        'privacy'     => ['en' => 'privacy-policy-2',          'de' => 'datenschutzerklarung',      'fr' => 'politique-de-confidentialite'],
        'cookie'      => ['en' => 'cookie-policy',             'de' => 'cookie-richtlinie',         'fr' => 'politique-en-matiere-de-cookies'],
        'recesso'     => ['en' => 'right-of-withdrawal',       'de' => 'widerrufsrecht',            'fr' => 'droit-de-retractation'],
        'condizioni'  => ['en' => 'terms-of-sale',             'de' => 'verkaufsbedingungen',       'fr' => 'conditions-de-vente'],
        'negozio'     => ['en' => 'shop',                      'de' => 'shop-2',                    'fr' => 'boutique'],
        'carrello'    => ['en' => 'cart',                      'de' => 'warenkorb',                 'fr' => 'panier'],
        'pagamento'   => ['en' => 'payment',                   'de' => 'zahlung',                   'fr' => 'paiement'],
        'mio-account' => ['en' => 'my-account',                'de' => 'mein-konto',                'fr' => 'mon-compte'],
    ];

    // Resolver per slug che ignora i filtri di lingua di Polylang (gli slug sono univoci).
    $resolve = function ($slug) {
        $q = get_posts([
            'post_type'       => 'page',
            'name'            => $slug,
            'post_status'     => 'publish',
            'numberposts'     => 1,
            'suppress_filters'=> true,
        ]);
        return $q ? $q[0] : null;
    };

    $done = 0;
    foreach ($groups as $it_slug => $tr) {
        $it = $resolve($it_slug);
        if (!$it) continue;
        pll_set_post_language($it->ID, 'it');
        $map = ['it' => $it->ID];
        foreach ($tr as $lang => $slug) {
            $p = $resolve($slug);
            if (!$p) continue;
            pll_set_post_language($p->ID, $lang);
            $map[$lang] = $p->ID;
        }
        if (count($map) > 1) {
            pll_save_post_translations($map);
            $done++;
        }
    }
    update_option('lcgf_pll_relink_v1', current_time('mysql') . " ({$done} gruppi)");
}, 110);

/**
 * Assegna il template page-faq.php alle pagine FAQ (it/en/de/fr). La pagina
 * /faq/ era uno stub; ora riusa le FAQ multilingua (chiavi faq_* di lcgf_t).
 * Idempotente, guardato da option.
 */
add_action('init', function () {
    if (get_option('lcgf_faq_tpl_v2')) return;
    // slug FAQ => lingua (per impostare una meta description pulita al posto dello stub)
    $faq = [
        'faq'                        => 'it',
        'frequently-asked-questions' => 'en',
        'haufig-gestellte-fragen'    => 'de',
        'foire-aux-questions'        => 'fr',
    ];
    $intro = [
        'it' => 'Le risposte alle domande più comuni su prodotti senza glutine, celiachia, spedizioni e conservazione.',
        'en' => 'Answers to the most common questions about gluten-free products, coeliac disease, shipping and storage.',
        'de' => 'Antworten auf die häufigsten Fragen zu glutenfreien Produkten, Zöliakie, Versand und Aufbewahrung.',
        'fr' => 'Les réponses aux questions les plus fréquentes sur les produits sans gluten, la maladie cœliaque, la livraison et la conservation.',
    ];
    $n = 0;
    foreach ($faq as $slug => $lang) {
        $q = get_posts(['post_type'=>'page','name'=>$slug,'numberposts'=>1,'post_status'=>'publish','suppress_filters'=>true]);
        if (!$q) continue;
        update_post_meta($q[0]->ID, '_wp_page_template', 'page-faq.php');
        // Sostituisce lo stub ("da personalizzare") con un'introduzione pulita
        // (il template non stampa the_content: serve solo per meta/SEO).
        wp_update_post(['ID' => $q[0]->ID, 'post_content' => '<!-- wp:paragraph --><p>' . esc_html($intro[$lang]) . '</p><!-- /wp:paragraph -->']);
        $n++;
    }
    update_option('lcgf_faq_tpl_v2', current_time('mysql') . " ({$n} pagine)");
}, 115);

/**
 * Ricerca sito "su tutto": la ricerca principale include prodotti, pagine e
 * articoli (così trova prodotti, descrizioni, info legali/pagamento, ecc.).
 * Esclude le pagine funzionali WooCommerce (shop/carrello/checkout/account)
 * dai risultati per non sporcarli. Polylang filtra già per lingua corrente.
 */
add_action('pre_get_posts', function ($q) {
    if (is_admin() || !$q->is_main_query() || !$q->is_search()) return;
    $q->set('post_type', ['product', 'page', 'post']);
    $q->set('posts_per_page', 12);
    if (function_exists('wc_get_page_id')) {
        $exclude = [];
        foreach (['shop', 'cart', 'checkout', 'myaccount'] as $k) {
            $id = wc_get_page_id($k);
            if ($id && $id > 0) {
                $exclude[] = $id;
                if (function_exists('pll_get_post_translations')) {
                    foreach (pll_get_post_translations($id) as $tid) $exclude[] = (int)$tid;
                }
            }
        }
        if ($exclude) $q->set('post__not_in', array_values(array_unique($exclude)));
    }
});

/**
 * Messaggio di benvenuto brandizzato in cima alla dashboard "Il mio account".
 */
add_action('woocommerce_account_dashboard', function () {
    $u = wp_get_current_user();
    $nome = $u && $u->display_name ? esc_html($u->display_name) : 'benvenuto';
    echo '<div style="background:var(--c-cream-2,#F4EDDC);border:1px solid var(--c-line,#E6DECB);border-radius:14px;padding:18px 22px;margin-bottom:22px">'
       . '<strong style="font-family:var(--f-display,Georgia);font-size:1.15rem;color:var(--c-olive-deep,#364E25)">Ciao ' . $nome . '! 🌾</strong>'
       . '<p style="margin:6px 0 0;color:var(--c-ink-soft,#3D362C);font-size:.95rem">Da qui gestisci i tuoi ordini, gli indirizzi di spedizione e i dati del tuo account. Per qualsiasi cosa scrivici su <a href="https://wa.me/393276999897" style="color:var(--c-olive-deep,#364E25);font-weight:600">WhatsApp</a>.</p>'
       . '</div>';
}, 5);

/* ============================================================
   SEO + scoperta da motori AI (GEO/AEO)
   ============================================================ */

// Keyword principali (riusate in meta + llms.txt)
function lcgf_seo_keywords() {
    return 'prodotti senza glutine, senza lattosio, celiachia, prodotti per celiaci, gluten free, '
         . 'pane senza glutine, pizza senza glutine, pinsa senza glutine, focaccia senza glutine, '
         . 'dolci senza glutine, cheesecake senza glutine, tiramisù senza glutine, AIC, '
         . 'Associazione Italiana Celiachia, prodotti mutuabili celiachia, buono celiachia, '
         . 'laboratorio senza glutine, surgelati senza glutine, Sicilia, Ravanusa, '
         . 'Agrigento, spedizione in tutta Italia';
}

// 1) Titolo corretto per la pagina account (Yoast sovrascrive il post_title),
//    localizzato per la lingua corrente (IT/EN/DE/FR).
add_filter('wpseo_title', function ($title) {
    if (function_exists('is_account_page') && is_account_page()) {
        $l = function_exists('pll_current_language') ? pll_current_language('slug') : 'it';
        $map = [
            'it' => 'Il mio account',
            'en' => 'My account',
            'de' => 'Mein Konto',
            'fr' => 'Mon compte',
        ];
        $name = $map[$l] ?? $map['it'];
        return $name . ' — La Compagnia del Gluten Free';
    }
    return $title;
});

// 2) Meta description ricca di fallback (home + shop) se non impostata in Yoast
add_filter('wpseo_metadesc', function ($desc) {
    if ($desc) return $desc;
    if (is_front_page()) {
        return 'Prodotti artigianali senza glutine e senza lattosio: pane, pinsa, pizza, focacce, '
             . 'cornetti, tiramisù e cheesecake da laboratorio esclusivamente gluten free. Accreditati AIC, '
             . 'prodotti mutuabili dal Servizio Sanitario Nazionale. Spedizione in tutta Italia.';
    }
    if (function_exists('is_shop') && is_shop()) {
        return 'Catalogo di prodotti senza glutine e senza lattosio: pane, basi pizza, focacce e dolci '
             . 'artigianali. Laboratorio dedicato, accreditati AIC. Spedizione in tutta Italia.';
    }
    // Pagine prodotto: usa la descrizione breve/estratto del prodotto, con fallback
    // (Lighthouse segnalava "Document does not have a meta description" sui prodotti).
    if (function_exists('is_product') && is_product()) {
        $p = function_exists('wc_get_product') ? wc_get_product(get_the_ID()) : null;
        $src = '';
        if ($p) $src = $p->get_short_description() ?: $p->get_description();
        if (!$src) $src = get_the_excerpt() ?: get_the_content();
        $src = trim(wp_strip_all_tags($src));
        if ($src) return wp_trim_words($src, 30, '…');
        // Fallback generico con il nome del prodotto
        return get_the_title() . ' — prodotto artigianale senza glutine e senza lattosio di La Compagnia del Gluten Free. Laboratorio dedicato, accreditati AIC, spedizione in tutta Italia.';
    }
    return $desc;
});

// 3) Meta keywords (richiesta cliente) — home + shop
add_action('wp_head', function () {
    if (is_front_page() || (function_exists('is_shop') && is_shop())) {
        echo '<meta name="keywords" content="' . esc_attr(lcgf_seo_keywords()) . '">' . "\n";
    }
}, 1);

// 4) Arricchisce lo schema Organization GIA' generato da Yoast (niente duplicati)
add_filter('wpseo_schema_organization', function ($data) {
    $data['description'] = 'Produzione e vendita online di prodotti artigianali senza glutine e senza lattosio '
        . '(pane, basi pizza, focacce, dolci) in laboratorio esclusivamente gluten free. Accreditati AIC; '
        . 'prodotti mutuabili dal Servizio Sanitario Nazionale.';
    $data['knowsAbout'] = ['Celiachia', 'Alimentazione senza glutine', 'Prodotti senza lattosio', 'Prodotti mutuabili SSN'];
    $data['slogan'] = 'Mangia senza glutine, ma con gusto!';
    $data['areaServed'] = ['@type' => 'Country', 'name' => 'Italia'];
    $data['address'] = [
        '@type' => 'PostalAddress',
        'streetAddress' => 'Via Angelo Giovanni Testasecca 7',
        'addressLocality' => 'Ravanusa',
        'postalCode' => '92029',
        'addressRegion' => 'AG',
        'addressCountry' => 'IT',
    ];
    $data['telephone'] = '+39 351 358 2074';
    $data['sameAs'] = [
        'https://www.instagram.com/',
        'https://www.facebook.com/',
        'https://www.tiktok.com/',
    ];
    return $data;
});

// 5) robots.txt: consenti i crawler AI (discoverability) + riferimento sitemap
add_filter('robots_txt', function ($output, $public) {
    if (!$public) return $output;
    $bots = ['GPTBot', 'OAI-SearchBot', 'ChatGPT-User', 'ClaudeBot', 'Claude-Web', 'PerplexityBot', 'Google-Extended', 'Applebot-Extended', 'CCBot'];
    $extra = "\n# Crawler AI — consentiti per la scoperta tramite assistenti AI\n";
    foreach ($bots as $b) { $extra .= "User-agent: {$b}\nAllow: /\n"; }
    $extra .= "\nSitemap: " . home_url('/sitemap_index.xml') . "\n";
    return $output . $extra;
}, 10, 2);

// 6) /llms.txt — riepilogo strutturato del business per gli assistenti AI
add_action('template_redirect', function () {
    $path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    if ($path !== 'llms.txt') return;
    status_header(200);
    nocache_headers();
    header('Content-Type: text/plain; charset=utf-8');
    $home = home_url('/');
    echo "# La Compagnia del Gluten Free — Mangia con Gusto\n\n";
    echo "> E-commerce artigianale di prodotti SENZA GLUTINE e SENZA LATTOSIO, prodotti in un laboratorio esclusivamente gluten free (nessuna contaminazione crociata). Accreditati AIC (Associazione Italiana Celiachia); diversi prodotti sono mutuabili dal Servizio Sanitario Nazionale (spendibili col buono celiachia, anche in farmacia). Sede: Ravanusa (AG), Sicilia. Spedizione in tutta Italia.\n\n";
    echo "## Per chi\nPersone celiache e con intolleranza al lattosio, famiglie e genitori di bambini celiaci, farmacie.\n\n";
    echo "## Categorie e prodotti\n";
    echo "- Pane & Basi: Pinsa Romana, Pan Focaccia, Focaccia Rotonda, Base Pizza, Pane Filoncino, Pane Rosetta, Box Family (assortimento)\n";
    echo "- Dolci & Colazione: Brioche col Tuppo, Cornetto, Crostate (vari formati), Biscotti gocce di cioccolato, Tiramisù (mono/torta), Cheesecake (5 gusti)\n\n";
    echo "## Certificazioni e garanzie\n";
    echo "- Accreditati AIC — Associazione Italiana Celiachia\n- Prodotti mutuabili dal Servizio Sanitario Nazionale\n- Laboratorio 100% senza glutine, tutti i prodotti anche senza lattosio\n\n";
    echo "## Contatti\n- Sito: {$home}\n- WhatsApp: +39 327 699 9897\n- Negozio: {$home}negozio/\n- Chi siamo: {$home}chi-siamo/\n\n";
    echo "## Parole chiave\n" . lcgf_seo_keywords() . "\n";
    exit;
});

// 6b) FAQPage schema sulla home (coerente con la FAQ visibile, lingua corrente)
add_action('wp_head', function () {
    if (!is_front_page()) return;
    $qa = [];
    for ($i = 1; $i <= 6; $i++) {
        $qa[] = ['@type' => 'Question', 'name' => wp_strip_all_tags(lcgf_t('faq_q' . $i)), 'acceptedAnswer' => ['@type' => 'Answer', 'text' => wp_strip_all_tags(lcgf_t('faq_a' . $i))]];
    }
    $schema = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $qa];
    echo "\n<script type=\"application/ld+json\">" . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "</script>\n";
});

/**
 * Restituisce il markup di un logo certificazione: l'immagine ufficiale se il
 * file è presente in /assets/certs/, altrimenti un placeholder pulito (così la
 * sezione resta presentabile finché il cliente non carica il file ufficiale).
 * I loghi ufficiali AIC e Ministero della Salute sono marchi protetti: vanno
 * forniti dal cliente, non ricreati.
 */
function lcgf_cert_logo($file, $alt) {
    $dir = get_stylesheet_directory() . '/assets/certs/' . $file;
    $uri = get_stylesheet_directory_uri() . '/assets/certs/' . $file;
    if (file_exists($dir)) {
        return '<img src="' . esc_url($uri) . '" alt="' . esc_attr($alt) . '" loading="lazy">';
    }
    return '<span class="lcgf-cert-ph" title="Carica ' . esc_attr($file) . ' in assets/certs/">' . esc_html($alt) . '</span>';
}

/**
 * Crea (una volta) la pagina "L'ABC della dieta senza glutine" e le assegna il
 * template page-abc-celiachia.php. Guardia option lcgf_abc_v1.
 */
add_action('init', function () {
    if (get_option('lcgf_abc_v1')) return;
    $slug = 'abc-dieta-senza-glutine';
    if (!get_page_by_path($slug)) {
        $id = wp_insert_post([
            'post_title'   => "L'ABC della dieta senza glutine",
            'post_name'    => $slug,
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => '',
        ]);
        if ($id && !is_wp_error($id)) {
            update_post_meta($id, '_wp_page_template', 'page-abc-celiachia.php');
        }
    }
    update_option('lcgf_abc_v1', current_time('mysql'));
}, 103);

/* ============================================================
   F3: recensioni · back-in-stock · pulizia plugin · filtri catalogo
   ============================================================ */

// (A) Once-run (lcgf_f3_v2): recensioni ON + apri commenti sui prodotti +
//     tabella back-in-stock + disattiva Jetpack/POS.
add_action('init', function () {
    if (get_option('lcgf_f3_v2')) return;
    if (!function_exists('WC')) return;
    global $wpdb;
    update_option('woocommerce_enable_reviews', 'yes');
    update_option('woocommerce_enable_review_rating', 'yes');
    update_option('woocommerce_review_rating_required', 'yes');
    $wpdb->query("UPDATE {$wpdb->posts} SET comment_status='open' WHERE post_type='product'");

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $t = $wpdb->prefix . 'lcgf_bis';
    dbDelta("CREATE TABLE {$t} (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      product_id BIGINT UNSIGNED NOT NULL,
      email VARCHAR(190) NOT NULL,
      created_at DATETIME NOT NULL,
      notified_at DATETIME NULL,
      PRIMARY KEY  (id),
      UNIQUE KEY prod_email (product_id,email)
    ) " . $wpdb->get_charset_collate() . ";");

    if (!function_exists('deactivate_plugins')) require_once ABSPATH . 'wp-admin/includes/plugin.php';
    foreach ((array) get_option('active_plugins', []) as $p) {
        $lp = strtolower($p);
        if (strpos($lp, 'jetpack') !== false || strpos($lp, 'point-of-sale') !== false
            || strpos($lp, 'woocommerce-pos') !== false || strpos($lp, 'wc-pos') !== false) {
            deactivate_plugins($p, true);
        }
    }
    update_option('lcgf_f3_v2', current_time('mysql'));
}, 104);

// (B) Back-in-stock: salva iscrizione (AJAX, anche non loggati)
function lcgf_bis_save() {
    check_ajax_referer('lcgf_bis', 'nonce');
    $pid = absint($_POST['product_id'] ?? 0);
    $email = sanitize_email($_POST['email'] ?? '');
    if (!$pid || !is_email($email)) wp_send_json_error(['msg' => 'Inserisci un indirizzo email valido.'], 400);
    global $wpdb; $t = $wpdb->prefix . 'lcgf_bis';
    $wpdb->query($wpdb->prepare("INSERT IGNORE INTO {$t} (product_id,email,created_at) VALUES (%d,%s,%s)", $pid, $email, current_time('mysql')));
    wp_send_json_success(['msg' => 'Perfetto! Ti avviseremo via email appena torna disponibile.']);
}
add_action('wp_ajax_lcgf_bis', 'lcgf_bis_save');
add_action('wp_ajax_nopriv_lcgf_bis', 'lcgf_bis_save');

// (C) Restock: avvisa gli iscritti quando il prodotto torna disponibile
add_action('woocommerce_product_set_stock_status', function ($product_id, $status) {
    if ($status !== 'instock') return;
    global $wpdb; $t = $wpdb->prefix . 'lcgf_bis';
    $rows = $wpdb->get_results($wpdb->prepare("SELECT id,email FROM {$t} WHERE product_id=%d AND notified_at IS NULL", $product_id));
    if (!$rows) return;
    $p = wc_get_product($product_id); if (!$p) return;
    $nm = $p->get_name(); $url = get_permalink($product_id);
    foreach ($rows as $r) {
        wp_mail($r->email, '🌾 ' . $nm . ' è di nuovo disponibile!',
            "Buone notizie!\n\n\"{$nm}\" è di nuovo disponibile su La Compagnia del Gluten Free.\n\nAcquistalo qui: {$url}\n\nA presto!");
        $wpdb->update($t, ['notified_at' => current_time('mysql')], ['id' => $r->id]);
    }
}, 10, 2);

// Form back-in-stock (usato nel template prodotto quando esaurito)
function lcgf_bis_form($product) {
    $pid = (int) $product->get_id();
    $nonce = wp_create_nonce('lcgf_bis');
    $ajax = admin_url('admin-ajax.php');
    ob_start(); ?>
    <div class="lcgf-bis" style="margin:24px 0;max-width:440px">
      <p style="font-weight:700;color:var(--c-terracotta);margin-bottom:6px"><?php echo lcgf_t('bis_out'); ?></p>
      <p style="color:var(--c-ink-soft);font-size:.95rem;margin-bottom:12px"><?php echo lcgf_t('bis_text'); ?></p>
      <form class="lcgf-bis-form" onsubmit="return lcgfBis(event,<?php echo $pid; ?>)" style="display:flex;gap:10px;flex-wrap:wrap">
        <input type="email" required placeholder="<?php echo esc_attr(lcgf_t('nl_email')); ?>" class="lcgf-bis-email" style="flex:1;min-width:200px">
        <button type="submit" class="btn"><?php echo lcgf_t('bis_btn'); ?></button>
      </form>
      <p class="lcgf-bis-msg" style="margin-top:10px;font-size:.9rem"></p>
    </div>
    <script>
    function lcgfBis(e,pid){e.preventDefault();var f=e.target,em=f.querySelector('.lcgf-bis-email').value,m=f.parentNode.querySelector('.lcgf-bis-msg');var d=new FormData();d.append('action','lcgf_bis');d.append('nonce','<?php echo esc_js($nonce); ?>');d.append('product_id',pid);d.append('email',em);fetch('<?php echo esc_url($ajax); ?>',{method:'POST',body:d}).then(r=>r.json()).then(j=>{m.textContent=(j.data&&j.data.msg)||'Fatto';m.style.color=j.success?'#4D8B5A':'#B14545';if(j.success)f.reset();}).catch(function(){m.textContent='Errore, riprova.';m.style.color='#B14545';});return false;}
    </script>
    <?php
    return ob_get_clean();
}

// (D) Catalogo: barra filtri per categoria sopra la griglia shop
add_action('woocommerce_before_shop_loop', function () {
    if (!function_exists('is_shop')) return;
    if (!is_shop() && !is_product_category()) return;
    $cats = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true]);
    if (is_wp_error($cats) || !$cats) return;
    $cur = is_product_category() ? get_queried_object_id() : 0;
    $shop = get_permalink(wc_get_page_id('shop'));
    echo '<nav class="lcgf-shop-filters"><a class="lcgf-chip' . ($cur ? '' : ' is-active') . '" href="' . esc_url($shop) . '">Tutti</a>';
    foreach ($cats as $c) {
        if (in_array(strtolower($c->slug), ['uncategorized', 'senza-categoria'], true)) continue;
        echo '<a class="lcgf-chip' . ($c->term_id === $cur ? ' is-active' : '') . '" href="' . esc_url(get_term_link($c)) . '">' . esc_html($c->name) . '</a>';
    }
    echo '</nav>';
}, 5);

/**
 * Pulizia cache/SEO post-migrazione dominio (una volta, guardia lcgf_flush_v1):
 * resetta gli indexable Yoast (rigenera canonical/hreflang con l'URL corrente,
 * gluten) e svuota transient + object cache (elimina i namespace REST stantii
 * di plugin ormai rimossi e gli URL sslip residui).
 */
add_action('init', function () {
    if (get_option('lcgf_flush_v1')) return;
    global $wpdb;
    foreach (['yoast_indexable', 'yoast_indexable_hierarchy'] as $tbl) {
        $t = $wpdb->prefix . $tbl;
        if ($wpdb->get_var("SHOW TABLES LIKE '{$t}'") === $t) {
            $wpdb->query("TRUNCATE TABLE {$t}");
        }
    }
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '\\_transient\\_%' OR option_name LIKE '\\_site\\_transient\\_%'");
    if (function_exists('wp_cache_flush')) wp_cache_flush();
    update_option('lcgf_flush_v1', current_time('mysql'));
}, 105);

/* ============================================================
   i18n stringhe del tema (IT/EN/DE/FR) — Polylang non traduce i
   testi fissi dei template, quindi li gestiamo con una mappa.
   Uso: <?php echo lcgf_t('chiave'); ?>  (output già pronto, NON ri-escapare
   le chiavi che contengono HTML volutamente, es. hero_h1/cert_sub).
   ============================================================ */
function lcgf_lang() {
    $l = function_exists('pll_current_language') ? pll_current_language('slug') : '';
    return in_array($l, ['it', 'en', 'de', 'fr'], true) ? $l : 'it';
}
function lcgf_t($key) {
    static $T = null;
    if ($T === null) $T = lcgf_i18n_strings();
    $lang = lcgf_lang();
    if (isset($T[$key][$lang])) return $T[$key][$lang];
    if (isset($T[$key]['it'])) return $T[$key]['it'];
    return $key;
}
function lcgf_i18n_strings() {
    return [
        // Header / nav
        'nav_catalogo' => ['it' => 'Catalogo', 'en' => 'Catalog', 'de' => 'Katalog', 'fr' => 'Catalogue'],
        'nav_fiere'    => ['it' => 'Fiere &amp; Eventi', 'en' => 'Fairs &amp; Events', 'de' => 'Messen &amp; Events', 'fr' => 'Foires &amp; Événements'],
        'nav_chisiamo' => ['it' => 'Chi siamo', 'en' => 'About us', 'de' => 'Über uns', 'fr' => 'À propos'],
        'nav_contatti' => ['it' => 'Contatti', 'en' => 'Contact', 'de' => 'Kontakt', 'fr' => 'Contact'],
        'aria_search'  => ['it' => 'Cerca', 'en' => 'Search', 'de' => 'Suche', 'fr' => 'Rechercher'],
        'aria_account' => ['it' => 'Account', 'en' => 'Account', 'de' => 'Konto', 'fr' => 'Compte'],
        'aria_cart'    => ['it' => 'Carrello', 'en' => 'Cart', 'de' => 'Warenkorb', 'fr' => 'Panier'],
        // Hero
        'hero_eyebrow' => ['it' => 'Mangia con Gusto · Senza glutine e senza lattosio', 'en' => 'Mangia con Gusto · Gluten-free and lactose-free', 'de' => 'Mangia con Gusto · Glutenfrei und laktosefrei', 'fr' => 'Mangia con Gusto · Sans gluten et sans lactose'],
        'hero_h1'      => ['it' => 'Senza glutine,<br/><em>ma con gusto.</em>', 'en' => 'Gluten-free,<br/><em>but full of flavor.</em>', 'de' => 'Glutenfrei,<br/><em>aber voller Geschmack.</em>', 'fr' => 'Sans gluten,<br/><em>mais plein de goût.</em>'],
        'hero_lead'    => ['it' => 'Pinsa romana, focacce, basi pizza, cornetti, tiramisù, cheesecake: il nostro panificato e i nostri dolci sono prodotti in un laboratorio dedicato, privi di contaminazioni, completamente senza glutine e senza lattosio.', 'en' => 'Roman pinsa, focaccia, pizza bases, croissants, tiramisù, cheesecake: our bakery and desserts are made in a dedicated lab, contamination-free, completely gluten-free and lactose-free.', 'de' => 'Pinsa Romana, Focaccia, Pizzaböden, Croissants, Tiramisù, Cheesecake: unsere Backwaren und Desserts entstehen in einem eigenen Labor, frei von Kontamination, vollständig glutenfrei und laktosefrei.', 'fr' => 'Pinsa romaine, focaccia, bases pizza, croissants, tiramisù, cheesecake : notre boulangerie et nos desserts sont produits dans un laboratoire dédié, sans contamination, entièrement sans gluten et sans lactose.'],
        'hero_cta1'    => ['it' => 'Scopri il catalogo', 'en' => 'Browse the catalog', 'de' => 'Katalog entdecken', 'fr' => 'Découvrir le catalogue'],
        'our_story'    => ['it' => 'La nostra storia', 'en' => 'Our story', 'de' => 'Unsere Geschichte', 'fr' => 'Notre histoire'],
        'stat_products' => ['it' => 'Prodotti artigianali', 'en' => 'Artisan products', 'de' => 'Handwerkliche Produkte', 'fr' => 'Produits artisanaux'],
        'stat_gluten'  => ['it' => 'Glutine · 0% lattosio', 'en' => 'Gluten · 0% lactose', 'de' => 'Gluten · 0% Laktose', 'fr' => 'Gluten · 0% lactose'],
        'stat_years'   => ['it' => 'Anni di esperienza', 'en' => 'Years of experience', 'de' => 'Jahre Erfahrung', 'fr' => "Ans d'expérience"],
        'gluten_free'  => ['it' => 'Senza glutine', 'en' => 'Gluten-free', 'de' => 'Glutenfrei', 'fr' => 'Sans gluten'],
        'lactose_free' => ['it' => 'Senza lattosio', 'en' => 'Lactose-free', 'de' => 'Laktosefrei', 'fr' => 'Sans lactose'],
        'dedicated_lab' => ['it' => 'Laboratorio dedicato', 'en' => 'Dedicated lab', 'de' => 'Eigenes Labor', 'fr' => 'Laboratoire dédié'],
        'frozen_ready' => ['it' => "Surgelati pronti all'uso", 'en' => 'Ready-to-use frozen', 'de' => 'Tiefkühlprodukte, sofort verwendbar', 'fr' => "Surgelés prêts à l'emploi"],
        'exp_20y'      => ['it' => '20 anni di esperienza', 'en' => '20 years of experience', 'de' => '20 Jahre Erfahrung', 'fr' => "20 ans d'expérience"],
        // Categorie
        'cat_eyebrow'  => ['it' => 'Le nostre famiglie', 'en' => 'Our families', 'de' => 'Unsere Familien', 'fr' => 'Nos familles'],
        'cat_h2'       => ['it' => 'Pane, basi e dolci', 'en' => 'Bread, bases & desserts', 'de' => 'Brot, Böden & Süßes', 'fr' => 'Pain, bases et desserts'],
        'cat_sub'      => ['it' => "Surgelati pronti all'uso. Senza glutine, senza lattosio, senza compromessi sul gusto.", 'en' => 'Ready-to-use frozen. Gluten-free, lactose-free, with no compromise on taste.', 'de' => 'Tiefkühlprodukte, sofort verwendbar. Glutenfrei, laktosefrei, ohne Kompromisse beim Geschmack.', 'fr' => "Surgelés prêts à l'emploi. Sans gluten, sans lactose, sans compromis sur le goût."],
        'products'     => ['it' => 'prodotti', 'en' => 'products', 'de' => 'Produkte', 'fr' => 'produits'],
        // Featured
        'feat_eyebrow' => ['it' => 'In evidenza', 'en' => 'Featured', 'de' => 'Im Fokus', 'fr' => 'En vedette'],
        'feat_h2'      => ['it' => 'I più amati', 'en' => 'Most loved', 'de' => 'Die Beliebtesten', 'fr' => 'Les plus aimés'],
        'feat_sub'     => ['it' => 'I prodotti che i nostri clienti riordinano sempre.', 'en' => 'The products our customers reorder again and again.', 'de' => 'Die Produkte, die unsere Kunden immer wieder bestellen.', 'fr' => 'Les produits que nos clients recommandent toujours.'],
        'add_to_cart'  => ['it' => 'Aggiungi al carrello', 'en' => 'Add to cart', 'de' => 'In den Warenkorb', 'fr' => 'Ajouter au panier'],
        'view_all'     => ['it' => 'Vedi tutti i prodotti', 'en' => 'View all products', 'de' => 'Alle Produkte ansehen', 'fr' => 'Voir tous les produits'],
        // Storia
        'story_h2'     => ['it' => "Da un'esigenza personale, una passione per tutti.", 'en' => 'From a personal need, a passion for everyone.', 'de' => 'Aus einem persönlichen Bedürfnis, eine Leidenschaft für alle.', 'fr' => "D'un besoin personnel, une passion pour tous."],
        'story_p'      => ['it' => 'Da una esigenza personale, un\'esperienza ventennale e un gruppo di amici a cui piace sognare nasce "Mangia con Gusto - La Compagnia del Gluten Free". Quotidianamente ci impegniamo ad offrirvi prodotti gustosi e con materia prima di qualità.', 'en' => 'From a personal need, twenty years of experience and a group of friends who love to dream, "Mangia con Gusto - La Compagnia del Gluten Free" was born. Every day we strive to offer you tasty products made with quality ingredients.', 'de' => 'Aus einem persönlichen Bedürfnis, zwanzig Jahren Erfahrung und einer Gruppe von Freunden, die gerne träumen, entstand "Mangia con Gusto - La Compagnia del Gluten Free". Jeden Tag bemühen wir uns, Ihnen schmackhafte Produkte aus hochwertigen Zutaten anzubieten.', 'fr' => 'D\'un besoin personnel, de vingt ans d\'expérience et d\'un groupe d\'amis qui aiment rêver est né "Mangia con Gusto - La Compagnia del Gluten Free". Chaque jour, nous nous efforçons de vous offrir des produits savoureux avec des matières premières de qualité.'],
        'story_li1'    => ['it' => 'Laboratorio dedicato, privo di contaminazioni da glutine.', 'en' => 'Dedicated lab, free from gluten contamination.', 'de' => 'Eigenes Labor, frei von Glutenkontamination.', 'fr' => 'Laboratoire dédié, sans contamination par le gluten.'],
        'story_li2'    => ['it' => 'Tutti i prodotti sono anche senza lattosio.', 'en' => 'All products are lactose-free too.', 'de' => 'Alle Produkte sind auch laktosefrei.', 'fr' => 'Tous les produits sont aussi sans lactose.'],
        'story_li3'    => ['it' => 'Surgelati pronti all\'uso, fragranza appena sfornata.', 'en' => 'Ready-to-use frozen, freshly baked fragrance.', 'de' => 'Tiefkühlprodukte, sofort verwendbar, frisch gebackener Duft.', 'fr' => 'Surgelés prêts à l\'emploi, parfum tout juste sorti du four.'],
        'story_li4'    => ['it' => 'Anche fuori casa puoi mangiare buono e sano.', 'en' => 'Even away from home you can eat well and healthy.', 'de' => 'Auch unterwegs gut und gesund essen.', 'fr' => 'Même hors de chez vous, mangez bon et sain.'],
        'story_cta'    => ['it' => 'Conosci la Compagnia', 'en' => 'Get to know us', 'de' => 'Lernen Sie uns kennen', 'fr' => 'Découvrez la Compagnia'],
        // Certificazioni
        'cert_eyebrow' => ['it' => 'Sicurezza &amp; certificazioni', 'en' => 'Safety &amp; certifications', 'de' => 'Sicherheit &amp; Zertifizierungen', 'fr' => 'Sécurité &amp; certifications'],
        'cert_h2'      => ['it' => 'Di fiducia per chi vive la celiachia', 'en' => 'Trusted by those living with celiac disease', 'de' => 'Vertrauen für Menschen mit Zöliakie', 'fr' => 'De confiance pour ceux qui vivent la maladie cœliaque'],
        'cert_sub'     => ['it' => 'Un laboratorio <strong>esclusivamente senza glutine</strong>, accreditati <strong>AIC</strong> e con prodotti <strong>mutuabili dal Servizio Sanitario Nazionale</strong>: la serenità che cercano le persone celiache e i genitori dei bambini celiaci.', 'en' => 'An <strong>exclusively gluten-free</strong> lab, <strong>AIC</strong> accredited and with products <strong>reimbursable by the National Health Service</strong>: the peace of mind sought by celiacs and parents of celiac children.', 'de' => 'Ein <strong>ausschließlich glutenfreies</strong> Labor, <strong>AIC</strong>-akkreditiert und mit Produkten, die <strong>vom nationalen Gesundheitsdienst erstattet</strong> werden: die Sicherheit, die Zöliakie-Betroffene und Eltern zöliakiekranker Kinder suchen.', 'fr' => 'Un laboratoire <strong>exclusivement sans gluten</strong>, accrédité <strong>AIC</strong> et avec des produits <strong>remboursables par le Service de Santé National</strong> : la sérénité que recherchent les cœliaques et les parents d\'enfants cœliaques.'],
        'cert1_t'      => ['it' => 'Accreditati AIC', 'en' => 'AIC accredited', 'de' => 'AIC-akkreditiert', 'fr' => 'Accrédités AIC'],
        'cert1_d'      => ['it' => 'Associazione Italiana Celiachia: la garanzia riconosciuta dalla community dei celiaci e dalle famiglie.', 'en' => 'Italian Celiac Association: the guarantee recognized by the celiac community and families.', 'de' => 'Italienische Zöliakie-Vereinigung: die von der Zöliakie-Gemeinschaft und Familien anerkannte Garantie.', 'fr' => 'Association Italienne de la Maladie Cœliaque : la garantie reconnue par la communauté cœliaque et les familles.'],
        'cert2_t'      => ['it' => 'Prodotti mutuabili', 'en' => 'Reimbursable products', 'de' => 'Erstattungsfähige Produkte', 'fr' => 'Produits remboursables'],
        'cert2_d'      => ['it' => 'Erogabili dal Servizio Sanitario Nazionale: puoi spendere il buono celiachia, anche in farmacia.', 'en' => 'Provided by the National Health Service: you can use the celiac voucher, even at the pharmacy.', 'de' => 'Erhältlich über den nationalen Gesundheitsdienst: Sie können den Zöliakie-Gutschein auch in der Apotheke einlösen.', 'fr' => 'Délivrés par le Service de Santé National : vous pouvez utiliser le bon cœliaque, même en pharmacie.'],
        'cert3_t'      => ['it' => 'Laboratorio 100% senza glutine', 'en' => '100% gluten-free lab', 'de' => '100% glutenfreies Labor', 'fr' => 'Laboratoire 100% sans gluten'],
        'cert3_d'      => ['it' => 'Produzione <em>esclusivamente</em> gluten free: nessun rischio di contaminazione crociata.', 'en' => '<em>Exclusively</em> gluten-free production: no risk of cross-contamination.', 'de' => '<em>Ausschließlich</em> glutenfreie Produktion: kein Risiko einer Kreuzkontamination.', 'fr' => 'Production <em>exclusivement</em> sans gluten : aucun risque de contamination croisée.'],
        'cert_cta'     => ['it' => "Scopri l'ABC della dieta senza glutine", 'en' => 'Discover the ABC of the gluten-free diet', 'de' => 'Entdecke das ABC der glutenfreien Ernährung', 'fr' => 'Découvrez l\'ABC du régime sans gluten'],
        // USP
        'usp1_t' => ['it' => 'Spedizione veloce', 'en' => 'Fast shipping', 'de' => 'Schneller Versand', 'fr' => 'Livraison rapide'],
        'usp1_s' => ['it' => '24-48h in Italia', 'en' => '24-48h in Italy', 'de' => '24-48h in Italien', 'fr' => '24-48h en Italie'],
        'usp2_t' => ['it' => 'Soddisfatti o rimborsati', 'en' => 'Satisfied or refunded', 'de' => 'Zufrieden oder Geld zurück', 'fr' => 'Satisfait ou remboursé'],
        'usp2_s' => ['it' => 'Recesso entro 14 giorni', 'en' => 'Withdrawal within 14 days', 'de' => 'Widerruf innerhalb von 14 Tagen', 'fr' => 'Rétractation sous 14 jours'],
        'usp3_t' => ['it' => 'Pagamenti sicuri', 'en' => 'Secure payments', 'de' => 'Sichere Zahlungen', 'fr' => 'Paiements sécurisés'],
        'usp4_t' => ['it' => 'Supporto su WhatsApp', 'en' => 'WhatsApp support', 'de' => 'WhatsApp-Support', 'fr' => 'Assistance WhatsApp'],
        'usp4_s' => ['it' => 'Rispondiamo entro 1h', 'en' => 'We reply within 1h', 'de' => 'Wir antworten innerhalb 1 Std.', 'fr' => 'Nous répondons sous 1h'],
        // Testimonial
        'test_eyebrow' => ['it' => 'Le voci di chi ci sceglie', 'en' => 'The voices of those who choose us', 'de' => 'Die Stimmen derer, die uns wählen', 'fr' => 'Les voix de ceux qui nous choisissent'],
        'test_h2'      => ['it' => 'Mille storie senza glutine', 'en' => 'A thousand gluten-free stories', 'de' => 'Tausend glutenfreie Geschichten', 'fr' => 'Mille histoires sans gluten'],
        'test_q1'      => ['it' => '"La pinsa è la cosa più simile alla pizza vera che abbia mangiato in 10 anni di celiachia."', 'en' => '"The pinsa is the closest thing to real pizza I\'ve eaten in 10 years of celiac disease."', 'de' => '"Die Pinsa ist das, was echter Pizza am nächsten kommt, das ich in 10 Jahren Zöliakie gegessen habe."', 'fr' => '"La pinsa est ce qui ressemble le plus à une vraie pizza que j\'aie mangé en 10 ans de maladie cœliaque."'],
        'test_q2'      => ['it' => '"I cornetti sono perfetti, mio figlio celiaco ha pianto di gioia. Ordinerò di nuovo a Natale."', 'en' => '"The croissants are perfect, my celiac son cried with joy. I\'ll order again at Christmas."', 'de' => '"Die Croissants sind perfekt, mein Sohn mit Zöliakie weinte vor Freude. Ich bestelle zu Weihnachten wieder."', 'fr' => '"Les croissants sont parfaits, mon fils cœliaque a pleuré de joie. Je recommanderai à Noël."'],
        'test_q3'      => ['it' => '"Il tiramisù senza glutine e senza lattosio è straordinario. Si sente l\'amore in ogni cucchiaio."', 'en' => '"The gluten-free and lactose-free tiramisù is extraordinary. You can feel the love in every spoonful."', 'de' => '"Das glutenfreie und laktosefreie Tiramisù ist außergewöhnlich. Man schmeckt die Liebe in jedem Löffel."', 'fr' => '"Le tiramisù sans gluten et sans lactose est extraordinaire. On sent l\'amour à chaque cuillère."'],
        // Newsletter
        'nl_eyebrow'   => ['it' => 'Resta aggiornato', 'en' => 'Stay updated', 'de' => 'Bleib auf dem Laufenden', 'fr' => 'Restez informé'],
        'nl_h2'        => ['it' => 'Ricette, novità e -10% sul primo ordine', 'en' => 'Recipes, news and -10% on your first order', 'de' => 'Rezepte, Neuigkeiten und -10% auf die erste Bestellung', 'fr' => 'Recettes, nouveautés et -10% sur la première commande'],
        'nl_p'         => ['it' => 'Iscriviti alla newsletter: ogni mese ricette gluten free, anteprime sui nuovi arrivi e uno sconto di benvenuto.', 'en' => 'Subscribe to the newsletter: every month gluten-free recipes, previews of new arrivals and a welcome discount.', 'de' => 'Abonniere den Newsletter: jeden Monat glutenfreie Rezepte, Vorschauen auf Neuheiten und einen Willkommensrabatt.', 'fr' => 'Inscrivez-vous à la newsletter : chaque mois des recettes sans gluten, des avant-premières et une remise de bienvenue.'],
        'nl_email'     => ['it' => 'La tua email', 'en' => 'Your email', 'de' => 'Deine E-Mail', 'fr' => 'Votre e-mail'],
        'nl_btn'       => ['it' => 'Iscriviti', 'en' => 'Subscribe', 'de' => 'Abonnieren', 'fr' => "S'abonner"],
        'nl_done'      => ['it' => '✓ Iscritto', 'en' => '✓ Subscribed', 'de' => '✓ Abonniert', 'fr' => '✓ Inscrit'],
        'nl_gdpr'      => ['it' => 'Acconsento al trattamento dei dati per ricevere la newsletter.', 'en' => 'I consent to data processing to receive the newsletter.', 'de' => 'Ich stimme der Datenverarbeitung zum Erhalt des Newsletters zu.', 'fr' => 'J\'accepte le traitement des données pour recevoir la newsletter.'],
        'nl_privacy'   => ['it' => 'Informativa privacy', 'en' => 'Privacy policy', 'de' => 'Datenschutz', 'fr' => 'Politique de confidentialité'],
        'faq_eye'      => ['it' => 'Domande frequenti', 'en' => 'FAQ', 'de' => 'Häufige Fragen', 'fr' => 'Questions fréquentes'],
        'faq_h2'       => ['it' => 'Tutto quello che vuoi sapere', 'en' => 'Everything you want to know', 'de' => 'Alles, was du wissen möchtest', 'fr' => 'Tout ce que vous voulez savoir'],
        'faq_q1'       => ['it' => 'I prodotti sono adatti ai celiaci?', 'en' => 'Are the products suitable for coeliacs?', 'de' => 'Sind die Produkte für Zöliakie-Betroffene geeignet?', 'fr' => 'Les produits conviennent-ils aux cœliaques ?'],
        'faq_a1'       => ['it' => 'Sì. Produciamo in un laboratorio esclusivamente senza glutine, senza alcun rischio di contaminazione crociata, e siamo accreditati AIC (Associazione Italiana Celiachia).', 'en' => 'Yes. We produce in an exclusively gluten-free lab, with no risk of cross-contamination, and we are AIC (Italian Coeliac Association) accredited.', 'de' => 'Ja. Wir produzieren in einem ausschließlich glutenfreien Labor, ohne Risiko einer Kreuzkontamination, und sind von der AIC (Italienische Zöliakie-Vereinigung) akkreditiert.', 'fr' => 'Oui. Nous produisons dans un laboratoire exclusivement sans gluten, sans risque de contamination croisée, et nous sommes accrédités AIC (Association Italienne de la Maladie Cœliaque).'],
        'faq_q2'       => ['it' => 'Sono anche senza lattosio?', 'en' => 'Are they also lactose-free?', 'de' => 'Sind sie auch laktosefrei?', 'fr' => 'Sont-ils aussi sans lactose ?'],
        'faq_a2'       => ['it' => 'Sì: tutti i nostri prodotti sono senza glutine e senza lattosio.', 'en' => 'Yes: all our products are gluten-free and lactose-free.', 'de' => 'Ja: Alle unsere Produkte sind glutenfrei und laktosefrei.', 'fr' => 'Oui : tous nos produits sont sans gluten et sans lactose.'],
        'faq_q3'       => ['it' => 'Posso usare il buono celiachia? I prodotti sono mutuabili?', 'en' => 'Can I use the coeliac voucher? Are the products reimbursable?', 'de' => 'Kann ich den Zöliakie-Gutschein nutzen? Sind die Produkte erstattungsfähig?', 'fr' => 'Puis-je utiliser le bon cœliaque ? Les produits sont-ils remboursables ?'],
        'faq_a3'       => ['it' => 'Diversi nostri prodotti sono mutuabili dal Servizio Sanitario Nazionale e spendibili con il buono celiachia. Contattaci per sapere quali.', 'en' => 'Several of our products are reimbursable by the National Health Service and can be purchased with the coeliac voucher. Contact us to find out which ones.', 'de' => 'Mehrere unserer Produkte sind vom nationalen Gesundheitsdienst erstattungsfähig und mit dem Zöliakie-Gutschein erhältlich. Kontaktiere uns, um zu erfahren, welche.', 'fr' => 'Plusieurs de nos produits sont remboursables par le service de santé national et peuvent être achetés avec le bon cœliaque. Contactez-nous pour savoir lesquels.'],
        'faq_q4'       => ['it' => 'Fate spedizioni in tutta Italia?', 'en' => 'Do you ship throughout Italy?', 'de' => 'Versendet ihr in ganz Italien?', 'fr' => 'Livrez-vous dans toute l\'Italie ?'],
        'faq_a4'       => ['it' => 'Sì, spediamo in tutta Italia con spedizione tracciata; consegniamo anche in diverse zone dell\'Unione Europea.', 'en' => 'Yes, we ship throughout Italy with tracked delivery; we also deliver to several areas of the European Union.', 'de' => 'Ja, wir versenden in ganz Italien mit Sendungsverfolgung; wir liefern auch in mehrere Gebiete der Europäischen Union.', 'fr' => 'Oui, nous livrons dans toute l\'Italie avec suivi ; nous livrons aussi dans plusieurs régions de l\'Union européenne.'],
        'faq_q5'       => ['it' => 'Come si conservano i prodotti?', 'en' => 'How should the products be stored?', 'de' => 'Wie werden die Produkte aufbewahrt?', 'fr' => 'Comment conserver les produits ?'],
        'faq_a5'       => ['it' => 'I prodotti da forno senza glutine si gustano al meglio freschi, ma si conservano benissimo in freezer: basta scongelarli o riscaldarli al momento del consumo.', 'en' => 'Gluten-free baked goods are best enjoyed fresh, but they freeze very well: just thaw or warm them up before eating.', 'de' => 'Glutenfreie Backwaren schmecken frisch am besten, lassen sich aber sehr gut einfrieren: einfach vor dem Verzehr auftauen oder erwärmen.', 'fr' => 'Les produits de boulangerie sans gluten se dégustent au mieux frais, mais se conservent très bien au congélateur : il suffit de les décongeler ou de les réchauffer avant de les consommer.'],
        'faq_q6'       => ['it' => 'Dove avete sede?', 'en' => 'Where are you based?', 'de' => 'Wo ist euer Sitz?', 'fr' => 'Où êtes-vous situés ?'],
        'faq_a6'       => ['it' => 'Siamo a Ravanusa (AG), in Sicilia. La vendita è online, con spedizione a domicilio.', 'en' => 'We are in Ravanusa (AG), Sicily. Sales are online, with home delivery.', 'de' => 'Wir sind in Ravanusa (AG), Sizilien. Der Verkauf erfolgt online mit Lieferung nach Hause.', 'fr' => 'Nous sommes à Ravanusa (AG), en Sicile. La vente se fait en ligne, avec livraison à domicile.'],
        'nl_invalid'   => ['it' => 'Inserisci un indirizzo email valido.', 'en' => 'Please enter a valid email address.', 'de' => 'Bitte gib eine gültige E-Mail-Adresse ein.', 'fr' => 'Veuillez saisir une adresse e-mail valide.'],
        'nl_gdpr_req'  => ['it' => 'Devi accettare l\'informativa privacy.', 'en' => 'You must accept the privacy policy.', 'de' => 'Du musst die Datenschutzerklärung akzeptieren.', 'fr' => 'Vous devez accepter la politique de confidentialité.'],
        'nl_check'     => ['it' => 'Controlla la tua email e conferma l\'iscrizione: ti abbiamo inviato un link.', 'en' => 'Check your email and confirm your subscription: we sent you a link.', 'de' => 'Prüfe deine E-Mail und bestätige die Anmeldung: Wir haben dir einen Link geschickt.', 'fr' => 'Vérifiez votre e-mail et confirmez l\'inscription : nous vous avons envoyé un lien.'],
        'nl_already'   => ['it' => 'Sei già iscritto, grazie!', 'en' => 'You are already subscribed, thank you!', 'de' => 'Du bist bereits angemeldet, danke!', 'fr' => 'Vous êtes déjà inscrit, merci !'],
        'nl_conf_h'    => ['it' => 'Iscrizione confermata! 🌾', 'en' => 'Subscription confirmed! 🌾', 'de' => 'Anmeldung bestätigt! 🌾', 'fr' => 'Inscription confirmée ! 🌾'],
        'nl_conf_p'    => ['it' => 'Grazie! Ecco il tuo sconto di benvenuto del 10% sul primo ordine. Usa questo codice al checkout:', 'en' => 'Thank you! Here is your 10% welcome discount on your first order. Use this code at checkout:', 'de' => 'Danke! Hier ist dein 10% Willkommensrabatt auf die erste Bestellung. Verwende diesen Code an der Kasse:', 'fr' => 'Merci ! Voici votre remise de bienvenue de 10% sur la première commande. Utilisez ce code au paiement :'],
        'nl_conf_cta'  => ['it' => 'Vai al negozio', 'en' => 'Go to shop', 'de' => 'Zum Shop', 'fr' => 'Aller à la boutique'],
        'nl_bad_h'     => ['it' => 'Link non valido', 'en' => 'Invalid link', 'de' => 'Ungültiger Link', 'fr' => 'Lien non valide'],
        'nl_bad_p'     => ['it' => 'Questo link di conferma non è valido o è scaduto. Prova a iscriverti di nuovo.', 'en' => 'This confirmation link is invalid or expired. Please try subscribing again.', 'de' => 'Dieser Bestätigungslink ist ungültig oder abgelaufen. Bitte melde dich erneut an.', 'fr' => 'Ce lien de confirmation n\'est pas valide ou a expiré. Veuillez vous réinscrire.'],
        // Footer
        'foot_blurb'   => ['it' => 'Mangia con Gusto — Prodotti senza glutine e senza lattosio. Pane, basi pizza, focacce, dolci e cheesecake prodotti in laboratorio dedicato.', 'en' => 'Mangia con Gusto — Gluten-free and lactose-free products. Bread, pizza bases, focaccia, desserts and cheesecake made in a dedicated lab.', 'de' => 'Mangia con Gusto — Glutenfreie und laktosefreie Produkte. Brot, Pizzaböden, Focaccia, Süßes und Cheesecake aus eigenem Labor.', 'fr' => 'Mangia con Gusto — Produits sans gluten et sans lactose. Pain, bases pizza, focaccia, desserts et cheesecake produits en laboratoire dédié.'],
        'foot_catalog_full' => ['it' => 'Catalogo completo', 'en' => 'Full catalog', 'de' => 'Vollständiger Katalog', 'fr' => 'Catalogue complet'],
        'foot_info'    => ['it' => 'Informazioni', 'en' => 'Information', 'de' => 'Informationen', 'fr' => 'Informations'],
        'foot_shipping' => ['it' => 'Spedizioni e resi', 'en' => 'Shipping & returns', 'de' => 'Versand & Rückgabe', 'fr' => 'Livraison et retours'],
        'foot_terms'   => ['it' => 'Condizioni di vendita', 'en' => 'Terms of sale', 'de' => 'Verkaufsbedingungen', 'fr' => 'Conditions de vente'],
        'foot_withdrawal' => ['it' => 'Diritto di recesso', 'en' => 'Right of withdrawal', 'de' => 'Widerrufsrecht', 'fr' => 'Droit de rétractation'],
        'foot_contact_form' => ['it' => 'Form contatti', 'en' => 'Contact form', 'de' => 'Kontaktformular', 'fr' => 'Formulaire de contact'],
        'foot_rights'  => ['it' => 'Tutti i diritti riservati.', 'en' => 'All rights reserved.', 'de' => 'Alle Rechte vorbehalten.', 'fr' => 'Tous droits réservés.'],
        'foot_designed' => ['it' => 'Progettato e Sviluppato da', 'en' => 'Designed & developed by', 'de' => 'Gestaltet & entwickelt von', 'fr' => 'Conçu et développé par'],
        'trust_mutuabili' => ['it' => 'Prodotti mutuabili SSN', 'en' => 'NHS-reimbursable products', 'de' => 'Erstattungsfähige Produkte', 'fr' => 'Produits remboursables'],
        // Pagina prodotto
        'sp_reviews'   => ['it' => 'recensioni', 'en' => 'reviews', 'de' => 'Bewertungen', 'fr' => 'avis'],
        'sp_sku'       => ['it' => 'Codice SKU', 'en' => 'SKU', 'de' => 'Artikelnr.', 'fr' => 'Réf. SKU'],
        'sp_avail'     => ['it' => 'Disponibilità', 'en' => 'Availability', 'de' => 'Verfügbarkeit', 'fr' => 'Disponibilité'],
        'sp_instock'   => ['it' => '✓ Disponibile', 'en' => '✓ In stock', 'de' => '✓ Verfügbar', 'fr' => '✓ Disponible'],
        'sp_outstock'  => ['it' => 'Esaurito', 'en' => 'Out of stock', 'de' => 'Ausverkauft', 'fr' => 'Épuisé'],
        'sp_ship'      => ['it' => 'Spedizione', 'en' => 'Shipping', 'de' => 'Versand', 'fr' => 'Livraison'],
        'sp_ship_val'  => ['it' => '24-48h · gratis sopra €59', 'en' => '24-48h · free over €59', 'de' => '24-48h · gratis ab €59', 'fr' => '24-48h · gratuit dès 59 €'],
        'sp_cert'      => ['it' => 'Certificazione', 'en' => 'Certification', 'de' => 'Zertifizierung', 'fr' => 'Certification'],
        'sp_cert_val'  => ['it' => 'Senza glutine + senza lattosio', 'en' => 'Gluten-free + lactose-free', 'de' => 'Glutenfrei + laktosefrei', 'fr' => 'Sans gluten + sans lactose'],
        'sp_lab'       => ['it' => 'Laboratorio', 'en' => 'Lab', 'de' => 'Labor', 'fr' => 'Laboratoire'],
        'sp_lab_val'   => ['it' => 'Dedicato, privo di contaminazioni', 'en' => 'Dedicated, contamination-free', 'de' => 'Eigenes, kontaminationsfrei', 'fr' => 'Dédié, sans contamination'],
        'sp_help'      => ['it' => 'Hai bisogno di aiuto?', 'en' => 'Need help?', 'de' => 'Brauchst du Hilfe?', 'fr' => "Besoin d'aide ?"],
        'sp_help_wa'   => ['it' => 'Scrivici su WhatsApp', 'en' => 'Message us on WhatsApp', 'de' => 'Schreib uns auf WhatsApp', 'fr' => 'Écrivez-nous sur WhatsApp'],
        'sp_desc'      => ['it' => 'Descrizione', 'en' => 'Description', 'de' => 'Beschreibung', 'fr' => 'Description'],
        'sp_related_eyebrow' => ['it' => 'Potrebbero piacerti', 'en' => 'You might also like', 'de' => 'Das könnte dir gefallen', 'fr' => 'Vous pourriez aimer'],
        'sp_related_h2' => ['it' => 'Prodotti correlati', 'en' => 'Related products', 'de' => 'Ähnliche Produkte', 'fr' => 'Produits associés'],
        'sp_select'    => ['it' => 'Seleziona opzioni', 'en' => 'Select options', 'de' => 'Optionen wählen', 'fr' => 'Choisir les options'],
        'sp_reviews_title' => ['it' => 'Recensioni', 'en' => 'Reviews', 'de' => 'Bewertungen', 'fr' => 'Avis'],
        'bis_out'      => ['it' => 'Momentaneamente esaurito', 'en' => 'Temporarily out of stock', 'de' => 'Vorübergehend ausverkauft', 'fr' => 'Temporairement épuisé'],
        'bis_text'     => ['it' => 'Lasciaci la tua email: ti avvisiamo appena torna disponibile.', 'en' => 'Leave us your email: we\'ll notify you as soon as it\'s back in stock.', 'de' => 'Hinterlasse uns deine E-Mail: Wir benachrichtigen dich, sobald es wieder verfügbar ist.', 'fr' => 'Laissez-nous votre e-mail : nous vous préviendrons dès son retour en stock.'],
        'bis_btn'      => ['it' => 'Avvisami', 'en' => 'Notify me', 'de' => 'Benachrichtige mich', 'fr' => 'Préviens-moi'],
        // Pagina ABC dieta senza glutine
        'abc_eyebrow'  => ['it' => 'Guida pratica', 'en' => 'Practical guide', 'de' => 'Praktischer Leitfaden', 'fr' => 'Guide pratique'],
        'abc_h1'       => ['it' => "L'ABC della dieta senza glutine", 'en' => 'The ABC of the gluten-free diet', 'de' => 'Das ABC der glutenfreien Ernährung', 'fr' => "L'ABC du régime sans gluten"],
        'abc_intro'    => ['it' => 'Poche nozioni chiare per vivere serenamente la celiachia, ogni giorno. Una piccola guida per chi inizia e per i genitori dei bambini celiaci. Per approfondimenti completi e sempre aggiornati fai riferimento all\'<strong>Associazione Italiana Celiachia (AIC)</strong>.', 'en' => 'A few clear notions to live serenely with celiac disease, every day. A short guide for beginners and for parents of celiac children. For complete and always up-to-date information, refer to the <strong>Italian Celiac Association (AIC)</strong>.', 'de' => 'Ein paar klare Hinweise, um den Alltag mit Zöliakie gelassen zu meistern. Ein kurzer Leitfaden für Einsteiger und Eltern zöliakiekranker Kinder. Für vollständige und stets aktuelle Informationen wende dich an die <strong>Italienische Zöliakie-Vereinigung (AIC)</strong>.', 'fr' => 'Quelques notions claires pour vivre sereinement la maladie cœliaque, au quotidien. Un petit guide pour débuter et pour les parents d\'enfants cœliaques. Pour des informations complètes et toujours à jour, référez-vous à l\'<strong>Association Italienne de la Maladie Cœliaque (AIC)</strong>.'],
        'abc_s1_t'     => ['it' => 'Le tre categorie di alimenti', 'en' => 'The three food categories', 'de' => 'Die drei Lebensmittelkategorien', 'fr' => 'Les trois catégories d\'aliments'],
        'abc_s1_p1'    => ['it' => 'Per orientarsi, gli alimenti si dividono in tre gruppi:', 'en' => 'To find your way, foods fall into three groups:', 'de' => 'Zur Orientierung lassen sich Lebensmittel in drei Gruppen einteilen:', 'fr' => 'Pour s\'y retrouver, les aliments se divisent en trois groupes :'],
        'abc_s1_li1'   => ['it' => '<strong>Permessi</strong> — naturalmente privi di glutine: riso, mais, patate, legumi, carne, pesce, uova, frutta, verdura, latte e formaggi naturali.', 'en' => '<strong>Allowed</strong> — naturally gluten-free: rice, corn, potatoes, legumes, meat, fish, eggs, fruit, vegetables, milk and natural cheeses.', 'de' => '<strong>Erlaubt</strong> — von Natur aus glutenfrei: Reis, Mais, Kartoffeln, Hülsenfrüchte, Fleisch, Fisch, Eier, Obst, Gemüse, Milch und Naturkäse.', 'fr' => '<strong>Autorisés</strong> — naturellement sans gluten : riz, maïs, pommes de terre, légumineuses, viande, poisson, œufs, fruits, légumes, lait et fromages naturels.'],
        'abc_s1_li2'   => ['it' => '<strong>Vietati</strong> — contengono glutine: frumento, orzo, segale, farro, kamut, spelta, triticale e tutti i loro derivati (pane, pasta, pizza, dolci comuni).', 'en' => '<strong>Forbidden</strong> — contain gluten: wheat, barley, rye, spelt, kamut, spelt, triticale and all their derivatives (common bread, pasta, pizza, sweets).', 'de' => '<strong>Verboten</strong> — enthalten Gluten: Weizen, Gerste, Roggen, Dinkel, Kamut, Emmer, Triticale und alle ihre Derivate (gewöhnliches Brot, Nudeln, Pizza, Süßes).', 'fr' => '<strong>Interdits</strong> — contiennent du gluten : blé, orge, seigle, épeautre, kamut, petit épeautre, triticale et tous leurs dérivés (pain, pâtes, pizza, gâteaux courants).'],
        'abc_s1_li3'   => ['it' => '<strong>A rischio</strong> — potrebbero contenere glutine per ingredienti o lavorazione (alcuni salumi, salse, preparati, avena non certificata): vanno sempre verificati.', 'en' => '<strong>At risk</strong> — may contain gluten due to ingredients or processing (some cured meats, sauces, mixes, uncertified oats): always check them.', 'de' => '<strong>Risikobehaftet</strong> — können aufgrund von Zutaten oder Verarbeitung Gluten enthalten (einige Wurstwaren, Saucen, Fertigmischungen, nicht zertifizierter Hafer): immer prüfen.', 'fr' => '<strong>À risque</strong> — peuvent contenir du gluten selon les ingrédients ou la transformation (certaines charcuteries, sauces, préparations, avoine non certifiée) : à toujours vérifier.'],
        'abc_s1_p2'    => ['it' => 'I nostri prodotti appartengono alla categoria dei <strong>sostitutivi senza glutine</strong>: pane, basi pizza, focacce e dolci pensati per sostituire in sicurezza quelli vietati.', 'en' => 'Our products belong to the <strong>gluten-free substitutes</strong> category: bread, pizza bases, focaccia and desserts designed to safely replace the forbidden ones.', 'de' => 'Unsere Produkte gehören zur Kategorie der <strong>glutenfreien Ersatzprodukte</strong>: Brot, Pizzaböden, Focaccia und Süßes, um die verbotenen sicher zu ersetzen.', 'fr' => 'Nos produits appartiennent à la catégorie des <strong>substituts sans gluten</strong> : pain, bases pizza, focaccia et desserts conçus pour remplacer en toute sécurité les aliments interdits.'],
        'abc_s2_t'     => ['it' => 'La contaminazione crociata', 'en' => 'Cross-contamination', 'de' => 'Kreuzkontamination', 'fr' => 'La contamination croisée'],
        'abc_s2_p1'    => ['it' => 'Per chi è celiaco anche piccole tracce di glutine sono dannose. La contaminazione avviene quando un alimento sicuro entra in contatto con uno che contiene glutine: stesse superfici, utensili, friggitrici o acqua di cottura condivisi.', 'en' => 'For people with celiac disease even small traces of gluten are harmful. Contamination occurs when a safe food comes into contact with one containing gluten: shared surfaces, utensils, fryers or cooking water.', 'de' => 'Für Menschen mit Zöliakie sind selbst kleine Glutenspuren schädlich. Eine Kontamination entsteht, wenn ein sicheres Lebensmittel mit einem glutenhaltigen in Kontakt kommt: gemeinsame Flächen, Utensilien, Fritteusen oder Kochwasser.', 'fr' => 'Pour les cœliaques, même de petites traces de gluten sont nocives. La contamination survient lorsqu\'un aliment sûr entre en contact avec un aliment contenant du gluten : surfaces, ustensiles, friteuses ou eau de cuisson partagés.'],
        'abc_s2_p2'    => ['it' => 'Per questo il nostro <strong>laboratorio è esclusivamente senza glutine</strong>: nessuna lavorazione con farine contenenti glutine, quindi nessun rischio di contaminazione crociata. A casa e fuori, usa utensili puliti e dedicati.', 'en' => 'That is why our <strong>lab is exclusively gluten-free</strong>: no processing with gluten-containing flours, so no risk of cross-contamination. At home and out, use clean, dedicated utensils.', 'de' => 'Deshalb ist unser <strong>Labor ausschließlich glutenfrei</strong>: keine Verarbeitung mit glutenhaltigem Mehl, also kein Risiko einer Kreuzkontamination. Zu Hause und unterwegs: saubere, eigene Utensilien verwenden.', 'fr' => 'C\'est pourquoi notre <strong>laboratoire est exclusivement sans gluten</strong> : aucune transformation avec des farines contenant du gluten, donc aucun risque de contamination croisée. Chez vous et à l\'extérieur, utilisez des ustensiles propres et dédiés.'],
        'abc_s3_t'     => ['it' => 'Come leggere le etichette', 'en' => 'How to read labels', 'de' => 'Etiketten richtig lesen', 'fr' => 'Comment lire les étiquettes'],
        'abc_s3_p1'    => ['it' => 'Controlla sempre l\'elenco ingredienti e la sezione allergeni: il glutine e i cereali che lo contengono vanno evidenziati per legge. Diffida dei prodotti senza etichetta chiara e, nel dubbio, scegli prodotti pensati e certificati per i celiaci.', 'en' => 'Always check the ingredient list and the allergens section: gluten and the cereals that contain it must be highlighted by law. Be wary of products without a clear label and, when in doubt, choose products designed and certified for celiacs.', 'de' => 'Prüfe immer die Zutatenliste und den Allergenhinweis: Gluten und glutenhaltige Getreide müssen gesetzlich hervorgehoben werden. Sei vorsichtig bei Produkten ohne klare Kennzeichnung und wähle im Zweifel speziell für Zöliakie zertifizierte Produkte.', 'fr' => 'Vérifiez toujours la liste des ingrédients et la section allergènes : le gluten et les céréales qui en contiennent doivent être mis en évidence par la loi. Méfiez-vous des produits sans étiquette claire et, en cas de doute, choisissez des produits conçus et certifiés pour les cœliaques.'],
        'abc_s4_t'     => ['it' => 'Prodotti mutuabili e buono celiachia', 'en' => 'Reimbursable products and celiac voucher', 'de' => 'Erstattungsfähige Produkte und Zöliakie-Gutschein', 'fr' => 'Produits remboursables et bon cœliaque'],
        'abc_s4_p1'    => ['it' => 'Le persone con diagnosi di celiachia hanno diritto a un <strong>contributo mensile</strong> del Servizio Sanitario Nazionale per l\'acquisto di prodotti sostitutivi senza glutine, registrati nell\'apposito Registro del <strong>Ministero della Salute</strong>. Il buono si utilizza nelle farmacie e nei punti vendita accreditati.', 'en' => 'People diagnosed with celiac disease are entitled to a <strong>monthly contribution</strong> from the National Health Service to buy gluten-free substitute products, registered in the dedicated Register of the <strong>Ministry of Health</strong>. The voucher is used in pharmacies and accredited points of sale.', 'de' => 'Menschen mit diagnostizierter Zöliakie haben Anspruch auf einen <strong>monatlichen Zuschuss</strong> des nationalen Gesundheitsdienstes für glutenfreie Ersatzprodukte, die im entsprechenden Register des <strong>Gesundheitsministeriums</strong> eingetragen sind. Der Gutschein wird in Apotheken und akkreditierten Verkaufsstellen eingelöst.', 'fr' => 'Les personnes diagnostiquées cœliaques ont droit à une <strong>contribution mensuelle</strong> du Service de Santé National pour l\'achat de produits de substitution sans gluten, inscrits au Registre dédié du <strong>Ministère de la Santé</strong>. Le bon s\'utilise en pharmacie et dans les points de vente agréés.'],
        'abc_s4_p2'    => ['it' => 'I nostri <strong>prodotti mutuabili</strong> rientrano tra quelli erogabili: chiedici quali e come utilizzare il tuo buono.', 'en' => 'Our <strong>reimbursable products</strong> are among those provided: ask us which ones and how to use your voucher.', 'de' => 'Unsere <strong>erstattungsfähigen Produkte</strong> gehören zu den erhältlichen: Frag uns, welche und wie du deinen Gutschein einlöst.', 'fr' => 'Nos <strong>produits remboursables</strong> font partie de ceux délivrés : demandez-nous lesquels et comment utiliser votre bon.'],
        'abc_s5_t'     => ['it' => 'Gli strumenti utili di AIC', 'en' => 'AIC\'s useful tools', 'de' => 'Nützliche AIC-Hilfsmittel', 'fr' => 'Les outils utiles de l\'AIC'],
        'abc_s5_p1'    => ['it' => 'L\'<strong>Associazione Italiana Celiachia</strong> mette a disposizione strumenti preziosi: il <em>Prontuario degli Alimenti</em>, l\'app per consultarlo, e il marchio della spiga barrata che identifica i prodotti idonei. Noi siamo <strong>accreditati AIC</strong>.', 'en' => 'The <strong>Italian Celiac Association</strong> offers valuable tools: the <em>Food Handbook</em>, the app to consult it, and the crossed-grain mark that identifies suitable products. We are <strong>AIC accredited</strong>.', 'de' => 'Die <strong>Italienische Zöliakie-Vereinigung</strong> bietet wertvolle Hilfsmittel: das <em>Lebensmittelverzeichnis</em>, die App zum Nachschlagen und das durchgestrichene Ähren-Zeichen für geeignete Produkte. Wir sind <strong>AIC-akkreditiert</strong>.', 'fr' => 'L\'<strong>Association Italienne de la Maladie Cœliaque</strong> propose des outils précieux : le <em>Répertoire des Aliments</em>, l\'application pour le consulter, et le logo de l\'épi barré qui identifie les produits adaptés. Nous sommes <strong>accrédités AIC</strong>.'],
        'abc_s5_link'  => ['it' => 'Approfondisci sul sito ufficiale di AIC →', 'en' => 'Learn more on the official AIC website →', 'de' => 'Mehr auf der offiziellen AIC-Website →', 'fr' => 'En savoir plus sur le site officiel de l\'AIC →'],
        'abc_disclaimer' => ['it' => 'Contenuto a scopo divulgativo, non sostituisce le indicazioni del medico o di AIC. Fonte autorevole:', 'en' => 'Informational content, it does not replace the advice of your doctor or AIC. Authoritative source:', 'de' => 'Informativer Inhalt, ersetzt nicht die Hinweise des Arztes oder der AIC. Verlässliche Quelle:', 'fr' => 'Contenu informatif, ne remplace pas les indications du médecin ou de l\'AIC. Source de référence :'],
    ];
}

/**
 * URL di una pagina nella lingua CORRENTE (Polylang). Dato lo slug della pagina
 * italiana, ritorna il permalink della traduzione attiva; fallback alla pagina IT.
 */
function lcgf_page_url($slug) {
    $page = get_page_by_path($slug);
    if ($page) {
        $id = $page->ID;
        if (function_exists('pll_get_post')) {
            $tr = pll_get_post($id);
            if ($tr) $id = $tr;
        }
        return get_permalink($id);
    }
    return home_url('/' . $slug . '/');
}

/**
 * Lingua all'accesso diretto alla home:
 *  - PRIMO accesso (nessun cookie): usa la lingua del browser; se non disponibile,
 *    default INGLESE. Salva la scelta nel cookie pll_language.
 *  - ACCESSI SUCCESSIVI: rispetta la lingua ricordata nel cookie (impostata anche
 *    da Polylang quando l'utente cambia lingua dal selettore).
 * Agisce solo su accessi DIRETTI alla home (no navigazione interna, no bot), così
 * non interferisce col selettore lingua né con la SEO.
 */
add_action('template_redirect', function () {
    if (is_admin()) return;
    if (defined('DOING_AJAX') && DOING_AJAX) return;
    if (defined('REST_REQUEST') && REST_REQUEST) return;
    if (isset($_GET['lcgf_action'])) return; // non interferire con gli endpoint custom (es. conferma newsletter)
    if (!function_exists('pll_languages_list') || !function_exists('pll_current_language') || !function_exists('pll_home_url')) return;
    if (!is_front_page()) return;

    // Salta i crawler (preserva l'indicizzazione per lingua via hreflang)
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    if ($ua && preg_match('/bot|crawl|spider|slurp|mediapartners|facebookexternalhit|embedly|whatsapp|telegram|preview/i', $ua)) return;

    // Non interferire con la navigazione interna (es. click sul selettore lingua)
    $ref = wp_get_referer();
    if ($ref && strpos($ref, home_url()) === 0) return;

    $available = pll_languages_list();
    if (empty($available)) return;
    $current = pll_current_language('slug');

    $cookie = '';
    if (!empty($_COOKIE['pll_language'])) {
        $cookie = substr(preg_replace('/[^a-z]/', '', strtolower($_COOKIE['pll_language'])), 0, 2);
    }

    if ($cookie && in_array($cookie, $available, true)) {
        // Accessi successivi: lingua ricordata
        $target = $cookie;
    } else {
        // Primo accesso: lingua del browser, fallback inglese
        $target = in_array('en', $available, true) ? 'en' : $current;
        $accept = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']) : '';
        if ($accept) {
            foreach (explode(',', $accept) as $part) {
                $code = substr(trim(explode(';', $part)[0]), 0, 2);
                if (in_array($code, $available, true)) { $target = $code; break; }
            }
        }
        @setcookie('pll_language', $target, time() + YEAR_IN_SECONDS, defined('COOKIEPATH') && COOKIEPATH ? COOKIEPATH : '/', defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN : '');
    }

    if ($target && $target !== $current) {
        wp_safe_redirect(pll_home_url($target));
        exit;
    }
}, 1);

/**
 * Crea le traduzioni Polylang (EN/DE/FR) della pagina ABC e le collega, così è
 * raggiungibile/visibile in tutte le lingue. Il testo viene dal template via
 * lcgf_t(), quindi le pagine-traduzione restano a contenuto vuoto. Guardia v1.
 */
add_action('init', function () {
    if (get_option('lcgf_abc_i18n_v1')) return;
    if (!function_exists('pll_set_post_language') || !function_exists('pll_save_post_translations') || !function_exists('pll_get_post')) return;
    $it = get_page_by_path('abc-dieta-senza-glutine');
    if (!$it) return;
    $strings = lcgf_i18n_strings();
    pll_set_post_language($it->ID, 'it');
    $map = ['it' => $it->ID];
    foreach (['en', 'de', 'fr'] as $lang) {
        $existing = pll_get_post($it->ID, $lang);
        if ($existing) { $map[$lang] = $existing; continue; }
        $title = isset($strings['abc_h1'][$lang]) ? $strings['abc_h1'][$lang] : $strings['abc_h1']['it'];
        $id = wp_insert_post([
            'post_title'   => $title,
            'post_name'    => 'abc-dieta-senza-glutine-' . $lang,
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => '',
        ]);
        if ($id && !is_wp_error($id)) {
            update_post_meta($id, '_wp_page_template', 'page-abc-celiachia.php');
            pll_set_post_language($id, $lang);
            $map[$lang] = $id;
        }
    }
    pll_save_post_translations($map);
    update_option('lcgf_abc_i18n_v1', current_time('mysql'));
}, 106);

/* =========================================================================
 * NEWSLETTER (double opt-in) — tabella iscritti, AJAX, conferma, coupon -10%
 * ========================================================================= */

// Once-run (lcgf_nl_v1): tabella iscritti + coupon di benvenuto BENVENUTO10
add_action('init', function () {
    if (get_option('lcgf_nl_v1')) return;
    if (!function_exists('WC')) return;
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $tb = $wpdb->prefix . 'lcgf_newsletter';
    dbDelta("CREATE TABLE {$tb} (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      email VARCHAR(190) NOT NULL,
      token VARCHAR(40) NOT NULL,
      status VARCHAR(12) NOT NULL DEFAULT 'pending',
      lang VARCHAR(5) NOT NULL DEFAULT 'it',
      created_at DATETIME NOT NULL,
      confirmed_at DATETIME NULL,
      PRIMARY KEY  (id),
      UNIQUE KEY email (email)
    ) " . $wpdb->get_charset_collate() . ";");

    if (function_exists('wc_get_coupon_id_by_code') && !wc_get_coupon_id_by_code('BENVENUTO10')) {
        $c = new WC_Coupon();
        $c->set_code('BENVENUTO10');
        $c->set_discount_type('percent');
        $c->set_amount(10);
        $c->set_individual_use(true);
        $c->set_description('Sconto di benvenuto newsletter -10%');
        $c->save();
    }
    update_option('lcgf_nl_v1', current_time('mysql'));
}, 107);

// Email di conferma (double opt-in), brandizzata + multilingua
function lcgf_nl_optin_email($email, $token, $lang) {
    $confirm = add_query_arg(
        ['lcgf_action' => 'nl_confirm', 'e' => rawurlencode($email), 't' => $token],
        home_url('/')
    );
    $S = [
        'it' => ['s' => 'Conferma la tua iscrizione 🌾', 'h' => 'Ci sei quasi!', 'p' => 'Conferma la tua iscrizione alla newsletter di La Compagnia del Gluten Free e ricevi subito il <strong>10% di sconto</strong> sul primo ordine.', 'b' => 'Conferma iscrizione', 'f' => 'Se non ti sei iscritto tu, ignora pure questa email.'],
        'en' => ['s' => 'Confirm your subscription 🌾', 'h' => 'Almost there!', 'p' => 'Confirm your subscription to the La Compagnia del Gluten Free newsletter and get <strong>10% off</strong> your first order right away.', 'b' => 'Confirm subscription', 'f' => 'If you did not subscribe, please ignore this email.'],
        'de' => ['s' => 'Bestätige deine Anmeldung 🌾', 'h' => 'Fast geschafft!', 'p' => 'Bestätige deine Anmeldung zum Newsletter von La Compagnia del Gluten Free und erhalte sofort <strong>10% Rabatt</strong> auf die erste Bestellung.', 'b' => 'Anmeldung bestätigen', 'f' => 'Wenn du dich nicht angemeldet hast, ignoriere diese E-Mail.'],
        'fr' => ['s' => 'Confirmez votre inscription 🌾', 'h' => 'Vous y êtes presque !', 'p' => 'Confirmez votre inscription à la newsletter de La Compagnia del Gluten Free et recevez tout de suite <strong>10% de remise</strong> sur la première commande.', 'b' => 'Confirmer l\'inscription', 'f' => 'Si vous ne vous êtes pas inscrit, ignorez cet e-mail.'],
    ];
    $t = $S[$lang] ?? $S['it'];
    $logo = get_stylesheet_directory_uri() . '/assets/img/logo.webp';
    $body = '<div style="font-family:Arial,Helvetica,sans-serif;max-width:520px;margin:0 auto;color:#2f3a2a">'
        . '<div style="text-align:center;padding:18px 0"><img src="' . esc_url($logo) . '" alt="La Compagnia del Gluten Free" width="90" style="width:90px;height:auto"></div>'
        . '<div style="background:#fff;border:1px solid #e6e0cf;border-radius:14px;padding:28px 26px">'
        . '<h1 style="font-size:21px;color:#4f6e37;margin:0 0 10px">' . esc_html($t['h']) . '</h1>'
        . '<p style="font-size:15px;line-height:1.6;margin:0 0 22px">' . $t['p'] . '</p>'
        . '<p style="text-align:center;margin:0 0 20px"><a href="' . esc_url($confirm) . '" style="display:inline-block;background:#6b8e4e;color:#fff;text-decoration:none;font-weight:700;padding:13px 28px;border-radius:999px">' . esc_html($t['b']) . '</a></p>'
        . '<p style="font-size:12px;color:#8a8a7a;margin:0">' . esc_html($t['f']) . '</p>'
        . '</div></div>';
    wp_mail($email, $t['s'], $body, ['Content-Type: text/html; charset=UTF-8']);
}

// AJAX iscrizione (anche non loggati)
function lcgf_nl_save() {
    check_ajax_referer('lcgf_nl', 'nonce');
    $email = sanitize_email($_POST['email'] ?? '');
    if (!is_email($email)) wp_send_json_error(['msg' => lcgf_t('nl_invalid')], 400);
    if (empty($_POST['gdpr'])) wp_send_json_error(['msg' => lcgf_t('nl_gdpr_req')], 400);
    global $wpdb; $tb = $wpdb->prefix . 'lcgf_newsletter';
    $lang = function_exists('pll_current_language') ? (pll_current_language('slug') ?: 'it') : 'it';
    $row = $wpdb->get_row($wpdb->prepare("SELECT id,status FROM {$tb} WHERE email=%s", $email));
    if ($row && $row->status === 'confirmed') wp_send_json_success(['msg' => lcgf_t('nl_already')]);
    $token = wp_generate_password(24, false);
    if ($row) {
        $wpdb->update($tb, ['token' => $token, 'lang' => $lang, 'created_at' => current_time('mysql')], ['id' => $row->id]);
    } else {
        $wpdb->insert($tb, ['email' => $email, 'token' => $token, 'status' => 'pending', 'lang' => $lang, 'created_at' => current_time('mysql')]);
    }
    lcgf_nl_optin_email($email, $token, $lang);
    wp_send_json_success(['msg' => lcgf_t('nl_check')]);
}
add_action('wp_ajax_lcgf_nl', 'lcgf_nl_save');
add_action('wp_ajax_nopriv_lcgf_nl', 'lcgf_nl_save');

// Conferma double opt-in → pagina brandizzata con coupon
add_action('template_redirect', function () {
    if (($_GET['lcgf_action'] ?? '') !== 'nl_confirm') return;
    $email = sanitize_email($_GET['e'] ?? '');
    $token = sanitize_text_field($_GET['t'] ?? '');
    global $wpdb; $tb = $wpdb->prefix . 'lcgf_newsletter';
    $row = ($email && $token) ? $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tb} WHERE email=%s AND token=%s", $email, $token)) : null;
    $ok = (bool) $row;
    if ($row && $row->status !== 'confirmed') {
        $wpdb->update($tb, ['status' => 'confirmed', 'confirmed_at' => current_time('mysql')], ['id' => $row->id]);
    }
    status_header(200); nocache_headers();
    get_header();
    echo '<main style="max-width:640px;margin:60px auto;padding:0 22px;text-align:center;min-height:42vh">';
    if ($ok) {
        echo '<h1 style="color:#4f6e37">' . esc_html(lcgf_t('nl_conf_h')) . '</h1>';
        echo '<p style="font-size:1.05rem;color:#4a4a3a">' . esc_html(lcgf_t('nl_conf_p')) . '</p>';
        echo '<div style="display:inline-block;border:2px dashed #6b8e4e;border-radius:12px;padding:14px 30px;font-size:1.7rem;font-weight:800;letter-spacing:4px;color:#4f6e37;margin:16px 0">BENVENUTO10</div>';
        echo '<p style="margin-top:18px"><a class="btn" href="' . esc_url(wc_get_page_permalink('shop')) . '">' . esc_html(lcgf_t('nl_conf_cta')) . '</a></p>';
    } else {
        echo '<h1>' . esc_html(lcgf_t('nl_bad_h')) . '</h1><p style="color:#4a4a3a">' . esc_html(lcgf_t('nl_bad_p')) . '</p>';
    }
    echo '</main>';
    get_footer();
    exit;
});

// Export CSV iscritti (solo admin): /?lcgf_action=nl_list
add_action('init', function () {
    if (($_GET['lcgf_action'] ?? '') !== 'nl_list') return;
    if (!current_user_can('manage_options')) { status_header(403); exit('forbidden'); }
    global $wpdb; $tb = $wpdb->prefix . 'lcgf_newsletter';
    $rows = $wpdb->get_results("SELECT email,status,lang,created_at,confirmed_at FROM {$tb} ORDER BY id DESC", ARRAY_A);
    nocache_headers();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=newsletter-lcgf.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['email', 'status', 'lang', 'created_at', 'confirmed_at']);
    foreach ((array) $rows as $r) fputcsv($out, $r);
    fclose($out);
    exit;
});

/* JSON-LD schema.org Product/Offer sulle pagine prodotto (single) */
add_action('wp_footer', function () {
    if (!function_exists('is_product') || !is_product()) return;
    $product = wc_get_product(get_queried_object_id());
    if (!$product instanceof WC_Product) return;
    $url = get_permalink($product->get_id());
    $img = wp_get_attachment_url($product->get_image_id());
    $desc = wp_strip_all_tags($product->get_short_description() ?: $product->get_description() ?: $product->get_name());
    $schema = [
        '@context'    => 'https://schema.org/',
        '@type'       => 'Product',
        'name'        => $product->get_name(),
        'description' => mb_substr($desc, 0, 500),
        'url'         => $url,
        'brand'       => ['@type' => 'Brand', 'name' => 'La Compagnia del Gluten Free'],
    ];
    if ($img) $schema['image'] = [$img];
    if ($product->get_sku()) $schema['sku'] = $product->get_sku();
    $instock = $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock';
    $cur = get_woocommerce_currency();
    if ($product->is_type('variable')) {
        $prices = $product->get_variation_prices(true);
        $arr = !empty($prices['price']) ? array_map('floatval', $prices['price']) : [];
        $schema['offers'] = [
            '@type'         => 'AggregateOffer',
            'priceCurrency' => $cur,
            'lowPrice'      => wc_format_decimal($arr ? min($arr) : $product->get_price(), 2),
            'highPrice'     => wc_format_decimal($arr ? max($arr) : $product->get_price(), 2),
            'offerCount'    => count($arr),
            'availability'  => $instock,
            'url'           => $url,
        ];
    } else {
        $schema['offers'] = [
            '@type'         => 'Offer',
            'priceCurrency' => $cur,
            'price'         => wc_format_decimal($product->get_price(), 2),
            'availability'  => $instock,
            'url'           => $url,
            'priceValidUntil' => gmdate('Y-12-31'),
        ];
    }
    if ((int) $product->get_review_count() > 0) {
        $schema['aggregateRating'] = [
            '@type'       => 'AggregateRating',
            'ratingValue' => (string) $product->get_average_rating(),
            'reviewCount' => (int) $product->get_review_count(),
        ];
    }
    echo "\n" . '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
}, 20);
