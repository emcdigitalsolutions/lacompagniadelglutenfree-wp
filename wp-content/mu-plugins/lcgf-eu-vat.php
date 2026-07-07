<?php
/**
 * Plugin Name: LCGF EU VAT — reverse charge B2B intra-UE
 * Description: Campo "Partita IVA" al checkout (blocchi) + esenzione IVA per aziende UE non-IT con P.IVA valida su VIES (art. 41 D.L. 331/1993). Privati e P.IVA non valide pagano IVA italiana come sempre. Il ritiro in sede e la spedizione in Italia NON danno esenzione (la merce deve lasciare l'Italia).
 * Author: EMC Digital Solutions
 */

if (!defined('ABSPATH')) exit;

/* ============================================================
 * Helpers
 * ============================================================ */

function lcgf_euvat_eu_countries() {
    return array('AT','BE','BG','HR','CY','CZ','DK','EE','FI','FR','DE','GR','EL','HU','IE','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','ES','SE');
}

function lcgf_euvat_lang() {
    $l = function_exists('pll_current_language') ? pll_current_language('slug') : 'it';
    return in_array($l, array('it','en','de','fr'), true) ? $l : 'it';
}

/** Normalizza: maiuscole, senza spazi/punti/trattini. */
function lcgf_euvat_norm($v) {
    return strtoupper(preg_replace('/[\s\.\-]/', '', (string) $v));
}

/** Controllo formale (sintassi) per i paesi rilevanti + fallback generico UE. */
function lcgf_euvat_format_ok($vat) {
    if ($vat === '') return true;
    if (preg_match('/^\d{11}$/', $vat)) return true; // P.IVA italiana senza prefisso
    $re = array(
        'IT' => '/^IT\d{11}$/',
        'DE' => '/^DE\d{9}$/',
        'MT' => '/^MT\d{8}$/',
        'FR' => '/^FR[A-Z0-9]{2}\d{9}$/',
        'AT' => '/^ATU\d{8}$/',
    );
    $cc = substr($vat, 0, 2);
    if (isset($re[$cc])) return (bool) preg_match($re[$cc], $vat);
    return (bool) preg_match('/^[A-Z]{2}[A-Z0-9\+\*]{2,12}$/', $vat);
}

/**
 * Verifica su VIES (REST, fallback SOAP), con cache transient.
 * @return string 'valid' | 'invalid' | 'unavailable'
 */
function lcgf_euvat_vies($vat) {
    $vat = lcgf_euvat_norm($vat);
    $cc  = substr($vat, 0, 2);
    $num = substr($vat, 2);
    if (!preg_match('/^[A-Z]{2}$/', $cc) || $num === '') return 'invalid';
    if ($cc === 'GR') $cc = 'EL'; // VIES usa EL per la Grecia

    $tkey = 'lcgf_vies_' . md5($cc . $num);
    $hit  = get_transient($tkey);
    if ($hit) return $hit;

    $status = 'unavailable';

    // 1) REST API ufficiale VIES
    $resp = wp_remote_post('https://ec.europa.eu/taxation_customs/vies/rest-api/check-vat-number', array(
        'timeout' => 8,
        'headers' => array('Content-Type' => 'application/json'),
        'body'    => wp_json_encode(array('countryCode' => $cc, 'vatNumber' => $num)),
    ));
    if (!is_wp_error($resp) && (int) wp_remote_retrieve_response_code($resp) === 200) {
        $j = json_decode(wp_remote_retrieve_body($resp), true);
        if (is_array($j) && array_key_exists('valid', $j)) {
            $status = $j['valid'] ? 'valid' : 'invalid';
        }
    }

    // 2) Fallback SOAP se la REST non risponde
    if ($status === 'unavailable' && class_exists('SoapClient')) {
        try {
            $client = new SoapClient('https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl', array(
                'exceptions'         => true,
                'connection_timeout' => 8,
            ));
            $r = $client->checkVat(array('countryCode' => $cc, 'vatNumber' => $num));
            $status = !empty($r->valid) ? 'valid' : 'invalid';
        } catch (Throwable $e) { /* resta unavailable */ }
    }

    // Cache: esiti certi 12h, servizio giù solo 5 min (fail-safe: niente esenzione)
    set_transient($tkey, $status, $status === 'unavailable' ? 5 * MINUTE_IN_SECONDS : 12 * HOUR_IN_SECONDS);
    return $status;
}

