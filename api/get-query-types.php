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
	$queryTypes = apply_filters('nds_query_types', []);

	return new WP_REST_Response($queryTypes , 200);
}
