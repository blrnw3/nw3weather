<?php
/**
 * File-cache wrapper for nw3_rankperiods_render().
 * Delegates to the shared expensive-page cache + busy cap (cache/v5/reports/).
 */
require_once __DIR__ . '/ExpensivePage.php';

/**
 * @param Report $report
 * @return array meta for AJAX clients (same as nw3_rankperiods_render)
 */
function nw3_rankperiods_cached_render($report) {
	return nw3_expensive_html(
		'rankperiods',
		$report,
		'nw3_rankperiods_render',
		nw3_expensive_key_parts('rankperiods', $report)
	);
}
