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

	function setLoading(ids, on) {
		if (!ids) { return; }
		(Array.isArray(ids) ? ids : [ids]).forEach(function (id) {
			var el = document.getElementById(id);
			if (!el) { return; }
			if (on) { el.classList.add('wxchart-loading'); }
			else { el.classList.remove('wxchart-loading'); }
		});
	}

	// Diagonal-stripe fill in the given colour (needs the pattern-fill module).
	// ---- Categorical (daily / monthly / annual / climate) ----
	function histChart(containerId, url, opts) {
		opts = opts || {};
		setLoading(containerId, true);
		return $.getJSON(url, function (json) {
			setLoading(containerId, false);
			if (!json || json.error) {
				$('#' + containerId).html('<p>No data available.</p>');
				return null;
			}
			// yMinZero=false (e.g. pressure): let the axis auto-fit. Do not pass
			// threshold:undefined — Highcharts 13 treats that as clearing the default
			// threshold (0) and column bars end up with null height (invisible, but
			// tooltips still work).
			var autoscale = (json.yMinZero === false);
			var series = json.series.map(function (s) {
				var o = {
					name: s.name,
					data: s.data,
					type: s.type || json.chartType || 'column',
					color: s.color,
					dashStyle: s.dashStyle || 'Solid',
					marker: { enabled: false },
					connectNulls: false
				};
				if (autoscale) {
					o.threshold = null;
					o.softThreshold = false;
				}
				return o;
			});
			var yAxis = {
				title: { text: (json.unit ? json.unit : ''), style: { color: TEXT, fontSize: '0.8rem' } },
				gridLineColor: '#ddd',
				labels: { style: { color: TEXT } }
			};
			if (autoscale) { yAxis.min = null; }
			var tips = json.categoryTips || null;
			var unitSuffix = json.unit ? (' ' + json.unit) : '';
			var decimals = (json.precision != null ? json.precision : undefined);
			var tooltip = { shared: true, valueSuffix: unitSuffix, valueDecimals: decimals };
			if (tips) {
				tooltip.formatter = function () {
					var idx = this.points[0].point.x;
					var head = (tips[idx] != null) ? tips[idx] : this.x;
					var html = '<span style="font-size:0.85em">' + head + '</span>';
					for (var i = 0; i < this.points.length; i++) {
						var p = this.points[i];
						var val = (p.y == null) ? '—' : Highcharts.numberFormat(p.y, decimals != null ? decimals : 1);
						html += '<br/><span style="color:' + p.color + '">\u25CF</span> '
							+ p.series.name + ': <b>' + val + unitSuffix + '</b>';
					}
					return html;
				};
			}
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
				yAxis: yAxis,
				tooltip: tooltip,
				plotOptions: {
					column: { borderWidth: 0, pointPadding: 0.02, groupPadding: 0.03 },
					series: { animation: false }
				},
				series: series
			});
		}).fail(function () {
			setLoading(containerId, false);
			$('#' + containerId).html('<p>Could not load chart.</p>');
		});
	}

	// ---- Intraday (per-minute datetime) ----
	var INTRA_TABS = {
		temp: { name: 'Temperature', grad: ['#d34f00', '#ffab4d'], dp: 1, area: false },
		dewp: { name: 'Dew point', grad: ['#0b8043', '#5ed17e'], dp: 1, area: false },
		humi: { name: 'Humidity', grad: ['#2e9d30', '#7fd97f'], dp: 0, area: false, min: 0, max: 100 },
		pres: { name: 'Pressure', grad: ['#5b3fb0', '#a98fe0'], dp: 0, area: false },
		wind: { name: 'Wind speed', grad: ['#c01717', '#f06a6a'], dp: 1, area: false, extra: 'gust' },
		gust: { name: 'Gust', grad: ['#c23b86', '#f08fc0'], dp: 1, area: false },
		rain: { name: 'Rainfall', col: '#2554c7', dp: 1, area: false },
		pm25: { name: 'Air quality (PM2.5)', grad: ['#4f7a4a', '#9ac98f'], dp: 1, area: false },
		wdir: { name: 'Wind direction', col: '#555', dp: 0, dir: true }
	};

	function pairs(json, key) {
		var t = json.time, v = json[key], out = [], i;
		for (i = 0; i < t.length; i++) {
			out.push([t[i], (v[i] === null ? null : v[i])]);
		}
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
					color: kc.col ? kc.col : gradient(kc.grad[0], kc.grad[1]),
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
		setLoading(containerId, true);
		return $.getJSON(url, function (json) {
			setLoading(containerId, false);
			renderIntraday(containerId, json, variable || 'temp');
		}).fail(function () {
			setLoading(containerId, false);
			$('#' + containerId).html('<p>Could not load chart.</p>');
		});
	}

	// Fetch a day's data once, render `initialVar`, and (optionally) wire a set of
	// buttons (with data-var attrs) to swap variables client-side without refetching.
	function intradayPanel(containerId, url, initialVar, buttonsSelector) {
		var current = initialVar || 'temp';
		setLoading(containerId, true);
		return $.getJSON(url, function (json) {
			setLoading(containerId, false);
			renderIntraday(containerId, json, current);
			if (buttonsSelector) {
				$(buttonsSelector).on('click', function () {
					$(buttonsSelector).removeClass('active');
					$(this).addClass('active');
					current = $(this).data('var');
					renderIntraday(containerId, json, current);
				});
			}
		}).fail(function () {
			setLoading(containerId, false);
			$('#' + containerId).html('<p>Could not load chart.</p>');
		});
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

	// Compact y-axis chrome so multi-axis charts keep more width for the plot.
	function multiAxisLabels(color, extra) {
		var o = { style: { color: color, fontSize: '11px' }, x: 0 };
		if (extra) { for (var k in extra) { if (extra.hasOwnProperty(k)) { o[k] = extra[k]; } } }
		return o;
	}
	function multiAxisTitle(text, color) {
		return { text: text, margin: 2, style: { color: color, fontSize: '11px', fontWeight: 'normal' } };
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
		var rtick = isIn ? 0.25 : 5;

		var colTemp = '#d34f00', colHumi = '#1d7806', colRain = '#2554c7';

		Highcharts.chart(containerId, {
			// alignTicks (default true) forces shared tick counts across axes and can
			// push humidity past 100% so its ticks line up with temp/rain — disable it.
			chart: { backgroundColor: '#ffffff', alignTicks: false, spacing: [10, 4, 8, 4], style: { color: TEXT } },
			title: { text: 'Temperature, humidity, rain', style: { color: TEXT, fontSize: '1.05rem', fontWeight: 'normal' } },
			credits: { enabled: true, href: '', text: '\u00A9 nw3weather', style: { color: '#999', fontSize: '9px' } },
			legend: { enabled: true, itemStyle: { color: TEXT, fontSize: '11px' } },
			xAxis: dashTimeX(),
			yAxis: [
				{
					title: multiAxisTitle('Temp / ' + tunit, colTemp),
					min: tmin, max: tmax, tickInterval: tincr,
					startOnTick: false, endOnTick: false,
					gridLineColor: '#ddd',
					labels: multiAxisLabels(colTemp, { align: 'right', x: -2 })
				},
				{
					title: multiAxisTitle('Hum %', colHumi),
					min: 0, max: 100, tickInterval: 20,
					startOnTick: false, endOnTick: false,
					opposite: true, gridLineWidth: 0,
					labels: multiAxisLabels(colHumi, { align: 'left', x: 2 })
				},
				{
					title: multiAxisTitle('Rain / ' + runit, colRain),
					min: 0, max: rmax, tickInterval: rtick,
					startOnTick: false, endOnTick: false,
					opposite: true, gridLineWidth: 0,
					labels: multiAxisLabels(colRain, {
						align: 'left', x: 2,
						formatter: function () { return this.value < 0 ? '' : this.value; }
					})
				}
			],
			tooltip: { shared: true, xDateFormat: '%a %H:%M' },
			plotOptions: { series: { animation: false, marker: { enabled: false }, connectNulls: false } },
			series: [
				{ name: 'Temperature', data: pairs(json, 'temp'), type: 'line', yAxis: 0, color: colTemp, lineWidth: 2, tooltip: { valueDecimals: 1, valueSuffix: ' ' + tunit } },
				{ name: 'Dew point', data: pairs(json, 'dewp'), type: 'line', yAxis: 0, color: '#56eb31', lineWidth: 1.5, tooltip: { valueDecimals: 1, valueSuffix: ' ' + tunit } },
				{ name: 'Humidity', data: pairs(json, 'humi'), type: 'line', yAxis: 1, color: colHumi, lineWidth: 2, tooltip: { valueDecimals: 0, valueSuffix: ' %' } },
				// Solid colour (not a vertical gradient): a flat rain line has ~0 SVG bbox height, so gradient strokes paint nothing.
				{ name: 'Rainfall', data: rain, type: 'line', yAxis: 2, color: colRain, lineWidth: 2.5, tooltip: { valueDecimals: 1, valueSuffix: ' ' + runit } }
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
		var pmin = isInHg ? 28 : 960, pmax = isInHg ? 31 : 1050;
		var pdp = isInHg ? 2 : 0;
		var pTick = isInHg ? 0.5 : 15;
		var colWind = '#c01717', colPres = '#5b3fb0';

		Highcharts.chart(containerId, {
			chart: { backgroundColor: '#ffffff', alignTicks: false, spacing: [10, 4, 8, 4], style: { color: TEXT } },
			title: { text: 'Wind speed, gust & pressure', style: { color: TEXT, fontSize: '1.05rem', fontWeight: 'normal' } },
			credits: { enabled: true, href: '', text: '\u00A9 nw3weather', style: { color: '#999', fontSize: '9px' } },
			legend: { enabled: true, itemStyle: { color: TEXT, fontSize: '11px' } },
			xAxis: dashTimeX(),
			yAxis: [
				{
					title: multiAxisTitle('Wind / ' + wunit, colWind),
					min: 0, max: wmax,
					startOnTick: false, endOnTick: false,
					gridLineColor: '#ddd',
					labels: multiAxisLabels(colWind, { align: 'right', x: -2 })
				},
				{
					title: multiAxisTitle(punit, colPres),
					min: pmin, max: pmax, tickInterval: pTick,
					startOnTick: false, endOnTick: false,
					opposite: true, gridLineWidth: 0,
					labels: multiAxisLabels(colPres, { align: 'left', x: 2 })
				}
			],
			tooltip: { shared: true, xDateFormat: '%a %H:%M' },
			plotOptions: { series: { animation: false, marker: { enabled: false }, connectNulls: false } },
			series: [
				{ name: 'Wind speed', data: pairs(json, 'wind'), type: 'line', yAxis: 0, color: colWind, lineWidth: 2, tooltip: { valueDecimals: 1, valueSuffix: ' ' + wunit } },
				{ name: 'Gust', data: pairs(json, 'gust'), type: 'line', yAxis: 0, color: '#c23b86', lineWidth: 0.5, tooltip: { valueDecimals: 1, valueSuffix: ' ' + wunit } },
				{ name: 'Pressure', data: pairs(json, 'pres'), type: 'line', yAxis: 1, color: colPres, lineWidth: 2, tooltip: { valueDecimals: pdp, valueSuffix: ' ' + punit } }
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

	// Left-hand selector: group → subtype (+ monthly aggregation / normals / cume / year).
	// cfg: { containerId, panelId, url, opts, groups, defaultType, defaultGroup,
	//        showAggregation, showNormals, showCume, showYears, defaultSummary,
	//        defaultLta, defaultCume, defaultYear, varMeta, aggLabels }
	function histSelectGrouped(cfg) {
		var panel = document.getElementById(cfg.panelId);
		if (!panel) { return; }
		var groupBtns = panel.querySelectorAll('.wxsel-groups button');
		var subEl = panel.querySelector('.wxsel-subtypes');
		var aggEl = panel.querySelector('.wxsel-agg');
		var yearBtns = panel.querySelectorAll('.wxsel-years button');
		var normBtn = panel.querySelector('.wxsel-toggle[data-lta]');
		var cumeBtn = panel.querySelector('.wxsel-toggle[data-cume]');
		var groups = cfg.groups || [];
		var varMeta = cfg.varMeta || {};
		var aggLabels = cfg.aggLabels || { 0: 'Mean', 1: 'Total', 2: 'Count', 3: 'Low', 4: 'High' };
		var state = {
			groupId: cfg.defaultGroup,
			type: cfg.defaultType,
			summary: cfg.defaultSummary != null ? cfg.defaultSummary : 0,
			lta: !!cfg.defaultLta,
			cume: !!cfg.defaultCume,
			year: cfg.defaultYear || null
		};

		function groupById(id) {
			var i;
			for (i = 0; i < groups.length; i++) {
				if (groups[i].id === id) { return groups[i]; }
			}
			return groups[0];
		}
		function metaFor(type) {
			return varMeta[type] || { aggregations: [0], primary: 0, summable: false };
		}
		function syncNormalsUi() {
			if (!normBtn) { return; }
			// histdata only overlays normals for mean/total summaries (or daily whole-year).
			var allowed = !cfg.showAggregation || state.summary === 0 || state.summary === 1;
			normBtn.disabled = !allowed;
			normBtn.classList.toggle('active', allowed && state.lta);
			normBtn.classList.toggle('disabled', !allowed);
		}
		function syncCumeUi() {
			if (!cumeBtn || !cfg.showCume) { return; }
			var allowed = !!metaFor(state.type).summable;
			if (!allowed) { state.cume = false; }
			cumeBtn.disabled = !allowed;
			cumeBtn.classList.toggle('active', allowed && state.cume);
			cumeBtn.classList.toggle('disabled', !allowed);
		}
		function load() {
			var url = cfg.url + (cfg.url.indexOf('?') >= 0 ? '&' : '?')
				+ 'type=' + encodeURIComponent(state.type);
			if (cfg.showYears && state.year) {
				url += '&year=' + encodeURIComponent(state.year);
			}
			if (cfg.showAggregation) {
				url += '&summary_type=' + encodeURIComponent(state.summary);
			}
			if (cfg.showNormals && state.lta && (!cfg.showAggregation || state.summary === 0 || state.summary === 1)) {
				url += '&lta=1';
			}
			if (cfg.showCume && state.cume && metaFor(state.type).summable) {
				url += '&cume=1';
			}
			histChart(cfg.containerId, url, cfg.opts || {});
		}
		function renderAggregation() {
			if (!cfg.showAggregation || !aggEl) { return; }
			var meta = metaFor(state.type);
			var aggs = meta.aggregations || [meta.primary];
			if (aggs.indexOf(state.summary) < 0) { state.summary = meta.primary; }
			var html = '', i, st;
			for (i = 0; i < aggs.length; i++) {
				st = aggs[i];
				html += '<button type="button" class="' + (st === state.summary ? 'active' : '')
					+ '" data-summary="' + st + '">' + (aggLabels[st] || st) + '</button>';
			}
			aggEl.innerHTML = html;
			syncNormalsUi();
			syncCumeUi();
		}
		function renderSubtypes() {
			var g = groupById(state.groupId);
			var opts = g.options || {};
			var keys = Object.keys(opts);
			if (keys.indexOf(state.type) < 0) { state.type = keys[0]; }
			if (!metaFor(state.type).summable) { state.cume = false; }
			var html = '', i, k;
			for (i = 0; i < keys.length; i++) {
				k = keys[i];
				html += '<button type="button" class="' + (k === state.type ? 'active' : '')
					+ '" data-type="' + k + '">' + opts[k] + '</button>';
			}
			subEl.innerHTML = html;
			$(subEl).find('button').on('click', function () {
				$(subEl).find('button').removeClass('active');
				$(this).addClass('active');
				state.type = String($(this).attr('data-type'));
				if (!metaFor(state.type).summable) { state.cume = false; }
				renderAggregation();
				syncCumeUi();
				load();
			});
			renderAggregation();
			syncCumeUi();
		}

		$(groupBtns).on('click', function () {
			$(groupBtns).removeClass('active');
			$(this).addClass('active');
			state.groupId = $(this).data('group');
			renderSubtypes();
			load();
		});
		if (cfg.showAggregation && aggEl) {
			$(aggEl).on('click', 'button', function () {
				$(aggEl).find('button').removeClass('active');
				$(this).addClass('active');
				state.summary = parseInt($(this).attr('data-summary'), 10);
				if (isNaN(state.summary)) { state.summary = metaFor(state.type).primary; }
				syncNormalsUi();
				load();
			});
		}
		if (cfg.showYears && yearBtns.length) {
			$(yearBtns).on('click', function () {
				$(yearBtns).removeClass('active');
				$(this).addClass('active');
				state.year = parseInt($(this).attr('data-year'), 10) || cfg.defaultYear;
				load();
			});
		}
		if (cfg.showNormals && normBtn) {
			$(normBtn).on('click', function () {
				if (normBtn.disabled) { return; }
				state.lta = !state.lta;
				syncNormalsUi();
				load();
			});
		}
		if (cfg.showCume && cumeBtn) {
			$(cumeBtn).on('click', function () {
				if (cumeBtn.disabled) { return; }
				state.cume = !state.cume;
				syncCumeUi();
				load();
			});
		}
		renderSubtypes();
		load();
	}

	// Full Chart Viewer (charts.php): group/measure + timescale + aggregation +
	// period/length selects + normals/cumulative toggles.
	function histViewer(cfg) {
		var panel = document.getElementById(cfg.panelId);
		if (!panel) { return; }
		var groupBtns = panel.querySelectorAll('.wxsel-groups button');
		var subEl = panel.querySelector('.wxsel-subtypes');
		var scaleBtns = panel.querySelectorAll('.wxsel-scale button');
		var aggEl = panel.querySelector('.wxsel-agg');
		var aggWrap = panel.querySelector('.wxsel-agg-wrap');
		var groups = cfg.groups || [];
		var varMeta = cfg.varMeta || {};
		var aggLabels = cfg.aggLabels || { 0: 'Mean', 1: 'Total', 2: 'Count', 3: 'Low', 4: 'High' };
		var state = {
			groupId: cfg.defaultGroup,
			type: cfg.defaultType,
			scale: 'daily',
			summary: cfg.defaultSummary != null ? cfg.defaultSummary : 0,
			lta: cfg.defaultLta !== false,
			cume: false
		};

		function groupById(id) {
			var i;
			for (i = 0; i < groups.length; i++) {
				if (groups[i].id === id) { return groups[i]; }
			}
			return groups[0];
		}
		function metaFor(type) {
			return varMeta[type] || { aggregations: [0], primary: 0, description: type, startYear: 2009, summable: false };
		}
		function setHidden(el, on) {
			if (!el) { return; }
			if (on) { el.setAttribute('hidden', 'hidden'); }
			else { el.removeAttribute('hidden'); }
		}
		function syncPeriodUi() {
			var yr = parseInt($('#cv-year').val(), 10) || 0;
			var mon = $('#cv-month').val();
			var summable = !!metaFor(state.type).summable;
			var showAgg = state.scale === 'monthly' || state.scale === 'annual';
			var showYear = state.scale !== 'annual';
			var showMonth = state.scale === 'daily' && yr > 0;
			var showLenD = state.scale === 'daily' && yr === 0;
			var showLenM = state.scale === 'monthly' && yr === 0;
			var showStartEnd = state.scale === 'annual';
			var showCume = summable && state.scale === 'daily' && yr > 0 && mon === '0';
			var showLta = (state.scale === 'monthly')
				|| (state.scale === 'daily' && yr > 0 && mon === '0');
			// Daily whole-year normals are always eligible; monthly needs mean/total.
			var ltaAllowed = (state.scale === 'daily')
				|| state.summary === 0 || state.summary === 1;

			if (!showCume) { state.cume = false; }

			setHidden(aggWrap, !showAgg);
			setHidden(document.getElementById('cv-year-wrap'), !showYear);
			setHidden(document.getElementById('cv-month-wrap'), !showMonth);
			setHidden(document.getElementById('cv-lengthD-wrap'), !showLenD);
			setHidden(document.getElementById('cv-lengthM-wrap'), !showLenM);
			setHidden(document.getElementById('cv-start-wrap'), !showStartEnd);
			setHidden(document.getElementById('cv-end-wrap'), !showStartEnd);
			setHidden(document.getElementById('cv-cume'), !showCume);
			setHidden(document.getElementById('cv-lta'), !showLta);

			var ltaBtn = document.getElementById('cv-lta');
			if (ltaBtn && showLta) {
				ltaBtn.disabled = !ltaAllowed;
				ltaBtn.classList.toggle('active', ltaAllowed && state.lta);
				ltaBtn.classList.toggle('disabled', !ltaAllowed);
			}
			var cumeBtn = document.getElementById('cv-cume');
			if (cumeBtn && showCume) {
				cumeBtn.classList.toggle('active', state.cume);
			}

			var histYear = false;
			if (state.scale === 'annual') {
				histYear = parseInt($('#cv-start').val(), 10) < 2009;
			} else if (yr > 0 && yr < 2009) {
				histYear = true;
			}
			setHidden(document.getElementById('cv-disclaimer'), !histYear);
		}
		function renderAggregation() {
			if (!aggEl) { return; }
			var meta = metaFor(state.type);
			var aggs = meta.aggregations || [meta.primary];
			if (aggs.indexOf(state.summary) < 0) { state.summary = meta.primary; }
			var html = '', i, st;
			for (i = 0; i < aggs.length; i++) {
				st = aggs[i];
				html += '<button type="button" class="' + (st === state.summary ? 'active' : '')
					+ '" data-summary="' + st + '">' + (aggLabels[st] || st) + '</button>';
			}
			aggEl.innerHTML = html;
		}
		function renderSubtypes() {
			var g = groupById(state.groupId);
			var opts = g.options || {};
			var keys = Object.keys(opts);
			if (keys.indexOf(state.type) < 0) { state.type = keys[0]; }
			if (!metaFor(state.type).summable) { state.cume = false; }
			var html = '', i, k;
			for (i = 0; i < keys.length; i++) {
				k = keys[i];
				html += '<button type="button" class="' + (k === state.type ? 'active' : '')
					+ '" data-type="' + k + '">' + opts[k] + '</button>';
			}
			subEl.innerHTML = html;
			$(subEl).find('button').on('click', function () {
				$(subEl).find('button').removeClass('active');
				$(this).addClass('active');
				state.type = String($(this).attr('data-type'));
				if (!metaFor(state.type).summable) { state.cume = false; }
				renderAggregation();
				syncPeriodUi();
				load();
			});
			renderAggregation();
		}
		function updateHeading() {
			var el = document.getElementById(cfg.headingId);
			if (!el) { return; }
			el.textContent = 'Data Charts \u2013 ' + metaFor(state.type).description;
		}
		function load() {
			syncPeriodUi();
			updateHeading();
			var yr = parseInt($('#cv-year').val(), 10) || 0;
			var mon = $('#cv-month').val();
			var params = { type: state.type, mode: state.scale };
			if (state.scale === 'daily') {
				if (yr > 0) {
					params.year = yr;
					params.month = mon;
					if (mon === '0') {
						if (state.cume && metaFor(state.type).summable) { params.cume = 1; }
						if (state.lta) { params.lta = 1; }
					}
				} else {
					params.length = $('#cv-lengthD').val();
				}
			} else if (state.scale === 'monthly') {
				params.summary_type = state.summary;
				if (state.lta && (state.summary === 0 || state.summary === 1)) { params.lta = 1; }
				if (yr > 0) { params.year = yr; }
				else { params.length = $('#cv-lengthM').val(); }
			} else {
				params.summary_type = state.summary;
				params.start = $('#cv-start').val();
				params.end = $('#cv-end').val();
			}
			histChart(cfg.containerId, cfg.url + '?' + $.param(params), cfg.opts || {});
		}

		$(groupBtns).on('click', function () {
			$(groupBtns).removeClass('active');
			$(this).addClass('active');
			state.groupId = $(this).data('group');
			renderSubtypes();
			load();
		});
		$(scaleBtns).on('click', function () {
			$(scaleBtns).removeClass('active');
			$(this).addClass('active');
			state.scale = $(this).data('scale');
			load();
		});
		if (aggEl) {
			$(aggEl).on('click', 'button', function () {
				$(aggEl).find('button').removeClass('active');
				$(this).addClass('active');
				state.summary = parseInt($(this).attr('data-summary'), 10);
				if (isNaN(state.summary)) { state.summary = metaFor(state.type).primary; }
				load();
			});
		}
		$('#cv-lta').on('click', function () {
			if (this.disabled) { return; }
			state.lta = !state.lta;
			load();
		});
		$('#cv-cume').on('click', function () {
			if (!metaFor(state.type).summable) { return; }
			state.cume = !state.cume;
			load();
		});
		$(panel).on('change', 'select', load);

		renderSubtypes();
		load();
	}

	// Custom Graph Viewer: multi-variable fixed-scale presets (thrd / wgp) or a
	// single auto-scaled series, with flat icon variable buttons and day-length buttons.
	// cfg: { containerId, panelId, url, defaultType, defaultMode, defaultNum }
	function graphViewer(cfg) {
		var panel = document.getElementById(cfg.panelId);
		if (!panel) { return; }
		var modeBtns = panel.querySelectorAll('#gv-mode button');
		var varBtns = panel.querySelectorAll('#gv-vars button');
		var numBtns = panel.querySelectorAll('#gv-num button');
		var varWrap = document.getElementById('gv-var-wrap');
		var state = {
			mode: cfg.defaultMode || 'thrd',
			type: cfg.defaultType || 'temp',
			num: cfg.defaultNum || 3,
			cacheKey: null,
			json: null
		};

		function pad(n) { return (n < 10 ? '0' : '') + n; }
		function setHidden(el, on) {
			if (!el) { return; }
			if (on) { el.setAttribute('hidden', 'hidden'); }
			else { el.removeAttribute('hidden'); }
		}
		function syncModeUi() {
			var single = state.mode === 'single';
			setHidden(varWrap, !single);
			$(modeBtns).removeClass('active');
			$(modeBtns).filter('[data-mode="' + state.mode + '"]').addClass('active');
		}
		function requestUrl() {
			var y = $('#gv-year').val();
			var m = pad(parseInt($('#gv-month').val(), 10));
			var d = pad(parseInt($('#gv-day').val(), 10));
			var num = state.num || 3;
			var u = cfg.url + (cfg.url.indexOf('?') >= 0 ? '&' : '?')
				+ 'date=' + y + m + d + '&num=' + num;
			// Decimate longer spans so transfer stays manageable (~1 pt / hour-ish).
			if (num > 5) { u += '&maxpts=' + Math.min(1440, Math.max(480, num * 48)); }
			return { url: u, key: y + m + d + '|' + num };
		}
		function draw() {
			syncModeUi();
			if (!state.json) { return; }
			if (state.mode === 'single') {
				renderIntraday(cfg.containerId, state.json, state.type);
			} else {
				multiChart(cfg.containerId, state.json, state.mode);
			}
		}
		function load() {
			var req = requestUrl();
			if (state.cacheKey === req.key && state.json) {
				draw();
				return;
			}
			setLoading(cfg.containerId, true);
			$.getJSON(req.url, function (json) {
				setLoading(cfg.containerId, false);
				state.cacheKey = req.key;
				state.json = json;
				draw();
			}).fail(function () {
				setLoading(cfg.containerId, false);
				state.cacheKey = null;
				state.json = null;
				$('#' + cfg.containerId).html('<p>Could not load chart.</p>');
			});
		}

		$(modeBtns).on('click', function () {
			state.mode = String($(this).attr('data-mode'));
			draw();
		});
		$(varBtns).on('click', function () {
			$(varBtns).removeClass('active');
			$(this).addClass('active');
			state.type = String($(this).attr('data-var'));
			draw();
		});
		$(numBtns).on('click', function () {
			$(numBtns).removeClass('active');
			$(this).addClass('active');
			state.num = parseInt($(this).attr('data-num'), 10) || 3;
			load();
		});
		$(panel).on('change', 'select', load);

		syncModeUi();
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

		function fetchSet(days, maxpts, cb, loadingIds) {
			var key = keyFor(days, maxpts);
			if (state.cache[key]) { cb(state.cache[key]); return; }
			setLoading(loadingIds, true);
			var waiter = { cb: cb, ids: loadingIds };
			if (state.pending[key]) { state.pending[key].push(waiter); return; }
			state.pending[key] = [waiter];
			$.getJSON(urlFor(days, maxpts), function (json) {
				state.cache[key] = json;
				var waiting = state.pending[key] || [];
				state.pending[key] = null;
				waiting.forEach(function (w) {
					setLoading(w.ids, false);
					w.cb(json);
				});
			}).fail(function () {
				var waiting = state.pending[key] || [];
				state.pending[key] = null;
				waiting.forEach(function (w) { setLoading(w.ids, false); });
				$('#' + cfg.mainId).html('<p>Could not load chart.</p>');
			});
		}

		function drawMain() {
			var r = ranges[state.rangeIdx];
			fetchSet(r.days, r.maxpts, function (json) {
				renderIntraday(cfg.mainId, sliceWindow(json, r.h), state.variable);
			}, cfg.mainId);
		}
		function drawMulti() {
			if (!cfg.multi) { return; }
			var multiIds = cfg.multi.map(function (m) { return m.id; });
			fetchSet(baseDays, 0, function (base) {
				var day = sliceWindow(base, 24);
				cfg.multi.forEach(function (m) { multiChart(m.id, day, m.kind); });
			}, multiIds);
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
		setLoading(containerId, true);
		return $.getJSON(url, function (json) {
			setLoading(containerId, false);
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
		}).fail(function () {
			setLoading(containerId, false);
			$('#' + containerId).html('<p>Could not load wind rose.</p>');
		});
	}

	window.NW3 = window.NW3 || {};
	window.NW3.histChart = histChart;
	window.NW3.intradayChart = intradayChart;
	window.NW3.intradayPanel = intradayPanel;
	window.NW3.intradayPage = intradayPage;
	window.NW3.multiChart = multiChart;
	window.NW3.histSelect = histSelect;
	window.NW3.histSelectGrouped = histSelectGrouped;
	window.NW3.histViewer = histViewer;
	window.NW3.graphViewer = graphViewer;
	window.NW3.windRose = windRose;
	window.NW3.INTRA_TABS = INTRA_TABS;
})(window);
