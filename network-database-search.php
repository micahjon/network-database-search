<?php
/*
	Plugin Name: Network Database Search
	Description: ...
	Version: 0.1
	Author: Micah Miller-Eshleman
	Author URI: http://micahjon.com
*/				

require 'api/get-sites.php';
// require 'api/search-blog.php';

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
 * Generate "Database Search" page HTML
 */
function nds_page_content()
{
	echo 'test';
}