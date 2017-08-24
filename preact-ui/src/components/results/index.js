import { h, Component } from 'preact';
import Highlight from 'react-highlighter';

export default function Results({ results, searchQuery }) {
	/**
	 * Highlight search query in string unless it's "(omitted)"
	 * @param  {String} string 			String to search within
	 * @return {JSX}        			A JSX template
	 */
	const highlight = string => {
		if (string === '(omitted)') return string;
		return <Highlight search={searchQuery}>{string}</Highlight>;
	};

	/**
	 * Combine postmeta (custom field) results for the same post.
	 * Also merge them with post results if possible.
	 */
	// if (Array.isArray(results)) {
	// 	results = results.map(siteResults => {
	// 		if (!siteResults.queries || !siteResults.queries.postmeta || !siteResults.queries.posts)
	// 			return siteResults;

	// 		console.log('start ----------------------');

	// 		siteResults.queries.postmeta = siteResults.queries.postmeta.reduce(
	// 			(acc, result, index) => {
	// 				/**
	// 				 * Merge postmeta result with post result
	// 				 */
	// 				let matchingPost = siteResults.queries.posts.find(
	// 					postResult => postResult.id === result.post_id
	// 				);
	// 				if (matchingPost) {
	// 					matchingPost.customFields = matchingPost.customFields || [];
	// 					matchingPost.customFields.push({
	// 						meta_key: result.meta_key,
	// 						meta_value: result.meta_value,
	// 					});
	// 					return acc;
	// 				}

	// 				/**
	// 				 * Transform postmeta result to accomodate multiple meta keys and meta
	// 				 * values if necessary.
	// 				 */
	// 				result.customFields = [
	// 					{
	// 						meta_key: result.meta_key,
	// 						meta_value: result.meta_value,
	// 					},
	// 				];

	// 				/**
	// 				 * Merge postmeta results that pertain to the same post
	// 				 */
	// 				if (acc.length && acc[acc.length - 1].post_id === result.post_id) {
	// 					acc[acc.length - 1].customFields.push({
	// 						meta_key: 0,
	// 						meta_value: 1,
	// 					});

	// 					console.log(JSON.stringify(acc[acc.length - 1].customFields));

	// 				} else {
	// 					acc.push(result);
	// 				}

	// 				return acc;
	// 			},
	// 			[]
	// 		);

	// 		return siteResults;
	// 	});
	// }

	// console.log(results);

	const resultTemplate = (queryType, data) => {
		// if (queryType === 'postmeta') console.log(queryType, data);

		switch (queryType) {
			case 'posts':
				return (
					<div>
						<h4>#{data.id}: {highlight(data.post_title)} ({data.post_type})</h4>
						<div>
							<a href={data.permalink}>View</a> | <a href={data.editlink}>Edit</a>
						</div>
						<div>Slug: {highlight(data.post_name)}</div>
						<div>Content: {highlight(data.post_content)}</div>
						{data.customFields &&
							data.customFields.map(customField => (
								<div>
									{customField.meta_key}:{' '}
									{highlight(customField.meta_value)}
								</div>
							))}
					</div>
				);
				break;

			case 'postmeta':
				if (data.customFields) {
					console.log(data.customFields);
				}

				return (
					<div>
						<h4>#{data.post_id}: {data.post_title} ({data.post_type})</h4>
						<div>
							<a href={data.permalink}>View</a> | <a href={data.editlink}>Edit</a>
						</div>
						{data.customFields &&
							data.customFields.map(customField => (
								<div>
									{customField.meta_key}:{' '}
									{highlight(customField.meta_value)}
								</div>
							))}
						<div>Meta Value: {highlight(data.meta_value)}</div>
					</div>
				);
				break;

			default:
				return <div>othesr</div>;
		}
	};

	return (
		(typeof results === 'object' &&
			<div>
				{results.map(({ name, id, queries }) => (
					<div>
						<hr />
						<h4>{name}</h4>
						{Object.keys(queries).map(queryType => {
							const amount = queries[queryType].length;

							return (
								<div>
									<p>{queryType}: {amount}</p>
									{queries[queryType].map(resultObject => {
										return resultTemplate(queryType, resultObject);
									})}
								</div>
							);
						})}
					</div>
				))}
			</div>) ||
		<p>{results}</p>
	);
}
