import { h, Component } from 'preact';
import style from './style.less';
import Events from '../events';
import Clipboard from 'clipboard';

export default class Home extends Component {
	// gets called when this route is navigated to
	componentDidMount() {
		this.clipboardButton = new Clipboard('.copy-to-clipboard');
	}

	// gets called just before navigating away from the route
	componentWillUnmount() {
		if ( this.clipboardButton ) this.clipboardButton.destroy();
	}

	render(props, state) {
		return (
			<div class={style.home}>
				<h1>Home</h1>
				<p>
					Homecoming Reports web application, built by Micah Miller-Eshleman (May '07)
				</p>
				<p>
					Uses data from 2 Google Spreadsheet worksheets to generate reports on homecoming registrations and events
				</p>

				<h2>All Ids</h2>
				<textarea id="list-of-ids" rows="10" cols="50">
					{props.registrations
						.reduce((ids, {id, spouseid}) => {
							if (id) ids.push(id)
								if (spouseid) ids.push(spouseid)
							return ids;
						}, [])
						.sort()
						.join(', ')}
				</textarea>
				<p>
					<button
						class="copy-to-clipboard pure-button pure-button-primary"
						data-clipboard-target="#list-of-ids"
						type="button"
					>
						Copy to Clipboard
					</button>
				</p>
			</div>
		);
	}
}
