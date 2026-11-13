<?php
/**
 * Curate the home-page notable-rankings list into an ordered, grouped top 10.
 *
 * Priority: temperature, rain and sun, plus observations (fog, snow, hail,
 * thunder); then wind; then everything else. Esoteric stats are dropped.
 * Long series and 1st-place ranks are preferred; duplicates (anomalies vs
 * absolute temperatures, day max / night min vs max / min, sun % vs sun
 * hours, full-period items vs the MTD/YTD equivalent) are removed, and
 * related records for the same period are grouped onto one line.
 *
 * An optional LLM classification (NW3_LLM_API_KEY in secrets.php) runs at
 * most once a calendar day, bundled with the daily recap-pack job and also
 * attempted from the rank-flags warmer if today's plan is not yet in cache.
 * When the key is missing or the call fails, a deterministic fallback that
 * implements the same priorities is used (and can be recomputed cheaply).
 */
class RankFlagsCurate {
	const FILE = 'rank_flags_curated.json';
	const VERSION = 1;
	const MAX_GROUPS = 10;
	const HIGHLIGHT_RANK = 3;
	const LLM_TIMEOUT = 20;

	/** Variables dropped as esoteric / unfriendly on a home-page card. */
	private static $drop = [
		'prange', 'hrange',
		'ratemax', 'ratemean', '10max', 'hrmax',
		'fmin', 'fmax', 'fmean',
		'tc10min', 'tc10max', 'tchrmin', 'tchrmax', 'hchrmin', 'hchrmax',
		'dmin', 'dmax', 'dmean',
	];

	/** Absolute / clearer variable that makes this one a duplicate. */
	private static $dupOf = [
		'tminanom' => 'tmin', 'tmaxanom' => 'tmax', 'tmeananom' => 'tmean',
		'nightmin' => 'tmin', 'daymax' => 'tmax',
		'sunhrp' => 'sunhr',
	];

	/** Same-period grouping families (items in one family share a line). */
	private static $family = [
		'tmin' => 'temp', 'tmax' => 'temp', 'tmean' => 'temp',
		'tminanom' => 'anom', 'tmaxanom' => 'anom', 'tmeananom' => 'anom',
		'fmin' => 'feels', 'fmax' => 'feels', 'fmean' => 'feels',
		'pmin' => 'press', 'pmean' => 'press', 'pmax' => 'press',
	];

	private static $familyOrder = [
		'temp' => ['tmin', 'tmax', 'tmean'],
		'anom' => ['tminanom', 'tmaxanom', 'tmeananom'],
		'feels' => ['fmin', 'fmax', 'fmean'],
		'press' => ['pmean', 'pmin', 'pmax'],
	];

	public static function file() {
		return CACHE_ROOT . self::FILE;
	}

	public static function today() {
		if (class_exists('Date', false) && !empty(Date::$dyear)) {
			return sprintf('%04d-%02d-%02d', Date::$dyear, Date::$dmonth, Date::$dday);
		}
		return date('Y-m-d');
	}

	/** Stable id for one qualifying item, e.g. "tmean|ytd". */
	public static function itemId($it) {
		return $it['var'] . '|' . self::periodKey($it);
	}

	public static function periodKey($it) {
		$lab = isset($it['label']) ? (string)$it['label'] : '';
		if (strpos($lab, 'month-to-date') !== false) { return 'mtd'; }
		if (strpos($lab, 'year-to-date') !== false) { return 'ytd'; }
		$kind = isset($it['kind']) ? $it['kind'] : 'other';
		if ($kind === 'month' || $kind === 'year') { return $kind; }
		if ($kind === 'day') { return 'day:' . $lab; }
		if ($kind === 'period') { return 'period:' . $lab; }
		if ($kind === 'spell') { return 'spell:' . $lab . ':' . (isset($it['spellDir']) ? $it['spellDir'] : ''); }
		return $kind;
	}

