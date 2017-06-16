<?php
/**
 * Return site metadata for each site on the multisite that the current user
 * has admin access to (or an empty array)
 * params => (none)
 * output => JSON Array of Objects:
 * 			{ (int) id, (string) name }
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
		$sites = array_map(function($site)
		{
			$id = $site->blog_id;

			return [
				'id' => (int) $id,
				'name' => get_blog_details($id)->blogname
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
				'name' => $site->blogname
			];
		}, $sites));
	}

	return new WP_REST_Response($sites , 200);
}
