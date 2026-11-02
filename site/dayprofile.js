/*
 * dayprofile.js - the daily profile page (wxdayprofile.php): draws the average of a variable through the day from
 * the precomputed profile JSON, and switches variable / year / month in place via dayprofiledata.php.
 * Needs Highcharts (loaded by the page).
 */
(function (window, document) {
	'use strict';

	var S = { cfg: null, p: null, v: 'temp', year: 0, month: 0, bin: null, cmp: null, chart: null, seq: 0 };
	var MONTHS_LONG = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September',
		'October', 'November', 'December'];

	function hhmm(mins) {
		var m = ((Math.round(mins) % 1440) + 1440) % 1440;
		return (m < 600 ? '0' : '') + Math.floor(m / 60) + ':' + (m % 60 < 10 ? '0' : '') + (m % 60);
	}
	function isMobile() { return window.innerWidth < 600; }
	function fmt(x, dp) { return x == null ? '–' : Number(x).toFixed(dp); }
	function unitSep(u) { return u === '%' ? '' : ' '; }

	// Weighted re-binning: bins of `bin` minutes from 5-minute averages and minute counts.
	function rebin(prof, step, bin) {
		if (!prof) { return null; }
		var k = Math.max(1, Math.round(bin / step)), out = [];
		for (var i = 0; i < prof.avg.length; i += k) {
			var s = 0, n = 0;
			for (var j = i; j < i + k && j < prof.avg.length; j++) {
				if (prof.avg[j] != null && prof.n[j]) { s += prof.avg[j] * prof.n[j]; n += prof.n[j]; }
			}
			out.push({ x: i * step + bin / 2, y: n ? s / n : null, n: n });
		}
		return out;
	}

	function periodLabel(year, month) {
		var m = month ? MONTHS_LONG[month - 1] : null;
		if (!year) { return m ? 'every ' + m : 'all years'; }
		return m ? m + ' ' + year : String(year);
	}
	function baseLabel(p) {
		if (!p.baseKey) { return null; }
		if (p.baseKey === 'all') { return 'all years, all months'; }
		return 'every ' + MONTHS_LONG[parseInt(p.baseKey.slice(1), 10) - 1];
	}
	function monthSpan(ym) {
		var a = ym.split('-');
		return S.cfg.months[parseInt(a[1], 10) - 1] + ' ' + a[0];
	}

	function draw() {
		var p = S.p, el = document.getElementById('dp-chart');
		var bin = S.bin || p.defaultBin;
		var main = rebin(p.main, p.bin, bin);
		// Compare by default except a single month against the all-months profile (different level; it squashes the chart).
		var cmp = S.cmp !== null ? S.cmp : !(p.baseKey === 'all' && S.month && !S.year);
		var cmpBtn = document.getElementById('dp-cmp');
		cmpBtn.classList.toggle('active', cmp); cmpBtn.setAttribute('aria-pressed', cmp ? 'true' : 'false');
		var base = cmp ? rebin(p.base, p.bin, bin) : null;
		var period = periodLabel(S.year, S.month);
		var has = main && main.some(function (q) { return q.y != null; });
		document.getElementById('dp-cmp-wrap').style.display = p.baseKey ? '' : 'none';
		if (S.chart) { S.chart.destroy(); S.chart = null; }
		if (!has) {
			el.innerHTML = '<p style="padding:2em 1em">No clean ' + p.name.toLowerCase() + ' data for ' + period + '.</p>';
			summary(null, p, period);
			return;
		}
		var mobile = isMobile(), dp = p.dp, unit = p.unit;
		var series = [];
		if (base) {
			series.push({ name: 'Long-term (' + baseLabel(p) + ')', type: 'line', color: '#868e96', dashStyle: 'ShortDash',
				lineWidth: 1.5, data: base, zIndex: 1, enableMouseTracking: true });
		}
		series.push({ name: p.name + ', ' + period, type: p.area ? 'area' : 'line', color: p.colour, lineWidth: 2,
			fillOpacity: p.area ? 0.3 : undefined, threshold: p.area ? 0 : null, data: main, zIndex: 2 });
		S.chart = Highcharts.chart(el, {
			credits: { enabled: false },
			chart: { height: mobile ? 340 : 440, zoomType: 'x', spacing: mobile ? [8, 4, 10, 0] : [12, 12, 12, 8],
				backgroundColor: '#fff' },
			title: { text: p.name + ', ' + period, style: { fontSize: mobile ? '13px' : '16px' } },
			legend: { enabled: !!base, itemStyle: { fontSize: mobile ? '10px' : '12px', fontWeight: 'normal' } },
			xAxis: {
				min: 0, max: 1440, tickInterval: mobile ? 240 : 120, crosshair: true,
				labels: { formatter: function () { return hhmm(this.value); }, style: { fontSize: mobile ? '10px' : '12px' } },
				title: { text: mobile ? null : 'Time of day (UK clock time)' }
			},
			yAxis: {
				min: p.yMin, title: { text: mobile ? null : unit }, softMax: unit === '%' ? 10 : undefined,
				labels: { style: { fontSize: mobile ? '10px' : '12px' } }
			},
			tooltip: {
				shared: true, useHTML: true,
				formatter: function () {
					var x0 = this.x - bin / 2, s = '<b>' + hhmm(x0) + '–' + hhmm(x0 + bin) + '</b>';
					(this.points || [this.point]).forEach(function (pt) {
						var q = pt.point || pt;
						s += '<br/><span style="color:' + pt.series.color + '">\u25CF</span> ' + pt.series.name + ': <b>'
							+ fmt(q.y, dp) + unitSep(unit) + unit + '</b>';
					});
					var m = (this.points || [this.point]).filter(function (pt) { return pt.series.index === series.length - 1; })[0];
					if (m && (m.point || m).n) { s += '<br/><span style="color:#777">' + (m.point || m).n.toLocaleString() + ' minutes of data</span>'; }
					return s;
				}
			},
			plotOptions: { series: { marker: { enabled: false }, animation: false, turboThreshold: 0,
				states: { inactive: { opacity: 1 } } } },
			series: series
		});
		summary(main, p, period);
	}

	function summary(main, p, period) {
		var sEl = document.getElementById('dp-summary'), fEl = document.getElementById('dp-foot');
		var nEl = document.getElementById('dp-note');
		nEl.textContent = S.cfg.notes[p.var] || '';
		if (!main) { sEl.textContent = ''; fEl.textContent = ''; return; }
		var hi = null, lo = null, s = 0, n = 0, dp = p.dp, u = p.unit, sep = unitSep(u);
		main.forEach(function (q) {
			if (q.y == null) { return; }
			if (!hi || q.y > hi.y) { hi = q; }
			if (!lo || q.y < lo.y) { lo = q; }
			// sunshine: weight by possible-sun minutes, so the average is the share of all possible sunshine
			var w = p.var === 'sun' ? (q.n || 0) : 1;
			s += q.y * w; n += w;
		});
		var bin = S.bin || p.defaultBin, at = function (q) { return hhmm(q.x - bin / 2); };
		var txt;
		if (p.var === 'rain') {
			txt = 'Wettest at ' + at(hi) + ' (' + fmt(hi.y, dp) + sep + u + '); driest at ' + at(lo) + ' (' + fmt(lo.y, dp)
				+ sep + u + '). Average ' + fmt(s / n * 24, Math.max(1, dp - 2)) + sep + u.replace('/h', '') + ' a day.';
		} else if (p.var === 'sun' || p.var === 'wet') {
			txt = 'Peak ' + fmt(hi.y, dp) + '% at ' + at(hi) + '; ' + fmt(s / n, dp)
				+ (p.var === 'sun' ? '% of the possible sunshine on average.' : '% of the whole day on average.');
		} else {
			txt = 'Highest ' + fmt(hi.y, dp) + sep + u + ' at ' + at(hi) + '; lowest ' + fmt(lo.y, dp) + sep + u + ' at '
				+ at(lo) + '; range ' + fmt(hi.y - lo.y, dp) + sep + u + '.';
		}
		sEl.textContent = txt;
		fEl.textContent = p.main.days.toLocaleString() + ' days of data (' + monthSpan(p.main.from)
			+ (p.main.from !== p.main.to ? ' to ' + monthSpan(p.main.to) : '') + '), ' + bin + '-minute averages.'
			+ (p.main.est ? ' ' + (p.main.est === p.main.days ? 'All' : p.main.est.toLocaleString() + ' of them')
				+ ' model-estimated from archived webcam frames, scaled to each day\'s sunshine total.' : '');
	}

	function setActive(row, v) {
		var as = document.querySelectorAll('[data-dp="' + row + '"] a.wxsel-chip');
		for (var i = 0; i < as.length; i++) { as[i].classList.toggle('active', as[i].getAttribute('data-v') === String(v)); }
		if (row === 'var') {
			// Group buttons and the measures shown follow the variable, as on the data tables.
			var cur = document.querySelector('[data-dp="var"] a.wxsel-chip[data-v="' + v + '"]');
			var g = cur ? cur.getAttribute('data-group') : null;
			var bs = document.querySelectorAll('#dp-controls .wxsel-groups button');
			for (var j = 0; j < bs.length; j++) { bs[j].classList.toggle('active', bs[j].getAttribute('data-group') === g); }
			for (var k = 0; k < as.length; k++) { as[k].hidden = as[k].getAttribute('data-group') !== g; }
			// No measure row for a group with a single measure: its group button already selects it.
			var shown = 0;
			for (var n = 0; n < as.length; n++) { if (!as[n].hidden) { shown++; } }
			document.getElementById('dp-var-row').hidden = shown < 2;
		}
		if (row === 'year') {
			// "Older" overflow: its summary shows the chosen older year, like the data tables.
			var det = document.querySelector('[data-dp="year"] details.wxsel-overflow');
			if (det) {
				var sel = det.querySelector('a.wxsel-chip.active'), sum = det.querySelector('summary');
				sum.textContent = sel ? sel.getAttribute('data-v') : 'Older';
				sum.classList.toggle('active', !!sel);
				det.open = false;
			}
		}
	}
	function refreshLinks() {
		var rows = ['var', 'year', 'month'];
		rows.forEach(function (row) {
			var as = document.querySelectorAll('[data-dp="' + row + '"] a.wxsel-chip');
			for (var i = 0; i < as.length; i++) {
				var x = as[i].getAttribute('data-v');
				var q = { v: S.v, year: S.year, month: S.month };
				if (row === 'var') { q.v = x; } else if (row === 'year') { q.year = +x; } else { q.month = +x; }
				as[i].setAttribute('href', 'wxdayprofile.php?' + qs(q));
				if (row === 'year' && x !== '0') {
					as[i].classList.toggle('dp-off', (S.cfg.varYears[S.v] || []).indexOf(+x) < 0);
					as[i].classList.toggle('est', !!(S.cfg.estYears && S.cfg.estYears[S.v] && S.cfg.estYears[S.v][x]));
				}
			}
		});
	}
	function qs(q) {
		var a = ['var=' + encodeURIComponent(q.v)];
		if (q.year) { a.push('year=' + q.year); }
		if (q.month) { a.push('month=' + q.month); }
		return a.join('&');
	}

	function load(q) {
		var my = ++S.seq;
		if ((S.cfg.varYears[q.v] || []).indexOf(q.year) < 0) { q.year = 0; }
		var url = '/dayprofiledata.php?' + qs(q);
		document.getElementById('dp-chart').classList.add('wxchart-loading');
		fetch(url, { credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (p) {
			if (my !== S.seq || !p || p.error) { return; }
			if (q.v !== S.v) { S.bin = null; syncBin(p); }
			S.v = q.v; S.year = q.year; S.month = q.month; S.p = p;
			setActive('var', S.v); setActive('year', S.year); setActive('month', S.month);
			refreshLinks();
			if (window.history && history.replaceState) { history.replaceState(null, '', 'wxdayprofile.php?' + qs(q)); }
			document.getElementById('dp-chart').classList.remove('wxchart-loading');
			draw();
		}).catch(function () {
			window.location.href = 'wxdayprofile.php?' + qs(q);
		});
	}

	function syncBin(p) {
		var b = S.bin || p.defaultBin, bs = document.querySelectorAll('#dp-bins .wxsel-chip');
		for (var i = 0; i < bs.length; i++) { bs[i].classList.toggle('active', +bs[i].getAttribute('data-bin') === b); }
	}

	function init(cfg) {
		S.cfg = cfg; S.p = cfg.payload; S.v = cfg.payload.var;
		var key = cfg.payload.key;
		S.year = /^y\d{4}$/.test(key) ? +key.slice(1) : (/^\d{4}-\d{2}$/.test(key) ? +key.slice(0, 4) : 0);
		S.month = /^m\d{2}$/.test(key) ? +key.slice(1) : (/^\d{4}-\d{2}$/.test(key) ? +key.slice(5) : 0);
		syncBin(S.p);
		var ctl = document.getElementById('dp-controls');
		ctl.addEventListener('click', function (e) {
			var t = e.target.closest ? e.target : null;
			var q = { v: S.v, year: S.year, month: S.month };
			var gb = t ? t.closest('.wxsel-groups button') : null;
			if (gb) {
				var first = gb.getAttribute('data-default-type');
				var inGroup = document.querySelector('[data-dp="var"] a.wxsel-chip[data-v="' + S.v + '"][data-group="' + gb.getAttribute('data-group') + '"]');
				if (!inGroup) { q.v = first; if (window.fetch) { load(q); } else { window.location.href = 'wxdayprofile.php?' + qs(q); } }
				return;
			}
			var bb = t ? t.closest('#dp-bins .wxsel-chip') : null;
			if (bb) { S.bin = +bb.getAttribute('data-bin'); syncBin(S.p); draw(); return; }
			if (t && t.closest('#dp-cmp')) { S.cmp = !document.getElementById('dp-cmp').classList.contains('active'); draw(); return; }
			var a = t ? t.closest('a.wxsel-chip') : null;
			if (!a || !window.fetch || e.ctrlKey || e.metaKey || e.shiftKey) { return; }
			e.preventDefault();
			var row = a.closest('[data-dp]').getAttribute('data-dp'), x = a.getAttribute('data-v');
			if (row === 'var') { q.v = x; } else if (row === 'year') { q.year = +x; } else { q.month = +x; }
			load(q);
		});
		draw();
		var w = window.innerWidth;
		window.addEventListener('resize', function () {
			if ((window.innerWidth < 600) !== (w < 600)) { w = window.innerWidth; draw(); }
		});
	}

	window.NW3DayProfile = { init: init, rebin: rebin };
})(window, document);