	/**
	 * Build and cache today's plan from $data (a RankFlags::build() result).
	 * Tries the LLM at most once per London calendar day when a key is set;
	 * otherwise (or on failure) writes the deterministic fallback.
	 * @return array curated plan
	 */
	public static function refresh($data) {
		$items = (isset($data['items']) && is_array($data['items'])) ? $data['items'] : [];
		$existing = self::loadPlan();
		$today = self::today();
		if ($existing && $existing['date'] === $today && $existing['source'] === 'llm') {
			return $existing;
		}
		$plan = null;
		$key = self::apiKey();
		if ($key !== '') {
			$plan = self::classifyLlm($items, $key);
		}
		if (!$plan) {
			$plan = self::fallback($items);
		}
		self::write($plan);
		return $plan;
	}

	/** Cached plan for today, or null. */
	public static function loadPlan() {
		$f = self::file();
		if (!is_readable($f)) { return null; }
		$plan = json_decode((string)@file_get_contents($f), true);
		if (!is_array($plan) || !isset($plan['date'], $plan['groups'], $plan['version'])) { return null; }
		if ((int)$plan['version'] !== self::VERSION) { return null; }
		if ($plan['date'] !== self::today()) { return null; }
		return $plan;
	}

	/**
	 * Resolve a plan against the current item list (fresh values/ranks).
	 * Missing ids are dropped; leftover highlights can be appended from the
	 * fallback so a new notable event the same day is not hidden.
	 * @return array list of groups, each ['items' => [...], 'headline' => ?string]
	 */
	public static function apply($plan, $items) {
		$byId = [];
		foreach ($items as $it) { $byId[self::itemId($it)] = $it; }
		$used = [];
		$out = [];
		if ($plan && !empty($plan['groups']) && is_array($plan['groups'])) {
			foreach ($plan['groups'] as $g) {
				if (count($out) >= self::MAX_GROUPS) { break; }
				$ids = (isset($g['ids']) && is_array($g['ids'])) ? $g['ids'] : [];
				$groupItems = [];
				foreach ($ids as $id) {
					if (!is_string($id) || !isset($byId[$id]) || isset($used[$id])) { continue; }
					$groupItems[] = $byId[$id];
					$used[$id] = true;
				}
				if (!$groupItems) { continue; }
				$headline = null;
				if (!empty($g['headline']) && is_string($g['headline'])) {
					$headline = self::sanitizeHeadline($g['headline']);
				}
				$out[] = ['items' => $groupItems, 'headline' => $headline];
			}
		}
		if (count($out) < self::MAX_GROUPS) {
			$fb = self::fallback($items);
			foreach ($fb['groups'] as $g) {
				if (count($out) >= self::MAX_GROUPS) { break; }
				$groupItems = [];
				foreach ($g['ids'] as $id) {
					if (!isset($byId[$id]) || isset($used[$id])) { continue; }
					$groupItems[] = $byId[$id];
					$used[$id] = true;
				}
				if ($groupItems) { $out[] = ['items' => $groupItems, 'headline' => null]; }
			}
		}
		return $out;
	}

	/** Deterministic plan implementing the same priorities as the LLM prompt. */
	public static function fallback($items) {
		$candidates = self::candidates($items);
		usort($candidates, [self::class, 'cmp']);
		$index = [];
		foreach ($candidates as $i => $it) { $index[self::itemId($it)] = $i; }
		$used = [];
		$groups = [];
		foreach ($candidates as $it) {
			$id = self::itemId($it);
			if (isset($used[$id])) { continue; }
			$memberIds = self::familyMembers($it, $candidates);
			$ids = [];
			foreach ($memberIds as $mid) {
				if (!isset($used[$mid])) {
					$ids[] = $mid;
					$used[$mid] = true;
				}
			}
			if ($ids) { $groups[] = ['ids' => $ids, 'headline' => null]; }
			if (count($groups) >= self::MAX_GROUPS) { break; }
		}
		return [
			'version' => self::VERSION,
			'date' => self::today(),
			'generated' => time(),
			'source' => 'fallback',
			'model' => null,
			'groups' => $groups,
		];
	}

