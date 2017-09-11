<?php
/**
 * Combine posts & postmeta
 */
add_filter('nds_search_results_early', 'nds_add_postmeta_to_posts');
function nds_add_postmeta_to_posts($results)
{
	$results = nds_add_child_objects_to_parents('postmeta', 'posts', 'post_id', 'id', $results);

	// Get parent posts that haven't already been queried
	if ( !empty($results['postmeta']) ) {

		global $wpdb;
		
		$parentIds = array_unique(array_map(function($postmeta)
		{
			return $postmeta->post_id;
			
		}, $results['postmeta']));

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

/**
 * Combine gravity form entries & form settings/fields
 */
add_filter('nds_search_results_early', 'nds_add_entries_to_forms');
function nds_add_entries_to_forms($results)
{
	return nds_add_child_objects_to_parents('gravityformentries', 'gravityforms', 'form_id', 'id', $results);

	return $results;
}

/**
 * Helper function to add child objects to parents
 */
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