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
	 * Button selectors for daily/monthly data tables (group → measure, year chips, AJAX body).
	 * cfg: groups, mode, type, year, startYearRep, summaryType, summaryTypes, summaryLabels,
	 *      page, fragment, bodyId, headingPrefix, fragId
	 */
	function NW3_reportSel(cfg) {
		var groups = cfg.groups || [];
		var mode = cfg.mode || 'daily';
		var curType = cfg.type;
		var curYear = parseInt(cfg.year, 10) || 0;
		var curStart = parseInt(cfg.startYearRep, 10) || 0;
		var curSummary = parseInt(cfg.summaryType, 10);
		if (isNaN(curSummary)) { curSummary = 0; }
		var summaryTypes = cfg.summaryTypes || [];
		var summaryLabels = cfg.summaryLabels || {};
		var page = cfg.page;
		var fragment = cfg.fragment;
		var bodyId = cfg.bodyId || 'dd-ajax';
		var headingPrefix = cfg.headingPrefix || 'Data Tables';
		var fragId = cfg.fragId || (mode === 'monthly' ? 'dm-fragment' : 'dd-fragment');
		var root = document.getElementById('report-sel');
		if (!root) { return; }
		var sub = root.querySelector('.wxsel-subtypes');
		var yearsEl = root.querySelector('.wxsel-years');
		var startEl = root.querySelector('.wxsel-start-years');
		var summaryEl = root.querySelector('.wxsel-summary');
		var headingEl = document.getElementById('report-sel-heading');
		var reqSeq = 0;

		function bodyEl() { return document.getElementById(bodyId); }
		function pageUrl(overrides) {
			var p = { vartype: curType };
			if (mode === 'monthly') {
				p.start_year_rep = curStart;
				p.summary_type = curSummary;
			} else {
				p.year = curYear;
			}
			if (overrides) {
				var k;
				for (k in overrides) {
					if (Object.prototype.hasOwnProperty.call(overrides, k)) { p[k] = overrides[k]; }
				}
			}
			var qs = [];
			for (k in p) {
				if (Object.prototype.hasOwnProperty.call(p, k)) {
					qs.push(encodeURIComponent(k) + '=' + encodeURIComponent(p[k]));
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
			chips.forEach(function (a) {
				var y = parseInt(a.getAttribute('data-year'), 10);
				var on = y === curYear;
				a.classList.toggle('active', on);
				a.setAttribute('href', pageUrl({ year: y }));
				if (on && !a.closest('.wxsel-overflow-menu')) { inRecent = true; }
			});
			var details = yearsEl.querySelector('.wxsel-overflow');
			if (details) {
				var sum = details.querySelector('summary');
				if (sum) {
					sum.classList.toggle('active', !inRecent);
					sum.textContent = inRecent ? 'Older' : String(curYear);
				}
				details.open = false;
			}
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
		function applyMeta(meta) {
			if (!meta) { return; }
			if (meta.type) { curType = meta.type; }
			if (meta.year) { curYear = parseInt(meta.year, 10); }
			if (meta.startYearRep != null) { curStart = parseInt(meta.startYearRep, 10); }
			if (meta.summaryType != null) { curSummary = parseInt(meta.summaryType, 10); }
			if (meta.summaryTypes) {
				summaryTypes = meta.summaryTypes.map(function (x) { return parseInt(x, 10); });
			}
			root.setAttribute('data-type', curType);
			root.setAttribute('data-year', String(curYear));
			root.setAttribute('data-start-year-rep', String(curStart));
			root.setAttribute('data-summary-type', String(curSummary));
			if (headingEl && meta.title) {
				headingEl.textContent = headingPrefix + ' - ' + meta.title;
			}
			setYearWarn(meta.yearDefaulted ? (meta.yearWarn || '') : '');
			syncGroupActive();
			syncYearChips();
			syncStartYearChips();
			syncSummaryChips();
			if (mode === 'monthly') { showSummaryTab(curSummary); }
			syncNavVartype();
		}
		function sameState(type, year, start, summary) {
			if (type !== curType) { return false; }
			if (mode === 'monthly') {
				return start === curStart && summary === curSummary;
			}
			return year === curYear;
		}
		function load(opts, push) {
			opts = opts || {};
			var type = opts.type != null ? opts.type : curType;
			var year = opts.year != null ? parseInt(opts.year, 10) : curYear;
			var start = opts.start != null ? parseInt(opts.start, 10) : curStart;
			var summary = opts.summary != null ? parseInt(opts.summary, 10) : curSummary;

			// Summary-only change on monthly: switch tabs in place (all already rendered).
			if (mode === 'monthly' && type === curType && start === curStart && summary !== curSummary) {
				showSummaryTab(summary);
				if (push !== false) {
					history.pushState(
						{ vartype: curType, start_year_rep: curStart, summary_type: curSummary },
						'',
						pageUrl()
					);
				}
				return;
			}

			if (sameState(type, year, start, summary) && push !== false) {
				setYearWarn('');
				return;
			}

			var nextPage = mode === 'monthly'
				? pageUrl({ vartype: type, start_year_rep: start, summary_type: summary })
				: pageUrl({ vartype: type, year: year });
			var body = bodyEl();
			if (!fragment || !body || !window.fetch) {
				location.href = nextPage;
				return;
			}
			var seq = ++reqSeq;
			body.classList.add('dd-ajax-loading');
			var fetchUrl = mode === 'monthly'
				? fragmentUrl({ vartype: type, start_year_rep: start, summary_type: summary })
				: fragmentUrl({ vartype: type, year: year });
			fetch(fetchUrl, { credentials: 'same-origin', cache: 'no-store' })
				.then(function (r) { if (!r.ok) { throw new Error('bad status'); } return r.text(); })
				.then(function (html) {
					if (seq !== reqSeq) { return; }
					var wrap = document.createElement('div');
					wrap.innerHTML = html;
					var frag = wrap.querySelector('#' + fragId);
					if (!frag) { throw new Error('missing fragment'); }
					body.innerHTML = frag.innerHTML;
					var stAttr = frag.getAttribute('data-summary-types') || '';
					applyMeta({
						type: frag.getAttribute('data-type'),
						year: frag.getAttribute('data-year'),
						startYearRep: frag.getAttribute('data-start-year-rep'),
						summaryType: frag.getAttribute('data-summary-type'),
						summaryTypes: stAttr ? stAttr.split(',') : summaryTypes,
						title: frag.getAttribute('data-title'),
						yearDefaulted: frag.getAttribute('data-year-defaulted') === '1',
						yearWarn: frag.getAttribute('data-year-warn') || ''
					});
					if (push !== false) {
						history.pushState(
							mode === 'monthly'
								? { vartype: curType, start_year_rep: curStart, summary_type: curSummary }
								: { vartype: curType, year: curYear },
							'',
							pageUrl()
						);
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
			var start = a.getAttribute('data-start-year');
			var summary = a.getAttribute('data-summary');
			if (type == null && year == null && start == null && summary == null) { return; }
			e.preventDefault();
			load({
				type: type != null ? type : curType,
				year: year != null ? year : curYear,
				start: start != null ? start : curStart,
				summary: summary != null ? summary : curSummary
			});
		});
		window.addEventListener('popstate', function (e) {
			var st = e.state;
			if (st && st.vartype) {
				if (mode === 'monthly') {
					load({
						type: st.vartype,
						start: st.start_year_rep != null ? st.start_year_rep : curStart,
						summary: st.summary_type != null ? st.summary_type : curSummary
					}, false);
				} else {
					load({ type: st.vartype, year: st.year != null ? st.year : curYear }, false);
				}
				return;
			}
			var q = new URL(location.href).searchParams;
			if (mode === 'monthly') {
				load({
					type: q.get('vartype') || curType,
					start: q.get('start_year_rep') || curStart,
					summary: q.get('summary_type') || curSummary
				}, false);
			} else {
				load({ type: q.get('vartype') || curType, year: q.get('year') || curYear }, false);
			}
		});

		syncNavVartype();
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