<?php

/**
 * Add query types Gravity Forms and their entries
 * @param  Array $queryTypes 
 * @return Array             $queryTypes
 */
function nds_add_gravityforms_query_types($queryTypes)
{
	return array_merge($queryTypes, [
		['id' => 'gravityforms', 'name' => 'Gravity Forms', 'description' => 'Search Gravity Form confirmations, notifications, and fields.'],
		['id' => 'gravityformentries', 'name' => 'Gravity Form Entries', 'description' => 'Search Gravity Form entries.'],
	]);
}

/**
 * Check if Gravity Forms is activated before enabling query types
 */
add_action('plugins_loaded', function()
{
	if ( class_exists('GFCommon') ) {
		add_filter('nds_query_types', 'nds_add_gravityforms_query_types');
	}
});

/**
 * Database query functions for searching gravity forms and their entries
 */
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