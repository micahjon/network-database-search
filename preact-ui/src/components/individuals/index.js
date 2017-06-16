import { h, Component } from 'preact';
import style from './style.less';

export default class Individuals extends Component {
	state = {
		selectedType: 'All'
	}

	// gets called when this route is navigated to
	componentDidMount() {
	}

	// gets called just before navigating away from the route
	componentWillUnmount() {}

	render({ registrations, events }, { selectedType }) {
		return (
			<div class={style.individuals}>
				<h1>Individuals</h1>
				<form class={`${style.individuals__form} pure-form`}>
					<label for="show-all" class="pure-radio">
						<input
							id="show-all"
							type="radio"
							name="toggleType"
							onChange={() => this.setState({selectedType: 'All'}) }
						/>
						All (default)
					</label>
					<label for="show-alumni" class="pure-radio">
						<input
							id="show-alumni"
							type="radio"
							name="toggleType"
							onChange={() => this.setState({selectedType: 'Alumni'}) }
						/>
						Alumni
					</label>
					<label for="show-grandparents" class="pure-radio">
						<input
							id="show-grandparents"
							type="radio"
							name="toggleType"
							onChange={() => this.setState({selectedType: 'Grandparent'}) }
						/>
						Grandparents
					</label>
					<label for="show-parents" class="pure-radio">
						<input
							id="show-parents"
							type="radio"
							name="toggleType"
							onChange={() => this.setState({selectedType: 'Parent'}) }
						/>
						Parents
					</label>
				</form>

				{registrations
					.filter(reg => selectedType === 'All' || reg.type === selectedType)
					.map(reg => {
						const name = `${reg.firstname} ${reg.lastname}`;
						const spouse = reg.spouselastname
							? `Spouse: ${reg.spousefirstname} ${reg.spouselastname}`
							: ``;
						return (
							<section class="page-break-before">
								<h2>{name}</h2>
								<p>{spouse}</p>
								<p>Id: {reg.id || '(none)'}</p>
								<p>Phone: {reg.phone}</p>
								<p>Email: {reg.email}</p>
								<p>Registering As: {reg.type}</p>
								<table
									class={`${style.individuals__table} pure-table pure-table-horizontal pure-table-striped`}
								>
									<thead>
										<th>Event</th>
										<th>Qty</th>
										<th>Cost</th>
										<th>Subtotal</th>
										<th>Day</th>
									</thead>
									{events
										.filter(event => {
											return reg[event.slug];
										})
										.map(event => {
											return (
												<tr>
													<td>{event.name}</td>
													<td>{reg[event.slug]}</td>
													<td>
														{event.cost
															? `$${event.cost}`
															: ` - `}
													</td>
													<td>
														{event.cost
															? `$${reg[event.slug] * event.cost}`
															: `$0`}
													</td>
													<td>{event.day}</td>
												</tr>
											);
										})}
								</table>
							</section>
						);
					})}

			</div>
		);
	}
}
