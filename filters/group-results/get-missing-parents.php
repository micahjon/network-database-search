<?php
/**
 * Fetch parent objects for parent-less child objects
 * Run early so that filters can populate these parents with default
 * fields, __title, __link, etc.
 */
add_filter('nds_search_results_early', 'nds_fetch_parent_objects');

function nds_fetch_parent_objects($results)
{
	$results = nds_fetch_posts_for_postmeta($results);
	
	$results = nds_fetch_gforms_for_entries($results);

	return $results;
}

function nds_fetch_posts_for_postmeta($results)
{
	global $wpdb;
	
	if ( empty($results['postmeta']) or !array_key_exists('posts', $results) ) {
		return $results;
	}

	// Get all parent post ids except those we've already queried
	$missingPostIds = nds_get_missing_parent_ids($results['postmeta'], $results['posts'], 'post_id', 'id');

	if ( empty($missingPostIds) ) return $results;

	// Sanitize parent ids 
	// Source: https://coderwall.com/p/zepnaw/sanitizing-queries-with-in-clauses-with-wpdb-on-wordpress
	// $format = '%d, %d, %d, %d, %d, [...]'
	$format = implode(', ', array_fill(0, count($missingPostIds), '%d'));

	$table = $wpdb->prefix . 'posts';

	$parentResults = $wpdb->get_results($wpdb->prepare(
		"
		SELECT id, post_content, post_name, post_title, post_type
		FROM {$table}
		WHERE id IN ({$format})
		",
		$missingPostIds
	));

	// Merge missing parent posts with existing parent posts
	$results['posts'] = array_merge($results['posts'], $parentResults);
	
	return $results;
}

function nds_fetch_gforms_for_entries($results)
{
	global $wpdb;
	
	if ( empty($results['gravityformentries']) or !array_key_exists('gravityforms', $results) ) {
		return $results;
	}

	// Get all parent form ids except those we've already queried
	$missingFormIds = nds_get_missing_parent_ids($results['gravityformentries'], $results['gravityforms'], 'form_id', 'id');

	if ( empty($missingFormIds) ) return $results;

	// Sanitize parent ids 
	// Source: https://coderwall.com/p/zepnaw/sanitizing-queries-with-in-clauses-with-wpdb-on-wordpress
	// $format = '%d, %d, %d, %d, %d, [...]'
	$format = implode(', ', array_fill(0, count($missingFormIds), '%d'));

	$formTable = $wpdb->prefix . 'rg_form';
	$metaTable = $wpdb->prefix . 'rg_form_meta';

	$formResults = $wpdb->get_results($wpdb->prepare(
		"
		SELECT  form.id, form.is_active, form.is_trash, form.title, 
				meta.display_meta, meta.confirmations, meta.notifications
		FROM {$formTable} as form
		INNER JOIN {$metaTable} AS meta
			ON form.id = meta.form_id
		WHERE id IN ({$format})
		",
		$missingFormIds
	));

	// Merge missing parent forms with existing parent forms
	$results['gravityforms'] = array_merge($results['gravityforms'], $formResults);
	
	return $results;
}

function nds_get_missing_parent_ids($children, $parents, $foreignKey, $parentKey)
{
	$desiredParentIds = array_unique(array_map(function($child) use ($foreignKey)
	{
		return $child->$foreignKey;

	}, $children));

	$existingParentIds = array_unique(array_map(function($parent) use ($parentKey)
	{
		return $parent->$parentKey;

	}, $parents));

	return array_diff($desiredParentIds, $existingParentIds);
}
