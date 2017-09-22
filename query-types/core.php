<?php

/**
 * Add query types for core WordPress objects
 * @param  Array $queryTypes 
 * @return Array             $queryTypes
 */
function nds_add_core_query_types($queryTypes)
{
	return array_merge($queryTypes, [
		['id' => 'posts', 'name' => 'Posts', 'description' => 'Search post titles, content, and slugs. Ignore deleted and draft posts.'],
		['id' => 'postmeta', 'name' => 'Custom Fields', 'description' => 'Search all custom fields.'],
		['id' => 'options', 'name' => 'Options', 'description' => 'Search Wordpress site & plugin settings.'],
	]);
}
add_filter('nds_query_types', 'nds_add_core_query_types', 5);

/**
 * Database query functions for searching posts, postmeta, and options
 */

function nds_query_posts($term, $limit = 10)
{
	global $wpdb;

	$table = $wpdb->prefix . 'posts';

	return $wpdb->get_results($wpdb->prepare(
		"
		SELECT id, post_content, post_name, post_title, post_type, post_parent
		FROM {$table}
		WHERE post_type NOT IN ('acf-field', 'acf-field-group', 'revision')
		AND post_status NOT IN ('draft', 'trash', 'auto-draft')
		AND (
			post_title LIKE '%%%s%%' OR
			post_name LIKE '%%%s%%' OR
			post_content LIKE '%%%s%%'
		)
		LIMIT %d
		",
		$term,
		$term,
		$term,
		$limit
	));
}

function nds_query_postmeta($term, $limit = 10)
{
	global $wpdb;

	$postmetaTable = $wpdb->prefix . 'postmeta';
	$postsTable = $wpdb->prefix . 'posts';

	return $wpdb->get_results( $wpdb->prepare(
		"
		SELECT postmeta.post_id, postmeta.meta_key, postmeta.meta_value
		FROM {$postmetaTable} AS postmeta
		INNER JOIN {$postsTable} AS posts 
			ON posts.id = postmeta.post_id
		WHERE posts.post_type NOT IN ('acf-field', 'acf-field-group', 'revision')
			AND posts.post_status NOT IN ('draft', 'trash', 'auto-draft')
			AND postmeta.meta_value LIKE '%%%s%%'
		LIMIT %d
		",
		$term,
		$limit
	));
}

function nds_query_options($term, $limit = 10)
{
	global $wpdb;

	$table = $wpdb->prefix . 'options';

	return $wpdb->get_results( $wpdb->prepare(
		"
		SELECT option_id, option_name, option_value
		FROM {$table} 
		WHERE option_value LIKE '%%%s%%'
		LIMIT %d
		",
		$term,
		$limit
	));
}

