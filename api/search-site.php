<?php
/**
 * Helper functions used for database search queryTypes
 */
require 'query-types.php';

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
	$ndsUserId  = 124;

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

	$results = apply_filters('nds_search_results', $results, $term);
	
	return new WP_REST_Response($results , 200);
}

/**
 * Combine posts & postmeta
 */
function nds_add_postmeta_to_posts($results)
{
	$results = nds_add_child_objects_to_parents('postmeta', 'posts', 'post_id', 'id', $results);

	// Get parent posts that haven't already been queried
	if ( !empty($results['postmeta']) ) {

		global $wpdb;
		
		$parentIds = join(',', array_unique(array_map(function($postmeta)
		{
			return $postmeta->post_id;
		}, $results['postmeta'])));

		// Sanitize parent ids 
		// Probably not necessary, but better to play things safe!
		// Source: https://coderwall.com/p/zepnaw/sanitizing-queries-with-in-clauses-with-wpdb-on-wordpress
		// $format = '%d, %d, %d, %d, %d, [...]'
		$format = implode(', ', array_fill(0, count($parentIds), '%d'));

		$table = $wpdb->prefix . 'posts';

		$queryResults = $wpdb->get_results($wpdb->prepare(
			"
			SELECT id, post_content, post_name, post_title, post_type
			FROM {$table}
			WHERE id IN ({$format})
			",
			$parentIds
		));

		$parentPosts = array_map(function($result) use (&$results)
		{
			$result->__title = 'post_title';
			$result->__link = get_edit_post_link($result->id, '');			

			// Omit post content
			$result->post_content = '(omitted)';

			// Add postmeta with this parent as children, and remove them from postmeta array
			foreach ($results['postmeta'] as $key => $postmeta) {
				if ( $postmeta->post_id === $result->id ) {
					
					if (empty($result->postmeta)) $result->postmeta = [];
					$result->postmeta[] = $postmeta;
					
					unset($results['postmeta'][$key]);
				}
			}

			return $result;

		}, $queryResults);

		// Append to posts array
		$results['posts'] = array_merge($results['posts'], $parentPosts);

		// Reset postmeta keys
		$results['postmeta'] = array_values($results['postmeta']);
	}

	return $results;
}
add_filter('nds_search_results', 'nds_add_postmeta_to_posts');

/**
 * Combine gravity form entries & form settings/fields
 */
function nds_add_entries_to_forms($results)
{
	return nds_add_child_objects_to_parents('gravityformentries', 'gravityforms', 'form_id', 'id', $results);

	return $results;
}
add_filter('nds_search_results', 'nds_add_entries_to_forms');


function nds_add_child_objects_to_parents($children, $parents, $foreignKey, $parentKey, $results)
{
	if ( empty($results[$children]) or empty($results[$parents]) ) return $results;

	foreach ($results[$children] as $childIndex => $child) {
		// Search for matching post
		foreach ($results[$parents] as $parentIndex => $parent) {
			
			if ( $child->$foreignKey === $parent->$parentKey ) {

				// Matching parent found. Append the child result to the parent
				if ( empty($results[$parents][$parentIndex]->$children) ) {
					$results[$parents][$parentIndex]->$children = [];
				}

				$results[$parents][$parentIndex]->$children[] = $child;

				unset($results[$children][$childIndex]);

				break;
			}
		}
	}

	// Reset $children array keys
	$results[$children] = array_values($results[$children]);

	return $results;
}








