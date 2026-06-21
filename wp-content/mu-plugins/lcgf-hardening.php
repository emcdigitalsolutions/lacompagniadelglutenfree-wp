<?php
/* Plugin Name: LCGF Hardening
   Description: Hardening leggero set-and-forget: nasconde versione WP, disabilita XML-RPC, blocca enumerazione utenti. */
remove_action('wp_head','wp_generator');
add_filter('the_generator','__return_empty_string');
add_filter('xmlrpc_enabled','__return_false');
add_action('template_redirect', function(){
  if (!is_admin() && isset($_GET['author']) && is_numeric($_GET['author'])) {
    wp_safe_redirect(home_url('/'), 301); exit;
  }
});