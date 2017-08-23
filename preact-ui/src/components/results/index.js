import { h, Component } from 'preact';

export default function Results({ results }) {
	const resultTemplate = (queryType, data) => {

		if (queryType === 'posts') console.log(queryType, data);
		
		switch (queryType) {
			case 'posts':
				return <div>
					<h4>{data.post_title}</h4>
				</div>
				break;

			default:
				return <div>other</div>;
		}
	};

	return (
		(typeof results === 'object' &&
			<div>
				{results.map(({ name, id, queries }) => (
					<div>
						<hr />
						<h4>{name}</h4>
						{queries.map(query => {
							const queryType = Object.keys(query)[0],
								amount = query[queryType].length;

							return (
								<div>
									<p>{queryType}: {amount}</p>
									{query[queryType].map(resultObject => {
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
