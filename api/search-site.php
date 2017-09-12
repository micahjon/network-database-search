<?php
/**
 * Searches site database table(s) for specified string
 * params:
 * 		- term (string to search for)
 * 		- queryTypes (database query types to run - each corresponds to a function)
 * 	
 * output: 
 * 		JSON Array of Objects:
 * 			{ (int) id, (string) name }
 */
function nds_restapi_route_search_site()
{
	global $ndsUserId;
	$ndsUserId = get_current_user_id();

	// Just for testing
	// $ndsUserId  = 124;

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
			'queryTypes' => [
				'required' => true,
				'type' => 'string',
				'validate_callback' => function($queryTypes)
				{
					if ( empty($queryTypes) ) return false;

					// Get query names from comma-separated string
					$queryTypes = array_map('trim', explode(',', $queryTypes));

					// Ensure there are no duplicate queryTypes
					if ( count($queryTypes) !== count(array_unique($queryTypes)) ) {
						return new WP_Error('nds_invalid_query', "Duplicate query", ['status' => 400]);
					}

					// Each query must correspond to a function
					// e.g. 'posts,postmeta' correspond to nds_query_posts() & nds_query_postmeta()
					foreach ($queryTypes as $query) {
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

			return is_super_admin($ndsUserId) or user_can($ndsUserId, 'administrator');
		}
	]);
}
add_action('rest_api_init', 'nds_restapi_route_search_site');


function nds_restapi_search_site( WP_REST_Request $request )
{
	// Get search term
	$term = $request['term'];

	// Get names of search queryTypes
	$queryNames = array_map('trim', explode(',', $request['queryTypes']));
	
	// Get search results
	$results = [];
	foreach ($queryNames as $queryName) {
		$results[$queryName] = call_user_func("nds_query_{$queryName}", $term);
	}

	// Apply 'early' filter to all results
	$results = apply_filters('nds_search_results_early', $results, $term);
	
	// Apply individual filters (by query type)
	foreach ($results as $queryType => $resultsOfType) {
		$results[$queryType] = apply_filters('nds_search_results_'. $queryType, $resultsOfType, $term);
	}

	// Apply 'late' filter to all results
	$results = apply_filters('nds_search_results', $results, $term);
	
	return new WP_REST_Response($results , 200);
}
