<?php
/**
 * Clip long fields around search term, or just use '(omitted)' 
 * if no search term was found.
 */

add_filter('nds_search_results', 'nds_clip_long_fields', 10, 2);
function nds_clip_long_fields($results, $term)
{
	$fieldsToClip = [
		'posts' => ['post_content'],
		'postmeta' => ['meta_value'],
		'options' => ['option_value'],
		'gravityforms' => ['display_meta', 'confirmations', 'notifications'],
		'gravityformentries' => ['value']
	];

	foreach ($results as $queryType => $resultsOfType) {
		if ( !empty($fieldsToClip[$queryType]) ) {
			$results[$queryType] = nds_clip_properties($resultsOfType, $fieldsToClip[$queryType], $term);
		}
	}

	return $results;
}
/**
 * Helper function
 */
function nds_clip_properties($results, $properties, $term)
{
	return array_map(function($result) use ($properties, $term)
	{
		foreach ($properties as $property) {
			$result = nds_clip_property($result, $property, $term);
		}

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