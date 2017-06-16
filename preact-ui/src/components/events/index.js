import { h, Component } from 'preact';
import style from './style.less';

export default class Events extends Component {
	// gets called when this route is navigated to
	componentDidMount() {}

	// gets called just before navigating away from the route
	componentWillUnmount() {}

	// Count how many registrations there are for each event
	getAttendance = (registrations, events) => {
		let attendance = {};

		events.forEach(({ slug }) => {
			attendance[slug] = registrations.reduce((acc, registration) => {
				if (registration[slug]) acc += registration[slug];
				return acc;
			}, 0);
		});

		return attendance;
	};

	render({ registrations, events }) {
		const attendance = this.getAttendance(registrations, events);

		return (
			<div class={style.events}>
				<h1>Events</h1>
				<table
					class={`${style.events__table} pure-table pure-table-horizontal pure-table-striped`}
				>
					<thead>
						<th>Event Name</th>
						<th>Location</th>
						<th>Attendance</th>
					</thead>
					{events.map(({ name, location, slug }) => (
						<tr>
							<td>
								<a
									class={style.events__anchor}
									href={`#${slug}`}
								>
									{name}
								</a>
							</td>
							<td>{location}</td>
							<td>{attendance[slug]}</td>
						</tr>
					))}
				</table>
				{events.map(event => {
					return (
						<div id={`${event.slug}`}>
							<h2
								class={`${style.events__title} page-break-before`}
							>
								{event.name} ({attendance[event.slug]})
							</h2>
							<table
								class={`${style.events__table} pure-table pure-table-horizontal pure-table-striped`}
							>
								<thead>
									<th>Alum</th>
									<th>Spouse</th>
									<th>ID</th>
									<th>Qty</th>
									<th>Dietary Restriction</th>
								</thead>
								{registrations
									.filter(reg => {
										return reg[event.slug];
									})
									.map(reg => {
										const spouseName = reg.spouselastname
											? `${reg.spouselastname}, ${reg.spousefirstname}`
											: ``;
										return (
											<tr>
												<td>
													{`${reg.lastname}, ${reg.firstname}`}
												</td>
												<td>{spouseName}</td>
												<td>{reg.id}</td>
												<td>{reg[event.slug]}</td>
												<td>
													{reg.dietaryrestrictions}
												</td>
											</tr>
										);
									})}
							</table>
						</div>
					);
				})}

			</div>
		);
	}
}
