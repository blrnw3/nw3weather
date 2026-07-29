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
$camImg .= (Page::$fileNum === 1) ? '_small.jpg' : '.jpg';
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
		document.getElementById('live-wx').innerHTML = data;
		setTimeout('shownewhead()',2000);
	}
	shownewhead();
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
	 * cfg.mode: daily | monthly | rank-daily | rank-monthly | rank-annual | rank-spells
	 */
	function NW3_reportSel(cfg) {
		var groups = cfg.groups || [];
		var mode = cfg.mode || 'daily';
		var isRank = (mode === 'rank-daily' || mode === 'rank-monthly' || mode === 'rank-annual' || mode === 'rank-spells');
		var curType = cfg.type;
		var curYear = parseInt(cfg.year, 10) || 0;
		var curAgg = cfg.agg || '';
		var curMonth = parseInt(cfg.month, 10);
		if (isNaN(curMonth)) { curMonth = 0; }
		var curStart = parseInt(cfg.startYearRep, 10) || 0;
		var curSummary = parseInt(cfg.summaryType, 10);
		if (isNaN(curSummary)) { curSummary = 0; }
		var curRankLimit = parseInt(cfg.rankLimit, 10) || 25;
		var curSpellDir = cfg.spellDir || 'above';
		var curThreshold = cfg.threshold != null ? parseFloat(cfg.threshold) : 0;
		var curThresholds = cfg.thresholds || [];
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
		var headingEl = document.getElementById('report-sel-heading');
		var reqSeq = 0;

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
				if (agg) { p.agg = agg; }
				else { p.year = year; }
			} else if (mode === 'monthly') {
				p.start_year_rep = curStart;
				p.summary_type = curSummary;
			} else if (isRank) {
				p.start_year_rep = curStart;
				if (mode !== 'rank-annual') { p.rankLimit = curRankLimit; }
				if (mode === 'rank-daily' || mode === 'rank-monthly' || mode === 'rank-spells') { p.month = curMonth; }
				if (mode === 'rank-monthly' || mode === 'rank-annual') { p.summary_type = curSummary; }
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
		}
		function syncMonthChips() {
			if (!monthsEl) { return; }
			monthsEl.querySelectorAll('a.wxsel-chip[data-month]').forEach(function (a) {
				var m = parseInt(a.getAttribute('data-month'), 10);
				a.classList.toggle('active', m === curMonth);
				a.setAttribute('href', pageUrl({ month: m }));
			});
		}
		function syncStartYearChips() {
			if (!startEl) { return; }
			startEl.querySelectorAll('a.wxsel-chip[data-start-year]').forEach(function (a) {
				var y = parseInt(a.getAttribute('data-start-year'), 10);
				a.classList.toggle('active', y === curStart);
				a.setAttribute('href', pageUrl({ start_year_rep: y }));
			});
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
		function syncSpellDirChips() {
			if (!spellDirEl) { return; }
			spellDirEl.querySelectorAll('a.wxsel-chip[data-spell-dir]').forEach(function (a) {
				var d = a.getAttribute('data-spell-dir');
				a.classList.toggle('active', d === curSpellDir);
				a.setAttribute('href', pageUrl({ spell_dir: d }));
			});
		}
		function syncThresholdChips() {
			if (!thresholdEl) { return; }
			var html = '', i, th, active;
			for (i = 0; i < curThresholds.length; i++) {
				th = curThresholds[i];
				active = Math.abs(parseFloat(th) - curThreshold) < 1e-9;
				html += '<a class="wxsel-chip' + (active ? ' active' : '')
					+ '" data-threshold="' + th + '" href="' + pageUrl({ threshold: th }) + '">'
					+ th + '</a>';
			}
			thresholdEl.innerHTML = html;
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
				return { vartype: curType, start_year_rep: curStart, summary_type: curSummary };
			}
			if (mode === 'rank-daily') {
				return { vartype: curType, month: curMonth, start_year_rep: curStart, rankLimit: curRankLimit };
			}
			if (mode === 'rank-monthly') {
				return {
					vartype: curType, month: curMonth, start_year_rep: curStart,
					summary_type: curSummary, rankLimit: curRankLimit
				};
			}
			if (mode === 'rank-annual') {
				return { vartype: curType, start_year_rep: curStart, summary_type: curSummary };
			}
			if (mode === 'rank-spells') {
				return {
					vartype: curType, month: curMonth, start_year_rep: curStart,
					rankLimit: curRankLimit, spell_dir: curSpellDir, threshold: curThreshold
				};
			}
			return { vartype: curType, year: curYear, agg: curAgg };
		}
		function applyMeta(meta) {
			if (!meta) { return; }
			if (meta.type) { curType = meta.type; }
			if (meta.year) { curYear = parseInt(meta.year, 10); }
			if (meta.agg != null) { curAgg = meta.agg || ''; }
			if (meta.month != null) { curMonth = parseInt(meta.month, 10); }
			if (meta.startYearRep != null) { curStart = parseInt(meta.startYearRep, 10); }
			if (meta.summaryType != null) { curSummary = parseInt(meta.summaryType, 10); }
			if (meta.rankLimit != null) { curRankLimit = parseInt(meta.rankLimit, 10); }
			if (meta.spellDir) { curSpellDir = meta.spellDir; }
			if (meta.threshold != null) { curThreshold = parseFloat(meta.threshold); }
			if (meta.thresholds) {
				curThresholds = meta.thresholds.map(function (x) { return parseFloat(x); });
			}
			if (meta.summaryTypes) {
				summaryTypes = meta.summaryTypes.map(function (x) { return parseInt(x, 10); });
			}
			root.setAttribute('data-type', curType);
			root.setAttribute('data-year', String(curYear));
			root.setAttribute('data-agg', curAgg);
			root.setAttribute('data-month', String(curMonth));
			root.setAttribute('data-start-year-rep', String(curStart));
			root.setAttribute('data-summary-type', String(curSummary));
			root.setAttribute('data-rank-limit', String(curRankLimit));
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
			syncSpellDirChips();
			syncThresholdChips();
			if (mode === 'monthly' || mode === 'rank-monthly' || mode === 'rank-annual') { showSummaryTab(curSummary); }
			syncNavVartype();
		}
		function sameState(o) {
			if (o.type !== curType) { return false; }
			if (mode === 'daily') {
				return (o.agg || '') === (curAgg || '') && (o.agg ? true : o.year === curYear);
			}
			if (mode === 'monthly') { return o.start === curStart && o.summary === curSummary; }
			if (mode === 'rank-daily') {
				return o.month === curMonth && o.start === curStart && o.rankLimit === curRankLimit;
			}
			if (mode === 'rank-monthly') {
				return o.month === curMonth && o.start === curStart
					&& o.summary === curSummary && o.rankLimit === curRankLimit;
			}
			if (mode === 'rank-annual') {
				return o.start === curStart && o.summary === curSummary;
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
			var start = opts.start != null ? parseInt(opts.start, 10) : curStart;
			var summary = opts.summary != null ? parseInt(opts.summary, 10) : curSummary;
			var rankLimit = opts.rankLimit != null ? parseInt(opts.rankLimit, 10) : curRankLimit;
			var spellDir = opts.spellDir != null ? opts.spellDir : curSpellDir;
			var threshold = opts.threshold != null ? parseFloat(opts.threshold) : curThreshold;

			// Summary-only change when all summary tabs are already in the DOM.
			if ((mode === 'monthly' || mode === 'rank-monthly' || mode === 'rank-annual')
				&& type === curType && start === curStart && month === curMonth
				&& rankLimit === curRankLimit && summary !== curSummary) {
				showSummaryTab(summary);
				if (push !== false) { history.pushState(stateObj(), '', pageUrl()); }
				return;
			}

			var next = {
				type: type, year: year, agg: agg || '', month: month, start: start, summary: summary,
				rankLimit: rankLimit, spellDir: spellDir, threshold: threshold
			};
			if (sameState(next) && push !== false) {
				setYearWarn('');
				return;
			}

			var urlOverrides = { vartype: type };
			if (mode === 'daily') {
				if (agg) { urlOverrides.agg = agg; }
				else { urlOverrides.year = year; }
			} else if (mode === 'monthly') {
				urlOverrides.start_year_rep = start;
				urlOverrides.summary_type = summary;
			} else if (isRank) {
				urlOverrides.start_year_rep = start;
				if (mode !== 'rank-annual') { urlOverrides.rankLimit = rankLimit; }
				if (mode === 'rank-daily' || mode === 'rank-monthly' || mode === 'rank-spells') {
					urlOverrides.month = month;
				}
				if (mode === 'rank-monthly' || mode === 'rank-annual') { urlOverrides.summary_type = summary; }
				if (mode === 'rank-spells') {
					urlOverrides.spell_dir = spellDir;
					urlOverrides.threshold = threshold;
				}
			}
			var nextPage = pageUrl(urlOverrides);
			var body = bodyEl();
			if (!fragment || !body || !window.fetch) {
				location.href = nextPage;
				return;
			}
			var seq = ++reqSeq;
			body.classList.add('dd-ajax-loading');
			fetch(fragmentUrl(urlOverrides), { credentials: 'same-origin', cache: 'no-store' })
				.then(function (r) { if (!r.ok) { throw new Error('bad status'); } return r.text(); })
				.then(function (html) {
					if (seq !== reqSeq) { return; }
					var wrap = document.createElement('div');
					wrap.innerHTML = html;
					var frag = wrap.querySelector('#' + fragId);
					if (!frag) { throw new Error('missing fragment'); }
					body.innerHTML = frag.innerHTML;
					var stAttr = frag.getAttribute('data-summary-types') || '';
					var thAttr = frag.getAttribute('data-thresholds') || '';
					applyMeta({
						type: frag.getAttribute('data-type'),
						year: frag.getAttribute('data-year'),
						agg: frag.getAttribute('data-agg') || '',
						month: frag.getAttribute('data-month'),
						startYearRep: frag.getAttribute('data-start-year-rep'),
						summaryType: frag.getAttribute('data-summary-type'),
						rankLimit: frag.getAttribute('data-rank-limit'),
						spellDir: frag.getAttribute('data-spell-dir'),
						threshold: frag.getAttribute('data-threshold'),
						thresholds: thAttr ? thAttr.split(',') : curThresholds,
						summaryTypes: stAttr ? stAttr.split(',') : summaryTypes,
						title: frag.getAttribute('data-title'),
						yearDefaulted: frag.getAttribute('data-year-defaulted') === '1',
						yearWarn: frag.getAttribute('data-year-warn') || ''
					});
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
			var start = a.getAttribute('data-start-year');
			var summary = a.getAttribute('data-summary');
			var rankLimit = a.getAttribute('data-rank-limit');
			var spellDir = a.getAttribute('data-spell-dir');
			var threshold = a.getAttribute('data-threshold');
			if (type == null && year == null && agg == null && month == null && start == null
				&& summary == null && rankLimit == null && spellDir == null && threshold == null) {
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
				threshold: threshold != null ? threshold : curThreshold
			};
			if (agg != null) {
				opts.agg = agg;
				opts.year = curYear;
			} else if (year != null) {
				opts.year = year;
				opts.agg = '';
			} else {
				opts.year = curYear;
				opts.agg = curAgg;
			}
			load(opts);
		});
		window.addEventListener('popstate', function (e) {
			var st = e.state;
			var q = new URL(location.href).searchParams;
			if (st && st.vartype) {
				load({
					type: st.vartype,
					year: st.year != null ? st.year : curYear,
					agg: st.agg != null ? st.agg : '',
					month: st.month != null ? st.month : curMonth,
					start: st.start_year_rep != null ? st.start_year_rep : curStart,
					summary: st.summary_type != null ? st.summary_type : curSummary,
					rankLimit: st.rankLimit != null ? st.rankLimit : curRankLimit,
					spellDir: st.spell_dir != null ? st.spell_dir : curSpellDir,
					threshold: st.threshold != null ? st.threshold : curThreshold
				}, false);
				return;
			}
			load({
				type: q.get('vartype') || curType,
				year: q.get('year') || curYear,
				agg: q.get('agg') || '',
				month: q.get('month') != null ? q.get('month') : curMonth,
				start: q.get('start_year_rep') || curStart,
				summary: q.get('summary_type') || curSummary,
				rankLimit: q.get('rankLimit') || curRankLimit,
				spellDir: q.get('spell_dir') || curSpellDir,
				threshold: q.get('threshold') != null ? q.get('threshold') : curThreshold
			}, false);
		});

		syncNavVartype();
		syncMonthChips();
		syncRankLimitChips();
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

	/**
	 * Start-year chips for detail-page sections (averages + rankings).
	 * Multiple sections share one start year: changing either reloads all and updates the URL.
	 */
	var NW3_detailStartYear = {
		sections: [],
		curStart: 2009,
		page: '',
		reqSeq: 0,
		bound: false
	};
	function NW3_detailStartYearSel(cfg) {
		var root = document.getElementById(cfg.selId || 'vd-avg-sel');
		var body = document.getElementById(cfg.bodyId || 'vd-avg-ajax');
		if (!root || !body) { return; }
		var st = NW3_detailStartYear;
		st.curStart = parseInt(cfg.startYearRep, 10) || st.curStart || 2009;
		st.page = cfg.page || st.page || window.location.pathname;
		st.sections.push({
			root: root,
			body: body,
			group: cfg.group,
			fragment: cfg.fragment || '/v5/detailavgdata.php'
		});

		function pageUrl(y) {
			var page = st.page;
			var base = page.indexOf('?') >= 0 ? page.substring(0, page.indexOf('?')) : page;
			return base + '?start_year_rep=' + encodeURIComponent(y);
		}
		function syncChips() {
			st.sections.forEach(function (sec) {
				sec.root.querySelectorAll('a.wxsel-chip[data-start-year]').forEach(function (a) {
					var y = parseInt(a.getAttribute('data-start-year'), 10);
					a.classList.toggle('active', y === st.curStart);
					a.setAttribute('href', pageUrl(y));
				});
			});
		}
		function setBusy(on) {
			st.sections.forEach(function (sec) {
				if (sec.root) {
					sec.root.classList.toggle('wxsel-busy', on);
					sec.root.setAttribute('aria-busy', on ? 'true' : 'false');
				}
				if (sec.body) {
					sec.body.classList.toggle('wxsel-loading', on);
					sec.body.setAttribute('aria-busy', on ? 'true' : 'false');
				}
			});
		}
		function loadSection(sec, y, seq) {
			var url = sec.fragment + '?group=' + encodeURIComponent(sec.group)
				+ '&start_year_rep=' + encodeURIComponent(y);
			return fetch(url, { credentials: 'same-origin' }).then(function (res) {
				return res.text();
			}).then(function (html) {
				if (seq !== st.reqSeq) { return; }
				sec.body.innerHTML = html;
			}).catch(function () {
				if (seq !== st.reqSeq) { return; }
				window.location = pageUrl(y);
			});
		}
		function load(y) {
			st.curStart = y;
			syncChips();
			if (window.history && history.replaceState) {
				history.replaceState({ start_year_rep: y }, '', pageUrl(y));
			}
			if (!window.fetch) {
				window.location = pageUrl(y);
				return;
			}
			var seq = ++st.reqSeq;
			setBusy(true);
			var pending = st.sections.map(function (sec) { return loadSection(sec, y, seq); });
			Promise.all(pending).then(function () {
				if (seq !== st.reqSeq) { return; }
				setBusy(false);
			});
		}
		if (!st.bound) {
			st.bound = true;
			document.addEventListener('click', function (ev) {
				var a = ev.target.closest ? ev.target.closest('a.wxsel-chip[data-start-year]') : null;
				if (!a) { return; }
				var owned = false;
				for (var i = 0; i < st.sections.length; i++) {
					if (st.sections[i].root.contains(a)) { owned = true; break; }
				}
				if (!owned) { return; }
				ev.preventDefault();
				var y = parseInt(a.getAttribute('data-start-year'), 10);
				if (!isNaN(y) && y !== st.curStart) { load(y); }
			});
		}
		syncChips();
	}
	// Back-compat alias used by older inline calls.
	function NW3_detailAvgSel(cfg) {
		if (!cfg.selId) { cfg.selId = 'vd-avg-sel'; }
		if (!cfg.bodyId) { cfg.bodyId = 'vd-avg-ajax'; }
		NW3_detailStartYearSel(cfg);
	}
	//]]>
</script>