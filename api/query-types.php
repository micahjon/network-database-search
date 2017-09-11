<?php

function nds_query_posts($term, $limit = 10)
{
	global $wpdb;

	$table = $wpdb->prefix . 'posts';

	$results = $wpdb->get_results($wpdb->prepare(
		"
		SELECT id, post_content, post_name, post_title, post_type
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

	return array_map(function($result) use ($term)
	{
		$result->__title = 'post_title';
		$result->__link = get_edit_post_link($result->id, '');

		// Clip post content
		$result = nds_clip_property($result, 'post_content', $term);

		return $result;

	}, $results);
}

function nds_query_postmeta($term, $limit = 10)
{
	global $wpdb;

	$postmetaTable = $wpdb->prefix . 'postmeta';
	$postsTable = $wpdb->prefix . 'posts';

	$results = $wpdb->get_results( $wpdb->prepare(
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

	return array_map(function($result) use ($term)
	{
		$result->__title = 'meta_key';

		// Clip meta value
		$result = nds_clip_property($result, 'meta_value', $term);

		return $result;

	}, $results);
}

function nds_query_options($term, $limit = 10)
{
	global $wpdb;

	$table = $wpdb->prefix . 'options';

	$results = $wpdb->get_results( $wpdb->prepare(
		"
		SELECT option_id, option_name, option_value
		FROM {$table} 
		WHERE option_value LIKE '%%%s%%'
		LIMIT %d
		",
		$term,
		$limit
	));

	return array_map(function($result) use ($term)
	{
		$result->__title = 'option_name';

		// Clip option value
		$result = nds_clip_property($result, 'option_value', $term);

		return $result;

	}, $results);
}

function nds_query_gravityforms($term, $limit = 10)
{
	global $wpdb;

	$formTable = $wpdb->prefix . 'rg_form';
	$metaTable = $wpdb->prefix . 'rg_form_meta';

	$results = $wpdb->get_results( $wpdb->prepare(
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

	return array_map(function($result) use ($term)
	{
		$result->__title = 'title';
		$result->__link = admin_url('admin.php?page=gf_edit_forms&id='. $result->id);

		// Clip gravity form settings & fields, confirmations, and notifications
		$tables = ['display_meta', 'confirmations', 'notifications'];

		foreach ($tables as $table) {
			$result = nds_clip_property($result, $table, $term);
		}

		return $result;

	}, $results);
}

function nds_query_gravityformentries($term, $limit = 10)
{
	global $wpdb;

	$formTable = $wpdb->prefix . 'rg_form';
	$entryTable = $wpdb->prefix . 'rg_lead_detail';

	$results = $wpdb->get_results( $wpdb->prepare(
		"
		SELECT form_id, lead_id, field_number, value
		FROM {$entryTable}
		WHERE value LIKE '%%%s%%'
		LIMIT %d
		",
		$term,
		$limit
	));

	return array_map(function($result) use ($term)
	{
		$result->__title = 'Entry #'. $result->lead_id;
		$result->__link = admin_url('admin.php?page=gf_entries&view=entry&id='. $result->form_id .'&lid='. $result->lead_id);

		// Clip entry field value
		$result = nds_clip_property($result, 'value', $term);

		return $result;

	}, $results);
}

/**
 * Clip object property and or set it to '(omitted)'
 */
function nds_clip_property($obj, $property, $term, $chars = 100)
{
	$obj->$property = nds_clip_around_term($obj->$property, $term, $chars);

	if ( $obj->$property === false ) $obj->$property = '(omitted)';

	return $obj;
}

/**
 * Clip string around term
 * @param  String  $result The full result (to be clipped)
 * @param  String  $term   The search term
 * @param  Integer $chars  How many characters from term to start clipping at
 * @return Mixed           Clipped result (string) -or- False (boolean)
 */
function nds_clip_around_term($result, $term, $chars = 100)
{
	$pos = stripos($result, $term);

	if ( $pos !== false ) {
		
		$start = $pos - $chars < 0 ? 0 : $pos - $chars;
		$end = $pos + $chars > strlen($result) ? strlen($result) : $pos + $chars;

		return substr($result, $start, $end - $start);
	}

	return false;
}