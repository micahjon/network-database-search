import { h, Component } from 'preact';
import Highlight from 'react-highlighter';
import style from './style.less';

export default function Results({ site, results, searchQuery, queryTypes }) {
	const searchRegex = new RegExp(escapeRegExp(searchQuery), 'i'),
		totalResults = Object.keys(results).reduce((acc, queryType) => {
			return (acc += results[queryType].length);
		}, 0);

	let classes = [style.results];
	if ( !totalResults ) {
		classes.push(style['results--empty']);
	}

	// console.log('render Results', site, Object.keys(results));

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
		} else {
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
			return <h3 class={style.result__title} />;
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
								<h4 class={style.result__childrenTitle}>
									{queryTypes.find(obj => obj.id === key).name}:
								</h4>
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
		<div class={classes.join(' ')}>
			<h4 class={style.results__siteTitle}>{site}</h4>
			{totalResults &&
				Object.keys(results).map(queryType => {
					const amount = results[queryType].length;
					if (!amount) return '';

					return (
						<div class={style.results__group}>
							<p class={style.results__groupTitle}>
								{queryTypes.find(obj => obj.id === queryType).name}: {amount}
							</p>
							{results[queryType].map(resultObject => {
								return resultTemplate(resultObject, queryType);
							})}
						</div>
					);
				}) || <p class={style.results__empty}>No results</p>}
		</div>
	);
}
