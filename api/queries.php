<?php

function nds_query_posts($term, $limit = 10)
{
	global $wpdb;

	$table = $wpdb->prefix . 'posts';

	$results = $wpdb->get_results($wpdb->prepare(
		"
			SELECT id, post_content, post_name, post_title, post_type, post_status
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

	$results = array_map(function($result) use ($term)
	{
		// Add permalink
		$result->permalink = get_permalink($result->id);

		// Clip post content
		$clippedContent = nds_clip_around_term($result->post_content, $term);

		$result->post_content = $clippedContent === false ? '(omitted)' : $clippedContent;

		return $result;

	}, $results);

	return $results;

// 	/**
//  * Limit post_content to text right around search string
//  */
// foreach ($results as &$post) {
// 	// find position of $term in each title, name (slug), and content
// 	foreach ($postsSearchFields as $field) {

// 		// escape all HTML entities/
// 		$fieldVal = htmlentities($post->$field);

// 		$clipped = clipAndBold( $fieldVal, $term, 100 );

// 		if ($clipped) {
// 			$post->$field = $clipped;
// 		}
// 		else if ( $field === 'post_content' ) {
// 			$post->$field = '(omitted)';
// 		}
// 		else {
// 			$post->$field = $fieldVal;
// 		}
// 	}
// 	// add links for pages & posts (using id)
// 	$post = addTitleLinks( $post, 'id', $sitePath );
// }
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