	/** Filter, drop esoteric items, and remove duplicates. */
	public static function candidates($items) {
		$ids = [];
		foreach ($items as $it) { $ids[self::itemId($it)] = true; }
		$out = [];
		foreach ($items as $it) {
			$var = $it['var'];
			if (in_array($var, self::$drop, true)) { continue; }
			$pk = self::periodKey($it);
			if (isset(self::$dupOf[$var])) {
				$abs = self::$dupOf[$var] . '|' . $pk;
				if (isset($ids[$abs])) { continue; }
			}
			if ($pk === 'month' && isset($ids[$var . '|mtd'])) { continue; }
			if ($pk === 'year' && isset($ids[$var . '|ytd'])) { continue; }
			$out[] = $it;
		}
		return $out;
	}

	/** Priority band: 0 core temp/rain, 1 sun, 2 observations, 3 other temp, 4 wind, 5 other, 6 leftover esoteric. */
	public static function band($var) {
		if (in_array($var, ['tmin', 'tmax', 'tmean'], true)) { return 0; }
		if (in_array($var, ['rain', 'wethr'], true) || strpos($var, 'rdays') !== false) { return 0; }
		if (in_array($var, ['sunhr', 'sunhrp'], true)) { return 1; }
		if (in_array($var, ['fog', 'snow', 'lysnw', 'hail', 'thunder'], true)) { return 2; }
		if (in_array($var, ['trange', 'nightmin', 'daymax', 'tminanom', 'tmaxanom', 'tmeananom', 'afhrs'], true)) { return 3; }
		if (in_array($var, ['wmax', 'gust', 'w10max', 'wmean', 'w10', 'wind'], true) || $var === 'wdir') { return 4; }
		if (in_array($var, self::$drop, true)) { return 6; }
		return 5;
	}

	private static function cmp($a, $b) {
		// Highlights (rank <= 3) only jump ahead inside temp/rain/sun/obs/wind,
		// not "everything else" (pressure etc.), so a 10th-sunniest long series
		// still precedes a 2nd-highest short-series pressure record.
		$ha = ($a['rank'] <= self::HIGHLIGHT_RANK && self::band($a['var']) <= 4) ? 0 : 1;
		$hb = ($b['rank'] <= self::HIGHLIGHT_RANK && self::band($b['var']) <= 4) ? 0 : 1;
		if ($ha !== $hb) { return $ha - $hb; }
		$ba = self::band($a['var']);
		$bb = self::band($b['var']);
		if ($ba !== $bb) { return $ba - $bb; }
		if ($a['rank'] !== $b['rank']) { return $a['rank'] - $b['rank']; }
		if ($a['n'] !== $b['n']) { return $b['n'] - $a['n']; }
		$pa = self::periodTie($a);
		$pb = self::periodTie($b);
		if ($pa !== $pb) { return $pa - $pb; }
		return strcmp(self::itemId($a), self::itemId($b));
	}

	/** On a tie, month-to-date before year-to-date (matches the agreed card order). */
	private static function periodTie($it) {
		$pk = self::periodKey($it);
		if ($pk === 'mtd') { return 0; }
		if ($pk === 'ytd') { return 1; }
		if ($pk === 'month') { return 2; }
		if ($pk === 'year') { return 3; }
		return 4;
	}

	private static function familyMembers($it, $candidates) {
		$pk = self::periodKey($it);
		$fam = isset(self::$family[$it['var']]) ? self::$family[$it['var']] : $it['var'];
		$ids = [];
		if (isset(self::$familyOrder[$fam])) {
			$want = array_flip(self::$familyOrder[$fam]);
			$found = [];
			foreach ($candidates as $c) {
				if (self::periodKey($c) === $pk && isset($want[$c['var']])) {
					$found[$c['var']] = self::itemId($c);
				}
			}
			foreach (self::$familyOrder[$fam] as $v) {
				if (isset($found[$v])) { $ids[] = $found[$v]; }
			}
			return $ids ?: [self::itemId($it)];
		}
		return [self::itemId($it)];
	}

