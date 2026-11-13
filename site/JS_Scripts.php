<script type="text/javascript">
	//<![CDATA[
	var timePHP = <?php echo time(); ?>;
	var dateJS = new Date(timePHP*1000);
	var cntJS = 0;
	function updateTime() {
		dateJS = new Date(timePHP*1000);
		timePHP++;
		setTimeout("updateTime()", 1000);
	}
	updateTime();
	//]]>
</script>

<script type="text/javascript">
	//<![CDATA[
	/**
	 * Changes tab based on dynamic input
	 * @param n sets the summary_type tab to show
	 * @returns {undefined}
	 * @author &copy; Ben Masschelein-Rodgers, nw3weather, Feb 2024
	 */
	function changeTab(n) {
		$(".rank-tab-button").prop("disabled", false);
		$("#rank-btn-" + n).prop("disabled", "disabled");
		$(".rank-tab").hide();
		$("#rank-" + n).show();
		$("#summary-type-input").val(n);
		$(".arrow").each(function() {
			$(this).attr("href", $(this).attr("href").replace(/(summary_type=)\d+/, 'summary_type=' + n));
		});
		history.pushState(null, null, window.location.href.replace(/(summary_type=)\d+/, 'summary_type=' + n));
	}
	//]]>
</script>

<?php
$camImgNew = '/skycam';
$camImgLarge = '/skycam.jpg';
$camImgNew .= (Page::$fileNum === 1) ? '_home.jpg' : '_wx2.jpg';
?>
<script type="text/javascript">
	//<![CDATA[
	var imageNew = "<?php echo $camImgNew; ?>";
	var imageLarge = "<?php echo $camImgLarge; ?>";

	function camFreshify(name, img) {
		if(document.images[name]) {
			document.images[name].src = img+"?"+timePHP;
		}
	}
	function refreshAll() {
		if(!document.hidden) {
			camFreshify("refresh-new", imageNew);
			camFreshify("refresh-home", imageNew);
			camFreshify("refresh-lg", imageLarge);
		}
	}
	function camRefresher() {
		refreshAll();
		setTimeout("camRefresher()", 10000);
	}

	document.addEventListener("visibilitychange", refreshAll, false);
	camRefresher();
	//]]>
</script>

<script type='text/javascript'>
	//<![CDATA[
	function shownewhead() {
		var curr = new Date(); var currms = curr.getTime();
		var currsec = Math.round(currms / 1000); var data = '';
		if(currsec % 16 < 4) { data = "<?php echo 'Temperature: ',Wx::conv(Live::$temp,Wx::Temperature,1); ?>"; }
		else if(currsec % 16 < 8) { data = "<?php echo 'Wind Speed: ', Wx::conv(Live::$wind,Wx::Wind,1); ?>"; }
		else if(currsec % 16 < 12) { data = "<?php echo 'Daily Rain: ', Wx::conv(Live::$rain,Wx::Rain,1); ?>"; }
		else { data = "<?php echo 'Pressure: ', Wx::conv(Live::$pres,Wx::Pressure,1); ?>"; }
		var el = document.getElementById('live-wx');
		if(!el) { return; } // page without the header ticker
		el.innerHTML = data;
		setTimeout('shownewhead()',2000);
	}
	// This script is printed in <head>, before #live-wx exists, so start the ticker once the DOM is parsed
	if(document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', shownewhead, false);
	} else {
		shownewhead();
	}
	//]]>
</script>

<script type="text/javascript">
	//<![CDATA[
	function loadVid(vid, seek, sel, noautoplay) {
		$("#skycam-selector span").removeClass("selected");
		$("#timelapse-" + sel).addClass("selected");

		var src = '/cam/timelapse/' + vid + '.mp4';
		console.log("Loading " + src);
		var vidBox = document.getElementById('timelapse');
		vidBox.innerHTML = '<video id="timelapse-vid" width="864" height="576" controls><source src="' + src + '" type="video/mp4"></video>';

		var vid = document.getElementById('timelapse-vid');
		vid.currentTime = seek;
		if(!noautoplay) {
			vid.play();
		}
	}
	//]]>
</script>

