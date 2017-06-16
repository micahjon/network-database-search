<?php
/**
 * Return site metadata for each site on the multisite that the current user
 * has admin access to (or an empty array)
 * params => (none)
 * output => JSON 
 */
function nds_restapi_route_get_sites()
{
	global $ndsUserId;
	$ndsUserId = get_current_user_id();

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
		$sites = get_sites();
	}

	// Otherwise, return sites they are admins on
	else {
		$sites = get_blogs_of_user($userId);
		$sites = array_filter($sites, function($site)
		{
			$user = new WP_User($userId, '', $site->user_id);
			return ( !empty($user->roles[0]) and $user->roles[0] === 'administrator' );
		});
	}

	return new WP_REST_Response($sites , 200);
}