/**
 * Decide l'esenzione.
 * Regole: billing UE non-IT + P.IVA del MEDESIMO paese valida su VIES + spedizione UE non-IT (merce lascia l'Italia).
 * @return array{exempt:bool,status:string,vat:string}
 */
function lcgf_euvat_decide($billing_cc, $shipping_cc, $vat_raw) {
    $vat = lcgf_euvat_norm($vat_raw);
    if ($vat !== '' && preg_match('/^\d{11}$/', $vat)) $vat = 'IT' . $vat; // 11 cifre nude = IT
    $out = array('exempt' => false, 'status' => 'none', 'vat' => $vat);
    if ($vat === '') return $out;

    $eu = lcgf_euvat_eu_countries();
    if ($billing_cc === 'IT' || !in_array($billing_cc, $eu, true)) {
        $out['status'] = 'domestic'; // azienda IT (o extra-UE): IVA italiana normale
        return $out;
    }
    $expected = ($billing_cc === 'GR') ? 'EL' : $billing_cc;
    if (substr($vat, 0, 2) !== $expected) {
        $out['status'] = 'country_mismatch'; // P.IVA di un paese diverso dal billing
        return $out;
    }
    $shipping_cc = $shipping_cc ? $shipping_cc : $billing_cc;
    if ($shipping_cc === 'IT' || !in_array($shipping_cc, $eu, true)) {
        $out['status'] = 'ships_to_it'; // consegna in Italia: niente cessione intracomunitaria
        return $out;
    }
    $v = lcgf_euvat_vies($vat);
    $out['status'] = $v;
    $out['exempt'] = ($v === 'valid');
    return $out;
}

/* ============================================================
 * Campo checkout (WooCommerce Blocks — Additional Checkout Fields)
 * ============================================================ */

add_action('woocommerce_init', function () {
    if (!function_exists('woocommerce_register_additional_checkout_field')) return;
    $lang   = lcgf_euvat_lang();
    $labels = array(
        'it' => 'Partita IVA (solo aziende)',
        'en' => 'VAT number (businesses only)',
        'de' => 'USt-IdNr. (nur Unternehmen)',
        'fr' => 'N° TVA intracommunautaire (entreprises)',
    );
    $opt = array(
        'it' => 'Partita IVA (facoltativa, solo aziende)',
        'en' => 'VAT number (optional, businesses only)',
        'de' => 'USt-IdNr. (optional, nur Unternehmen)',
        'fr' => 'N° TVA (facultatif, entreprises)',
    );
    woocommerce_register_additional_checkout_field(array(
        'id'            => 'lcgf/piva',
        'label'         => $labels[$lang],
        'optionalLabel' => $opt[$lang],
        'location'      => 'address',
        'type'          => 'text',
        'required'      => false,
        'attributes'    => array(
            'autocomplete' => 'off',
            'maxLength'    => '16',
            'placeholder'  => 'IT01234567890 / DE123456789',
        ),
    ));
});

// Sanitizzazione del valore
add_filter('woocommerce_sanitize_additional_field', function ($value, $key) {
    if ($key === 'lcgf/piva') $value = lcgf_euvat_norm($value);
    return $value;
}, 10, 2);

// Validazione formale (non blocca per VIES: solo sintassi palesemente errata)
add_action('woocommerce_validate_additional_field', function ($errors, $key, $value) {
    if ($key !== 'lcgf/piva' || $value === '') return;
    if (!lcgf_euvat_format_ok(lcgf_euvat_norm($value))) {
        $msgs = array(
            'it' => 'La Partita IVA inserita non sembra valida (es. IT01234567890, DE123456789, MT12345678).',
            'en' => 'The VAT number entered does not look valid (e.g. IT01234567890, DE123456789, MT12345678).',
            'de' => 'Die eingegebene USt-IdNr. scheint ungültig zu sein (z. B. DE123456789).',
            'fr' => 'Le numéro de TVA saisi ne semble pas valide (ex. FRXX999999999).',
        );
        $errors->add('lcgf_piva_format', $msgs[lcgf_euvat_lang()]);
    }
}, 10, 3);