<script type="text/javascript">
	//<![CDATA[
	/**
	 * Button selectors for data/ranking tables (group → measure, chips, AJAX body).
	 * cfg.mode: daily | monthly | rank-daily | rank-monthly | rank-annual | rank-spells | rank-periods
	 */
	function NW3_reportSel(cfg) {
		var groups = cfg.groups || [];
		var mode = cfg.mode || 'daily';
		var isRank = (mode === 'rank-daily' || mode === 'rank-monthly' || mode === 'rank-annual' || mode === 'rank-spells' || mode === 'rank-periods');
		var curType = cfg.type;
		var curYear = parseInt(cfg.year, 10) || 0;
		var curAgg = cfg.agg || '';
		var curShowYears = cfg.showYears !== 0 && cfg.showYears !== false && cfg.showYears !== '0';
		var curMonth = parseInt(cfg.month, 10);
		if (isNaN(curMonth)) { curMonth = 0; }
		var curDay = parseInt(cfg.day, 10);
		if (isNaN(curDay) || curDay < 0) { curDay = 0; }
		var currentDay = parseInt(cfg.currentDay, 10) || 0;
		var curWin = parseInt(cfg.win, 10);
		if (isNaN(curWin) || curWin < 0) { curWin = 0; }
		function coerceWin(win, day) {
			if (!(day > 0)) { return 0; }
			win = parseInt(win, 10);
			if (win === 3 || win === 5 || win === 10) { return win; }
			return 0;
		}
		curWin = coerceWin(curWin, curDay);
		function daysInMonth(month) {
			return [0, 31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][month] || 31;
		}
		function fillDaySelect(sel, month, day) {
			if (!sel) { return 1; }
			var max = daysInMonth(month);
			var cur = parseInt(day, 10);
			if (isNaN(cur) || cur < 1) { cur = 1; }
			if (cur > max) { cur = max; }
			var html = '', d;
			for (d = 1; d <= max; d++) {
				html += '<option value="' + d + '"' + (d === cur ? ' selected' : '') + '>' + d + '</option>';
			}
			sel.innerHTML = html;
			sel.value = String(cur);
			return cur;
		}
		function syncDateSel() {
			var row = document.getElementById('report-sel-datesel');
			if (!row || mode !== 'rank-daily') { return; }
			var on = curDay > 0;
			if (on) { row.removeAttribute('hidden'); }
			else { row.setAttribute('hidden', ''); }
			var monthSel = document.getElementById('wxsel-date-month');
			var daySel = document.getElementById('wxsel-date-day');
			var m = on ? curMonth : currentMonth;
			var d = on ? curDay : currentDay;
			if (monthSel) { monthSel.value = String(m); }
			if (daySel) { fillDaySelect(daySel, m, d); }
			var form = document.getElementById('wxsel-date-form');
			if (form) {
				var vt = form.querySelector('input[name="vartype"]');
				var sy = form.querySelector('input[name="start_year_rep"]');
				var rl = form.querySelector('input[name="rankLimit"]');
				if (vt) { vt.value = curType; }
				if (sy) { sy.value = String(curStart); }
				if (rl) { rl.value = String(curRankLimit); }
				var winInp = form.querySelector('input[name="win"]');
				if (winInp) { winInp.value = String(on ? curWin : 0); }
			}
			syncWinChips();
		}
		function syncWinChips() {
			var wrap = document.getElementById('wxsel-win');
			if (!wrap) { return; }
			wrap.querySelectorAll('a.wxsel-chip[data-win]').forEach(function (a) {
				var w = parseInt(a.getAttribute('data-win'), 10) || 0;
				a.classList.toggle('active', curDay > 0 && w === curWin);
				var dm = curDay > 0 ? curMonth : currentMonth;
				var dd = curDay > 0 ? curDay : currentDay;
				a.setAttribute('href', pageUrl({ month: dm, day: dd, win: w }));
			});
		}
		var curStart = parseInt(cfg.startYearRep, 10) || 0;
		var curSummary = parseInt(cfg.summaryType, 10);
		if (isNaN(curSummary)) { curSummary = 0; }
		var curRankLimit = parseInt(cfg.rankLimit, 10) || 25;
		var curSpellDir = cfg.spellDir || 'above';
		var curThreshold = cfg.threshold != null ? parseFloat(cfg.threshold) : 0;
		var curThresholds = cfg.thresholds || [];
		var curThresholdLabels = cfg.thresholdLabels || [];
		var curPeriod = parseInt(cfg.periodLength, 10) || 5;
		var periodOpts = (cfg.periodLengthOptions || [3,5,7,14,30,90,365]).map(function (x) { return parseInt(x, 10); });
		var curNoOverlap = !!parseInt(cfg.periodNoOverlap == null ? 0 : cfg.periodNoOverlap, 10);
		var SPAN_KNOWN = {full:1, mtd:1, ytd:1, winter:1, spring:1, summer:1, autumn:1, seasonal:1, h1:1, h2:1};
		var currentMonth = parseInt(cfg.currentMonth, 10) || 0;
		function spanAllowed(span, month) {
			month = parseInt(month, 10);
			if (!SPAN_KNOWN[span]) { return false; }
			if (mode === 'rank-annual') {
				return span === 'full' || span === 'ytd' || span === 'winter'
					|| span === 'spring' || span === 'summer' || span === 'autumn';
			}
			if (mode === 'rank-monthly') {
				if (span === 'mtd') { return month === currentMonth; }
				return span === 'full' || span === 'h1' || span === 'h2';
			}
			if (mode === 'monthly') {
				if (span === 'winter' || span === 'spring' || span === 'summer' || span === 'autumn') {
					return false; // coerced to seasonal below
				}
				if (span === 'mtd') {
					return false; // coerced to ytd below
				}
				return span === 'full' || span === 'ytd' || span === 'seasonal';
			}
			return span === 'full';
		}
		function coerceSpan(span, month) {
			if (mode === 'monthly' && (span === 'winter' || span === 'spring' || span === 'summer' || span === 'autumn')) {
				return 'seasonal';
			}
			if (mode === 'monthly' && span === 'mtd') {
				return 'ytd';
			}
			return spanAllowed(span, month) ? span : 'full';
		}
		var curSpan = coerceSpan(cfg.span, cfg.month);
		var hasSpan = (mode === 'monthly' || mode === 'rank-monthly' || mode === 'rank-annual');
		var spellDirLabels = cfg.spellDirLabels || {};
		var startYearOpts = (cfg.startYearOptions || []).map(function (x) { return parseInt(x, 10); });
		var summaryTypes = cfg.summaryTypes || [];
		var summaryLabels = cfg.summaryLabels || {};
		var page = cfg.page;
		var fragment = cfg.fragment;
		var bodyId = cfg.bodyId || 'dd-ajax';
		var headingPrefix = cfg.headingPrefix || 'Data Tables';
		var fragId = cfg.fragId || 'dd-fragment';
		var root = document.getElementById('report-sel');
		if (!root) { return; }
		var sub = root.querySelector('.wxsel-subtypes');
		var yearsEl = root.querySelector('.wxsel-years');
		var monthsEl = root.querySelector('.wxsel-months');
		var startEl = root.querySelector('.wxsel-start-years');
		var summaryEl = root.querySelector('.wxsel-summary');
		var limitEl = root.querySelector('.wxsel-rank-limit');
		var spellDirEl = root.querySelector('.wxsel-spell-dir');
		var thresholdEl = root.querySelector('.wxsel-threshold');
		var periodEl = root.querySelector('.wxsel-periods');
		var overlapEl = root.querySelector('.wxsel-period-overlap');
		var spanEl = root.querySelector('.wxsel-span');
		var headingEl = document.getElementById('report-sel-heading');
		var reqSeq = 0;
		function isThresholdSummary(st) {
			st = parseInt(st, 10);
			return st === 6 || st === 7 || st === 8; // 8 = anomaly (± normal)
		}
		function syncThresholdVisibility() {
			var row = document.getElementById('report-sel-threshold');
			if (!row) { return; }
			var show = (mode === 'rank-spells') || isThresholdSummary(curSummary);
			if (show) { row.removeAttribute('hidden'); }
			else { row.setAttribute('hidden', ''); }
		}

		function bodyEl() { return document.getElementById(bodyId); }
		function pageUrl(overrides) {
			var p = { vartype: curType };
			var agg = curAgg;
			var year = curYear;
			if (overrides) {
				if (Object.prototype.hasOwnProperty.call(overrides, 'agg')) { agg = overrides.agg || ''; }
				if (Object.prototype.hasOwnProperty.call(overrides, 'year')) {
					year = overrides.year;
					agg = '';
				}
			}
			if (mode === 'daily') {
				if (agg) {
					p.agg = agg;
					p.start_year_rep = curStart;
				} else {
					p.year = year;
				}
				if (!curShowYears) { p.years = 0; }
			} else if (mode === 'monthly') {
				p.start_year_rep = curStart;
				p.summary_type = curSummary;
				p.span = curSpan;
				if (isThresholdSummary(curSummary)) { p.threshold = curThreshold; }
			} else if (isRank) {
				p.start_year_rep = curStart;
				if (mode !== 'rank-annual') { p.rankLimit = curRankLimit; }
				if (mode === 'rank-daily' || mode === 'rank-monthly' || mode === 'rank-spells' || mode === 'rank-periods') { p.month = curMonth; }
				if (mode === 'rank-daily' && curDay > 0) {
					p.day = curDay;
					if (curWin > 0) { p.win = curWin; }
				}
				if (mode === 'rank-monthly' || mode === 'rank-annual' || mode === 'rank-periods') {
					p.summary_type = curSummary;
					if (isThresholdSummary(curSummary)) { p.threshold = curThreshold; }
				}
				if (mode === 'rank-periods') {
					p.period = curPeriod;
					p.no_overlap = curNoOverlap ? 1 : 0;
				}
				if (mode === 'rank-monthly' || mode === 'rank-annual') {
					p.span = curSpan;
				}
				if (mode === 'rank-spells') {
					p.spell_dir = curSpellDir;
					p.threshold = curThreshold;
				}
			}
			if (overrides) {
				var k;
				for (k in overrides) {
					if (!Object.prototype.hasOwnProperty.call(overrides, k)) { continue; }
					if (mode === 'daily' && (k === 'year' || k === 'agg')) { continue; }
					p[k] = overrides[k];
				}
			}
			if (!p.day || parseInt(p.day, 10) <= 0) { delete p.day; delete p.win; }
			if (!p.win || parseInt(p.win, 10) <= 0) { delete p.win; }
			var qs = [], key;
			for (key in p) {
				if (Object.prototype.hasOwnProperty.call(p, key)) {
					qs.push(encodeURIComponent(key) + '=' + encodeURIComponent(p[key]));
				}
			}
			return page + '?' + qs.join('&');
		}
		function fragmentUrl(overrides) {
			var base = fragment + (fragment.indexOf('?') >= 0 ? '&' : '?');
			var p = pageUrl(overrides);
			return base + p.substring(p.indexOf('?') + 1);
		}
		function groupById(gid) {
			var i;
			for (i = 0; i < groups.length; i++) {
				if (groups[i].id === gid) { return groups[i]; }
			}
			return null;
		}
		function renderMeasures(gid) {
			var g = groupById(gid);
			if (!g || !sub) { return; }
			var html = '', type, label, opts = g.options;
			for (type in opts) {
				if (!Object.prototype.hasOwnProperty.call(opts, type)) { continue; }
				label = opts[type];
				html += '<a class="wxsel-chip' + (type === curType ? ' active' : '')
					+ '" data-vartype="' + type + '" href="' + pageUrl({ vartype: type }) + '">'
					+ label + '</a>';
			}
			sub.innerHTML = html;
		}
		function syncYearChips() {
			if (!yearsEl) { return; }
			var chips = yearsEl.querySelectorAll('a.wxsel-chip[data-year]');
			var inRecent = false;
			var yearMode = !curAgg;
			chips.forEach(function (a) {
				var y = parseInt(a.getAttribute('data-year'), 10);
				var on = yearMode && y === curYear;
				a.classList.toggle('active', on);
				a.setAttribute('href', pageUrl({ year: y }));
				if (on && !a.closest('.wxsel-overflow-menu')) { inRecent = true; }
			});
			var details = yearsEl.querySelector('.wxsel-overflow:not(.wxsel-day-agg)');
			if (details) {
				var sum = details.querySelector('summary');
				if (sum) {
					sum.classList.toggle('active', yearMode && !inRecent);
					sum.textContent = (yearMode && !inRecent) ? String(curYear) : 'Older';
				}
				details.open = false;
			}
			syncAggChips();
		}
		function syncAggChips() {
			if (!yearsEl) { return; }
			var labels = { min: 'Min', max: 'Max', mean: 'Mean' };
			yearsEl.querySelectorAll('a.wxsel-chip[data-agg]').forEach(function (a) {
				var agg = a.getAttribute('data-agg');
				a.classList.toggle('active', agg === curAgg);
				a.setAttribute('href', pageUrl({ agg: agg }));
			});
			var details = yearsEl.querySelector('.wxsel-day-agg');
			if (details) {
				var sum = details.querySelector('summary');
				if (sum) {
					sum.classList.toggle('active', !!curAgg);
					sum.textContent = curAgg && labels[curAgg] ? labels[curAgg] : 'Avg/Extreme';
				}
				details.open = false;
			}
			syncStartYearVisibility();
		}
		function syncStartYearVisibility() {
			var row = document.getElementById('report-sel-start-year');
			if (row && mode === 'daily') {
				if (curAgg) { row.removeAttribute('hidden'); }
				else { row.setAttribute('hidden', ''); }
			}
			syncYearsToggle();
		}
		function applyYearsClass() {
			var grid = document.querySelector('#dd-ajax .dd-grid');
			if (!grid) { return; }
			var record = (curAgg === 'min' || curAgg === 'max');
			grid.classList.toggle('dd-has-years', record);
			grid.classList.toggle('dd-years-off', record && !curShowYears);
		}
		function syncYearsToggle() {
			if (mode !== 'daily') { return; }
			var row = document.getElementById('report-sel-show-years');
			if (row) {
				if (curAgg === 'min' || curAgg === 'max') { row.removeAttribute('hidden'); }
				else { row.setAttribute('hidden', ''); }
			}
			var box = document.getElementById('dd-show-years');
			if (box) { box.checked = !!curShowYears; }
			applyYearsClass();
		}
		function syncMonthChips() {
			if (!monthsEl) { return; }
			var dateSel = (mode === 'rank-daily' && curDay > 0);
			monthsEl.querySelectorAll('a.wxsel-chip[data-month]').forEach(function (a) {
				var m = parseInt(a.getAttribute('data-month'), 10);
				var isDateChip = a.getAttribute('data-datesel');
				if (isDateChip) {
					a.classList.toggle('active', dateSel);
					var dm = dateSel ? curMonth : currentMonth;
					var dd = dateSel ? curDay : currentDay;
					a.setAttribute('data-month', String(dm));
					a.setAttribute('data-day', String(dd));
					a.setAttribute('href', pageUrl({ month: dm, day: dd }));
				} else {
					a.classList.toggle('active', !dateSel && m === curMonth);
					a.setAttribute('href', pageUrl({ month: m, day: 0 }));
				}
			});
			syncDateSel();
		}
		function nearestStartYear(want, opts) {
			if (!opts || !opts.length) { return want; }
			want = parseInt(want, 10);
			var best = null, i, y;
			for (i = 0; i < opts.length; i++) {
				y = parseInt(opts[i], 10);
				if (y === want) { return y; }
				if (y <= want) { best = y; }
			}
			return best != null ? best : parseInt(opts[0], 10);
		}
		function syncStartYearChips() {
			if (!startEl) { return; }
			// Options depend on the variable's first year of data, so re-render them.
			var html = '', i, y;
			curStart = nearestStartYear(curStart, startYearOpts);
			for (i = 0; i < startYearOpts.length; i++) {
				y = startYearOpts[i];
				html += '<a class="wxsel-chip' + (y === curStart ? ' active' : '')
					+ '" data-start-year="' + y + '" href="' + pageUrl({ start_year_rep: y })
					+ '" title="Show data from this year">' + y + '</a>';
			}
			startEl.innerHTML = html;
		}
		function syncSummaryChips() {
			if (!summaryEl) { return; }
			var html = '', i, st, label;
			for (i = 0; i < summaryTypes.length; i++) {
				st = summaryTypes[i];
				label = summaryLabels[st] || ('Type ' + st);
				html += '<a class="wxsel-chip' + (st === curSummary ? ' active' : '')
					+ '" data-summary="' + st + '" href="' + pageUrl({ summary_type: st }) + '">'
					+ label + '</a>';
			}
			summaryEl.innerHTML = html;
		}
		function syncRankLimitChips() {
			if (!limitEl) { return; }
			limitEl.querySelectorAll('a.wxsel-chip[data-rank-limit]').forEach(function (a) {
				var n = parseInt(a.getAttribute('data-rank-limit'), 10);
				a.classList.toggle('active', n === curRankLimit);
				a.setAttribute('href', pageUrl({ rankLimit: n }));
			});
		}
		function syncPeriodChips() {
			if (!periodEl) { return; }
			var html = '', i, plen;
			for (i = 0; i < periodOpts.length; i++) {
				plen = periodOpts[i];
				html += '<a class="wxsel-chip' + (plen === curPeriod ? ' active' : '')
					+ '" data-period="' + plen + '" href="' + pageUrl({ period: plen }) + '">'
					+ plen + 'd</a>';
			}
			periodEl.innerHTML = html;
		}
		function syncOverlapChips() {
			if (!overlapEl) { return; }
			overlapEl.querySelectorAll('a.wxsel-chip[data-no-overlap]').forEach(function (a) {
				var v = a.getAttribute('data-no-overlap') === '1';
				a.classList.toggle('active', v === curNoOverlap);
				a.setAttribute('href', pageUrl({ no_overlap: v ? 1 : 0 }));
			});
		}
		function syncSpanChips() {
			if (!spanEl) { return; }
			spanEl.querySelectorAll('a.wxsel-chip[data-span]').forEach(function (a) {
				var v = a.getAttribute('data-span');
				a.classList.toggle('active', v === curSpan);
				if (v === 'mtd' && mode === 'rank-monthly') {
					a.removeAttribute('hidden');
					a.setAttribute('data-month', String(currentMonth));
					a.setAttribute('href', pageUrl({ span: v, month: currentMonth }));
				} else {
					a.setAttribute('href', pageUrl({ span: v }));
				}
			});
		}
		function syncSpellDirChips() {
			if (!spellDirEl) { return; }
			// Chip set depends on the variable (wet/dry, above/below, heatwave/cold snap).
			var html = '', d;
			if (!spellDirLabels[curSpellDir]) { curSpellDir = 'above'; }
			for (d in spellDirLabels) {
				if (!Object.prototype.hasOwnProperty.call(spellDirLabels, d)) { continue; }
				html += '<a class="wxsel-chip' + (d === curSpellDir ? ' active' : '')
					+ '" data-spell-dir="' + d + '" href="' + pageUrl({ spell_dir: d }) + '">'
					+ spellDirLabels[d] + '</a>';
			}
			spellDirEl.innerHTML = html;
		}
		function syncThresholdChips() {
			if (!thresholdEl) { return; }
			var html = '', i, th, label, active;
			for (i = 0; i < curThresholds.length; i++) {
				th = curThresholds[i];
				label = curThresholdLabels[i] != null ? curThresholdLabels[i] : th;
				active = Math.abs(parseFloat(th) - curThreshold) < 1e-9;
				html += '<a class="wxsel-chip' + (active ? ' active' : '')
					+ '" data-threshold="' + th + '" href="' + pageUrl({ threshold: th }) + '">'
					+ label + '</a>';
			}
			thresholdEl.innerHTML = html;
			syncThresholdVisibility();
		}
		function syncGroupActive() {
			var gid = null, i;
			for (i = 0; i < groups.length; i++) {
				if (groups[i].options && groups[i].options[curType]) { gid = groups[i].id; break; }
			}
			root.querySelectorAll('.wxsel-groups button').forEach(function (b) {
				b.classList.toggle('active', b.getAttribute('data-group') === gid);
			});
			if (gid) { renderMeasures(gid); }
		}
		function setYearWarn(text) {
			var warn = document.getElementById('report-year-warn');
			if (!warn) { return; }
			if (text) {
				warn.textContent = text;
				warn.hidden = false;
			} else {
				warn.textContent = '';
				warn.hidden = true;
			}
		}
		function showSummaryTab(st) {
			curSummary = parseInt(st, 10);
			document.querySelectorAll('.rank-tab').forEach(function (el) {
				el.style.display = 'none';
			});
			var tab = document.getElementById('rank-' + curSummary);
			if (tab) { tab.style.display = ''; }
			if (summaryEl) {
				summaryEl.querySelectorAll('a.wxsel-chip[data-summary]').forEach(function (a) {
					a.classList.toggle('active', parseInt(a.getAttribute('data-summary'), 10) === curSummary);
				});
			}
			root.setAttribute('data-summary-type', String(curSummary));
		}
		function syncNavVartype() {
			if (!curType) { return; }
			document.querySelectorAll('#nav a[data-keep-vartype]').forEach(function (a) {
				var href = a.getAttribute('href') || '';
				var base = href.split('?')[0];
				a.setAttribute('href', base + '?vartype=' + encodeURIComponent(curType));
			});
		}
		function stateObj() {
			if (mode === 'monthly') {
				var o = { vartype: curType, start_year_rep: curStart, summary_type: curSummary, span: curSpan };
				if (isThresholdSummary(curSummary)) { o.threshold = curThreshold; }
				return o;
			}
			if (mode === 'rank-daily') {
				var od = { vartype: curType, month: curMonth, start_year_rep: curStart, rankLimit: curRankLimit };
				if (curDay > 0) { od.day = curDay; }
				if (curDay > 0 && curWin > 0) { od.win = curWin; }
				return od;
			}
			if (mode === 'rank-monthly') {
				var om = {
					vartype: curType, month: curMonth, start_year_rep: curStart,
					summary_type: curSummary, rankLimit: curRankLimit, span: curSpan
				};
				if (isThresholdSummary(curSummary)) { om.threshold = curThreshold; }
				return om;
			}
			if (mode === 'rank-annual') {
				var oa = { vartype: curType, start_year_rep: curStart, summary_type: curSummary, span: curSpan };
				if (isThresholdSummary(curSummary)) { oa.threshold = curThreshold; }
				return oa;
			}
			if (mode === 'rank-periods') {
				var op = {
					vartype: curType, month: curMonth, start_year_rep: curStart,
					summary_type: curSummary, rankLimit: curRankLimit, period: curPeriod
				};
				if (isThresholdSummary(curSummary)) { op.threshold = curThreshold; }
				op.no_overlap = curNoOverlap ? 1 : 0;
				return op;
			}
			if (mode === 'rank-spells') {
				return {
					vartype: curType, month: curMonth, start_year_rep: curStart,
					rankLimit: curRankLimit, spell_dir: curSpellDir, threshold: curThreshold
				};
			}
			return { vartype: curType, year: curYear, agg: curAgg, start_year_rep: curStart, years: curShowYears ? 1 : 0 };
		}
		function applyMeta(meta) {
			if (!meta) { return; }
			if (meta.type) { curType = meta.type; }
			if (meta.year) { curYear = parseInt(meta.year, 10); }
			if (meta.agg != null) { curAgg = meta.agg || ''; }
			if (meta.month != null) { curMonth = parseInt(meta.month, 10); }
			if (meta.day != null) {
				curDay = parseInt(meta.day, 10);
				if (isNaN(curDay) || curDay < 0) { curDay = 0; }
			}
			if (meta.win != null) {
				curWin = coerceWin(meta.win, curDay);
			} else if (curDay <= 0) {
				curWin = 0;
			}
			if (meta.startYearRep != null) { curStart = parseInt(meta.startYearRep, 10); }
			if (meta.summaryType != null) { curSummary = parseInt(meta.summaryType, 10); }
			if (meta.rankLimit != null) { curRankLimit = parseInt(meta.rankLimit, 10); }
			if (meta.periodLength != null) { curPeriod = parseInt(meta.periodLength, 10); }
			if (meta.periodNoOverlap != null) { curNoOverlap = !!parseInt(meta.periodNoOverlap, 10); }
			if (meta.span != null) { curSpan = coerceSpan(meta.span, meta.month != null ? meta.month : curMonth); }
			if (meta.spellDir) { curSpellDir = meta.spellDir; }
			if (meta.threshold != null) { curThreshold = parseFloat(meta.threshold); }
			if (meta.thresholds) {
				curThresholds = meta.thresholds.map(function (x) { return parseFloat(x); });
			}
			if (meta.thresholdLabels) {
				curThresholdLabels = meta.thresholdLabels;
			}
			if (meta.summaryTypes) {
				summaryTypes = meta.summaryTypes.map(function (x) { return parseInt(x, 10); });
			}
			if (meta.startYears && meta.startYears.length) {
				startYearOpts = meta.startYears.map(function (x) { return parseInt(x, 10); });
			}
			if (meta.spellDirLabels && meta.spellDirLabels.length) {
				var sdl = {};
				meta.spellDirLabels.forEach(function (pair) {
					var ix = pair.indexOf(':');
					if (ix > 0) { sdl[pair.substring(0, ix)] = pair.substring(ix + 1); }
				});
				if (sdl.above) { spellDirLabels = sdl; }
			}
			root.setAttribute('data-type', curType);
			root.setAttribute('data-year', String(curYear));
			root.setAttribute('data-agg', curAgg);
			root.setAttribute('data-month', String(curMonth));
			root.setAttribute('data-day', String(curDay));
			root.setAttribute('data-win', String(curWin));
			root.setAttribute('data-start-year-rep', String(curStart));
			root.setAttribute('data-summary-type', String(curSummary));
			root.setAttribute('data-rank-limit', String(curRankLimit));
			root.setAttribute('data-period', String(curPeriod));
			root.setAttribute('data-no-overlap', curNoOverlap ? '1' : '0');
			root.setAttribute('data-span', curSpan);
			root.setAttribute('data-spell-dir', curSpellDir);
			root.setAttribute('data-threshold', String(curThreshold));
			if (headingEl && meta.title) {
				headingEl.textContent = headingPrefix + ' - ' + meta.title;
			}
			setYearWarn(meta.yearDefaulted ? (meta.yearWarn || '') : '');
			syncGroupActive();
			syncYearChips();
			syncMonthChips();
			syncStartYearChips();
			syncSummaryChips();
			syncRankLimitChips();
			syncPeriodChips();
			syncOverlapChips();
			syncSpanChips();
			syncSpellDirChips();
			syncThresholdChips();
			syncStartYearVisibility();
			if (mode === 'monthly' || mode === 'rank-monthly' || mode === 'rank-annual' || mode === 'rank-periods') { showSummaryTab(curSummary); }
			syncNavVartype();
		}
		function sameState(o) {
			if (o.type !== curType) { return false; }
			if (mode === 'daily') {
				if ((o.agg || '') !== (curAgg || '')) { return false; }
				if (o.agg) { return o.start === curStart; }
				return o.year === curYear;
			}
			if (mode === 'monthly') {
				if (o.start !== curStart || o.summary !== curSummary || (o.span || 'full') !== curSpan) { return false; }
				if (isThresholdSummary(curSummary) && Math.abs(o.threshold - curThreshold) >= 1e-9) { return false; }
				return true;
			}
			if (mode === 'rank-daily') {
				return o.month === curMonth && (o.day || 0) === (curDay || 0)
					&& (o.win || 0) === (curWin || 0)
					&& o.start === curStart && o.rankLimit === curRankLimit;
			}
			if (mode === 'rank-monthly') {
				if (o.month !== curMonth || o.start !== curStart
					|| o.summary !== curSummary || o.rankLimit !== curRankLimit
					|| (o.span || 'full') !== curSpan) { return false; }
				if (isThresholdSummary(curSummary) && Math.abs(o.threshold - curThreshold) >= 1e-9) { return false; }
				return true;
			}
			if (mode === 'rank-annual') {
				if (o.start !== curStart || o.summary !== curSummary || (o.span || 'full') !== curSpan) { return false; }
				if (isThresholdSummary(curSummary) && Math.abs(o.threshold - curThreshold) >= 1e-9) { return false; }
				return true;
			}
			if (mode === 'rank-periods') {
				if (o.month !== curMonth || o.start !== curStart || o.summary !== curSummary
					|| o.rankLimit !== curRankLimit || o.period !== curPeriod
					|| !!o.noOverlap !== curNoOverlap) { return false; }
				if (isThresholdSummary(curSummary) && Math.abs(o.threshold - curThreshold) >= 1e-9) { return false; }
				return true;
			}
			if (mode === 'rank-spells') {
				return o.month === curMonth && o.start === curStart && o.rankLimit === curRankLimit
					&& o.spellDir === curSpellDir && Math.abs(o.threshold - curThreshold) < 1e-9;
			}
			return true;
		}
		function load(opts, push) {
			opts = opts || {};
			var type = opts.type != null ? opts.type : curType;
			var year = opts.year != null ? parseInt(opts.year, 10) : curYear;
			var agg = opts.agg != null ? opts.agg : curAgg;
			if (opts.year != null && opts.agg == null) { agg = ''; }
			if (opts.agg) { /* keep year for restore when leaving agg */ }
			var month = opts.month != null ? parseInt(opts.month, 10) : curMonth;
			var day = opts.day != null ? parseInt(opts.day, 10) : curDay;
			if (isNaN(day) || day < 0) { day = 0; }
			if (mode === 'rank-daily' && day > 0) {
				if (!(month >= 1 && month <= 12)) { month = currentMonth; }
				if (day > daysInMonth(month)) { day = daysInMonth(month); }
			} else if (mode !== 'rank-daily') {
				day = 0;
			}
			var win = opts.win != null ? parseInt(opts.win, 10) : curWin;
			if (isNaN(win) || win < 0) { win = 0; }
			win = coerceWin(win, day);
			var start = opts.start != null ? parseInt(opts.start, 10) : curStart;
			var summary = opts.summary != null ? parseInt(opts.summary, 10) : curSummary;
			var rankLimit = opts.rankLimit != null ? parseInt(opts.rankLimit, 10) : curRankLimit;
			var spellDir = opts.spellDir != null ? opts.spellDir : curSpellDir;
			var threshold = opts.threshold != null ? parseFloat(opts.threshold) : curThreshold;

			// Summary-only change: if that tab is already in the DOM, switch client-side.
			// Rank/month pages ship only the active summary (AJAX on chip change).
			var period = opts.period != null ? parseInt(opts.period, 10) : curPeriod;
			var noOverlap = opts.noOverlap != null ? !!opts.noOverlap : curNoOverlap;
			var span = opts.span != null ? opts.span : curSpan;
			if (mode === 'rank-monthly' && span === 'mtd' && month !== currentMonth) {
				month = currentMonth;
			}
			span = coerceSpan(span, month);
			if ((mode === 'monthly' || mode === 'rank-monthly' || mode === 'rank-annual')
				&& type === curType && start === curStart && month === curMonth
				&& rankLimit === curRankLimit && period === curPeriod && noOverlap === curNoOverlap
				&& span === curSpan
				&& summary !== curSummary
				&& !isThresholdSummary(summary) && !isThresholdSummary(curSummary)
				&& document.getElementById('rank-' + summary)) {
				showSummaryTab(summary);
				syncThresholdVisibility();
				if (push !== false) { history.pushState(stateObj(), '', pageUrl()); }
				return;
			}

			var next = {
				type: type, year: year, agg: agg || '', month: month, day: day, win: win, start: start, summary: summary,
				rankLimit: rankLimit, spellDir: spellDir, threshold: threshold, period: period,
				noOverlap: noOverlap, span: span
			};
			if (sameState(next) && push !== false) {
				setYearWarn('');
				return;
			}

			var urlOverrides = { vartype: type };
			if (mode === 'daily') {
				if (agg) {
					urlOverrides.agg = agg;
					urlOverrides.start_year_rep = start;
				} else {
					urlOverrides.year = year;
				}
			} else if (mode === 'monthly') {
				urlOverrides.start_year_rep = start;
				urlOverrides.summary_type = summary;
				urlOverrides.span = span;
				if (isThresholdSummary(summary)) { urlOverrides.threshold = threshold; }
			} else if (isRank) {
				urlOverrides.start_year_rep = start;
				if (mode !== 'rank-annual') { urlOverrides.rankLimit = rankLimit; }
				if (mode === 'rank-daily' || mode === 'rank-monthly' || mode === 'rank-spells' || mode === 'rank-periods') {
					urlOverrides.month = month;
				}
				if (mode === 'rank-daily') {
					urlOverrides.day = day > 0 ? day : 0;
					urlOverrides.win = win;
				}
				if (mode === 'rank-monthly' || mode === 'rank-annual' || mode === 'rank-periods') {
					urlOverrides.summary_type = summary;
					if (isThresholdSummary(summary)) { urlOverrides.threshold = threshold; }
				}
				if (mode === 'rank-periods') {
					urlOverrides.period = period;
					urlOverrides.no_overlap = noOverlap ? 1 : 0;
				}
				if (mode === 'rank-monthly' || mode === 'rank-annual') {
					urlOverrides.span = span;
				}
				if (mode === 'rank-spells') {
					urlOverrides.spell_dir = spellDir;
					urlOverrides.threshold = threshold;
				}
			}
			var nextPage = pageUrl(urlOverrides);
			var body = bodyEl();
			if (!fragment || !window.fetch) {
				location.href = nextPage;
				return;
			}
			if (!body) {
				// RankPeriods echoes NW3_reportSel before #rp-ajax; retry once DOM catches up
				// instead of location.href (that re-hits stripped PHP and loops on deep links).
				setTimeout(function () { load(opts, push); }, 0);
				return;
			}
			var seq = ++reqSeq;
			var useJson = (mode === 'daily');
			body.classList.add('dd-ajax-loading');
			var headers = { 'X-Requested-With': 'NW3' };
			if (useJson) { headers['Accept'] = 'application/json'; }
			fetch(fragmentUrl(urlOverrides), { credentials: 'same-origin', cache: 'no-store', headers: headers })
				.then(function (r) { if (!r.ok) { throw new Error('bad status'); } return useJson ? r.json() : r.text(); })
				.then(function (data) {
					if (seq !== reqSeq) { return; }
					if (useJson) {
						if (!data || !data.meta || !window.NW3 || typeof NW3.hydrateDaily !== 'function') {
							throw new Error('bad json');
						}
						NW3.hydrateDaily(data, body);
						applyYearsClass();
						applyMeta({
							type: data.meta.type,
							year: data.meta.year,
							agg: data.meta.agg || '',
							startYearRep: data.meta.startYearRep,
							startYears: data.meta.startYearOptions,
							title: data.meta.title,
							yearDefaulted: !!data.meta.yearDefaulted,
							yearWarn: data.meta.yearWarn || ''
						});
					} else {
						var wrap = document.createElement('div');
						wrap.innerHTML = data;
						var frag = wrap.querySelector('#' + fragId);
						if (!frag) { throw new Error('missing fragment'); }
						body.innerHTML = frag.innerHTML;
						var stAttr = frag.getAttribute('data-summary-types') || '';
						var thAttr = frag.getAttribute('data-thresholds') || '';
						var thLabAttr = frag.getAttribute('data-threshold-labels') || '';
						var syAttr = frag.getAttribute('data-start-years') || '';
						var sdlAttr = frag.getAttribute('data-spell-dir-labels') || '';
						applyMeta({
							type: frag.getAttribute('data-type'),
							year: frag.getAttribute('data-year'),
							agg: frag.getAttribute('data-agg') || '',
							month: frag.getAttribute('data-month'),
							day: frag.getAttribute('data-day'),
							win: frag.getAttribute('data-win'),
							startYearRep: frag.getAttribute('data-start-year-rep'),
							startYears: syAttr ? syAttr.split(',') : startYearOpts,
							summaryType: frag.getAttribute('data-summary-type'),
							rankLimit: frag.getAttribute('data-rank-limit'),
							periodLength: frag.getAttribute('data-period'),
							periodNoOverlap: frag.getAttribute('data-no-overlap'),
							span: frag.getAttribute('data-span'),
							spellDir: frag.getAttribute('data-spell-dir'),
							spellDirLabels: sdlAttr ? sdlAttr.split(',') : null,
							threshold: frag.getAttribute('data-threshold'),
							thresholds: thAttr ? thAttr.split(',') : curThresholds,
							thresholdLabels: thLabAttr ? thLabAttr.split('|') : curThresholdLabels,
							summaryTypes: stAttr ? stAttr.split(',') : summaryTypes,
							title: frag.getAttribute('data-title'),
							yearDefaulted: frag.getAttribute('data-year-defaulted') === '1',
							yearWarn: frag.getAttribute('data-year-warn') || ''
						});
					}
					if (push !== false) {
						history.pushState(stateObj(), '', pageUrl());
					}
				})
				.catch(function () { location.href = nextPage; })
				.then(function () {
					if (seq === reqSeq && body) { body.classList.remove('dd-ajax-loading'); }
				});
		}

		root.querySelectorAll('.wxsel-groups button').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var gid = btn.getAttribute('data-group');
				var def = btn.getAttribute('data-default-type');
				root.querySelectorAll('.wxsel-groups button').forEach(function (b) {
					b.classList.toggle('active', b === btn);
				});
				var g = groupById(gid);
				if (g && g.options && g.options[curType]) { renderMeasures(gid); return; }
				load({ type: def });
			});
		});
		root.addEventListener('click', function (e) {
			var a = e.target.closest('a.wxsel-chip');
			if (!a || !root.contains(a)) { return; }
			var type = a.getAttribute('data-vartype');
			var year = a.getAttribute('data-year');
			var agg = a.getAttribute('data-agg');
			var month = a.getAttribute('data-month');
			var dayAttr = a.getAttribute('data-day');
			var dateSelAttr = a.getAttribute('data-datesel');
			var winAttr = a.getAttribute('data-win');
			var start = a.getAttribute('data-start-year');
			var summary = a.getAttribute('data-summary');
			var rankLimit = a.getAttribute('data-rank-limit');
			var spellDir = a.getAttribute('data-spell-dir');
			var threshold = a.getAttribute('data-threshold');
			var period = a.getAttribute('data-period');
			var noOverlapAttr = a.getAttribute('data-no-overlap');
			var spanAttr = a.getAttribute('data-span');
			if (type == null && year == null && agg == null && month == null && start == null
				&& summary == null && rankLimit == null && spellDir == null && threshold == null
				&& period == null && noOverlapAttr == null && spanAttr == null && dateSelAttr == null
				&& dayAttr == null && winAttr == null) {
				return;
			}
			e.preventDefault();
			var opts = {
				type: type != null ? type : curType,
				month: month != null ? month : curMonth,
				start: start != null ? start : curStart,
				summary: summary != null ? summary : curSummary,
				rankLimit: rankLimit != null ? rankLimit : curRankLimit,
				spellDir: spellDir != null ? spellDir : curSpellDir,
				threshold: threshold != null ? threshold : curThreshold,
				period: period != null ? period : curPeriod,
				noOverlap: noOverlapAttr != null ? (noOverlapAttr === '1') : curNoOverlap,
				span: spanAttr != null ? spanAttr : curSpan
			};
			if (agg != null) {
				opts.agg = agg;
				opts.year = curYear;
				if (!curStart) { opts.start = 2009; }
			} else if (year != null) {
				opts.year = year;
				opts.agg = '';
			} else {
				opts.year = curYear;
				opts.agg = curAgg;
			}
			if (mode === 'rank-daily') {
				if (winAttr != null) {
					opts.win = parseInt(winAttr, 10) || 0;
					if (opts.day == null) {
						opts.day = curDay > 0 ? curDay : currentDay;
					}
					if (opts.month == null) {
						opts.month = curDay > 0 ? curMonth : currentMonth;
					}
				} else if (dateSelAttr != null) {
					opts.day = dayAttr != null ? parseInt(dayAttr, 10) : (curDay > 0 ? curDay : currentDay);
					if (month == null) {
						opts.month = curDay > 0 ? curMonth : currentMonth;
					}
					opts.win = curDay > 0 ? curWin : 0;
				} else if (month != null && a.closest('.wxsel-months')) {
					opts.day = 0;
					opts.win = 0;
				} else {
					opts.day = curDay;
					opts.win = curWin;
				}
			}
			load(opts);
		});
		var yearsForm = document.getElementById('dd-years-form');
		var yearsBox = document.getElementById('dd-show-years');
		if (yearsForm) {
			yearsForm.addEventListener('submit', function (e) {
				if (!window.history || !history.replaceState) { return; }
				e.preventDefault();
				curShowYears = !!(yearsBox && yearsBox.checked);
				applyYearsClass();
				history.replaceState(stateObj(), '', pageUrl());
			});
		}
		if (yearsBox) {
			yearsBox.addEventListener('change', function () {
				curShowYears = !!yearsBox.checked;
				applyYearsClass();
				if (window.history && history.replaceState) {
					history.replaceState(stateObj(), '', pageUrl());
				}
			});
		}

		window.addEventListener('popstate', function (e) {
			var st = e.state;
			var q = new URL(location.href).searchParams;
			if (mode === 'daily') {
				if (st && st.years != null) {
					curShowYears = st.years !== 0 && st.years !== '0';
				} else {
					curShowYears = q.get('years') !== '0';
				}
			}
			if (st && st.vartype) {
				load({
					type: st.vartype,
					year: st.year != null ? st.year : curYear,
					agg: st.agg != null ? st.agg : '',
					month: st.month != null ? st.month : curMonth,
					day: st.day != null ? st.day : 0,
					win: st.win != null ? st.win : 0,
					start: st.start_year_rep != null ? st.start_year_rep : curStart,
					summary: st.summary_type != null ? st.summary_type : curSummary,
					rankLimit: st.rankLimit != null ? st.rankLimit : curRankLimit,
					spellDir: st.spell_dir != null ? st.spell_dir : curSpellDir,
					threshold: st.threshold != null ? st.threshold : curThreshold,
					period: st.period != null ? st.period : curPeriod,
					noOverlap: st.no_overlap != null ? !!parseInt(st.no_overlap, 10) : curNoOverlap,
					span: st.span != null ? st.span : curSpan
				}, false);
				return;
			}
			load({
				type: q.get('vartype') || curType,
				year: q.get('year') || curYear,
				agg: q.get('agg') || '',
				month: q.get('month') != null ? q.get('month') : curMonth,
				day: q.get('day') != null ? q.get('day') : 0,
				win: q.get('win') != null ? q.get('win') : 0,
				start: q.get('start_year_rep') || curStart,
				summary: q.get('summary_type') || curSummary,
				rankLimit: q.get('rankLimit') || curRankLimit,
				spellDir: q.get('spell_dir') || curSpellDir,
				threshold: q.get('threshold') != null ? q.get('threshold') : curThreshold,
				period: q.get('period') != null ? q.get('period') : curPeriod,
				noOverlap: q.get('no_overlap') != null ? (q.get('no_overlap') === '1') : curNoOverlap,
				span: q.get('span') != null ? q.get('span') : curSpan
			}, false);
		});

		function bindDateSel() {
			if (mode !== 'rank-daily') { return; }
			var form = document.getElementById('wxsel-date-form');
			if (!form || form.getAttribute('data-bound')) { return; }
			form.setAttribute('data-bound', '1');
			var monthSel = document.getElementById('wxsel-date-month');
			var daySel = document.getElementById('wxsel-date-day');
			if (monthSel) {
				monthSel.addEventListener('change', function () {
					var d = fillDaySelect(daySel, parseInt(monthSel.value, 10), daySel ? daySel.value : 1);
					load({ month: parseInt(monthSel.value, 10), day: d });
				});
			}
			if (daySel) {
				daySel.addEventListener('change', function () {
					load({ month: parseInt(monthSel.value, 10), day: parseInt(daySel.value, 10) });
				});
			}
			form.addEventListener('submit', function (e) {
				e.preventDefault();
				if (!monthSel || !daySel) { return; }
				load({ month: parseInt(monthSel.value, 10), day: parseInt(daySel.value, 10) });
			});
		}
		syncNavVartype();
		syncMonthChips();
		bindDateSel();
		syncRankLimitChips();
		syncPeriodChips();
		syncOverlapChips();
		syncSpanChips();
		syncStartYearVisibility();
		syncThresholdVisibility();
		syncThresholdChips();
		syncAggChips();
		syncStartYearChips();

		// Hydrate RankPeriods deep links via AJAX. Full-page PHP ignores query
		// params (bot mitigation); real browsers re-apply location.search here.
		// Defer until #rp-ajax exists — controls() script runs before that div.
		function bootRankPeriodsHydrate() {
			if (mode !== 'rank-periods' || !fragment || !window.URL) { return; }
			if (!bodyEl()) { return; }
			var q = new URL(location.href).searchParams;
			var hasCustom = q.has('month') || q.has('period') || q.has('rankLimit')
				|| q.has('summary_type') || q.has('start_year_rep') || q.has('vartype')
				|| q.has('no_overlap') || q.has('threshold');
			if (!hasCustom) { return; }
			load({
				type: q.get('vartype') || curType,
				month: q.get('month') != null ? q.get('month') : curMonth,
				start: q.get('start_year_rep') || curStart,
				summary: q.get('summary_type') != null ? q.get('summary_type') : curSummary,
				rankLimit: q.get('rankLimit') || curRankLimit,
				threshold: q.get('threshold') != null ? q.get('threshold') : curThreshold,
				period: q.get('period') != null ? q.get('period') : curPeriod,
				noOverlap: q.get('no_overlap') != null ? (q.get('no_overlap') === '1') : curNoOverlap
			}, false);
		}
		if (bodyEl()) {
			bootRankPeriodsHydrate();
		} else if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', bootRankPeriodsHydrate);
		} else {
			setTimeout(bootRankPeriodsHydrate, 0);
		}
	}

	/**
	 * Monthly table crosshair: hover a value cell → border the whole row and column.
	 * Document-delegated so it survives AJAX body swaps.
	 */
	function NW3_dmCrosshair() {
		if (window._nw3DmCrosshairBound) { return; }
		window._nw3DmCrosshairBound = true;
		var activeGrid = null;

		function clear() {
			if (!activeGrid) { return; }
			activeGrid.querySelectorAll('.dm-hl-row').forEach(function (el) {
				el.classList.remove('dm-hl-row');
			});
			activeGrid.querySelectorAll('.dm-hl-col, .dm-hl-cell').forEach(function (el) {
				el.classList.remove('dm-hl-col', 'dm-hl-cell');
			});
			activeGrid = null;
		}

		document.addEventListener('mouseover', function (e) {
			var cell = e.target.closest('.dm-cell');
			if (!cell || cell.classList.contains('dm-future')) {
				clear();
				return;
			}
			var row = cell.parentElement;
			var grid = cell.closest('.dm-grid');
			if (!grid || !row || !row.classList.contains('dm-row')) {
				clear();
				return;
			}
			if (cell.classList.contains('dm-hl-cell') && activeGrid === grid) { return; }

			clear();
			activeGrid = grid;
			var idx = Array.prototype.indexOf.call(row.children, cell);
			if (idx < 0) { return; }
			row.classList.add('dm-hl-row');
			cell.classList.add('dm-hl-cell');
			grid.querySelectorAll('.dm-row').forEach(function (r) {
				var c = r.children[idx];
				if (c) { c.classList.add('dm-hl-col'); }
			});
		});
	}
	NW3_dmCrosshair();

	//]]>
