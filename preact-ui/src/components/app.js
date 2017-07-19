import { h, Component } from 'preact';
// import { route, Router } from 'preact-router';
// import { publicPath } from '../lib/public-path';

// import Header from './header';
// import Home from './home';
// import Events from './events';
// import Individuals from './individuals';
// import Sheets from './sheets';

export default class App extends Component {
	state = {
		sites: 'Loading sites...',
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
		.post('http://gc.edu' + ajaxurl, { action: 'nds_get_sites_api_url' })
		.fail(() => {
			this.setState({ sites: 'Sorry, unable to get url to API.' })
		})
		.done((url) => {
			// Got url, not fetch sites
			jQuery
			.getJSON(url)
			.fail(() => {
				this.setState({ sites: 'Sorry, unable to get list of sites.' })
			})
			.done((sites) => {
				if ( !jQuery.isArray(sites) ) {
					this.setState({ sites: 'Sorry, unable to get list of sites.' })
				}
				else if ( !sites.length ) {
					this.setState({ sites: 'Sorry, your user doesn\'t have access to any sites.' })
				}
				else {
					this.setState({ sites })
				}
			})
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
					{
						// Sites have loaded
						jQuery.isArray(state.sites) && state.sites.map((site) => 
						{ 
							return <label><input type="checkbox" /> {site.name}</label>
						})
						|| 
						// Sites are loading or something went wrong
						<p>{state.sites}</p>

					}
					<br />
					<input type="search" name="term" class="nds__search-input" />
					<input class="button-primary nds__button" type="submit" value="Search" autocomplete="off" />
				</form>
			</div>
		);
	}
}