	public static function formatValue($it) {
		if (!empty($it['count'])) {
			return ((int)$it['value'] . ' day' . ((int)$it['value'] === 1 ? '' : 's'));
		}
		return Wx::plainText(Wx::conv($it['value'], $it['unit'], true));
	}

	public static function rankText($it) {
		$scope = ['day' => 'days', 'month' => 'months', 'year' => 'years', 'period' => 'periods', 'spell' => 'spells'];
		$kind = isset($it['kind']) ? $it['kind'] : 'year';
		$word = $it['word'];
		$n = number_format($it['n']);
		$rank = ($it['tied'] ? '=' : '') . Report::ordinal($it['rank']) . ' ' . $word;
		$out = $rank . ' of ' . $n . ' ' . (isset($scope[$kind]) ? $scope[$kind] : $kind);
		if (!empty($it['since'])) { $out .= ' since ' . (int)$it['since']; }
		$out .= ' (' . Report::pctText($it['pct']) . ')';
		return $out;
	}

	/** Shared rank text when every item in the group has the same rank/n/word/since. */
	public static function groupRankText($groupItems) {
		$first = $groupItems[0];
		foreach ($groupItems as $it) {
			if ($it['rank'] !== $first['rank'] || $it['n'] !== $first['n'] || $it['word'] !== $first['word']
				|| !empty($it['tied']) !== !empty($first['tied'])
				|| (int)$it['since'] !== (int)$first['since']) {
				return null;
			}
		}
		return self::rankText($first);
	}

	public static function periodPhrase($it) {
		$pk = self::periodKey($it);
		if ($pk === 'mtd') { return 'month to date'; }
		if ($pk === 'ytd') { return 'year to date'; }
		if ($pk === 'month' || $pk === 'year' || $pk === 'day' || strpos($pk, 'day:') === 0) {
			return strtolower((string)$it['label']);
		}
		return (string)$it['label'];
	}

	/**
	 * Deterministic headline (without values). Values are appended at render time.
	 * Optional $hint is a sanitized LLM headline; used when it looks usable.
	 */
	public static function headline($groupItems, $hint = null) {
		if ($hint) { return $hint; }
		if (count($groupItems) === 1) { return self::headlineOne($groupItems[0]); }
		$fam = isset(self::$family[$groupItems[0]['var']]) ? self::$family[$groupItems[0]['var']] : null;
		if ($fam === 'temp') { return self::headlineTemp($groupItems); }
		if ($fam === 'press') { return self::headlinePress($groupItems); }
		// Generic: list short names.
		$period = self::periodPhrase($groupItems[0]);
		$names = [];
		foreach ($groupItems as $it) { $names[] = strtolower($it['desc']); }
		return self::andList($names) . ', ' . $period;
	}

	private static function headlineTemp($items) {
		$by = [];
		foreach ($items as $it) { $by[$it['var']] = $it; }
		$parts = [];
		if (isset($by['tmin'])) { $parts[] = 'min'; }
		if (isset($by['tmax'])) { $parts[] = 'max'; }
		if (isset($by['tmean'])) { $parts[] = 'mean'; }
		$what = self::andList($parts) . ' temperature';
		$period = self::periodPhrase($items[0]);
		$dirs = [];
		$ranks = [];
		$allFirst = true;
		foreach ($items as $it) {
			$dirs[$it['dir']] = true;
			$ranks[$it['rank']] = true;
			if ($it['rank'] !== 1 || !empty($it['tied'])) { $allFirst = false; }
		}
		$on = $allFirst ? ' on record' : '';
		if (count($dirs) === 1 && count($ranks) === 1) {
			$dir = $items[0]['dir'];
			$adj = ($dir === 'hi') ? 'warmest' : 'coldest';
			$rank = (int)$items[0]['rank'];
			$prefix = ($rank === 1 && empty($items[0]['tied'])) ? ucfirst($adj) : (Report::ordinal($rank) . ' ' . $adj);
			return $prefix . ' ' . $period . $on . ' for ' . $what;
		}
		return ucfirst($what) . ', ' . $period;
	}

