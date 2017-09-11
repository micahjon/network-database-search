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
		let containerClass = style.result__hiddenAttribute,
			value = string;

		if (string !== '(omitted)' && searchRegex.test(string)) {
			containerClass = '';
			value = <Highlight search={searchQuery}>{string}</Highlight>;
		}

		return (
			<div class={containerClass}>
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

		// switch (queryType) {
		// 	case 'posts':
		// 		return (
		// 			<div>
		// 				<h4>{highlightAndLabel(data.post_title)} ({data.post_type} #{data.id})</h4>
		// 				<div>
		// 					<a href={data.permalink}>View</a> | <a href={data.editlink}>Edit</a>
		// 				</div>
		// 				<div>{highlightAndLabel(data.post_name, `Slug`)}</div>
		// 				<div>{highlightAndLabel(data.post_content, `Content`)}</div>
		// 			</div>
		// 		);
		// 		break;

		// case 'postmeta':
		// 	return (
		// 		<div>
		// 			<h4>{data.meta_key} ({data.post_type} #{data.post_id})</h4>
		// 			<div>
		// 				<a href={data.permalink}>View</a> | <a href={data.editlink}>Edit</a>
		// 			</div>
		// 			<div>{highlightAndLabel(data.meta_value, `Meta Value`)}</div>
		// 		</div>
		// 	);
		// 	break;

		// case 'options':
		// 	return (
		// 		<div>
		// 			<h4>{data.option_name} (option)</h4>
		// 			<div>{highlightAndLabel(data.option_value, `Option Value`)}</div>
		// 		</div>
		// 	);

		// case 'gravityforms':
		// 	return (
		// 		<div>
		// 			<h4>{data.title} (Form #{data.id}) ({data.is_trash === '1' ? 'trashed' : (data.is_active === '1' ? 'active' : 'deactivated')})</h4>
		// 			<div>
		// 				<a href={data.editlink}>Edit</a>
		// 			</div>
		// 			<div>{highlightAndLabel(data.display_meta, `Fields & Settings`)}</div>
		// 			<div>{highlightAndLabel(data.confirmations, `Confirmations`)}</div>
		// 			<div>{highlightAndLabel(data.notifications, `Notifications`)}</div>
		// 		</div>
		// 	);

		// case 'gravityformentries':

		// 	console.log(data);
		// 	return (
		// 		<div>
		// 			<h4>{data.title} (Form #{data.id}) ({data.is_trash === '1' ? 'trashed' : (data.is_active === '1' ? 'active' : 'deactivated')})</h4>
		// 			<div>
		// 				<a href={data.editlink}>Edit</a>
		// 			</div>
		// 			<div>{highlightAndLabel(data.display_meta, `Fields & Settings`)}</div>
		// 			<div>{highlightAndLabel(data.confirmations, `Confirmations`)}</div>
		// 			<div>{highlightAndLabel(data.notifications, `Notifications`)}</div>
		// 		</div>
		// 	);

		// 	default:
		// 		return <div>othesr</div>;
		// }
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
