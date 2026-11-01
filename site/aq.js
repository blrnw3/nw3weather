/*
 * aq.js - shared UK DAQI / US AQI helpers for the v5 site.
 *
 *   NW3AQ.daqi(pm)            DAQI index 1-10 from PM2.5 (ug/m3)
 *   NW3AQ.usAqi(pm)           US EPA AQI from PM2.5
 *   NW3AQ.chartBands(unit, max) Highcharts yAxis plotBands for DAQI (in the chart's unit)
 *   home card popover         click/tap/Enter on .daqi-btn shows the DAQI scale
 *
 * Air-quality values elsewhere are rendered server-side in the visitor's chosen
 * units (sidebar 'Air quality': PM2.5 | AQI | DAQI), so there is no client toggle.
 *
 * The scale itself comes from window.NW3_AQ (Wx::aqScale(), emitted by Page.php),
 * so the thresholds are defined once, server-side.
 */
(function (window, document) {
	'use strict';
	var S = window.NW3_AQ;
	if (!S || !S.daqi) { return; }
	var IS_US = (S.unit === 'AQI');
	var MODE = S.mode || (IS_US ? 'aqi' : 'pm');
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
		var aqi = (unit === 'AQI'), isDaqi = (unit === 'DAQI');
		var conv = aqi ? usAqi : function (v) { return v; };
		if (isDaqi) {
			// Index axis: band n spans n-0.5 .. n+0.5.
			var topD = (dataMax == null || isNaN(dataMax)) ? 3 : dataMax + 1;
			var outD = [];
			for (var k = 0; k < S.daqi.length && k + 0.5 <= topD; k++) {
				var bd = S.daqi[k];
				outD.push({ from: k === 0 ? 0 : k + 0.5, to: k + 1.5, color: rgba(bd.colour, 0.16), zIndex: 0,
					label: (k === 0 || S.daqi[k - 1].name !== bd.name) ? { text: bd.name, align: 'right', verticalAlign: 'bottom', x: -4, y: -3,
						style: { color: '#777', fontSize: '9px' } } : undefined });
			}
			return { plotBands: outD, note: 'Shading: UK DAQI bands (DAQI is defined on the 24h mean)' };
		}
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

	window.NW3AQ = { daqi: daqi, usAqi: usAqi, band: band, chartBands: chartBands, isUs: IS_US, mode: MODE, groups: GROUPS };
})(window, document);