/* ============================================================
 * Applicazione esenzione sul carrello (ricalcolata a ogni totals)
 * ============================================================ */

// Valore più fresco durante l'update Store API (prima del save del customer)
add_action('woocommerce_store_api_cart_update_customer_from_request', function ($customer, $request) {
    $ba = $request['billing_address'];
    if (is_array($ba) && array_key_exists('lcgf/piva', $ba)) {
        $GLOBALS['lcgf_euvat_req_val'] = lcgf_euvat_norm((string) $ba['lcgf/piva']);
    }
}, 10, 2);

/** Legge la P.IVA corrente del cliente di sessione. */
function lcgf_euvat_cart_vat() {
    if (isset($GLOBALS['lcgf_euvat_req_val'])) return $GLOBALS['lcgf_euvat_req_val'];
    if (!function_exists('WC') || !WC()->customer) return '';
    $v = (string) WC()->customer->get_meta('_wc_billing/lcgf/piva');
    if ($v === '' && class_exists('Automattic\\WooCommerce\\Blocks\\Package')) {
        try {
            $cf = Automattic\WooCommerce\Blocks\Package::container()->get(
                Automattic\WooCommerce\Blocks\Domain\Services\CheckoutFields::class
            );
            $v = (string) $cf->get_field_from_object('lcgf/piva', WC()->customer, 'billing');
        } catch (Throwable $e) { /* noop */ }
    }
    return $v;
}

add_action('woocommerce_before_calculate_totals', function ($cart) {
    if (!function_exists('WC') || !WC()->customer) return;
    static $busy = false;
    if ($busy) return;
    $busy = true;

    $cust   = WC()->customer;
    $pickup = false;
    // Carrello con prodotti "solo ritiro in sede" (cheesecake/tiramisù): il ritiro è
    // inevitabile (filtro #11 in lcgf-fixes) → la merce NON lascia l'Italia, mai esente.
    if ($cart instanceof WC_Cart) {
        foreach ($cart->get_cart() as $ci) {
            $p = isset($ci['data']) ? $ci['data'] : null;
            if ($p && is_callable(array($p, 'get_shipping_class')) && $p->get_shipping_class() === 'solo-ritiro') { $pickup = true; break; }
        }
    }
    // Belt & braces: metodo ritiro già scelto in sessione
    if (!$pickup && WC()->session) {
        foreach ((array) WC()->session->get('chosen_shipping_methods') as $m) {
            if (strpos((string) $m, 'local_pickup') === 0 || strpos((string) $m, 'pickup_location') === 0) { $pickup = true; break; }
        }
    }

    $vat = lcgf_euvat_cart_vat();
    if ($pickup) {
        $d = array('exempt' => false, 'status' => 'pickup', 'vat' => lcgf_euvat_norm($vat));
    } else {
        $d = lcgf_euvat_decide($cust->get_billing_country(), $cust->get_shipping_country(), $vat);
    }
    if ((bool) $cust->get_is_vat_exempt() !== $d['exempt']) {
        $cust->set_is_vat_exempt($d['exempt']);
    }
    $busy = false;
}, 8);

/* ============================================================
 * Timbro sull'ordine + note audit + dicitura reverse charge
 * ============================================================ */

