<?php
/**
 * Searches site database table(s) for specified string
 * params:
 * 		- term (string to search for)
 * 		- schema (JSON object with table names and column names)
 * 	
 * output: 
 * 		JSON Array of Objects:
 * 			{ (int) id, (string) name }
 */
function nds_restapi_route_search_site()
{
	global $ndsUserId;
	$ndsUserId = get_current_user_id();

	register_rest_route( 'nds/v1', '/search/', [
		'methods' => 'GET',
		'callback' => 'nds_restapi_search_site',
		'args' => [
			'term' => [
				'required' => true,
				'type' => 'string',
				'validate_callback' => function($term)
				{
					// Search term must be at least 3 characters
					return strlen($term) >= 3;
				}
			],
			'queries' => [
				'required' => true,
				'type' => 'string',
				'validate_callback' => function($queries)
				{
					if ( empty($queries) ) return false;

					// Get query names from comma-separated string
					$queries = array_map('trim', explode(',', $queries));

					// Ensure there are no duplicate queries
					if ( count($queries) !== count(array_unique($queries)) ) {
						return new WP_Error('nds_invalid_query', "Duplicate query", ['status' => 400]);
					}

					// Each query must correspond to a function
					// e.g. 'posts,postmeta' correspond to nds_query_posts() & nds_query_postmeta()
					foreach ($queries as $query) {
						if ( !function_exists('nds_query_'. $query) ) {
							return new WP_Error('nds_invalid_query', "Query '{$query}' has no corresponding function: nds_query_{$query}()", ['status' => 400]);
						}
					}

					return true;
				}
			]
		],
		'permission_callback' => function()
		{
			global $ndsUserId;
			return is_super_admin($ndsUserId) or current_user_can('administrator');
		}
	]);
}
add_action('rest_api_init', 'nds_restapi_route_search_site');


function nds_restapi_search_site( WP_REST_Request $request )
{
	// Used to generate table names on this site
	global $blog_id, $wpdb;

	// Get search term
	$term = $request['term'];
	
	// Get query names and convert them to function names
	$queryFunctions = array_map(function($query)
	{
		return 'nds_query_'. trim($query);
	}, 
	explode(',', $request['queries']));

	// Get search results
	$results = array_map(function($queryFunction) use ($term)
	{
		return call_user_func($queryFunction, $term);
	}, 
	$queryFunctions);
	
	return new WP_REST_Response($results , 200);
}