	private static function headlinePress($items) {
		$by = [];
		foreach ($items as $it) { $by[$it['var']] = $it; }
		$parts = [];
		if (isset($by['pmean'])) { $parts[] = 'mean'; }
		if (isset($by['pmin'])) { $parts[] = 'minimum'; }
		if (isset($by['pmax'])) { $parts[] = 'maximum'; }
		$period = self::periodPhrase($items[0]);
		$allFirst = true;
		$dirs = [];
		$ranks = [];
		foreach ($items as $it) {
			$dirs[$it['dir']] = true;
			$ranks[$it['rank']] = true;
			if ($it['rank'] !== 1 || !empty($it['tied'])) { $allFirst = false; }
		}
		$on = $allFirst ? ' on record' : '';
		$what = self::andList($parts) . ' pressure';
		if (count($dirs) === 1 && count($ranks) === 1) {
			$rank = (int)$items[0]['rank'];
			$word = ($items[0]['dir'] === 'hi') ? 'highest' : 'lowest';
			$prefix = ($rank === 1 && empty($items[0]['tied'])) ? ucfirst($word) : (Report::ordinal($rank) . ' ' . $word);
			return $prefix . ' ' . $what . ' ' . $period . $on;
		}
		return ucfirst($what) . ', ' . $period;
	}

	private static function headlineOne($it) {
		$period = self::periodPhrase($it);
		$rank = (int)$it['rank'];
		$first = ($rank === 1 && empty($it['tied']));
		$on = $first ? ' on record' : '';
		$adj = self::adjective($it);
		$prefix = $first ? ucfirst($adj) : (($it['tied'] ? '=' : '') . Report::ordinal($rank) . ' ' . $adj);
		$noun = self::noun($it);
		// "3rd sunniest year to date", "Most fog days month to date on record"
		if ($noun !== '') {
			return $prefix . ' ' . $noun . ' ' . $period . $on;
		}
		return $prefix . ' ' . $period . $on;
	}

	private static function adjective($it) {
		$var = $it['var'];
		$hi = ($it['dir'] === 'hi');
		if (in_array($var, ['tmin', 'tmax', 'tmean', 'nightmin', 'daymax', 'tminanom', 'tmaxanom', 'tmeananom'], true)) {
			return $hi ? 'warmest' : 'coldest';
		}
		if ($var === 'sunhr' || $var === 'sunhrp') { return $hi ? 'sunniest' : 'dullest'; }
		if (in_array($var, ['fog', 'snow', 'lysnw', 'hail', 'thunder'], true)) { return $hi ? 'most' : 'fewest'; }
		if ($var === 'rain' || $var === 'wethr') { return $hi ? 'wettest' : 'driest'; }
		if ($var === 'trange') { return $hi ? 'largest' : 'smallest'; }
		if (in_array($var, ['pmin', 'pmean', 'pmax'], true)) { return $hi ? 'highest' : 'lowest'; }
		if (in_array($var, ['wmax', 'gust', 'w10max', 'wmean'], true)) { return $hi ? 'windiest' : 'calmest'; }
		return $it['word'];
	}

	private static function noun($it) {
		$var = $it['var'];
		if ($var === 'sunhr') { return ''; } // "3rd sunniest year to date"
		if ($var === 'sunhrp') { return 'sun percentage'; }
		if ($var === 'fog') { return 'fog days'; }
		if ($var === 'snow') { return 'snow days'; }
		if ($var === 'lysnw') { return 'days with lying snow'; }
		if ($var === 'hail') { return 'days with hail'; }
		if ($var === 'thunder') { return 'days with thunder'; }
		if ($var === 'trange') { return 'temperature range'; }
		if ($var === 'tmin') { return 'minimum temperature'; }
		if ($var === 'tmax') { return 'maximum temperature'; }
		if ($var === 'tmean') { return 'mean temperature'; }
		if ($var === 'pmean') { return 'mean pressure'; }
		if ($var === 'pmin') { return 'minimum pressure'; }
		if ($var === 'pmax') { return 'maximum pressure'; }
		if ($var === 'rain') { return ''; }
		return strtolower($it['desc']);
	}

