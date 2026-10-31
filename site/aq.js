/*
 * aq.js - shared UK DAQI / US AQI helpers for the v5 site.
 *
 *   NW3AQ.daqi(pm)            DAQI index 1-10 from PM2.5 (ug/m3)
 *   NW3AQ.usAqi(pm)           US EPA AQI from PM2.5
 *   NW3AQ.chartBands(unit, max) Highcharts yAxis plotBands for DAQI (in the chart's unit)
 *   home card popover         click/tap/Enter on .daqi-btn shows the DAQI scale
 *   report toggle             any .aqv value (data-pm) gets a PM2.5 | AQI | DAQI switch
 *
 * The scale itself comes from window.NW3_AQ (Wx::aqScale(), emitted by Page.php),
 * so the thresholds are defined once, server-side.
 */
(function (window, document) {
	'use strict';
	var S = window.NW3_AQ;
	if (!S || !S.daqi) { return; }
	var IS_US = (S.unit === 'AQI');
	var GROUPS = { Low: 'Low', Moderate: 'Moderate', High: 'High', 'Very High': 'Very High' };
	// US EPA AQI category colours, for colouring cells in AQI mode.
	var AQI_CATS = [[50, '#00e400', 'Good'], [100, '#ffff00', 'Moderate'], [150, '#ff7e00', 'Unhealthy (sensitive)'],
		[200, '#ff0000', 'Unhealthy'], [300, '#8f3f97', 'Very unhealthy'], [Infinity, '#7e0023', 'Hazardous']];

	function num(v) { var n = parseFloat(v); return isNaN(n) ? null : n; }

	function daqi(pm) {
		pm = num(pm); if (pm === null) { return null; }
		for (var i = 0; i < S.daqiPm.length; i++) { if (pm <= S.daqiPm[i]) { return i + 1; } }
		return 10;
	}
	function usAqi(pm) {
		pm = num(pm); if (pm === null) { return null; }
		var c = Math.floor(pm * 10) / 10, bp = S.aqiBp;
		for (var i = 0; i < bp.length; i++) {
			var b = bp[i];
			if (c <= b[1]) { return Math.round((b[3] - b[2]) / (b[1] - b[0]) * (c - b[0]) + b[2]); }
		}
		return 500;
	}
	function band(n) { return S.daqi[n - 1]; }
	function textOn(hex) {
		var h = hex.replace('#', ''), r = parseInt(h.substr(0, 2), 16), g = parseInt(h.substr(2, 2), 16), b = parseInt(h.substr(4, 2), 16);
		return (0.299 * r + 0.587 * g + 0.114 * b) > 120 ? '#111' : '#fff';
	}
	function rgba(hex, a) {
		var h = hex.replace('#', '');
		return 'rgba(' + parseInt(h.substr(0, 2), 16) + ',' + parseInt(h.substr(2, 2), 16) + ',' + parseInt(h.substr(4, 2), 16) + ',' + a + ')';
	}
	function aqiCat(a) { for (var i = 0; i < AQI_CATS.length; i++) { if (a <= AQI_CATS[i][0]) { return AQI_CATS[i]; } } return AQI_CATS[5]; }
	function esc(s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }

	/**
	 * Faint DAQI background bands for a PM2.5 (ug/m3) or AQI axis. Only bands
	 * reaching just above the data max are drawn so the axis is not stretched.
	 * Returns { plotBands: [...], note: '...' }.
	 */
	function chartBands(unit, dataMax) {
		var aqi = (unit === 'AQI');
		var conv = aqi ? usAqi : function (v) { return v; };
		var top = (dataMax == null || isNaN(dataMax)) ? conv(S.daqiPm[2]) : dataMax * 1.15;
		var out = [], from = 0;
		for (var i = 0; i < S.daqi.length; i++) {
			var b = S.daqi[i];
			var to = (b.pmHi === null) ? 500 : conv(b.pmHi) + 0.5;
			if (from > top) { break; }
			var groupStart = (i === 0 || S.daqi[i - 1].name !== b.name);
			out.push({
				from: from, to: to, color: rgba(b.colour, 0.16), zIndex: 0,
				label: groupStart ? { text: b.name, align: 'right', verticalAlign: 'bottom', x: -4, y: -3,
					style: { color: '#777', fontSize: '9px' } } : undefined
			});
			from = to;
		}
		return { plotBands: out, note: 'Shading: UK DAQI bands (PM2.5 thresholds are defined on the 24h mean)' };
	}

	// ---- Home card popover ----
	var pop = null, openBtnId = 'var6';
	function currentBtn() { var b = document.getElementById(openBtnId); return (b && b.classList.contains('daqi-btn')) ? b : null; }
	function buildPop() {
		pop = document.createElement('div');
		pop.id = 'daqi-pop';
		pop.className = 'daqi-pop';
		pop.setAttribute('role', 'dialog');
		pop.setAttribute('aria-label', 'UK DAQI scale');
		pop.hidden = true;
		var rows = S.daqi.map(function (b) {
			var pmTxt = (b.pmHi === null) ? b.pmLo + '+' : b.pmLo + '\u2013' + b.pmHi;
			var aqTxt = (b.aqiHi === null) ? b.aqiLo + '+' : b.aqiLo + '\u2013' + b.aqiHi;
			return '<tr data-band="' + b.band + '"><td><span class="daqi-sw" style="background:' + b.colour + '"></span>' + b.band
				+ '</td><td>' + esc(b.name) + '</td><td>' + pmTxt + '</td><td>' + aqTxt + '</td></tr>';
		}).join('');
		pop.innerHTML = '<div class="daqi-pop-head"><b>UK Daily Air Quality Index</b>'
			+ '<button type="button" class="daqi-pop-close" aria-label="Close">\u00D7</button></div>'
			+ '<table><thead><tr><th>DAQI</th><th>Band</th><th>PM2.5 \u00B5g/m\u00B3</th><th>US AQI</th></tr></thead><tbody>'
			+ rows + '</tbody></table>'
			+ '<p class="daqi-pop-note">PM2.5 thresholds are for the 24h mean; the card bands the latest reading as a live guide. '
			+ 'US AQI is the EPA (2024) value for the same PM2.5.</p>';
		document.body.appendChild(pop);
		pop.querySelector('.daqi-pop-close').addEventListener('click', function () { closePop(true); });
	}
	function highlight() {
		var btn = currentBtn(), cur = btn ? parseInt(btn.getAttribute('data-band'), 10) : 0;
		Array.prototype.forEach.call(pop.querySelectorAll('tbody tr'), function (tr) {
			var on = (parseInt(tr.getAttribute('data-band'), 10) === cur);
			tr.classList.toggle('daqi-cur', on);
			if (on) { tr.setAttribute('aria-current', 'true'); } else { tr.removeAttribute('aria-current'); }
		});
	}
	function place() {
		var btn = currentBtn();
		if (!btn || !pop || pop.hidden) { return; }
		if (window.innerWidth <= 600) { pop.classList.add('daqi-pop-sheet'); pop.style.left = pop.style.top = ''; return; }
		pop.classList.remove('daqi-pop-sheet');
		var r = btn.getBoundingClientRect(), w = pop.offsetWidth;
		var left = Math.max(8, Math.min(r.left + window.pageXOffset, window.pageXOffset + document.documentElement.clientWidth - w - 8));
		pop.style.left = left + 'px';
		pop.style.top = (r.bottom + window.pageYOffset + 6) + 'px';
	}
	function openPop() {
		if (!pop) { buildPop(); }
		pop.hidden = false;
		highlight(); place();
		var btn = currentBtn(); if (btn) { btn.setAttribute('aria-expanded', 'true'); }
		pop.querySelector('.daqi-pop-close').focus({ preventScroll: true });
	}
	function closePop(refocus) {
		if (!pop || pop.hidden) { return; }
		pop.hidden = true;
		var btn = currentBtn();
		if (btn) { btn.setAttribute('aria-expanded', 'false'); if (refocus) { btn.focus({ preventScroll: true }); } }
	}
	// Delegated handlers, installed once: the card markup is replaced every live refresh.
	document.addEventListener('click', function (e) {
		var btn = e.target.closest ? e.target.closest('.daqi-btn') : null;
		if (btn) { e.preventDefault(); if (pop && !pop.hidden) { closePop(false); } else { openPop(); } return; }
		if (pop && !pop.hidden && !pop.contains(e.target)) { closePop(false); }
	});
	document.addEventListener('keydown', function (e) {
		if ((e.key === 'Escape' || e.key === 'Esc') && pop && !pop.hidden) { closePop(true); }
	});
	window.addEventListener('resize', place);
	// After a live refresh swaps the card in, keep an open popover open and in sync.
	if (window.MutationObserver) {
		new MutationObserver(function () {
			if (!pop || pop.hidden) { return; }
			var btn = currentBtn();
			if (!btn) { return; }
			btn.setAttribute('aria-expanded', 'true');
			highlight(); place();
		}).observe(document.documentElement, { childList: true, subtree: true });
	}

	// ---- Report toggle: PM2.5 | AQI | DAQI ----
	var MODE_KEY = 'nw3.aqMode';
	var LEVEL_RE = /\blevelaqi_\d+\b/g;
	function getMode() {
		var m = null; try { m = window.localStorage.getItem(MODE_KEY); } catch (e) {}
		return (m === 'pm' || m === 'aqi' || m === 'daqi') ? m : 'native';
	}
	function setMode(m) { try { window.localStorage.setItem(MODE_KEY, m); } catch (e) {} }
	var AVG_NOTE = { max: 'from the max reading', min: 'from the min reading', mean: 'from the mean' };

	function applyOne(el, mode) {
		var pm = num(el.getAttribute('data-pm'));
		if (pm === null) { return; }
		if (el.getAttribute('data-orig') === null) { el.setAttribute('data-orig', el.innerHTML); }
		var cell = el.closest('.aqc');
		if (cell && cell.getAttribute('data-origc') === null) { cell.setAttribute('data-origc', cell.className); }
		var avg = el.getAttribute('data-avg') || 'mean';
		var eff = mode;
		if (mode === 'native') { eff = IS_US ? 'aqi' : 'pm'; }
		var txt, col = null, tip;
		if (mode === 'native') {
			el.innerHTML = el.getAttribute('data-orig');
			if (cell) { cell.className = cell.getAttribute('data-origc'); cell.style.backgroundColor = cell.style.color = ''; }
			el.removeAttribute('title');
			return;
		}
		if (eff === 'pm') {
			txt = pm.toFixed(1); tip = 'PM2.5 ' + txt + ' \u00B5g/m\u00B3';
			var bp = band(daqi(pm)); col = bp.colour;
		} else if (eff === 'aqi') {
			var a = usAqi(pm); var cat = aqiCat(a);
			txt = String(a); col = cat[1]; tip = 'US AQI ' + a + ' (' + cat[2] + '), ' + AVG_NOTE[avg] + ' PM2.5 ' + pm.toFixed(1);
		} else {
			var d = daqi(pm); var b = band(d);
			txt = String(d); col = b.colour; tip = 'DAQI ' + d + ' (' + b.name + '), ' + AVG_NOTE[avg] + ' PM2.5 ' + pm.toFixed(1)
				+ (avg === 'mean' ? '' : ' (DAQI is defined on the 24h mean)');
		}
		if (el.getAttribute('data-u')) {
			txt = (eff === 'pm') ? txt + ' \u00B5g/m\u00B3' : (eff === 'aqi' ? 'AQI ' + txt : 'DAQI ' + txt);
		}
		if (avg !== 'mean' && eff !== 'pm') { txt += '<sup class="aq-ind">*</sup>'; }
		el.innerHTML = txt;
		el.title = tip;
		if (cell) {
			cell.className = cell.getAttribute('data-origc').replace(LEVEL_RE, '').trim() + ' aq-coloured';
			cell.style.backgroundColor = col; cell.style.color = textOn(col);
		}
	}
	function applyAll(mode) {
		Array.prototype.forEach.call(document.querySelectorAll('.aqv'), function (el) { applyOne(el, mode); });
		Array.prototype.forEach.call(document.querySelectorAll('.aq-toggle button'), function (b) {
			var on = (b.getAttribute('data-mode') === (mode === 'native' ? (IS_US ? 'aqi' : 'pm') : mode));
			b.classList.toggle('active', on); b.setAttribute('aria-pressed', on ? 'true' : 'false');
		});
	}
	function toggleHtml() {
		return '<span class="aq-toggle-lab">Air pollution as</span>'
			+ '<span class="aq-toggle-btns" role="group" aria-label="Air pollution units">'
			+ '<button type="button" data-mode="pm">PM2.5</button><button type="button" data-mode="aqi">AQI</button>'
			+ '<button type="button" data-mode="daqi">DAQI</button></span>'
			+ '<span class="aq-toggle-note" title="AQI and DAQI are defined on 24h means. Values marked * are converted from a min or max reading, so are indicative only.">'
			+ '* from min/max: indicative</span>';
	}
	function initToggles() {
		var vals = document.querySelectorAll('.aqv');
		if (!vals.length) { return; }
		Array.prototype.forEach.call(vals, function (el) {
			if (el.getAttribute('data-aqinit')) { return; }
			el.setAttribute('data-aqinit', '1');
			var host = el.closest('.aq-host') || el.closest('table, .dm-grid, .dd-grid, .report-table-scroll, .dm-scroll');
			if (!host) { return; }
			var outer = host.closest('.report-table-scroll, .dm-scroll') || host;
			var prev = outer.previousElementSibling;
			if (prev && prev.classList.contains('aq-toggle')) { return; }
			var t = document.createElement('div');
			t.className = 'aq-toggle';
			t.innerHTML = toggleHtml();
			outer.parentNode.insertBefore(t, outer);
		});
		applyAll(getMode());
	}
	document.addEventListener('click', function (e) {
		var b = e.target.closest ? e.target.closest('.aq-toggle button') : null;
		if (!b) { return; }
		var m = b.getAttribute('data-mode');
		setMode(m); applyAll(m);
	});
	var pending = false;
	function schedule() {
		if (pending) { return; }
		pending = true;
		setTimeout(function () { pending = false; initToggles(); }, 50);
	}
	if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', initToggles); } else { initToggles(); }
	if (window.MutationObserver) {
		// Data tables swap in over AJAX; pick up new values.
		new MutationObserver(function (muts) {
			for (var i = 0; i < muts.length; i++) {
				for (var j = 0; j < muts[i].addedNodes.length; j++) {
					var n = muts[i].addedNodes[j];
					if (n.nodeType === 1 && (n.classList.contains('aqv') || n.querySelector('.aqv'))) { schedule(); return; }
				}
			}
		}).observe(document.documentElement, { childList: true, subtree: true });
	}

	window.NW3AQ = { daqi: daqi, usAqi: usAqi, band: band, chartBands: chartBands, isUs: IS_US, groups: GROUPS };
})(window, document);
