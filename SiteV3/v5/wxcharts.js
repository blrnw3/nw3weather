/*
 * wxcharts.js - reusable Highcharts renderers for the v5 site.
 *
 *   NW3.histChart(containerId, url, opts)     categorical daily/monthly/annual
 *   NW3.intradayChart(containerId, url, var)  per-minute datetime series
 *   NW3.windRose(containerId, url)            polar wind rose (needs highcharts-more)
 *
 * Requires Highcharts (and highcharts-more for the wind rose) plus jQuery, both
 * loaded by the page. All endpoints return the standard JSON shapes documented
 * in histdata.php / intradaydata.php / rosedata.php.
 */
(function (window) {
	'use strict';

	var TEXT = '#222';
	var COMPASS = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW', 'N'];

	if (typeof Highcharts !== 'undefined') {
		Highcharts.setOptions({ time: { timezone: 'Europe/London' }, lang: { thousandsSep: ',' } });
	}

	function compass(deg) { return COMPASS[Math.round(deg / 45)] || ''; }

	function gradient(dark, light) {
		return { linearGradient: { x1: 0, x2: 0, y1: 0, y2: 1 }, stops: [[0, light], [1, dark]] };
	}

	// ---- Categorical (daily / monthly / annual / climate) ----
	function histChart(containerId, url, opts) {
		opts = opts || {};
		return $.getJSON(url, function (json) {
			if (!json || json.error) {
				$('#' + containerId).html('<p>No data available.</p>');
				return null;
			}
			var series = json.series.map(function (s) {
				return {
					name: s.name,
					data: s.data,
					type: s.type || json.chartType || 'column',
					color: s.color,
					dashStyle: s.dashStyle || 'Solid',
					marker: { enabled: false },
					connectNulls: false
				};
			});
			Highcharts.chart(containerId, {
				chart: { backgroundColor: '#ffffff', spacing: [12, 12, 8, 6], style: { color: TEXT } },
				title: { text: opts.title || json.title, style: { color: TEXT, fontSize: '1.05rem', fontWeight: 'normal' } },
				credits: { enabled: true, href: '', text: '\u00A9 nw3weather', style: { color: '#999', fontSize: '9px' } },
				legend: { enabled: series.length > 1, itemStyle: { color: TEXT } },
				xAxis: {
					categories: json.categories,
					tickInterval: opts.tickInterval || Math.max(1, Math.ceil(json.categories.length / 24)),
					labels: { style: { color: TEXT, fontSize: '0.7rem' } },
					gridLineWidth: 0
				},
				yAxis: {
					title: { text: (json.unit ? json.unit : ''), style: { color: TEXT, fontSize: '0.8rem' } },
					gridLineColor: '#ddd',
					labels: { style: { color: TEXT } }
				},
				tooltip: { shared: true, valueSuffix: json.unit ? (' ' + json.unit) : '' },
				plotOptions: {
					column: { borderWidth: 0, pointPadding: 0.02, groupPadding: 0.08 },
					series: { animation: false }
				},
				series: series
			});
		}).fail(function () { $('#' + containerId).html('<p>Could not load chart.</p>'); });
	}

	// ---- Intraday (per-minute datetime) ----
	var INTRA_TABS = {
		temp: { name: 'Temperature', grad: ['#d34f00', '#ffab4d'], dp: 1, area: false },
		dewp: { name: 'Dew point', grad: ['#0b8043', '#5ed17e'], dp: 1, area: false },
		humi: { name: 'Humidity', grad: ['#2e9d30', '#7fd97f'], dp: 0, area: false },
		pres: { name: 'Pressure', grad: ['#5b3fb0', '#a98fe0'], dp: 0, area: false },
		wind: { name: 'Wind speed', grad: ['#c01717', '#f06a6a'], dp: 1, area: false, extra: 'gust' },
		gust: { name: 'Gust', grad: ['#c23b86', '#f08fc0'], dp: 1, area: false },
		rain: { name: 'Rainfall', grad: ['#2554c7', '#7fa8f0'], dp: 1, area: true },
		pm25: { name: 'Air quality (PM2.5)', grad: ['#4f7a4a', '#9ac98f'], dp: 1, area: false },
		wdir: { name: 'Wind direction', dp: 0, dir: true }
	};

	function pairs(json, key) {
		var t = json.time, v = json[key], out = [], i;
		for (i = 0; i < t.length; i++) { out.push([t[i], (v[i] === null ? null : v[i])]); }
		return out;
	}

	function renderIntraday(containerId, json, variable) {
			variable = variable || 'temp';
			if (!json) { $('#' + containerId).html('<p>No data available.</p>'); return; }
			var cfg = INTRA_TABS[variable] || INTRA_TABS.temp;
			var unit = (json.units && json.units[variable]) ? json.units[variable] : '';
			var isDir = !!cfg.dir;

			var series = [];
			var keys = [variable];
			if (cfg.extra) { keys.push(cfg.extra); }
			keys.forEach(function (k) {
				var kc = INTRA_TABS[k] || cfg;
				series.push({
					name: kc.name,
					data: pairs(json, k),
					type: isDir ? 'scatter' : (kc.area ? 'area' : 'line'),
					color: isDir ? '#555' : gradient(kc.grad[0], kc.grad[1]),
					lineWidth: isDir ? 0 : 2,
					marker: { enabled: isDir, radius: 2 },
					connectNulls: false,
					fillColor: kc.area ? { linearGradient: { x1: 0, x2: 0, y1: 0, y2: 1 },
						stops: [[0, 'rgba(37,84,199,0.30)'], [1, 'rgba(37,84,199,0.02)']] } : undefined
				});
			});

			Highcharts.chart(containerId, {
				chart: { backgroundColor: '#ffffff', spacing: [12, 12, 10, 10], style: { color: TEXT } },
				title: { text: cfg.name, style: { color: TEXT, fontSize: '1.05rem', fontWeight: 'normal' } },
				credits: { enabled: true, href: '', text: '\u00A9 nw3weather', style: { color: '#999', fontSize: '9px' } },
				legend: { enabled: keys.length > 1, itemStyle: { color: TEXT } },
				xAxis: { type: 'datetime', crosshair: true, gridLineWidth: 1, gridLineColor: '#ddd',
					dateTimeLabelFormats: { minute: '%H:%M', hour: '%H:%M', day: '%e %b' }, labels: { style: { color: TEXT } } },
				yAxis: isDir
					? { title: { text: 'Wind direction', style: { color: TEXT } }, min: 0, max: 360, tickInterval: 45,
						labels: { formatter: function () { return compass(this.value); }, style: { color: TEXT } }, gridLineColor: '#ddd' }
					: { title: { text: cfg.name + (unit ? ' / ' + unit : ''), style: { color: TEXT } },
						min: cfg.area ? 0 : null, gridLineColor: '#ddd', labels: { style: { color: TEXT } } },
				tooltip: isDir
					? { formatter: function () { return Highcharts.dateFormat('%a %H:%M', this.x) + '<br/><b>' + Math.round(this.y) + '\u00B0 (' + compass(this.y) + ')</b>'; } }
					: { shared: true, xDateFormat: '%a %H:%M', valueSuffix: unit ? (' ' + unit) : '', valueDecimals: cfg.dp },
				plotOptions: { series: { animation: false } },
				series: series
			});
	}

	function intradayChart(containerId, url, variable) {
		return $.getJSON(url, function (json) {
			renderIntraday(containerId, json, variable || 'temp');
		}).fail(function () { $('#' + containerId).html('<p>Could not load chart.</p>'); });
	}

	// Fetch a day's data once, render `initialVar`, and (optionally) wire a set of
	// buttons (with data-var attrs) to swap variables client-side without refetching.
	function intradayPanel(containerId, url, initialVar, buttonsSelector) {
		var current = initialVar || 'temp';
		return $.getJSON(url, function (json) {
			renderIntraday(containerId, json, current);
			if (buttonsSelector) {
				$(buttonsSelector).on('click', function () {
					$(buttonsSelector).removeClass('active');
					$(this).addClass('active');
					current = $(this).data('var');
					renderIntraday(containerId, json, current);
				});
			}
		}).fail(function () { $('#' + containerId).html('<p>Could not load chart.</p>'); });
	}

	// ---- Wind rose (polar stacked column) ----
	function windRose(containerId, url) {
		return $.getJSON(url, function (json) {
			if (!json || !json.series) { $('#' + containerId).html('<p>No wind data available.</p>'); return null; }
			Highcharts.chart(containerId, {
				chart: { polar: true, type: 'column', backgroundColor: '#ffffff' },
				title: { text: json.title, style: { color: TEXT, fontSize: '1.05rem', fontWeight: 'normal' } },
				credits: { enabled: true, href: '', text: '\u00A9 nw3weather', style: { color: '#999', fontSize: '9px' } },
				pane: { size: '85%' },
				legend: { align: 'right', verticalAlign: 'top', layout: 'vertical', itemStyle: { color: TEXT } },
				xAxis: { categories: json.categories, tickmarkPlacement: 'on', labels: { style: { color: TEXT } } },
				yAxis: { min: 0, endOnTick: false, showLastLabel: true, title: { text: '% of time' },
					labels: { format: '{value}%' }, reversedStacks: false, gridLineColor: '#ddd' },
				tooltip: { valueSuffix: '%', valueDecimals: 1, shared: true },
				plotOptions: { series: { stacking: 'normal', shadow: false, groupPadding: 0, pointPlacement: 'on', borderWidth: 0 } },
				series: json.series
			});
		}).fail(function () { $('#' + containerId).html('<p>Could not load wind rose.</p>'); });
	}

	window.NW3 = window.NW3 || {};
	window.NW3.histChart = histChart;
	window.NW3.intradayChart = intradayChart;
	window.NW3.intradayPanel = intradayPanel;
	window.NW3.windRose = windRose;
	window.NW3.INTRA_TABS = INTRA_TABS;
})(window);
