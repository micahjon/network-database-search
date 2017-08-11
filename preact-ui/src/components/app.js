import { h, Component } from 'preact';
import './app';
// import { route, Router } from 'preact-router';
// import { publicPath } from '../lib/public-path';

// import Header from './header';
// import Home from './home';
// import Events from './events';
// import Individuals from './individuals';
// import Sheets from './sheets';

export default class App extends Component {
	state = {
		// A string (message) or a list of site objects:
		// {
		// 		id: (int) 31
		// 		name: (string) Academics
		// 		rest_url: (string) http://gc.edu/wp-json/nds/v1/
		// 		active: (boolean) true
		// }
		sites: 'Loading sites...',
		// A string (message) or a list of query objects:
		// {
		// 		id: (string) posts
		// 		name: (string) Posts
		// 		description: (string) Search post titles, content, and slugs. Ignore deleted and draft posts.
		// }
		queryTypes: 'Loading search query types...',
	};

	// /**
	//  * Refresh model every time this tab gains focus
	//  */
	// handleVisibilityChange = () => {
	// 	// Refresh model
	// 	if (!document.hidden) {
	// 		if (this.checkForUpdates) this.checkForUpdates();
	// 		else console.log('this.checkForUpdates is not available');
	// 	}
	// };

	componentDidMount() {
		// Get list of sites user has access to
		jQuery
			.post('http://gc.edu' + ajaxurl, {
				action: 'nds_get_rest_api_url',
			})
			.fail(() => {
				this.setState({ sites: 'Sorry, unable to get url to API.' });
			})
			.done(url => {
				// Got REST API url

				// Fetch sites user has privileges to search
				jQuery
					.getJSON(`${url}/get-sites/`)
					.fail(() => {
						this.setState({
							sites: 'Sorry, unable to get list of sites.',
						});
					})
					.done(sites => {
						if (!jQuery.isArray(sites)) {
							this.setState({
								sites: 'Sorry, unable to get list of sites.',
							});
						} else if (!sites.length) {
							this.setState({
								sites: "Sorry, your user doesn't have access to any sites.",
							});
						} else {
							// By default, set all sites to active (searchable)
							this.setState({
								sites: sites.map(site => {
									site.active = true;
									return site;
								}),
							});
						}
					});

				// Fetch query types
				jQuery
					.getJSON(`${url}/get-query-types/`)
					.fail(() => {
						this.setState({
							queryTypes: 'Sorry, unable to get list of query types.',
						});
					})
					.done(queryTypes => {
						if (!jQuery.isArray(queryTypes)) {
							this.setState({
								queryTypes: 'Sorry, unable to get list of query types.',
							});
						} else if (!queryTypes.length) {
							this.setState({
								queryTypes: "Sorry, your user doesn't have access to any query types.",
							});
						} else {
							// By default, set all sites to active (searchable)
							this.setState({
								queryTypes: queryTypes.map(query => {
									query.active = true;
									return query;
								}),
							});
						}
					});
			});

		// 	// Go to /sheets/ route if the model isn't ready
		// 	if (!this.state.events.length || !this.state.registrations.length) {
		// 		if (Router.getCurrentUrl() !== publicPath + 'sheets/') {
		// 			// Manually initialize /sheets/ component to fetch model the first time
		// 			console.log('Manually instantiating Sheets');
		// 			new Sheets({
		// 				allowAppToTriggerUpdates: updateFunction => {
		// 					this.checkForUpdates = updateFunction;
		// 				},
		// 				updateAppModel: model => this.setState(model),
		// 			});
		// 		}
		// 	}

		// 	// On tab focus, refresh the model
		// 	document.addEventListener(
		// 		'visibilitychange',
		// 		this.handleVisibilityChange,
		// 		false
		// 	);
	}

	// componentWillUnmount() {
	// 	// Stop listening to tab focus
	// 	document.removeEventListener(
	// 		'visibilitychange',
	// 		this.handleVisibilityChange
	// 	);
	// }

	render(props, state) {
		return (
			<div id="app" class="wrap nds">
				<h1>Network Database Search</h1>
				<form class="nds__search-form">
					<h2>Search</h2>
					<div class="nds__search">
						<input
							type="search"
							name="term"
							class="nds__search-input"
						/>
						<input
							class="button-primary nds__button"
							type="submit"
							value="Search"
							autocomplete="off"
						/>
					</div>
					<h2>Sites</h2>
					{// Sites have loaded
					(jQuery.isArray(state.sites) &&
						<div class="nds__sites">
							{state.sites.map(site => {
								return (
									<label style="margin-right: 1em;">
										<input
											type="checkbox"
											checked={site.active}
											onChange={evt => {
												const checked = evt.target.checked;
												site.active = checked;
												this.setState({
													sites: state.sites,
												});
											}}
										/>
										{site.name}
									</label>
								);
							})}
							<br /><br />
							<label>
								<input
									type="checkbox"
									checked={state.sites.every(site => {
										return site.active;
									})}
									onChange={evt => {
										const checked = evt.target.checked;
										this.setState({
											sites: state.sites.map(site => {
												site.active = checked;
												return site;
											}),
										});
									}}
								/> <em>Select All</em>
							</label>
						</div>) ||
						// Sites are loading or something went wrong
						<p>{state.sites}</p>}
						<h2>Search Queries</h2>
						{(jQuery.isArray(state.queryTypes) &&
						<div class="nds__sites">
							{state.queryTypes.map(queryType => {
								return (
									<label style="margin-right: 1em;">
										<input
											type="checkbox"
											checked={queryType.active}
											onChange={evt => {
												const checked = evt.target.checked;
												queryType.active = checked;
												this.setState({
													queryTypes: state.queryTypes,
												});
											}}
										/>
										{queryType.name}
									</label>
								);
							})}
							<br /><br />
							<label>
								<input
									type="checkbox"
									checked={state.queryTypes.every(queryType => {
										return queryType.active;
									})}
									onChange={evt => {
										const checked = evt.target.checked;
										this.setState({
											queryTypes: state.queryTypes.map(queryType => {
												queryType.active = checked;
												return queryType;
											}),
										});
									}}
								/> <em>Select All</em>
							</label>
						</div>) ||
						// queryTypes are loading or something went wrong
						<p>{state.queryTypes}</p>}
				</form>
				<h2>Results</h2>
				...
			</div>
		);
	}
}
