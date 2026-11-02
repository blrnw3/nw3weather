<?php
/**
 * Recap - short written weather summaries (yesterday, month so far, year so far, last month, a whole year).
 *
 * The text is written elsewhere and posted to api_recap.php with a bearer token. Each report is one JSON file
 * per (kind, period) in NW3_RECAP_DIR, outside the docroot:  <dir>/<kind>/<period>.json
 *
 *   kind           period       allowed (London calendar, today = T)
 *   day            YYYY-MM-DD   2009-01-01 .. T
 *   month_to_date  YYYY-MM      2009-01 .. this month
 *   month          YYYY-MM      2009-01 .. last month (a finished month)
 *   year_to_date   YYYY         2009 .. this year
 *   year           YYYY         2009 .. last year (a finished year)
 *
 * Payload (JSON object): kind, period, body_md, generated_at (ISO 8601 with a zone) and, for the *_to_date
 * kinds, an optional "through" (YYYY-MM-DD, the last day covered). Unknown keys are ignored.
 *
 * body_md is a tiny markdown subset, parsed into blocks and rendered with every text node escaped:
 * paragraphs, "-"/"*"/"+" or "1." lists, **bold** and [text](https://...) links. Anything else stays text.
 */
if (!defined('NW3_RECAP_DIR')) {
	define('NW3_RECAP_DIR', '/var/www/nw3-recap/');
}

class RecapError extends Exception {
	public $status;
	function __construct($status, $msg) {
		parent::__construct($msg);
		$this->status = $status;
	}
}

class Recap {
	const KINDS = ['day', 'month_to_date', 'year_to_date', 'month', 'year'];
	const FIELDS = ['kind', 'period', 'body_md', 'generated_at', 'through'];
	const MAX_BODY = 1500;
	const MAX_LINES = 30;
	const MAX_BYTES = 8192;
	const MAX_FUTURE = 600;
	const FIRST_YEAR = 2009;
	const DAY_FRESH_DAYS = 2;
	const MAX_URL = 500;

	private static function tz() {
		return new DateTimeZone('Europe/London');
	}

	/** London calendar date (Y-m-d) of a unix time. */
	static function today($now = null) {
		$d = new DateTime('@' . ($now === null ? time() : (int)$now));
		$d->setTimezone(self::tz());
		return $d->format('Y-m-d');
	}

	private static function bad($msg) {
		return new RecapError(400, $msg);
	}

