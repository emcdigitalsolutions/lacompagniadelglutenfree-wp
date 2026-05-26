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

    $form_data = [
        'id'       => 0,
        'field_id' => 6,
        'fields'   => [
            0 => [
                'id'       => '0',
                'type'     => 'name',
                'label'    => 'Nome e cognome',
                'format'   => 'simple',
                'required' => '1',
                'size'     => 'medium',
            ],
            1 => [
                'id'       => '1',
                'type'     => 'email',
                'label'    => 'Email',
                'required' => '1',
                'size'     => 'medium',
            ],
            2 => [
                'id'       => '2',
                'type'     => 'phone',
                'label'    => 'Telefono (opzionale)',
                'format'   => 'international',
                'size'     => 'medium',
            ],
            3 => [
                'id'       => '3',
                'type'     => 'select',
                'label'    => 'Oggetto',
                'choices'  => [
                    1 => ['label' => 'Domanda su un ordine', 'value' => ''],
                    2 => ['label' => 'Informazioni su un prodotto', 'value' => ''],
                    3 => ['label' => 'Spedizione e resi', 'value' => ''],
                    4 => ['label' => 'Gift card e regali', 'value' => ''],
                    5 => ['label' => 'Collaborazioni B2B', 'value' => ''],
                    6 => ['label' => 'Altro', 'value' => ''],
                ],
                'required' => '1',
                'size'     => 'medium',
            ],
            4 => [
                'id'       => '4',
                'type'     => 'textarea',
                'label'    => 'Messaggio',
                'required' => '1',
                'size'     => 'medium',
            ],
            5 => [
                'id'        => '5',
                'type'      => 'gdpr-checkbox',
                'label'     => 'Privacy',
                'choices'   => [
                    1 => ['label' => 'Acconsento al trattamento dei dati personali ai sensi della <a href="/privacy/">Privacy Policy</a> per essere ricontattato.', 'value' => ''],
                ],
                'required'  => '1',
            ],
        ],
        'settings' => [
            'form_title'         => 'Contatti LCGF',
            'form_desc'          => '',
            'submit_text'        => 'Invia messaggio',
            'submit_text_processing' => 'Invio in corso...',
            'honeypot'           => '1',
            'antispam'           => '1',
            'antispam_v3'        => '1',
            'notification_enable' => '1',
            'notifications'      => [
                1 => [
                    'enable'            => '1',
                    'notification_name' => 'Notifica admin',
                    'email'             => $admin_email,
                    'subject'           => '[LCGF] Nuovo messaggio da {field_id="0"}',
                    'sender_name'       => 'La Compagnia del Gluten Free',
                    'sender_address'    => $admin_email,
                    'replyto'           => '{field_id="1"}',
                    'message'           => "Hai ricevuto un nuovo messaggio dal form contatti del sito.\n\n— DETTAGLI —\nNome: {field_id=\"0\"}\nEmail: {field_id=\"1\"}\nTelefono: {field_id=\"2\"}\nOggetto: {field_id=\"3\"}\n\n— MESSAGGIO —\n{field_id=\"4\"}\n\n---\nInviato da {site_name} il {date format=\"d/m/Y H:i\"}\nIP: {entry_ip}",
                ],
            ],
            'confirmations'      => [
                1 => [
                    'type'    => 'message',
                    'message' => '<div style="text-align:center;padding:30px 20px"><h2 style="color:#2f4823">Grazie! Messaggio inviato.</h2><p>Abbiamo ricevuto il tuo messaggio e ti risponderemo entro <strong>24 ore lavorative</strong>. Nel frattempo puoi anche scriverci su WhatsApp al <strong>+39 327 699 9897</strong>.</p></div>',
                    'message_scroll' => '1',
                ],
            ],
        ],
        'meta'     => [
            'template' => 'simple_contact_form',
        ],
    ];

    $post_id = wp_insert_post([
        'post_type'    => 'wpforms',
        'post_status'  => 'publish',
        'post_title'   => 'Contatti LCGF',
        'post_excerpt' => 'Form contatti principale del sito',
        'post_content' => wp_slash(wp_json_encode($form_data)),
    ]);

    if ($post_id && !is_wp_error($post_id)) {
        // riassegna l'id reale nel JSON e risalva
        $form_data['id'] = (string)$post_id;
        wp_update_post([
            'ID'           => $post_id,
            'post_content' => wp_slash(wp_json_encode($form_data)),
        ]);
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
        <input type="text" name="lcgf_evento[citta]" value="<?php echo esc_attr($f['citta']); ?>" placeholder="Es. Campobello di Licata (AG)" />
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
        update_post_meta($post_id, '_lcgf_evento_citta',       'Campobello di Licata (AG)');
        update_post_meta($post_id, '_lcgf_evento_prezzo',      'Ingresso libero');
    }
    update_option('lcgf_evento_seeded_v1', 'done');
});
