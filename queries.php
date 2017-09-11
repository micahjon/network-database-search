<?php

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

function nds_query_gravityforms($term, $limit = 10)
{
	global $wpdb;

	$formTable = $wpdb->prefix . 'rg_form';
	$metaTable = $wpdb->prefix . 'rg_form_meta';

	return $wpdb->get_results( $wpdb->prepare(
		"
		SELECT  form.id, form.is_active, form.is_trash, form.title, 
				meta.display_meta, meta.confirmations, meta.notifications
		FROM {$formTable} as form
		INNER JOIN {$metaTable} AS meta
			ON form.id = meta.form_id
		WHERE (
			meta.display_meta LIKE '%%%s%%' OR
			meta.confirmations LIKE '%%%s%%' OR
			meta.notifications LIKE '%%%s%%'
		)
		LIMIT %d
		",
		$term,
		$term,
		$term,
		$limit
	));
}

function nds_query_gravityformentries($term, $limit = 10)
{
	global $wpdb;

	$formTable = $wpdb->prefix . 'rg_form';
	$entryTable = $wpdb->prefix . 'rg_lead_detail';

	return $wpdb->get_results( $wpdb->prepare(
		"
		SELECT form_id, lead_id, field_number, value
		FROM {$entryTable}
		WHERE value LIKE '%%%s%%'
		LIMIT %d
		",
		$term,
		$limit
	));
}