	private static function andList($parts) {
		$parts = array_values($parts);
		$n = count($parts);
		if ($n === 0) { return ''; }
		if ($n === 1) { return $parts[0]; }
		if ($n === 2) { return $parts[0] . ' and ' . $parts[1]; }
		return implode(', ', array_slice($parts, 0, -1)) . ' and ' . $parts[$n - 1];
	}

	/** Render one curated group as an <li>, values linking to the matching ranking pages. */
	public static function renderItem($group) {
		$items = $group['items'];
		$hint = isset($group['headline']) ? $group['headline'] : null;
		$head = self::headline($items, $hint);
		$vals = [];
		$soFar = false;
		$dirs = [];
		$allFirst = true;
		foreach ($items as $it) {
			$val = self::formatValue($it);
			$vals[] = '<a href="' . htmlspecialchars($it['url']) . '" title="Open the ranking">'
				. htmlspecialchars($val) . '</a>';
			if (!empty($it['soFar'])) { $soFar = true; }
			$dirs[$it['dir']] = true;
			if ($it['rank'] !== 1 || !empty($it['tied'])) { $allFirst = false; }
		}
		$dir = $items[0]['dir'];
		$top = $allFirst ? ' rkf-record' : '';
		$rank = self::groupRankText($items);
		$html = '<li class="clim-note rkf-item' . $top . '"><span class="rkf-dot rkf-dot-'
			. htmlspecialchars($dir) . '"></span>'
			. htmlspecialchars($head)
			. ' (' . implode(', ', $vals) . ')';
		if ($rank) {
			$html .= ' &middot; <b>' . htmlspecialchars($rank) . '</b>';
		}
		if ($soFar) { $html .= ' <span class="rkf-prov">provisional</span>'; }
		$html .= '</li>';
		return $html;
	}

	private static function sanitizeHeadline($s) {
		$s = trim(html_entity_decode($s, ENT_QUOTES, 'UTF-8'));
		$s = preg_replace('/\s+/', ' ', $s);
		if ($s === '' || strlen($s) > 180) { return null; }
		if (preg_match('/[<>]|&lt;|https?:|www\./i', $s)) { return null; }
		return $s;
	}

	private static function write($plan) {
		nw3_atomic_write(self::file(), json_encode($plan, JSON_UNESCAPED_UNICODE));
	}

	/** Load secrets.php if needed and return the configured LLM key, or ''. Never logs the key. */
	public static function apiKey() {
		if (!defined('NW3_LLM_API_KEY') && defined('ROOT') && is_readable(ROOT . 'secrets.php')) {
			@require_once ROOT . 'secrets.php';
		}
		if (!defined('NW3_LLM_API_KEY')) { return ''; }
		$key = (string)NW3_LLM_API_KEY;
		if ($key === '' || preg_match('/CHANGE_ME|placeholder|your[_-]?key/i', $key)) { return ''; }
		return $key;
	}

	public static function apiUrl() {
		if (defined('NW3_LLM_API_URL') && NW3_LLM_API_URL) { return (string)NW3_LLM_API_URL; }
		return 'https://api.openai.com/v1/chat/completions';
	}

	public static function apiModel() {
		if (defined('NW3_LLM_MODEL') && NW3_LLM_MODEL) { return (string)NW3_LLM_MODEL; }
		return 'gpt-4o-mini';
	}

	/** True when a usable key is configured (not whether a call has been made). */
	public static function llmConfigured() {
		return self::apiKey() !== '';
	}

