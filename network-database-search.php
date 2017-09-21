<?php
/*
	Plugin Name: Network Database Search
	Description: Powerful multisite database search for WordPress administrators. Search posts, menus, options, custom fields, and forms.
	Version: 0.1
	Author: Micah Miller-Eshleman
	Author URI: http://micahjon.com
	License: GPL2
	License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/				

/**
 * WordPress database query functions
 */
require 'queries.php';

/**
 * REST APIs
 */
// Get url of REST API (via admin-ajax, whose url is accessible in browser as window.ajaxurl)
require 'api/get-rest-api-url.php';

// Get list of sites
require 'api/get-sites.php';

// Get list of available search queries
require 'api/get-query-types.php';

// Search sites
require 'api/search-site.php';

/**
 * Filters for common result manipulations
 */
require 'filters/add-edit-links.php';
require 'filters/add-titles.php';
require 'filters/clip-long-fields.php';
require 'filters/group-results/get-missing-parents.php';
require 'filters/group-results/group-children-with-parents.php';

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
	$pluginVersion = get_plugin_data( __FILE__ )['Version'];
	$pluginDir = plugin_dir_url( __FILE__ );

	// Development: only use a large JS bundle
	if (defined('NDS_PLUGIN_DEV_USER_ID')) {
		$jsPath = $pluginDir .'preact-ui/build/bundle.js?v='. $pluginVersion;
	}
	// Production: use small JS bundle and external stylesheet
	else {
		$cssPath = $pluginDir .'preact-ui/dist/style.css?v='. $pluginVersion;
		$jsPath = $pluginDir .'preact-ui/build/bundle.js?v='. $pluginVersion;
	}
?>
	
	<?php if ( isset($cssPath) ): ?>
		<link rel="stylesheet" type="text/css" href="<?php echo $cssPath ?>">
	<?php endif; ?>

	<div id="nds-root"></div>

	<script type="text/javascript" src="<?php echo $jsPath; ?>"></script>

<?php 
}

/**
 * For local development, allow http://localhost:3000 origin
 */
if ( defined('NDS_PLUGIN_DEV_USER_ID') ) {
	add_filter('allowed_http_origins', 'nds_allow_dev_origin');
}
function nds_allow_dev_origin($origins) 
{
    $origins[] = 'http://localhost:3000';
    return $origins;
}