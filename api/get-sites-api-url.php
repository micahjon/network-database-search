<?php
/**
 * Simple returns WP REST API route for 'get-sites'
 * Uses admin-ajax.php since Preact client knows window.ajaxurl global JS variable
 */

function nds_admin_ajax_get_sites_api_url() 
{
	wp_die(get_rest_url(null, 'nds/v1/get-sites/'));
}
add_action('wp_ajax_nds_get_sites_api_url', 'nds_admin_ajax_get_sites_api_url');

// For testing, but not a security issue so it's ok to leave it in production
add_action('wp_ajax_nopriv_nds_get_sites_api_url', 'nds_admin_ajax_get_sites_api_url');