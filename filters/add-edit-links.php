<?php
/**
 * Add __link properties
 */

/**
 * Add edit links to posts
 */
add_filter('nds_search_results_posts', 'nds_add_post_links');
function nds_add_post_links($results)
{
	global $wpdb;

	return array_map(function($result) use ($wpdb)
	{
		// Get edit post link
		if ( $link = nds_get_edit_post_link($result->id, $result->post_type) ) {
			$result->__link = $link;
		}
		// Link to navigation menus for menu items
		else if ( $result->post_type === 'nav_menu_item' ) {
			
			$termRelationshipsTable = $wpdb->prefix . 'term_relationships';
			$menuIdResults = $wpdb->get_results($wpdb->prepare(
				"
				SELECT term_taxonomy_id AS menu_id 
				FROM {$termRelationshipsTable} 
				WHERE object_id = %d
				",
				$result->id
			));

			if ( !empty($menuIdResults[0]->menu_id) ) {
				$result->__link = admin_url( sprintf('nav-menus.php?action=edit&menu=%d', $menuIdResults[0]->menu_id) );
			}
		}

		return $result;

	}, $results);
}

/**
 * Add edit links to Gravity Forms
 */
add_filter('nds_search_results_gravityforms', 'nds_add_gravityform_links');
function nds_add_gravityform_links($results)
{
	return array_map(function($result)
	{
		$result->__link = admin_url('admin.php?page=gf_edit_forms&id='. $result->id);

		return $result;

	}, $results);
}

/**
 * Add edit links to Gravity Form Entries
 */
add_filter('nds_search_results_gravityformentries', 'nds_add_gravityformentry_links');
function nds_add_gravityformentry_links($results)
{
	return array_map(function($result)
	{
		$result->__link = admin_url('admin.php?page=gf_entries&view=entry&id='. $result->form_id .'&lid='. $result->lead_id);

		return $result;

	}, $results);
}

/**
 * Get edit post link using standard WordPress function in production and use
 * fallback in development (where current_user_can() doesn't work)
 * @param  Integer $postId 	Post id
 * @param  String $postType Post type
 * @return String          	Link or empty string
 */
function nds_get_edit_post_link($postId, $postType)
{
	// Production
	if ( !defined('NDS_PLUGIN_DEV_USER_ID') ) {
		return get_edit_post_link($postId, '');
	}
	
	// Development
	if ( !empty($editLink = get_post_type_object($postType)->_edit_link) ) {
		return admin_url( sprintf( $editLink . '&action=edit', $postId ) );
	}
	
	return '';
}