/*
 * Client-side hydrate for historical data-table JSON (wxdataday first).
 * PHP sends display-ready text + CSS classes; this stamps the existing grid markup.
 */
(function (window) {
	'use strict';

	var NW3 = window.NW3 || (window.NW3 = {});

	var HIST_NOTE = 'Data from before 2009 are mostly from the historical site at Whitestone Pond in Hampstead. '
		+ 'Where data from that record is missing, other nearby sites were used, including St James Park, Heathrow, and Kew Gardens (pre-1910). '
		+ 'Best efforts have been made to adjust for site differences, but uncertainties are somewhat greater for this data. '
		+ 'I am grateful to the Met Office for making this data available for free through the ';

	function el(tag, className, text) {
		var node = document.createElement(tag);
		if (className) { node.className = className; }
		if (text != null && text !== '') { node.textContent = text; }
		return node;
	}

	function ddLab(text, extraClass, href) {
		var lab = el('div', 'dd-lab' + (extraClass ? ' ' + extraClass : ''));
		if (href) {
			var a = el('a', 'hidden-link', text);
			a.href = href;
			a.title = 'View detailed report for month';
			lab.appendChild(a);
		} else {
			lab.textContent = text;
		}
		return lab;
	}

	function ddCell(cell, extraClass, href) {
		var cls = 'dd-cell ' + (cell.c || 'reportday');
		if (extraClass) { cls += ' ' + extraClass; }
		var node = el('div', cls);
		var text = cell.t != null ? String(cell.t) : '';
		if (href) {
			var a = el('a', 'hidden-link', text);
			a.href = href;
			a.title = 'View detailed report for day';
			node.appendChild(a);
		} else if (text) {
			node.textContent = text;
		}
		if (cell.a) {
			node.appendChild(document.createElement('br'));
			node.appendChild(document.createTextNode('(' + cell.a + ')'));
		}
		return node;
	}

	function appendFooter(body, footer) {
		if (!footer) { return; }
		var p = document.createElement('p');
		p.appendChild(document.createTextNode(footer.blurb || ''));
		if (footer.anomNote) {
			p.appendChild(document.createElement('br'));
			p.appendChild(document.createTextNode('Figures in brackets refer to departure from '));
			var strong = document.createElement('strong');
			strong.textContent = 'recent';
			p.appendChild(strong);
			p.appendChild(document.createTextNode(' '));
			var avg = el('a', null, 'average conditions');
			avg.href = '/wxaverages.php';
			avg.title = 'Long-term NW3 climate averages';
			p.appendChild(avg);
			p.appendChild(document.createTextNode('.'));
		}
		if (footer.qcNote) {
			p.appendChild(document.createElement('br'));
			p.appendChild(document.createTextNode(
				'Values for recent days are subject to quality control and may be adjusted at any time.'
			));
		}
		body.appendChild(p);

		if (footer.histNote) {
			var note = el('p', 'hist-note');
			note.appendChild(document.createTextNode('*' + HIST_NOTE));
			var midas = el('a', null, 'MIDAS Open database');
			midas.href = 'https://data.ceda.ac.uk/badc/ukmo-midas-open/';
			note.appendChild(midas);
			note.appendChild(document.createTextNode('.'));
			body.appendChild(note);
		}

		if (footer.about) {
			var about = el('p', 'report-var-about', footer.about);
			about.id = 'report-var-about';
			body.appendChild(about);
		}
	}

	function hydrateDaily(payload, body) {
		if (!payload || !payload.grid || !body) { throw new Error('bad daily payload'); }
		var grid = payload.grid;
		var year = grid.year;
		var dayLinks = !!grid.dayLinks;
		var months = grid.months || [];
		var frag = document.createDocumentFragment();

		var scroll = el('div', 'dd-scroll');
		var table = el('div', 'dd-grid');

		var head = el('div', 'dd-row dd-head');
		head.appendChild(ddLab('Day'));
		months.forEach(function (mon, i) {
			var href = mon.link ? ('/wxhistmonth.php?year=' + year + '&month=' + (i + 1)) : null;
			head.appendChild(ddLab(mon.name, mon.future ? 'dd-future-lab' : '', href));
		});
		table.appendChild(head);

		(grid.rows || []).forEach(function (row) {
			var day = row.day;
			var line = el('div', 'dd-row');
			line.appendChild(el('div', 'dd-day', String(day)));
			(row.cells || []).forEach(function (cell, i) {
				var cls = cell.c || '';
				var href = (dayLinks && cls !== 'noday' && cls !== 'dd-future')
					? ('/wxhistday.php?year=' + year + '&month=' + (i + 1) + '&day=' + day)
					: null;
				line.appendChild(ddCell(cell, '', href));
			});
			table.appendChild(line);
		});

		var sep = el('div', 'dd-row dd-head dd-sep');
		sep.appendChild(ddLab(''));
		months.forEach(function (mon) {
			sep.appendChild(ddLab(mon.name, mon.future ? 'dd-future-lab' : ''));
		});
		table.appendChild(sep);

		(grid.sums || []).forEach(function (sum) {
			var line = el('div', 'dd-row dd-sum');
			if (sum.labelBr) {
				var lab = el('div', 'dd-lab dd-sum-lab');
				lab.appendChild(document.createTextNode('Cumu-'));
				lab.appendChild(document.createElement('br'));
				lab.appendChild(document.createTextNode('lative'));
				line.appendChild(lab);
			} else {
				line.appendChild(ddLab(sum.label, 'dd-sum-lab'));
			}
			(sum.cells || []).forEach(function (cell) {
				line.appendChild(ddCell(cell, 'dd-sum-cell', null));
			});
			table.appendChild(line);
		});

		scroll.appendChild(table);
		frag.appendChild(scroll);
		body.textContent = '';
		body.appendChild(frag);
		appendFooter(body, payload.footer);
	}

	NW3.hydrateDaily = hydrateDaily;
	NW3.hydrateReport = function (payload, body) {
		if (payload && payload.mode === 'daily') {
			hydrateDaily(payload, body);
			return;
		}
		throw new Error('unsupported report mode');
	};
})(window);
