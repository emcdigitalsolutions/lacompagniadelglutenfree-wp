<?php
/**
 * Plugin Name: LCGF — Sync stock tra traduzioni Polylang
 * Description: Ogni prodotto esiste in 4 lingue come post separati (Polylang) con
 *   stock indipendente. Senza sincronizzazione, mettere un prodotto "esaurito" in
 *   IT lo lascia disponibile in EN/DE/FR → un cliente estero potrebbe comprarlo.
 *   Questo plugin propaga stock_status (+ manage_stock e quantità, se gestita) dal
 *   prodotto modificato alle sue traduzioni, mantenendo le 4 lingue allineate.
 * Note: la sincronizzazione è a livello di PRODOTTO. Lo stock per-variazione dei
 *   prodotti variabili (cheesecake/tiramisù/crostata) NON è sincronizzato: oggi
 *   le quantità non sono gestite (manage_stock=N), quindi il rischio reale è solo
 *   lo stato del prodotto, che qui è coperto.
 *
 * @author EMC Digital Solutions
 */

if (!defined('ABSPATH')) exit;

/**
 * Guardia anti-ricorsione condivisa fra gli handler: salvare una traduzione fa
 * scattare di nuovo gli stessi hook → senza guardia si avrebbe un loop infinito.
 */
function lcgf_stock_sync_busy($set = null) {
    static $busy = false;
    if ($set !== null) $busy = (bool) $set;
    return $busy;
}

/** Traduzioni Polylang di un prodotto, esclusa la lingua di partenza. */
function lcgf_stock_sync_targets($product_id) {
    if (!function_exists('pll_get_post_translations')) return array();
    $trans = pll_get_post_translations($product_id);
    if (!is_array($trans) || count($trans) < 2) return array();
    $out = array();
    foreach ($trans as $pid) {
        if ((int) $pid !== (int) $product_id) $out[] = (int) $pid;
    }
    return $out;
}

/**
 * Cambio di stock_status (es. admin mette "Esaurito"/"Disponibile", o il flusso
 * back-in-stock) → propaga lo stato alle traduzioni.
 */
add_action('woocommerce_product_set_stock_status', function ($product_id, $status, $product = null) {
    if (lcgf_stock_sync_busy()) return;
    $targets = lcgf_stock_sync_targets($product_id);
    if (empty($targets)) return;

    lcgf_stock_sync_busy(true);
    foreach ($targets as $pid) {
        $p = wc_get_product($pid);
        if (!$p) continue;
        if ($p->get_stock_status() !== $status) {
            $p->set_stock_status($status);
            $p->save();
        }
    }
    lcgf_stock_sync_busy(false);
}, 20, 3);

/**
 * Cambio di quantità / gestione magazzino → propaga manage_stock, quantità e stato
 * alle traduzioni (utile se in futuro si attiva il tracciamento delle giacenze).
 */
add_action('woocommerce_product_set_stock', function ($product) {
    if (lcgf_stock_sync_busy()) return;
    if (!is_a($product, 'WC_Product')) return;
    $product_id = $product->get_id();
    $targets    = lcgf_stock_sync_targets($product_id);
    if (empty($targets)) return;

    $manage = $product->get_manage_stock();
    $qty    = $product->get_stock_quantity();
    $status = $product->get_stock_status();

    lcgf_stock_sync_busy(true);
    foreach ($targets as $pid) {
        $p = wc_get_product($pid);
        if (!$p) continue;
        $changed = false;
        if ($p->get_manage_stock() !== $manage) { $p->set_manage_stock($manage); $changed = true; }
        if ($manage && $p->get_stock_quantity() !== $qty) { $p->set_stock_quantity($qty); $changed = true; }
        if ($p->get_stock_status() !== $status) { $p->set_stock_status($status); $changed = true; }
        if ($changed) $p->save();
    }
    lcgf_stock_sync_busy(false);
}, 20, 1);
