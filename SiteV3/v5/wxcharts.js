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

	// Diagonal-stripe fill in the given colour (needs the pattern-fill module).
	// ---- Categorical (daily / monthly / annual / climate) ----
	function histChart(containerId, url, opts) {
		opts = opts || {};
		return $.getJSON(url, function (json) {
			if (!json || json.error) {
				$('#' + containerId).html('<p>No data available.</p>');
				return null;
			}
			var autoscale = (json.yMinZero === false);
			var series = json.series.map(function (s) {
				return {
					name: s.name,
					data: s.data,
					type: s.type || json.chartType || 'column',
					color: s.color,
					dashStyle: s.dashStyle || 'Solid',
					marker: { enabled: false },
					connectNulls: false,
					threshold: autoscale ? null : undefined,
					softThreshold: autoscale ? false : undefined
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
					labels: { style: { color: TEXT, fontSize: opts.labelFontSize || '0.7rem' } },
					gridLineWidth: 0
				},
				yAxis: {
					title: { text: (json.unit ? json.unit : ''), style: { color: TEXT, fontSize: '0.8rem' } },
					gridLineColor: '#ddd',
					min: autoscale ? null : undefined,
					labels: { style: { color: TEXT } }
				},
				tooltip: { shared: true, valueSuffix: json.unit ? (' ' + json.unit) : '', valueDecimals: (json.precision != null ? json.precision : undefined) },
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
		humi: { name: 'Humidity', grad: ['#2e9d30', '#7fd97f'], dp: 0, area: false, min: 0, max: 100 },
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

			var rainUnit = (unit === 'in' || unit === 'in/h');
			var areaFloor = rainUnit ? -0.01 : -0.2;

			var series = [];
			var keys = [variable];
			if (cfg.extra) { keys.push(cfg.extra); }
			keys.forEach(function (k) {
				var kc = INTRA_TABS[k] || cfg;
				var data = pairs(json, k);
				series.push({
					name: kc.name,
					data: data,
					type: isDir ? 'scatter' : (kc.area ? 'area' : 'line'),
					color: isDir ? '#555' : gradient(kc.grad[0], kc.grad[1]),
					lineWidth: isDir ? 0 : (kc.area ? 2.5 : 2),
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
						min: (cfg.min != null ? cfg.min : (cfg.area ? areaFloor : null)),
						max: (cfg.max != null ? cfg.max : undefined), gridLineColor: '#ddd',
						labels: { style: { color: TEXT }, formatter: cfg.area ? function () { return this.value < 0 ? '' : this.value; } : undefined } },
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

	// ---- Multi-day / rolling-window dashboard ----
	// Fetches one intraday dataset (typically num=2, ie yesterday+today) and drives
	// a main single-variable chart (with a client-side range slice) plus any number
	// of fixed multi-variable charts, all from the one fetch.

	function minOf(a) { var m = null, i; for (i = 0; i < a.length; i++) { if (a[i] != null && (m === null || a[i] < m)) { m = a[i]; } } return m; }
	function maxOf(a) { var m = null, i; for (i = 0; i < a.length; i++) { if (a[i] != null && (m === null || a[i] > m)) { m = a[i]; } } return m; }

	var DASH_KEYS = ['time', 'temp', 'dewp', 'humi', 'pres', 'wind', 'gust', 'wdir', 'rain', 'pm25'];

	// Return a shallow copy of `json` keeping only the last `hours` of samples.
	function sliceWindow(json, hours) {
		var t = json.time || [], n = t.length;
		var out = { units: json.units, num: json.num, date: json.date };
		if (!n) { DASH_KEYS.forEach(function (k) { out[k] = []; }); return out; }
		var cutoff = t[n - 1] - hours * 3600 * 1000;
		var start = 0;
		while (start < n && t[start] < cutoff) { start++; }
		DASH_KEYS.forEach(function (k) { out[k] = (json[k] || []).slice(start); });
		return out;
	}

	function dashTimeX() {
		return {
			type: 'datetime', crosshair: true, gridLineWidth: 1, gridLineColor: '#ddd',
			dateTimeLabelFormats: { minute: '%H:%M', hour: '%H:%M', day: '%e %b' }, labels: { style: { color: TEXT } }
		};
	}

	// Temperature + humidity + rain + dew point (legacy graphday.php layout).
	function multiTHRD(containerId, json) {
		var u = json.units || {};
		var tunit = u.temp || '', runit = u.rain || '';
		var isF = tunit.indexOf('F') >= 0, isIn = (runit === 'in');
		var tmax, tincr, tspan;
		if (isF) { tmax = 90; tincr = 10; tspan = 75; } else { tmax = 30; tincr = 5; tspan = 30; }
		var tHi = maxOf(json.temp), dLo = minOf(json.dewp);
		if (tHi != null && tHi > tmax) { tmax += tincr; }
		if (!isF && dLo != null) {
			if (dLo < tmax - tincr * 7) { tmax -= tincr * 2; }
			else if (dLo < tmax - tincr * 6) { tmax -= tincr; }
		}
		var tmin = tmax - tspan;

		var rain = pairs(json, 'rain');
		var rHi = maxOf(rain.map(function (p) { return p[1]; }));
		var rmax = isIn ? 0.75 : 15;
		if (rHi != null && rHi > rmax) { rmax *= 2; }
		if (rHi != null && rHi > rmax) { rmax *= 2; }

		Highcharts.chart(containerId, {
			chart: { backgroundColor: '#ffffff', spacing: [12, 12, 10, 10], style: { color: TEXT } },
			title: { text: 'Temperature, humidity, rain & dew point', style: { color: TEXT, fontSize: '1.05rem', fontWeight: 'normal' } },
			credits: { enabled: true, href: '', text: '\u00A9 nw3weather', style: { color: '#999', fontSize: '9px' } },
			legend: { enabled: true, itemStyle: { color: TEXT } },
			xAxis: dashTimeX(),
			yAxis: [
				{ title: { text: 'Temp / ' + tunit, style: { color: TEXT } }, min: tmin, max: tmax, gridLineColor: '#ddd', labels: { style: { color: TEXT } } },
				{ title: { text: 'Humidity / %', style: { color: TEXT } }, min: 0, max: 100, opposite: true, gridLineWidth: 0, labels: { style: { color: TEXT } } },
				{ title: { text: 'Rain / ' + runit, style: { color: TEXT } }, min: -(rmax * 0.04), max: rmax, opposite: true, gridLineWidth: 0, labels: { style: { color: TEXT }, formatter: function () { return this.value < 0 ? '' : this.value; } } }
			],
			tooltip: { shared: true, xDateFormat: '%a %H:%M' },
			plotOptions: { series: { animation: false, marker: { enabled: false }, connectNulls: false } },
			series: [
				{ name: 'Temperature', data: pairs(json, 'temp'), type: 'line', yAxis: 0, color: gradient('#d34f00', '#ffab4d'), lineWidth: 2, tooltip: { valueDecimals: 1, valueSuffix: ' ' + tunit } },
				{ name: 'Dew point', data: pairs(json, 'dewp'), type: 'line', yAxis: 0, color: gradient('#0b8043', '#5ed17e'), lineWidth: 2, tooltip: { valueDecimals: 1, valueSuffix: ' ' + tunit } },
				{ name: 'Humidity', data: pairs(json, 'humi'), type: 'line', yAxis: 1, color: gradient('#2e9d30', '#7fd97f'), lineWidth: 1, dashStyle: 'ShortDot', tooltip: { valueDecimals: 0, valueSuffix: ' %' } },
				{ name: 'Rainfall', data: rain, type: 'area', yAxis: 2, color: gradient('#2554c7', '#7fa8f0'), lineWidth: 2.5,
					fillColor: { linearGradient: { x1: 0, x2: 0, y1: 0, y2: 1 }, stops: [[0, 'rgba(37,84,199,0.30)'], [1, 'rgba(37,84,199,0.02)']] },
					tooltip: { valueDecimals: 1, valueSuffix: ' ' + runit } }
			]
		});
	}

	// Wind speed + gust + pressure (legacy graphday2.php layout).
	function multiWGP(containerId, json) {
		var u = json.units || {};
		var wunit = u.wind || 'mph', punit = u.pres || 'hPa';
		var isKph = (wunit === 'kph'), isInHg = (punit.indexOf('Hg') >= 0 || punit.indexOf('in') >= 0);
		var wmax = isKph ? 60 : 30, gHi = maxOf(json.gust);
		while (gHi != null && gHi > wmax) { wmax += isKph ? 30 : 15; }
		var pmin = isInHg ? 28 : 970, pmax = isInHg ? 31 : 1045;
		var pdp = isInHg ? 2 : 0;

		Highcharts.chart(containerId, {
			chart: { backgroundColor: '#ffffff', spacing: [12, 12, 10, 10], style: { color: TEXT } },
			title: { text: 'Wind speed, gust & pressure', style: { color: TEXT, fontSize: '1.05rem', fontWeight: 'normal' } },
			credits: { enabled: true, href: '', text: '\u00A9 nw3weather', style: { color: '#999', fontSize: '9px' } },
			legend: { enabled: true, itemStyle: { color: TEXT } },
			xAxis: dashTimeX(),
			yAxis: [
				{ title: { text: 'Wind / ' + wunit, style: { color: TEXT } }, min: 0, max: wmax, gridLineColor: '#ddd', labels: { style: { color: TEXT } } },
				{ title: { text: 'Pressure / ' + punit, style: { color: TEXT } }, min: pmin, max: pmax, opposite: true, gridLineWidth: 0, labels: { style: { color: TEXT } } }
			],
			tooltip: { shared: true, xDateFormat: '%a %H:%M' },
			plotOptions: { series: { animation: false, marker: { enabled: false }, connectNulls: false } },
			series: [
				{ name: 'Wind speed', data: pairs(json, 'wind'), type: 'line', yAxis: 0, color: gradient('#c01717', '#f06a6a'), lineWidth: 2, tooltip: { valueDecimals: 1, valueSuffix: ' ' + wunit } },
				{ name: 'Gust', data: pairs(json, 'gust'), type: 'line', yAxis: 0, color: gradient('#c23b86', '#f08fc0'), lineWidth: 1, tooltip: { valueDecimals: 1, valueSuffix: ' ' + wunit } },
				{ name: 'Pressure', data: pairs(json, 'pres'), type: 'line', yAxis: 1, color: gradient('#5b3fb0', '#a98fe0'), lineWidth: 2, tooltip: { valueDecimals: pdp, valueSuffix: ' ' + punit } }
			]
		});
	}

	function multiChart(containerId, json, kind) {
		if (!json || !json.time || !json.time.length) { $('#' + containerId).html('<p>No data available.</p>'); return; }
		if (kind === 'wgp') { multiWGP(containerId, json); } else { multiTHRD(containerId, json); }
	}

	// Categorical chart whose variable is chosen from a <select>; `baseUrl` carries
	// the mode/length/lta params and the picked type is appended on each render.
	function histSelect(containerId, selectId, baseUrl, opts) {
		var sel = document.getElementById(selectId);
		function load() {
			var type = sel ? sel.value : 'wmean';
			var url = baseUrl + (baseUrl.indexOf('?') >= 0 ? '&' : '?') + 'type=' + encodeURIComponent(type);
			histChart(containerId, url, opts);
		}
		if (sel) { $(sel).on('change', load); }
		load();
	}

	// cfg.ranges: [{ h: hours, days: daysToFetch, maxpts: serverDecimation }, ...]
	// Short ranges (<=24h) share one full-resolution "base" fetch (num=baseDays);
	// longer ranges fetch a separately-decimated dataset so transfer stays small.
	function intradayPage(cfg) {
		var ranges = cfg.ranges || [{ h: 24, days: 2 }];
		var baseDays = cfg.baseDays || 2;
		var state = { variable: cfg.initialVar || 'temp', rangeIdx: cfg.initialRangeIdx || 0, cache: {}, pending: {} };

		function urlFor(days, maxpts) {
			var u = cfg.url + (cfg.url.indexOf('?') >= 0 ? '&' : '?') + 'num=' + days;
			if (maxpts) { u += '&maxpts=' + maxpts; }
			return u;
		}
		function keyFor(days, maxpts) { return days + '|' + (maxpts || 0); }

		function fetchSet(days, maxpts, cb) {
			var key = keyFor(days, maxpts);
			if (state.cache[key]) { cb(state.cache[key]); return; }
			if (state.pending[key]) { state.pending[key].push(cb); return; }
			state.pending[key] = [cb];
			$.getJSON(urlFor(days, maxpts), function (json) {
				state.cache[key] = json;
				var waiting = state.pending[key] || [];
				state.pending[key] = null;
				waiting.forEach(function (fn) { fn(json); });
			}).fail(function () {
				state.pending[key] = null;
				$('#' + cfg.mainId).html('<p>Could not load chart.</p>');
			});
		}

		function drawMain() {
			var r = ranges[state.rangeIdx];
			fetchSet(r.days, r.maxpts, function (json) {
				renderIntraday(cfg.mainId, sliceWindow(json, r.h), state.variable);
			});
		}
		function drawMulti() {
			if (!cfg.multi) { return; }
			fetchSet(baseDays, 0, function (base) {
				var day = sliceWindow(base, 24);
				cfg.multi.forEach(function (m) { multiChart(m.id, day, m.kind); });
			});
		}

		drawMulti();
		drawMain();

		if (cfg.varSel) {
			$(cfg.varSel).on('click', function () {
				$(cfg.varSel).removeClass('active');
				$(this).addClass('active');
				state.variable = $(this).data('var');
				drawMain();
			});
		}
		if (cfg.rangeSel) {
			$(cfg.rangeSel).on('click', function () {
				$(cfg.rangeSel).removeClass('active');
				$(this).addClass('active');
				state.rangeIdx = parseInt($(this).data('idx'), 10) || 0;
				drawMain();
			});
		}
		if (cfg.refresh) {
			setInterval(function () { state.cache = {}; state.pending = {}; drawMulti(); drawMain(); }, cfg.refresh);
		}
	}

	// ---- Wind rose (polar stacked column) ----
	function windRose(containerId, url, opts) {
		opts = opts || {};
		var showLegend = (opts.legend !== false);
		return $.getJSON(url, function (json) {
			if (!json || !json.series) { $('#' + containerId).html('<p>No wind data available.</p>'); return null; }
			Highcharts.chart(containerId, {
				chart: { polar: true, type: 'column', backgroundColor: '#ffffff' },
				title: { text: json.title, style: { color: TEXT, fontSize: '1.05rem', fontWeight: 'normal' } },
				credits: { enabled: true, href: '', text: '\u00A9 nw3weather', style: { color: '#999', fontSize: '9px' } },
				pane: { size: '85%' },
				legend: { enabled: showLegend, align: 'right', verticalAlign: 'top', layout: 'vertical', itemStyle: { color: TEXT } },
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
	window.NW3.intradayPage = intradayPage;
	window.NW3.multiChart = multiChart;
	window.NW3.histSelect = histSelect;
	window.NW3.windRose = windRose;
	window.NW3.INTRA_TABS = INTRA_TABS;
})(window);