	private static function classifyLlm($items, $key) {
		$candidates = self::candidates($items);
		if (!$candidates) { return null; }
		$payload = [];
		foreach ($candidates as $it) {
			$payload[] = [
				'id' => self::itemId($it),
				'var' => $it['var'],
				'desc' => $it['desc'],
				'period' => self::periodKey($it),
				'label' => $it['label'],
				'agg' => isset($it['agg']) ? $it['agg'] : null,
				'value' => self::formatValue($it),
				'dir' => $it['dir'],
				'word' => $it['word'],
				'rank' => $it['rank'],
				'n' => $it['n'],
				'since' => isset($it['since']) ? $it['since'] : null,
				'pct' => $it['pct'],
				'tied' => !empty($it['tied']),
			];
		}
		$system = 'You curate the "Notable in the rankings" card for a personal weather station in Hampstead, London (nw3weather). '
			. 'From the candidate notable records, choose an ordered top 10 (or fewer) and group related records for the same period onto one line. '
			. 'Priority: temperature, rain and sun first, plus observations such as fog and snow; then wind; then everything else. '
			. 'Prefer long historical series (e.g. 146 years) and 1st-place ranks. '
			. 'Do not include esoteric stats (pressure range, averaged maximum or 10-min rain rates, feels-like, 10-min temperature falls, dew point). '
			. 'Duplicates are already removed. Group min/max/mean temperature for the same period; you may group mean and minimum pressure for the same period. '
			. 'Keep month-to-date and year-to-date fog (or other observations) on separate lines. '
			. 'Write a short headline for each group, without numeric values or ranks (those are added later). Style: "Warmest year to date on record for min, max and mean temperature", "3rd sunniest year to date", "Most fog days month to date on record", "3rd smallest temperature range month to date", "10th dullest month to date". '
			. 'Return ONLY JSON: {"groups":[{"ids":["tmin|ytd","tmax|ytd","tmean|ytd"],"headline":"..."}]}. '
			. 'Use only ids from the candidate list. At most 10 groups. No markdown.';
		$user = json_encode(['candidates' => $payload], JSON_UNESCAPED_UNICODE);
		$body = json_encode([
			'model' => self::apiModel(),
			'temperature' => 0,
			'response_format' => ['type' => 'json_object'],
			'messages' => [
				['role' => 'system', 'content' => $system],
				['role' => 'user', 'content' => $user],
			],
		], JSON_UNESCAPED_UNICODE);
		$url = self::apiUrl();
		$ctx = stream_context_create([
			'http' => [
				'method' => 'POST',
				'header' => "Content-Type: application/json\r\nAuthorization: Bearer " . $key . "\r\n",
				'content' => $body,
				'timeout' => self::LLM_TIMEOUT,
				'ignore_errors' => true,
			],
		]);
		$raw = @file_get_contents($url, false, $ctx);
		if ($raw === false || $raw === '') { return null; }
		$resp = json_decode($raw, true);
		if (!is_array($resp)) { return null; }
		$content = '';
		if (isset($resp['choices'][0]['message']['content'])) {
			$content = $resp['choices'][0]['message']['content'];
		} elseif (isset($resp['content'][0]['text'])) { // some OpenAI-compatible shapes
			$content = $resp['content'][0]['text'];
		}
		if (!is_string($content) || $content === '') { return null; }
		$parsed = json_decode($content, true);
		if (!is_array($parsed) && preg_match('/\{.*\}/s', $content, $m)) {
			$parsed = json_decode($m[0], true);
		}
		if (!is_array($parsed) || empty($parsed['groups']) || !is_array($parsed['groups'])) { return null; }
		$allowed = [];
		foreach ($payload as $row) { $allowed[$row['id']] = true; }
		$groups = [];
		$used = [];
		foreach ($parsed['groups'] as $g) {
			if (count($groups) >= self::MAX_GROUPS) { break; }
			if (!is_array($g) || empty($g['ids']) || !is_array($g['ids'])) { continue; }
			$ids = [];
			foreach ($g['ids'] as $id) {
				if (!is_string($id) || !isset($allowed[$id]) || isset($used[$id])) { continue; }
				$ids[] = $id;
				$used[$id] = true;
			}
			if (!$ids) { continue; }
			$hl = (isset($g['headline']) && is_string($g['headline'])) ? self::sanitizeHeadline($g['headline']) : null;
			$groups[] = ['ids' => $ids, 'headline' => $hl];
		}
		if (!$groups) { return null; }
		return [
			'version' => self::VERSION,
			'date' => self::today(),
			'generated' => time(),
			'source' => 'llm',
			'model' => self::apiModel(),
			'groups' => $groups,
		];
	}
}
