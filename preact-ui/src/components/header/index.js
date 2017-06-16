import { h, Component } from 'preact';
import { Link } from 'preact-router';
import { publicPath } from '../../lib/public-path';
import style from './style.less';

export default class Header extends Component {
	render({ sheetsReady }, state) {
		return (
			<header class={style.header + ' no-print'}>
				<h1>Homecoming Reports</h1>
				<nav>
					<Link href={publicPath}>Home</Link>
					<Link href={`${publicPath}events/`}>Events</Link>
					<Link href={`${publicPath}individuals/`}>Individuals</Link>
					<Link href={`${publicPath}sheets/`}>
						<svg
							class={`${style.header__spreadsheet} ${style['header__spreadsheet--' + (sheetsReady ? 'ok' : 'warning')]}`}
							viewBox="0 0 48 48"
						>
							<path d="M37,45H11c-1.657,0-3-1.343-3-3V6c0-1.657,1.343-3,3-3h19l10,10v29C40,43.657,38.657,45,37,45z" />
							<polygon points="40,13 30,13 30,3 " />
							<polygon points="30,13 40,23 40,13 " />
							<path d="M31,23H17h-2v2v2v2v2v2v2v2h18v-2v-2v-2v-2v-2v-2v-2H31z M17,25h4v2h-4V25z M17,29h4v2h-4V29z M17,33&#10;&#9;h4v2h-4V33z M31,35h-8v-2h8V35z M31,31h-8v-2h8V31z M31,27h-8v-2h8V27z" />
						</svg>
					</Link>
				</nav>
			</header>
		);
	}
}
