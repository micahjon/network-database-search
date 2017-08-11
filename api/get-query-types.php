<?php
/**
 * Return list of search query types
 * params => (none)
 * output => JSON Array of Objects:
 * 			{ (string) id, (string) name, (string) description }
 */
function nds_restapi_route_get_query_types()
{
	register_rest_route( 'nds/v1', '/get-query-types', [
		'methods' => 'GET',
		'callback' => 'nds_restapi_get_query_types',
	]);
}
add_action('rest_api_init', 'nds_restapi_route_get_query_types');

function nds_restapi_get_query_types( WP_REST_Request $request )
{
	$queryTypes = [
		['id' => 'posts', 'name' => 'Posts', 'description' => 'Search post titles, content, and slugs. Ignore deleted and draft posts.'],
		['id' => 'postmeta', 'name' => 'Custom Fields', 'description' => 'Search all custom fields.'],
		['id' => 'options', 'name' => 'Options', 'description' => 'Search Wordpress site & plugin settings.'],
		['id' => 'gravityforms', 'name' => 'Gravity Form Settings', 'description' => 'Search Gravity Form confirmations, notifications, and fields.'],
		['id' => 'gravityformentries', 'name' => 'Gravity Form Entries', 'description' => 'Search Gravity Form entries.'],
	];

	return new WP_REST_Response($queryTypes , 200);
}
