<?php
/**
 * Return site metadata for each site on the multisite that the current user
 * has admin access to (or an empty array)
 * params => (none)
 * output => JSON Array of Objects:
 * 			{ (int) id, (string) name, (string) rest_url }
 */
function nds_restapi_route_get_sites()
{
	global $ndsUserId;

	// Get current user id (production) or hard-coded one (development)
	$ndsUserId = defined('NDS_PLUGIN_DEV_USER_ID') ? NDS_PLUGIN_DEV_USER_ID : get_current_user_id();

	register_rest_route( 'nds/v1', '/get-sites', [
		'methods' => 'GET',
		'callback' => 'nds_restapi_get_sites',
	]);
}
add_action('rest_api_init', 'nds_restapi_route_get_sites');


function nds_restapi_get_sites( WP_REST_Request $request )
{
	// Get current user id
	global $ndsUserId;
	$userId = $ndsUserId;

	// If they're a super admin, return all sites
	if ( is_super_admin($userId) ) {
		$sites = array_map(function($site)
		{
			$id = $site->blog_id;

			return [
				'id' => (int) $id,
				// Output single quotes, don't encode as "&#039;"
				'name' => wp_specialchars_decode(get_blog_details($id)->blogname, ENT_QUOTES),
				'rest_url' => get_rest_url($id, 'nds/v1/')
			];

		}, get_sites());
	}

	// Otherwise, return sites they are admins on
	else {
		$sites = array_filter(get_blogs_of_user($userId), function($site) use ($userId)
		{
			$user = new WP_User($userId, '', $site->userblog_id);
			return ( !empty($user->roles[0]) and $user->roles[0] === 'administrator' );
		});

		$sites = array_values(array_map(function($site)
		{
			return [
				'id' => $site->userblog_id,
				// Output single quotes, don't encode as "&#039;"
				'name' => wp_specialchars_decode($site->blogname, ENT_QUOTES),
				'rest_url' => get_rest_url($site->userblog_id, 'nds/v1/')
			];
		}, $sites));
	}

	return new WP_REST_Response($sites , 200);
}
