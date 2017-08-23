import { h, Component } from 'preact';

export default function SearchOptions({ options, updateOptions }) {
	return (
		(Array.isArray(options) &&
			<div class="nds__options">
				{options.map(option => {
					return (
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
					);
				})}
				<label class="nds__option nds__option--select-all">
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
					/> <em>Select All</em>
				</label>
			</div>) ||
		<p>{options}</p>
	);
}
