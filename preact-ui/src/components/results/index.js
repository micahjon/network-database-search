import { h, Component } from 'preact';
import Highlight from 'react-highlighter';
import style from './style.less';
// import { decode } from 'he';

export default function Results({ results, searchQuery, queryTypes }) {
	const searchRegex = new RegExp(escapeRegExp(searchQuery), 'i');

	/**
	 * Highlight search query in string and prepend label
	 * unless it's "(omitted)".
	 * @param  {String} string 			String to search within
	 * @param  {String} label 			(optional) string to prepend as label
	 * @return {JSX}        			A JSX template
	 */
	const highlightAndLabel = (string, label) => {
		let classes = [style.result__attribute],
			value = string;

		if (string !== '(omitted)' && searchRegex.test(string)) {
			value = <Highlight search={searchQuery}>{string}</Highlight>;
		}
		else {
			classes.push(style.result__hiddenAttribute);
		}

		return (
			<div class={classes.join(' ')}>
				<span class={style.result__label}>{label}</span>:{' '}
				<span class={style.result__value}>{value}</span>
			</div>
		);
	};

	/**
	 * Generates title for result object from __title property
	 * @param  {Object} result 
	 * @return {String}
	 */
	const getTitle = result => {
		if (!result.__title) {
			console.warn('Search result missing __title property', result);
			return '';
		} else if (result[result.__title]) {
			// __title generally just references another key (e.g. post_title) whose value
			// should be displayed as the title
			return (
				<div>
					<span class={style.result__label}>{result.__title}</span>
					<h3 class={style.result__title}>{result[result.__title]}</h3>
				</div>
			);
		} else {
			// __title doesn't reference another key, so just display its text (e.g. Entry #5)
			return <h3 class={style.result__title}>{result.__title}</h3>;
		}
	};

	const resultTemplate = (result, queryType) => {
		/**
		 * Toggle visibility of attributes which don't contain search query using 
		 * a hidden <input>, <label> and the CSS siblings ~ selector.
		 */
		const inputId = 'input--' + ((Math.random() * 100000001) | 0);

		// console.log(result);

		return (
			<div class={style.result}>
				<a class={style.result__link} href={result.__link} target="_blank">
					<span class={`${style.result__editLinkIcon} dashicons dashicons-edit`} />
					<input id={inputId} class={style.result__expandInput} type="checkbox" />
					<label for={inputId} class={style.result__expandLabel}>
						+
					</label>
					{getTitle(result)}
					{Object.keys(result)
						.filter(key => !key.startsWith('__') && typeof result[key] === 'string')
						.map(key => highlightAndLabel(result[key], key))}
				</a>
				{Object.keys(result)
					.filter(key => !key.startsWith('__') && Array.isArray(result[key]))
					.map(key => {
						// Child objects
						return (
							<div>
								<h4 class={style.result__childrenTitle}>{queryTypes.find(obj => obj.id === key).name}:</h4>
								{result[key].map(child => {
									return resultTemplate(child, key);
								})}
							</div>
						);
					})}
			</div>
		);
	};

	function escapeRegExp(str) {
		return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, '\\$&');
	}

	return (
		(typeof results === 'object' && (
			<div>
				{results.map(({ name, queries }) => (
					<div>
						<hr />
						<h4>{name}</h4>
						{Object.keys(queries).map(queryType => {
							const amount = queries[queryType].length;
							if (!amount) return;

							return (
								<div class={style.results}>
									<p class={style.results__title}>
										{queryTypes.find(obj => obj.id === queryType).name}: {amount}
									</p>
									{queries[queryType].map(resultObject => {
										return resultTemplate(resultObject, queryType);
									})}
								</div>
							);
						})}
					</div>
				))}
			</div>
		)) || <p>{results}</p>
	);
}
