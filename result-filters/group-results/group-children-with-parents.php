<?php
/**
 * Group child objects with parent objects
 * Runs late, after all the objects have been filtered
 */
add_filter('nds_search_results', 'nds_group_children_with_parents', 50);

function nds_group_children_with_parents($results)
{
	// Add postmeta to posts
	$results = nds_add_child_objects_to_parents('postmeta', 'posts', 'post_id', 'id', $results);

	// Add entries to gravity forms
	$results = nds_add_child_objects_to_parents('gravityformentries', 'gravityforms', 'form_id', 'id', $results);

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