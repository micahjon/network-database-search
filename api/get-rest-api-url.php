<?php
/**
 * Simple returns WP REST API route for 'get-sites'
 * Uses admin-ajax.php since Preact client knows window.ajaxurl global JS variable
 */

function nds_admin_ajax_get_rest_api_url() 
{
	// In production, use standard WordPress REST API url
	$restUrl = get_rest_url(null, 'nds/v1');
	
	// For development, use a custom domain of Webpack's BrowserSync proxy, e.g. localhost:3000
	if ( defined('NDS_PLUGIN_DEV_DOMAIN') ) {
		$restUrl = NDS_PLUGIN_DEV_DOMAIN . parse_url($restUrl, PHP_URL_PATH);
	}

	wp_die($restUrl);
}
add_action('wp_ajax_nds_get_rest_api_url', 'nds_admin_ajax_get_rest_api_url');

// Used for testing, but not a security issue so it's ok to leave it in production
add_action('wp_ajax_nopriv_nds_get_rest_api_url', 'nds_admin_ajax_get_rest_api_url');