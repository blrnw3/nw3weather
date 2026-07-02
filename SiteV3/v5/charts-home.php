<?php /* Home-page interactive chart logic (Highcharts). jQuery is loaded in the page head. */ ?>
<script type="text/javascript">
//<![CDATA[
(function () {
	var EP = 'chartdata.php';
	var TEXT = '#555';
	var AXES = "#666";
	var COMPASS = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW', 'N'];

	// One entry per tab. `series` lists the log keys drawn; each carries a
	// [dark, light] pair used as a vertical gradient stroke (rwcweather style).
	var TABS = {
		temp: { title: 'temperature', yTitle: 'Temperature', unit: '\u00B0C', dp: 1,
			series: [{ key: 'temp', name: 'Temperature', grad: ['#d34f00', '#ffab4d'] }] },
		dewp: { title: 'dew point', yTitle: 'Dew point', unit: '\u00B0C', dp: 1,
			series: [{ key: 'dewp', name: 'Dew point', grad: ['#0b8043', '#5ed17e'] }] },
		humi: { title: 'humidity', yTitle: 'Humidity', unit: '%', dp: 0,
			series: [{ key: 'humi', name: 'Humidity', grad: ['#2e9d30', '#7fd97f'] }] },
		pres: { title: 'pressure', yTitle: 'Pressure', unit: 'hPa', dp: 0,
			series: [{ key: 'pres', name: 'Pressure', grad: ['#5b3fb0', '#a98fe0'] }] },
		wind: { title: 'wind', yTitle: 'Wind speed', unit: 'mph', dp: 1,
			series: [
				{ key: 'wind', name: 'Wind speed', grad: ['#c01717', '#f06a6a'] },
				{ key: 'gust', name: 'Gust', grad: ['#c23b86', '#f08fc0'] }
			] },
		wdir: { title: 'wind direction', yTitle: 'Wind direction', unit: '', dp: 0, dir: true,
			series: [{ key: 'wdir', name: 'Wind direction', flat: '#555555' }] },
		rain: { title: 'rainfall', yTitle: 'Rainfall', unit: 'mm', dp: 1, area: true,
			series: [{ key: 'rain', name: 'Rainfall', grad: ['#2554c7', '#7fa8f0'] }] },
		pm25: { title: 'air quality', yTitle: 'PM2.5', unit: '\u00B5g/m\u00B3', dp: 1,
			series: [{ key: 'pm25', name: 'Air quality (PM2.5)', grad: ['#4f7a4a', '#9ac98f'] }] }
	};

	var currentRange = 12;
	var currentVar = 'temp';
	var cache = null;
	var chart = null;

	if (typeof Highcharts !== 'undefined') {
		Highcharts.setOptions({ time: { timezone: 'Europe/London' } });
	}

	function compass(deg) { return COMPASS[Math.round(deg / 45)] || ''; }

	function pairs(key) {
		var t = cache.time, v = cache[key], out = [], i;
		for (i = 0; i < t.length; i++) {
			out.push([t[i], (v[i] === null ? null : v[i])]);
		}
		return out;
	}

	function gradient(stops) {
		return { linearGradient: { x1: 0, x2: 0, y1: 0, y2: 1 }, stops: [[0, stops[0]], [1, stops[1]]] };
	}

	function titleFor(key) {
		return 'Last ' + currentRange + ' hrs ' + TABS[key].title + ' in nw3';
	}

	// A complete options object is built per tab and the chart is recreated,
	// so axis/tooltip settings never carry over between variables.
	function buildOptions(key) {
		var cfg = TABS[key];
		var isDir = !!cfg.dir;
		var yLabel = cfg.yTitle + (cfg.unit ? ' / ' + cfg.unit : '');

		var yAxis = isDir ? {
			title: { text: yLabel, style: { color: TEXT, fontSize: '0.8rem' } },
			gridLineColor: '#dddddd',
			min: 0, max: 360, tickInterval: 45,
			labels: { style: { color: AXES, fontSize: "11px" }, formatter: function () { return compass(this.value); } }
		} : {
			title: { text: yLabel, style: { color: TEXT, fontSize: '0.8rem' } },
			gridLineColor: '#dddddd',
			min: cfg.area ? 0 : null,
			labels: { style: { color: AXES, fontSize: "11px"} }
		};

		var tooltip = isDir ? {
			formatter: function () {
				return Highcharts.dateFormat('%a %H:%M', this.x)
					+ '<br/><b>' + Math.round(this.y) + '\u00B0 (' + compass(this.y) + ')</b>';
			}
		} : {
			shared: true,
			xDateFormat: '%a %H:%M',
			valueSuffix: cfg.unit ? (' ' + cfg.unit) : '',
			valueDecimals: cfg.dp
		};

		var series = [];
		for (var i = 0; i < cfg.series.length; i++) {
			var s = cfg.series[i];
			var stroke = s.flat ? s.flat : gradient(s.grad);
			var type = isDir ? 'scatter' : (cfg.area ? 'area' : 'line');
			var sObj = {
				name: s.name,
				data: pairs(s.key),
				type: type,
				color: stroke,
				lineColor: stroke,
				lineWidth: isDir ? 0 : 2,
				marker: { enabled: isDir, radius: 2 },
				connectNulls: false
			};
			if (cfg.area) {
				sObj.fillColor = { linearGradient: { x1: 0, x2: 0, y1: 0, y2: 1 },
					stops: [[0, 'rgba(37, 84, 199, 0.30)'], [1, 'rgba(37, 84, 199, 0.02)']] };
			}
			series.push(sObj);
		}

		return {
			chart: { backgroundColor: '#ffffff', spacing: [12, 12, 10, 10], style: { color: TEXT } },
			title: { text: titleFor(key), style: { color: TEXT, fontSize: '1rem', fontWeight: 'normal' } },
			credits: { enabled: true, href: '', text: '\u00A9 nw3weather', style: { color: '#999999', fontSize: '9px' } },
			legend: { enabled: cfg.series.length > 1, itemStyle: { color: TEXT } },
			xAxis: {
				type: 'datetime',
				crosshair: false,
				// gridLineWidth: 1,
				// gridLineColor: '#dddddd',
				dateTimeLabelFormats: { minute: '%H:%M', hour: '%H:%M', day: '%e %b' },
				labels: { style: { color: AXES, fontSize: "11px" } }
			},
			yAxis: yAxis,
			tooltip: tooltip,
			plotOptions: { series: { animation: false, lineWidth: 2, states: { hover: { lineWidthPlus: 1 } } } },
			series: series
		};
	}

	function renderVar(key) {
		if (!cache) { return; }
		currentVar = key;
		if (chart) { chart.destroy(); chart = null; }
		chart = Highcharts.chart('home-chart', buildOptions(key));
	}

	function loadData(range) {
		currentRange = range;
		$.getJSON(EP + '?range=' + range, function (json) {
			cache = json;
			renderVar(currentVar);
		});
	}

	$(function () {
		$('.home-chart-vars button').on('click', function () {
			$('.home-chart-vars button').removeClass('active');
			$(this).addClass('active');
			renderVar($(this).data('var'));
		});
		$('.home-graph-controls button').on('click', function () {
			$('.home-graph-controls button').removeClass('active');
			$(this).addClass('active');
			loadData(parseInt($(this).data('range'), 10));
		});
		loadData(currentRange);
		// Light refresh in step with the per-minute log.
		setInterval(function () { loadData(currentRange); }, 300000);
	});
})();
//]]>
</script>
