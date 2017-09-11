<?php
/*
	Plugin Name: Network Database Search
	Description: ...
	Version: 0.1
	Author: Micah Miller-Eshleman
	Author URI: http://micahjon.com
*/				

require 'queries.php';

/**
 * Register REST APIs
 */
// Get url of REST API (via admin-ajax, whose url is accessible in browser as window.ajaxurl)
require 'api/get-rest-api-url.php';

// Get list of sites
require 'api/get-sites.php';

// Get list of available search queries
require 'api/get-query-types.php';

// Search sites
require 'api/search-site.php';

// Filters for common result manipulations
require 'filters/add-edit-links.php';
require 'filters/add-titles.php';
require 'filters/clip-long-fields.php';
require 'filters/parent-child-objects.php';

/**
 * Add "Database Search" menu item under Settings on both Site Dashboard and Network Dashboard menus
 * 
 * @param  [type] $parentPageSlug slug (PHP filename) of Settings page
 * @return Array                  add_submenu_page() parameters
 */
function nds_menu_item_params($parentPageSlug)
{
	return [
		$parentPageSlug, 
		'Network Database Search', 
		'Database Search', 
		'manage_options', 
		'network-database-search', 
		'nds_page_content'
	];
}
add_action('network_admin_menu', function()
{
	call_user_func_array('add_submenu_page', nds_menu_item_params('settings.php'));
});
add_action('admin_menu', function()
{
	call_user_func_array('add_submenu_page', nds_menu_item_params('options-general.php'));
});

/**
 * Generate "Database Search" page HTML (client-side using Preact)
 */
function nds_page_content()
{
	echo '<script type="text/javascript" src="'. plugin_dir_url( __FILE__ ) . 'preact-ui/build/bundle.js"></script>';
}

/**
 * Register search query types
 */

// ...............................................

/**
 * For local development only
 */
add_filter('allowed_http_origins', 'add_allowed_origins');

function add_allowed_origins($origins) {
    $origins[] = 'http://localhost:3000';
    return $origins;
}