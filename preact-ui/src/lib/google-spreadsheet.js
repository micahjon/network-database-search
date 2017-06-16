/**
 * Fetch Google Spreadsheet data via JSONP
 * @param  {String} id 		Id of Google Spreadsheet
 * @param  {String} query  	Query function (optional) (see https://developers.google.com/chart/interactive/docs/querylanguage)
 * @return {Response}        
 *
 * Inspired by this Gist: https://gist.github.com/gf3/132080 
 */
const fetchSpreadsheet = (unique => (spreadsheetId, worksheetId, query) => {
	return new Promise((resolve, reject) => {
		const script = document.createElement('script');
		const name = `_jsonp_${unique++}`;

		// Default worksheet is the first one (id = 0)
		worksheetId = worksheetId || 0;

		let url = `https://docs.google.com/spreadsheets/d/${spreadsheetId}/gviz/tq?gid=${worksheetId}&tqx=responseHandler:${name}`;

		if (typeof query === 'string' && query.length) {
			url += `&tq=${query}`;
		}

		script.src = url;

		script.onerror = reason => resolve(reason);

		window[name] = json => {
			resolve(new Response(JSON.stringify(json)));
			script.remove();
			delete window[name];
		};

		document.body.appendChild(script);
	});
})(0);

const getRowObjects = (spreadsheetId, worksheetId, query) => {
	return (
		fetchSpreadsheet(spreadsheetId, worksheetId, query)
			.then(function(response) {
				if ( response.type !== 'error' ) {
					return response.json();
				}
				else {
					throw `Spreadsheet data could not be fetched`;
				}

			})
			// Transform rows & columns into objects { col_name: "row value", ... }
			.then(function(json) {
				const { cols } = json.table;
				const rows = json.table.rows.map(row => row.c);
				let objects = [];

				rows.forEach((row, rowIndex) => {
					let obj = {};
					row.forEach((value, colIndex) => {
						if (value && value.v) {
							obj[cols[colIndex].label.toLowerCase().replace(/[^a-zA-Z0-9_]/g, '')] = value.v;
						}
					});
					objects.push(obj);
				});

				return { columns: cols, rows: rows, rowObjects: objects };
			})
			.catch(function(err) {
				return { error: err }
			})
	);
};

const getSpreadsheetIdFromUrl = url => {
	const matches = url.match(/\/d\/([a-zA-Z0-9-_]+)\//);
	if (matches && matches[1]) {
		return matches[1];
	}
	return false;
};

const getWorksheetIdFromUrl = url => {
	const matches = url.match(/gid=([0-9]+)/);
	if (matches && matches[1]) {
		return matches[1];
	}
	return false;
};

module.exports = {
	fetch: fetchSpreadsheet,
	getRowObjects: getRowObjects,
	getSpreadsheetIdFromUrl: getSpreadsheetIdFromUrl,
	getWorksheetIdFromUrl: getWorksheetIdFromUrl,
};
