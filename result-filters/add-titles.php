<?php
/**
 * Add __title properties
 */
add_filter('nds_search_results', 'nds_add_titles', 10, 2);
function nds_add_titles($results, $term)
{
	$titles = [
		'posts' => 'post_title',
		'postmeta' => 'meta_key',
		'options' => 'option_name',
		'gravityforms' => 'title',
		'gravityformentries' => function($result) {
			return 'Entry #'. $result->lead_id;
		}
	];

	foreach ($results as $queryType => $resultsOfType) {
		if ( !empty($titles[$queryType]) ) {
			$results[$queryType] = nds_add_title_property($resultsOfType, $titles[$queryType]);
		}
	}

	return $results;
}
/**
 * Helper function
 */
function nds_add_title_property($results, $title)
{
	return array_map(function($result) use ($title)
	{
		$result->__title = is_callable($title) ? $title($result) : $title;
		
		return $result;

	}, $results);
}