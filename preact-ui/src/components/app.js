import { h, Component } from 'preact';
import SearchOptions from './search-options';
import Results from './results';

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

		// Last-searched query
		searchQuery: '',

		// What's currently typed in the search box
		newSearchQuery: '',

		// API url
		restAPIUrl: '',

		// Whether a current search is being performed
		isSearching: false,

		// A string (message) or a list of search result objects:
		// [
		// 		{id: 1, name: "Goshen College", queries: {…}},
		// 		{id: 4, name: "New & Events", queries: {…}},
		// ]
		results: '',
	};

	/**
	 * Generic function to update a piece of state.
	 * Curried so that component can send back state array/object 
	 * without knowing what's is called.
	 * @param  {String} type  Key on state object
	 * @return {Function}     Function that accepts new state object(s)
	 */
	updateOptions(type) {
		return options => {
			let newState = {};
			newState[type] = options;
			this.setState(newState);
		};
	}

	search(event) {
		event.preventDefault();

		const term = this.state.newSearchQuery,
			queryTypes = this.state.queryTypes
				.filter(type => type.active)
				.map(({ id }) => id)
				.join(','),
			sites = this.state.sites.filter(type => type.active).map(({ id, name }) => {
				return { id, name, queries: [] };
			});

		this.setState({
			isSearching: true,
			results: 'Loading search results...',
			searchQuery: term,
		});

		// Make a bunch of parallel requests, but display results sequentially
		const requests = this.state.sites
			.filter(site => site.active)
			.map(({ rest_url }) => {
				return new Promise(function(resolve, reject) {
					jQuery.ajax({
						url: `${rest_url}search/`,
						dataType: 'json',
						data: { term, queryTypes },
						headers: { 'X-WP-Nonce': window.nds_rest_api_nonce },
						success: resolve,
						error: reject,
					});
				});
			})
			.map((request, i) => {
				request.then(data => {
					// console.log(data);

					sites[i].queries = data;
					this.setState({ results: sites });
				});
			});

		// Done searching
		const endSearch = () => {
			this.setState({ isSearching: false });
		};
		Promise.all(requests)
			.then(endSearch)
			.catch(endSearch);
	}

	componentDidMount() {
		// Get list of sites user has access to
		jQuery
			.post(ajaxurl, {
				action: 'nds_get_rest_api_url',
			})
			.fail(() => {
				this.setState({ sites: 'Sorry, unable to get url to API.' });
			})
			.done(url => {
				// Got REST API url
				this.setState({ restAPIUrl: url });

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
								queryTypes:
									"Sorry, your user doesn't have access to any query types.",
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

		// Development HACK
		// setTimeout(() => {
		// 	jQuery('.nds__search input[type="submit"]').click();
		// }, 800);
	}

	render(props, state) {
		return (
			<div class="wrap nds">
				<h1>Network Database Search</h1>
				<form class="nds__search-form" onSubmit={this.search.bind(this)}>
					<h2>Search</h2>
					<div class="nds__search">
						<input
							type="search"
							name="term"
							class="nds__search-input"
							value={state.newSearchQuery}
							onChange={event => {
								this.setState({ newSearchQuery: event.target.value });
							}}
							onKeyUp={event => {
								this.setState({ newSearchQuery: event.target.value });
							}}
						/>
						<input
							class="button-primary nds__button"
							type="submit"
							value="Search"
							autocomplete="off"
							disabled={state.newSearchQuery.length < 3 || state.isSearching}
						/>
					</div>
					<SearchOptions
						title="Sites"
						options={state.sites}
						updateOptions={this.updateOptions('sites')}
					/>
					<SearchOptions
						title="Search Queries"
						options={state.queryTypes}
						updateOptions={this.updateOptions('queryTypes')}
					/>
				</form>
				{this.state.searchQuery && (
					<h2>
						Results for <code>{this.state.searchQuery}</code>
					</h2>
				)}
				{(Array.isArray(state.results) &&
					state.results.map(({ name, queries }, index) => (
						<Results
							site={name}
							results={queries}
							searchQuery={state.searchQuery}
							queryTypes={state.queryTypes}
						/>
					))) || <p>{state.results}</p>}
			</div>
		);
	}
}