function lcgf_euvat_stamp_order($order) {
    if (!($order instanceof WC_Order)) return;

    $vat = '';
    if (class_exists('Automattic\\WooCommerce\\Blocks\\Package')) {
        try {
            $cf  = Automattic\WooCommerce\Blocks\Package::container()->get(
                Automattic\WooCommerce\Blocks\Domain\Services\CheckoutFields::class
            );
            $vat = (string) $cf->get_field_from_object('lcgf/piva', $order, 'billing');
        } catch (Throwable $e) { /* noop */ }
    }
    if ($vat === '') $vat = (string) $order->get_meta('_wc_billing/lcgf/piva');
    if ($vat === '') return; // nessuna P.IVA: nulla da timbrare

    $pickup = false;
    foreach ($order->get_shipping_methods() as $sm) {
        if (strpos($sm->get_method_id(), 'local_pickup') === 0) { $pickup = true; break; }
    }
    if (!$pickup) {
        foreach ($order->get_items() as $item) {
            $p = is_callable(array($item, 'get_product')) ? $item->get_product() : null;
            if ($p && $p->get_shipping_class() === 'solo-ritiro') { $pickup = true; break; }
        }
    }
    if ($pickup) {
        $d = array('exempt' => false, 'status' => 'pickup', 'vat' => lcgf_euvat_norm($vat));
    } else {
        $ship = $order->get_shipping_country();
        $d = lcgf_euvat_decide($order->get_billing_country(), $ship ? $ship : $order->get_billing_country(), $vat);
    }

    $order->update_meta_data('_lcgf_euvat_number', $d['vat']);
    $order->update_meta_data('_lcgf_euvat_status', $d['status']);
    $order->update_meta_data('_lcgf_euvat_exempt', $d['exempt'] ? 'yes' : 'no');

    if ($d['exempt']) {
        $order->add_order_note(sprintf(
            'IVA non applicata — cessione intracomunitaria non imponibile art. 41 D.L. 331/1993 (reverse charge). P.IVA %s verificata su VIES.',
            $d['vat']
        ));
    } elseif ($d['status'] === 'invalid') {
        $order->add_order_note(sprintf('P.IVA %s NON risultata valida su VIES: applicata IVA italiana.', $d['vat']));
    } elseif ($d['status'] === 'unavailable') {
        $order->add_order_note(sprintf('VIES non raggiungibile per la P.IVA %s: applicata IVA italiana in via prudenziale.', $d['vat']));
    } elseif ($d['status'] === 'ships_to_it' || $d['status'] === 'pickup') {
        $order->add_order_note(sprintf('P.IVA %s registrata, ma consegna in Italia/ritiro in sede: IVA italiana applicata (nessuna cessione intracomunitaria).', $d['vat']));
    }
    $order->save();
}

add_action('woocommerce_store_api_checkout_order_processed', 'lcgf_euvat_stamp_order');
add_action('woocommerce_checkout_order_processed', function ($order_id) {
    $o = wc_get_order($order_id);
    if ($o) lcgf_euvat_stamp_order($o);
}, 10, 1);

/** Dicitura reverse charge (bilingue IT/EN) su pagina ordine + email. */
function lcgf_euvat_note_html($order) {
    if (!($order instanceof WC_Order) || $order->get_meta('_lcgf_euvat_exempt') !== 'yes') return '';
    $vat = esc_html($order->get_meta('_lcgf_euvat_number'));
    return '<p style="margin:14px 0;padding:12px 16px;background:#f4f1e8;border-left:4px solid #6b8e4e;border-radius:6px;font-size:13px;line-height:1.6;color:#4a4438;">'
        . "Operazione non imponibile IVA ai sensi dell'art. 41 D.L. 331/1993 (reverse charge) &mdash; P.IVA <strong>{$vat}</strong> verificata su VIES."
        . '<br><em>VAT-exempt intra-EU supply pursuant to Art. 41 of Italian Law Decree no. 331/1993 (reverse charge) &mdash; VAT ID <strong>' . $vat . '</strong> verified via VIES.</em></p>';
}

add_action('woocommerce_order_details_after_order_table', function ($order) {
    echo lcgf_euvat_note_html($order); // phpcs:ignore WordPress.Security.EscapeOutput
});
add_action('woocommerce_email_after_order_table', function ($order, $sent_to_admin, $plain_text) {
    if ($plain_text) {
        if ($order instanceof WC_Order && $order->get_meta('_lcgf_euvat_exempt') === 'yes') {
            echo "\nOperazione non imponibile IVA art. 41 D.L. 331/1993 (reverse charge) - P.IVA " . $order->get_meta('_lcgf_euvat_number') . " verificata su VIES.\n";
        }
        return;
    }
    echo lcgf_euvat_note_html($order); // phpcs:ignore WordPress.Security.EscapeOutput
}, 10, 3);
