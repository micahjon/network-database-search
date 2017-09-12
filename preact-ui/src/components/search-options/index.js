import { h, Component } from 'preact';

export default function SearchOptions({ title, options, updateOptions }) {
	return (
		(Array.isArray(options) && (
			<div>
				<h2 class="nds__options-title">{title}</h2>
				<label class="nds__select-all">
					<input
						type="checkbox"
						checked={options.every(option => {
							return option.active;
						})}
						onChange={evt => {
							const checked = evt.target.checked;
							updateOptions(
								options.map(option => {
									option.active = checked;
									return option;
								})
							);
						}}
					/>{' '}
					<em>Select All</em>
				</label>
				<ul class="nds__options">
					{options.map(option => {
						return (
							<li>
								<label class="nds__option">
									<input
										type="checkbox"
										checked={option.active}
										onChange={evt => {
											const checked = evt.target.checked;
											option.active = checked;
											updateOptions(options);
										}}
									/>
									{option.name}
								</label>
							</li>
						);
					})}
				</ul>
			</div>
		)) || <p>{options}</p>
	);
}