</script>

<script type="text/javascript">
	//<![CDATA[
	(function () {
		var MQ = '(max-width: 1020px)';
		function isNarrow() {
			return window.matchMedia && window.matchMedia(MQ).matches;
		}
		function setOpen(open) {
			var btn = document.getElementById('nav-toggle');
			var backdrop = document.getElementById('nav-backdrop');
			var nav = document.getElementById('nav');
			document.documentElement.classList.toggle('nav-open', open);
			document.body.classList.toggle('nav-open', open);
			if (btn) {
				btn.setAttribute('aria-expanded', open ? 'true' : 'false');
				btn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
			}
			if (backdrop) {
				if (open) { backdrop.removeAttribute('hidden'); }
				else { backdrop.setAttribute('hidden', ''); }
			}
			if (open && nav) {
				var focusable = nav.querySelector('a, button, input');
				if (focusable) { try { focusable.focus(); } catch (e) {} }
			} else if (!open && btn) {
				try { btn.focus(); } catch (e) {}
			}
		}
		function closeNav() { setOpen(false); }
		function toggleNav() { setOpen(!document.body.classList.contains('nav-open')); }

		function bind() {
			var btn = document.getElementById('nav-toggle');
			var backdrop = document.getElementById('nav-backdrop');
			var nav = document.getElementById('nav');
			if (!btn || !nav) { return; }

			btn.addEventListener('click', function (ev) {
				ev.preventDefault();
				toggleNav();
			});
			if (backdrop) {
				backdrop.addEventListener('click', closeNav);
			}
			document.addEventListener('keydown', function (ev) {
				if (ev.key === 'Escape' && document.body.classList.contains('nav-open')) {
					closeNav();
				}
			});
			nav.addEventListener('click', function (ev) {
				var a = ev.target.closest ? ev.target.closest('a') : null;
				if (a && isNarrow()) { closeNav(); }
			});

			if (window.matchMedia) {
				window.matchMedia(MQ).addEventListener('change', function (e) {
					if (!e.matches) { closeNav(); }
				});
			}
		}
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', bind);
		} else {
			bind();
		}
	})();
	//]]>
</script>