	/** [y, m, d] of a period (m/d = 1 for coarser kinds); throws on a bad format. */
	static function parsePeriod($kind, $period) {
		if (!is_string($period)) {
			throw self::bad('period must be a string');
		}
		if ($kind === 'day') {
			if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $period, $m) || !checkdate((int)$m[2], (int)$m[3], (int)$m[1])) {
				throw self::bad("period for kind 'day' must be a calendar date YYYY-MM-DD");
			}
			return [(int)$m[1], (int)$m[2], (int)$m[3]];
		}
		if ($kind === 'month' || $kind === 'month_to_date') {
			if (!preg_match('/^(\d{4})-(\d{2})$/', $period, $m) || (int)$m[2] < 1 || (int)$m[2] > 12) {
				throw self::bad("period for kind '$kind' must be YYYY-MM");
			}
			return [(int)$m[1], (int)$m[2], 1];
		}
		if (!preg_match('/^(\d{4})$/', $period, $m)) {
			throw self::bad("period for kind '$kind' must be YYYY");
		}
		return [(int)$m[1], 1, 1];
	}

	static function isKind($kind) {
		return is_string($kind) && in_array($kind, self::KINDS, true);
	}

	/** Whether a (valid) period is allowed on London date $today. Returns an error message or null. */
	static function periodError($kind, $p, $today) {
		list($ty, $tm) = array_map('intval', explode('-', $today));
		if ($p[0] < self::FIRST_YEAR) {
			return 'period is before the station records (2009)';
		}
		$pm = $p[0] * 12 + $p[1];
		$tmIdx = $ty * 12 + $tm;
		switch ($kind) {
			case 'day':
				return (sprintf('%04d-%02d-%02d', $p[0], $p[1], $p[2]) > $today) ? "period is after today ($today, London)" : null;
			case 'month_to_date':
				return ($pm > $tmIdx) ? 'period is after this month' : null;
			case 'month':
				return ($pm >= $tmIdx) ? "kind 'month' is for a finished month" : null;
			case 'year_to_date':
				return ($p[0] > $ty) ? 'period is after this year' : null;
			case 'year':
				return ($p[0] >= $ty) ? "kind 'year' is for a finished year" : null;
		}
		return 'unknown kind';
	}

	static function cleanBody($raw) {
		if (!is_string($raw)) {
			throw self::bad('body_md must be a string');
		}
		if (!mb_check_encoding($raw, 'UTF-8')) {
			throw self::bad('body_md must be valid UTF-8');
		}
		$body = str_replace(["\r\n", "\r", "\t"], ["\n", "\n", ' '], $raw);
		$body = implode("\n", array_map('rtrim', explode("\n", $body)));
		$body = preg_replace("/\n{3,}/", "\n\n", trim($body));
		if ($body === '') {
			throw self::bad('body_md must not be empty');
		}
		if (class_exists('Normalizer')) {
			$n = Normalizer::normalize($body, Normalizer::FORM_C);
			if ($n !== false) { $body = $n; }
		}
		$len = mb_strlen($body, 'UTF-8');
		if ($len > self::MAX_BODY) {
			throw self::bad("body_md is $len characters; at most " . self::MAX_BODY);
		}
		if (substr_count($body, "\n") + 1 > self::MAX_LINES) {
			throw self::bad('body_md has more than ' . self::MAX_LINES . ' lines');
		}
		// Control characters (other than newline), surrogates, and bidi overrides/isolates.
		if (preg_match('/[\x{0000}-\x{0009}\x{000B}-\x{001F}\x{007F}-\x{009F}\x{202A}-\x{202E}\x{2066}-\x{2069}]/u', $body, $m)) {
			throw self::bad(sprintf('body_md contains a control character (U+%04X)', mb_ord($m[0], 'UTF-8')));
		}
		return $body;
	}

	/** Unix time of an ISO 8601 timestamp that carries a zone. */
	static function parseGeneratedAt($raw, $now) {
		if (!is_string($raw) || $raw === '') {
			throw self::bad('generated_at must be an ISO 8601 string with a zone');
		}
		if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(:\d{2}(\.\d+)?)?(Z|[+-]\d{2}:?\d{2})$/', $raw)) {
			throw self::bad('generated_at must be ISO 8601 with a zone (Z or an offset), e.g. 2026-11-02T07:31:00Z');
		}
		try {
			$t = new DateTime($raw);
		} catch (Exception $e) {
			throw self::bad('bad generated_at');
		}
		$ts = $t->getTimestamp();
		if ($ts > $now + self::MAX_FUTURE) {
			throw self::bad('generated_at is in the future');
		}
		return $ts;
	}

	/** Normalised record plus the list of ignored keys. */
	static function validate($body, $now) {
		if (!is_array($body) || array_is_list($body)) {
			throw self::bad('body must be a JSON object');
		}
		$kind = $body['kind'] ?? null;
		if (!self::isKind($kind)) {
			throw self::bad('kind must be one of ' . implode(', ', self::KINDS));
		}
		$p = self::parsePeriod($kind, $body['period'] ?? null);
		$today = self::today($now);
		$err = self::periodError($kind, $p, $today);
		if ($err !== null) {
			throw self::bad($err);
		}
		$rec = [
			'kind' => $kind,
			'period' => self::periodKey($kind, $p),
			'body_md' => self::cleanBody($body['body_md'] ?? null),
			'generated_at' => gmdate('Y-m-d\TH:i:s\Z', self::parseGeneratedAt($body['generated_at'] ?? null, $now)),
		];
		if (isset($body['through']) && $body['through'] !== null && $body['through'] !== '') {
			if ($kind !== 'month_to_date' && $kind !== 'year_to_date') {
				throw self::bad("through is only for month_to_date and year_to_date");
			}
			$t = self::parsePeriod('day', $body['through']);
			$tk = self::periodKey('day', $t);
			$inPeriod = ($t[0] === $p[0]) && ($kind === 'year_to_date' || $t[1] === $p[1]);
			if (!$inPeriod || $tk > $today) {
				throw self::bad('through must be a day inside the period and not after today');
			}
			$rec['through'] = $tk;
		}
		$ignored = array_values(array_diff(array_keys($body), self::FIELDS));
		sort($ignored);
		return ['record' => $rec, 'ignored' => $ignored];
	}

	static function periodKey($kind, $p) {
		if ($kind === 'day') { return sprintf('%04d-%02d-%02d', $p[0], $p[1], $p[2]); }
		if ($kind === 'month' || $kind === 'month_to_date') { return sprintf('%04d-%02d', $p[0], $p[1]); }
		return sprintf('%04d', $p[0]);
	}

	// ---------------------------------------------------------------- storage

	/** The storage dir (with a trailing slash), or null when it has not been set up. */
	static function dir() {
		$d = rtrim(NW3_RECAP_DIR, '/') . '/';
		return is_dir($d) ? $d : null;
	}

	private static function path($dir, $kind, $period) {
		return $dir . $kind . '/' . $period . '.json';
	}

	/** One stored record, or null. */
	static function get($kind, $period) {
		$dir = self::dir();
		if ($dir === null || !self::isKind($kind)) {
			return null;
		}
		try {
			$p = self::parsePeriod($kind, $period);
		} catch (RecapError $e) {
			return null;
		}
		$f = self::path($dir, $kind, self::periodKey($kind, $p));
		if (!is_file($f)) {
			return null;
		}
		$rec = json_decode((string)@file_get_contents($f), true);
		if (!is_array($rec) || !isset($rec['body_md'], $rec['period']) || ($rec['kind'] ?? null) !== $kind) {
			return null;
		}
		return $rec;
	}

	/** 'inserted', 'updated' or 'unchanged' (same text, through and generated_at: nothing written). */
	static function upsert($rec, $now) {
		$dir = self::dir();
		if ($dir === null || !is_writable($dir)) {
			throw new RecapError(503, 'recap storage is not set up on this server');
		}
		$kdir = $dir . $rec['kind'] . '/';
		if (!is_dir($kdir) && !@mkdir($kdir, 02775) && !is_dir($kdir)) {
			throw new RecapError(503, 'recap storage is not writable');
		}
		$lock = @fopen($dir . '.lock', 'c');
		if ($lock === false || !flock($lock, LOCK_EX)) {
			throw new RecapError(503, 'recap storage is busy');
		}
		try {
			$f = self::path($dir, $rec['kind'], $rec['period']);
			$old = is_file($f) ? json_decode((string)file_get_contents($f), true) : null;
			$same = is_array($old)
				&& ($old['body_md'] ?? null) === $rec['body_md']
				&& ($old['generated_at'] ?? null) === $rec['generated_at']
				&& ($old['through'] ?? null) === ($rec['through'] ?? null);
			if ($same) {
				return 'unchanged';
			}
			$out = $rec;
			$out['updated_at'] = gmdate('Y-m-d\TH:i:s\Z', $now);
			$json = json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
			if (is_array($old)) {
				@copy($f, $kdir . $rec['period'] . '.prev.json');
			}
			$tmp = $f . '.tmp.' . getmypid();
			if (file_put_contents($tmp, $json) === false || !rename($tmp, $f)) {
				@unlink($tmp);
				throw new RecapError(503, 'recap storage write failed');
			}
			@chmod($f, 0664);
			return is_array($old) ? 'updated' : 'inserted';
		} finally {
			flock($lock, LOCK_UN);
			fclose($lock);
		}
	}

	/** Stored periods of a kind, oldest first. */
	static function periods($kind) {
		$dir = self::dir();
		if ($dir === null || !self::isKind($kind) || !is_dir($dir . $kind)) {
			return [];
		}
		$out = [];
		foreach (scandir($dir . $kind) as $f) {
			if (preg_match('/^(\d{4}(?:-\d{2}){0,2})\.json$/', $f, $m)) {
				try {
					$p = self::parsePeriod($kind, $m[1]);
					if (self::periodKey($kind, $p) === $m[1]) { $out[] = $m[1]; }
				} catch (RecapError $e) {
				}
			}
		}
		sort($out, SORT_STRING);
		return $out;
	}

	static function latest($kind) {
		$ps = self::periods($kind);
		return $ps ? self::get($kind, end($ps)) : null;
	}

	/** Whether an entry should still be shown on the home page on London date $today. */
	static function isFresh($kind, $period, $today) {
		try {
			$p = self::parsePeriod($kind, $period);
		} catch (RecapError $e) {
			return false;
		}
		list($ty, $tm) = array_map('intval', explode('-', $today));
		switch ($kind) {
			case 'day':
				$min = date('Y-m-d', strtotime($today . ' 12:00 -' . self::DAY_FRESH_DAYS . ' days'));
				$k = self::periodKey('day', $p);
				return $k >= $min && $k <= $today;
			case 'month_to_date':
				return $p[0] === $ty && $p[1] === $tm;
			case 'year_to_date':
				return $p[0] === $ty;
			case 'month':
				$prev = ($tm === 1) ? [$ty - 1, 12] : [$ty, $tm - 1];
				return $p[0] === $prev[0] && $p[1] === $prev[1];
			case 'year':
				return $p[0] === $ty - 1;
		}
		return false;
	}

	/** The period a fresh entry would normally carry on $today. */
	static function expected($kind, $today) {
		list($ty, $tm) = array_map('intval', explode('-', $today));
		switch ($kind) {
			case 'day': return date('Y-m-d', strtotime($today . ' 12:00 -1 day'));
			case 'month_to_date': return sprintf('%04d-%02d', $ty, $tm);
			case 'year_to_date': return sprintf('%04d', $ty);
			case 'month': return ($tm === 1) ? sprintf('%04d-12', $ty - 1) : sprintf('%04d-%02d', $ty, $tm - 1);
			case 'year': return sprintf('%04d', $ty - 1);
		}
		return null;
	}

	/** Human label for an entry, e.g. "Sunday 1 November 2026", "1–14 November 2026", "October 2026". */
	static function label($rec) {
		$kind = $rec['kind'];
		$p = self::parsePeriod($kind, $rec['period']);
		$through = $rec['through'] ?? null;
		if ($through === null && ($kind === 'month_to_date' || $kind === 'year_to_date') && !empty($rec['generated_at'])) {
			// Written in the morning about the days up to yesterday.
			$g = self::today(strtotime($rec['generated_at']));
			$y = date('Y-m-d', strtotime($g . ' 12:00 -1 day'));
			$t = self::parsePeriod('day', $y);
			if ($t[0] === $p[0] && ($kind === 'year_to_date' || $t[1] === $p[1])) { $through = $y; }
		}
		$mon = date('F', mktime(12, 0, 0, $p[1], 1, $p[0]));
		switch ($kind) {
			case 'day':
				return date('l j F Y', mktime(12, 0, 0, $p[1], $p[2], $p[0]));
			case 'month':
				return "$mon {$p[0]}";
			case 'year':
				return (string)$p[0];
			case 'month_to_date':
				if ($through !== null) {
					$d = (int)substr($through, 8, 2);
					return ($d === 1 ? "1 $mon" : "1–$d $mon") . " {$p[0]}";
				}
				return "$mon {$p[0]} so far";
			case 'year_to_date':
				if ($through !== null) {
					$t = self::parsePeriod('day', $through);
					return "1 January – " . date('j F', mktime(12, 0, 0, $t[1], $t[2], $t[0])) . " {$p[0]}";
				}
				return "{$p[0]} so far";
		}
		return $rec['period'];
	}

	/** "Mon 2 Nov, 07:31 GMT" for a stored ISO time. */
	static function writtenLabel($iso) {
		$t = strtotime((string)$iso);
		if ($t === false) { return ''; }
		$d = new DateTime('@' . $t);
		$d->setTimezone(self::tz());
		return $d->format('D j M, H:i T');
	}

	// ---------------------------------------------------------------- markdown subset

	/** The URL if it is a plain absolute http(s) URL with a host and no credentials, else null. */
	static function safeUrl($url) {
		if (!is_string($url) || $url === '' || strlen($url) > self::MAX_URL || preg_match('/[\x00-\x20\x7f<>"\'`\\\\]/', $url)) {
			return null;
		}
		$p = parse_url($url);
		if ($p === false || empty($p['scheme']) || empty($p['host'])) {
			return null;
		}
		if (!in_array(strtolower($p['scheme']), ['http', 'https'], true) || isset($p['user']) || isset($p['pass'])) {
			return null;
		}
		$rest = substr($url, strlen($p['scheme']) + 3);
		if (substr($url, strlen($p['scheme']), 3) !== '://' || strpos(explode('/', $rest, 2)[0], '@') !== false) {
			return null;
		}
		return $url;
	}

	private static function addText(&$out, $v) {
		if ($v === '') { return; }
		$n = count($out);
		if ($n && $out[$n - 1]['t'] === 'text') {
			$out[$n - 1]['v'] .= $v;
		} else {
			$out[] = ['t' => 'text', 'v' => $v];
		}
	}

	static function parseInline($s, $boldOk = true) {
		$link = '/\[([^\[\]\n]{1,200})\]\(([^()\s]{1,' . self::MAX_URL . '})\)/u';
		$bold = '/\*\*(?=\S)(.+?)(?<=\S)\*\*/u';
		$out = [];
		$pos = 0;
		$len = strlen($s);
		while ($pos < $len) {
			$lm = preg_match($link, $s, $l, PREG_OFFSET_CAPTURE, $pos) ? $l : null;
			$bm = ($boldOk && preg_match($bold, $s, $b, PREG_OFFSET_CAPTURE, $pos)) ? $b : null;
			$m = null; $isBold = false;
			if ($lm && (!$bm || $lm[0][1] <= $bm[0][1])) { $m = $lm; }
			elseif ($bm) { $m = $bm; $isBold = true; }
			if ($m === null) {
				self::addText($out, substr($s, $pos));
				break;
			}
			self::addText($out, substr($s, $pos, $m[0][1] - $pos));
			if ($isBold) {
				$out[] = ['t' => 'b', 'c' => self::parseInline($m[1][0], false)];
			} else {
				$href = self::safeUrl($m[2][0]);
				if ($href !== null) {
					$out[] = ['t' => 'a', 'href' => $href, 'v' => $m[1][0]];
				} else {
					self::addText($out, $m[0][0]);
				}
			}
			$pos = $m[0][1] + strlen($m[0][0]);
		}
		return $out;
	}

	/** Blocks: [{t: p, c: inlines} | {t: ul|ol, items: [inlines]}]. */
	static function parse($md) {
		$blocks = [];
		$cur = null; // [type, parts]
		$close = function () use (&$cur, &$blocks) {
			if ($cur) {
				if ($cur[0] === 'p') {
					$blocks[] = ['t' => 'p', 'c' => Recap::parseInline(implode(' ', $cur[1]))];
				} else {
					$blocks[] = ['t' => $cur[0], 'items' => array_map(function ($x) { return Recap::parseInline($x); }, $cur[1])];
				}
			}
			$cur = null;
		};
		foreach (explode("\n", str_replace(["\r\n", "\r"], "\n", (string)$md)) as $raw) {
			$line = rtrim($raw);
			if (trim($line) === '') {
				$close();
				continue;
			}
			if (preg_match('/^ {0,3}(?:([-*+])|(\d{1,2})[.)])[ \t]+(\S.*)$/u', $line, $m)) {
				$type = ($m[1] !== '') ? 'ul' : 'ol';
				if (!$cur || $cur[0] !== $type) {
					$close();
					$cur = [$type, []];
				}
				$cur[1][] = trim($m[3]);
			} elseif ($cur && $cur[0] !== 'p' && ($raw[0] === ' ' || $raw[0] === "\t")) {
				$cur[1][count($cur[1]) - 1] .= ' ' . trim($line);
			} else {
				if (!$cur || $cur[0] !== 'p') {
					$close();
					$cur = ['p', []];
				}
				$cur[1][] = trim($line);
			}
		}
		$close();
		return $blocks;
	}

	private static function esc($s) {
		return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
	}

	/** Keep a number with its unit on one line ("12.5 °C", "577 mm", "42.6 mph"). */
	private static function keepUnits($s) {
		return preg_replace('/(\d) (?=(?:°C|°F|mm|cm|in|hPa|mb|mph|km\/h|knots|h|hours|µg\/m³|%)(?![\p{L}\d]))/u', "$1\u{00A0}", $s) ?? $s;
	}

	private static function inlineHtml($nodes) {
		$out = '';
		foreach ($nodes as $n) {
			if ($n['t'] === 'text') {
				$out .= self::esc(self::keepUnits($n['v']));
			} elseif ($n['t'] === 'b') {
				$out .= '<strong>' . self::inlineHtml($n['c']) . '</strong>';
			} elseif ($n['t'] === 'a' && self::safeUrl($n['href']) !== null) {
				$ext = !preg_match('#^https?://(www\.)?nw3weather\.co\.uk(/|$)#i', $n['href']);
				$out .= '<a href="' . self::esc($n['href']) . '" rel="noopener nofollow"' . ($ext ? ' target="_blank"' : '') . '>'
					. self::esc($n['v']) . '</a>';
			}
		}
		return $out;
	}

	static function toHtml($blocks) {
		$out = [];
		foreach ($blocks as $b) {
			if ($b['t'] === 'p') {
				$out[] = '<p>' . self::inlineHtml($b['c']) . '</p>';
			} else {
				$items = '';
				foreach ($b['items'] as $i) { $items .= '<li>' . self::inlineHtml($i) . '</li>'; }
				$out[] = '<' . $b['t'] . '>' . $items . '</' . $b['t'] . '>';
			}
		}
		return implode("\n", $out);
	}

	static function render($md) {
		return self::toHtml(self::parse($md));
	}

	/** Public form of a record (for the JSON API). */
	static function publicRecord($rec, $today) {
		$out = [
			'kind' => $rec['kind'],
			'period' => $rec['period'],
			'label' => self::label($rec),
			'body_md' => $rec['body_md'],
			'blocks' => self::parse($rec['body_md']),
			'generated_at' => $rec['generated_at'] ?? null,
			'fresh' => self::isFresh($rec['kind'], $rec['period'], $today),
		];
		if (isset($rec['through'])) { $out['through'] = $rec['through']; }
		if (isset($rec['updated_at'])) { $out['updated_at'] = $rec['updated_at']; }
		return $out;
	}

	// ---------------------------------------------------------------- page fragments

	/** One report as an <article> (escaped HTML), for the monthly / annual report pages. */
	static function articleHtml($rec, $heading = null) {
		$h = $heading === null ? self::label($rec) : $heading;
		return '<article class="recap-entry recap-' . self::esc($rec['kind']) . '">'
			. '<div class="recap-period">' . self::esc($h) . '</div>'
			. '<div class="recap-body">' . self::render($rec['body_md']) . '</div>'
			. '<div class="recap-meta">Written ' . self::esc(self::writtenLabel($rec['generated_at'] ?? '')) . '</div>'
			. '</article>';
	}

	/** "October 2026" for a YYYY-MM key, "2026" for a year. */
	private static function shortLabel($kind, $period) {
		$p = self::parsePeriod($kind, $period);
		return ($kind === 'month' || $kind === 'month_to_date') ? date('M Y', mktime(12, 0, 0, $p[1], 1, $p[0])) : (string)$p[0];
	}

	/**
	 * The written report for a month on wxhistmonth.php: the finished month's report (or, for the current month,
	 * the month-so-far one), previous / next written reports and the other written reports for that year.
	 * Empty until the storage dir exists.
	 */
	static function monthSection($y, $m, $isCurrent) {
		if (self::dir() === null) { return ''; }
		$key = sprintf('%04d-%02d', $y, $m);
		$kind = $isCurrent ? 'month_to_date' : 'month';
		$rec = self::get($kind, $key);
		$all = self::periods('month');
		if ($rec === null && !$all) { return ''; }
		$h = '<section class="recap-report" id="written-report"><h2>Written report</h2>';
		$h .= $rec ? self::articleHtml($rec) : '<p class="recap-missing">No written report for ' . self::esc(date('F Y', mktime(12, 0, 0, $m, 1, $y))) . ' yet.</p>';
		$prev = null; $next = null;
		foreach ($all as $p) {
			if ($p < $key) { $prev = $p; }
			if ($p > $key && $next === null) { $next = $p; }
		}
		$link = function ($p, $text, $rel) {
			list($yy, $mm) = array_map('intval', explode('-', $p));
			return '<a href="wxhistmonth.php?year=' . $yy . '&amp;month=' . $mm . '#written-report" rel="' . $rel . '">' . $text . '</a>';
		};
		$nav = [];
		if ($prev) { $nav[] = $link($prev, '&lsaquo; ' . self::esc(self::shortLabel('month', $prev)), 'prev'); }
		if ($next) { $nav[] = $link($next, self::esc(self::shortLabel('month', $next)) . ' &rsaquo;', 'next'); }
		if ($nav) { $h .= '<p class="recap-nav">' . implode(' <span aria-hidden="true">&middot;</span> ', $nav) . '</p>'; }
		$inYear = array_values(array_filter($all, function ($p) use ($y) { return (int)substr($p, 0, 4) === (int)$y; }));
		if ($inYear) {
			$items = [];
			foreach ($inYear as $p) {
				$lab = self::esc(date('M', mktime(12, 0, 0, (int)substr($p, 5, 2), 1, $y)));
				$items[] = ($p === $key && !$isCurrent) ? '<b>' . $lab . '</b>' : $link($p, $lab, 'nofollow');
			}
			$h .= '<p class="recap-others">Written reports for ' . (int)$y . ': ' . implode(' ', $items);
			$yrKind = ($y < (int)substr(self::today(), 0, 4)) ? 'year' : 'year_to_date';
			if (self::get($yrKind, (string)$y)) {
				$h .= ' &middot; <a href="wxhistyear.php?year=' . (int)$y . '#written-report">' . ($yrKind === 'year' ? 'the year' : 'the year so far') . '</a>';
			}
			$h .= '</p>';
		}
		return $h . '</section>';
	}

	/** The written report for a year on wxhistyear.php (the year so far for the current year), with year links. */
	static function yearSection($y) {
		if (self::dir() === null) { return ''; }
		$thisYear = (int)substr(self::today(), 0, 4);
		$kind = ($y >= $thisYear) ? 'year_to_date' : 'year';
		$rec = self::get($kind, (string)$y);
		$all = self::periods('year');
		if ($rec === null && !$all) { return ''; }
		$h = '<section class="recap-report" id="written-report"><h2>Written report</h2>';
		$h .= $rec ? self::articleHtml($rec) : '<p class="recap-missing">No written report for ' . (int)$y . ' yet.</p>';
		if ($all) {
			$items = [];
			foreach ($all as $p) {
				$items[] = ((int)$p === (int)$y) ? '<b>' . self::esc($p) . '</b>'
					: '<a href="wxhistyear.php?year=' . (int)$p . '#written-report">' . self::esc($p) . '</a>';
			}
			$h .= '<p class="recap-others">Written annual reports: ' . implode(' ', $items) . '</p>';
		}
		$months = array_values(array_filter(self::periods('month'), function ($p) use ($y) { return (int)substr($p, 0, 4) === (int)$y; }));
		if ($months) {
			$items = [];
			foreach ($months as $p) {
				$mm = (int)substr($p, 5, 2);
				$items[] = '<a href="wxhistmonth.php?year=' . (int)$y . '&amp;month=' . $mm . '#written-report">' . self::esc(date('M', mktime(12, 0, 0, $mm, 1, $y))) . '</a>';
			}
			$h .= '<p class="recap-others">Monthly reports for ' . (int)$y . ': ' . implode(' ', $items) . '</p>';
		}
		return $h . '</section>';
	}

	/** Home-page card entries: the latest fresh entry of each home kind (empty when none, or no storage). */
	static function homeEntries($today = null) {
		$today = $today ?: self::today();
		$out = [];
		if (self::dir() === null) { return $out; }
		foreach (['day', 'month_to_date', 'year_to_date', 'month'] as $k) {
			$r = self::latest($k);
			if ($r && self::isFresh($k, $r['period'], $today)) { $out[$k] = $r; }
		}
		return $out;
	}
}
