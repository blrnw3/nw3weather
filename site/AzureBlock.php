<?php
/**
 * Early, cheap block of Microsoft Azure public-cloud client IPs.
 *
 * Ranges come from site/config/azure_cidrs.php (Azure Service Tags Public,
 * tag AzureCloud, with Bing/Microsoft search-crawler ranges removed).
 * Lookup is a binary search over precomputed packed start/end bounds.
 *
 * Only REMOTE_ADDR is trusted. On local/dev, X-NW3-Test-IP may override
 * so the block can be exercised without spoofing the socket address.
 */

function nw3_azure_cidrs_path() {
	return __DIR__ . '/config/azure_cidrs.php';
}

/**
 * Load and pack CIDR lists once per process.
 * @return array array('v4' => array(array($start,$end), ...), 'v6' => ...)
 */
function nw3_azure_ranges() {
	static $ranges = null;
	if ($ranges !== null) {
		return $ranges;
	}
	$ranges = array('v4' => array(), 'v6' => array());
	$path = nw3_azure_cidrs_path();
	if (!is_readable($path)) {
		return $ranges;
	}
	$data = include $path;
	if (!is_array($data)) {
		return $ranges;
	}
	foreach (array('v4', 'v6') as $fam) {
		$list = isset($data[$fam]) ? $data[$fam] : array();
		$packed = array();
		for ($i = 0, $n = count($list); $i < $n; $i++) {
			$bounds = nw3_cidr_bounds($list[$i]);
			if ($bounds !== null) {
				$packed[] = $bounds;
			}
		}
		usort($packed, function ($a, $b) {
			return strcmp($a[0], $b[0]);
		});
		$ranges[$fam] = $packed;
	}
	return $ranges;
}

/**
 * @param string $cidr
 * @return array|null array($startPacked, $endPacked)
 */
function nw3_cidr_bounds($cidr) {
	$pos = strrpos($cidr, '/');
	if ($pos === false) {
		return null;
	}
	$ip = substr($cidr, 0, $pos);
	$bits = (int)substr($cidr, $pos + 1);
	$packed = @inet_pton($ip);
	if ($packed === false || $packed === '') {
		return null;
	}
	$len = strlen($packed); // 4 or 16
	$maxBits = $len * 8;
	if ($bits < 0 || $bits > $maxBits) {
		return null;
	}
	$mask = '';
	$full = (int)($bits / 8);
	$rem = $bits % 8;
	if ($full > 0) {
		$mask .= str_repeat("\xff", $full);
	}
	if ($rem > 0) {
		$mask .= chr((0xff << (8 - $rem)) & 0xff);
		$full++;
	}
	if ($full < $len) {
		$mask .= str_repeat("\x00", $len - $full);
	}
	$start = $packed & $mask;
	$inv = '';
	for ($i = 0; $i < $len; $i++) {
		$inv .= chr((~ord($mask[$i])) & 0xff);
	}
	$end = $start | $inv;
	return array($start, $end);
}

/**
 * Binary search of sorted packed [start, end] ranges.
 * @param string $packed inet_pton() form
 * @param array $ranges
 * @return bool
 */
function nw3_ip_in_packed_ranges($packed, $ranges) {
	$lo = 0;
	$hi = count($ranges) - 1;
	while ($lo <= $hi) {
		$mid = (int)(($lo + $hi) / 2);
		if (strcmp($packed, $ranges[$mid][0]) < 0) {
			$hi = $mid - 1;
		} elseif (strcmp($packed, $ranges[$mid][1]) > 0) {
			$lo = $mid + 1;
		} else {
			return true;
		}
	}
	return false;
}

/**
 * Normalise a client address (strip IPv4-mapped IPv6) and test the blocklist.
 * @param string $ip
 * @return bool
 */
function nw3_ip_is_azure($ip) {
	if ($ip === null || $ip === '') {
		return false;
	}
	$ip = trim($ip);
	// IPv4-mapped IPv6, e.g. ::ffff:20.245.1.1
	if (stripos($ip, ':ffff:') !== false) {
		$maybe = substr($ip, strrpos($ip, ':') + 1);
		if (filter_var($maybe, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			$ip = $maybe;
		}
	}
	if (strpos($ip, '.') !== false && strpos($ip, ':') !== false) {
		// Some stacks use ::ffff:20.245.1.1 already handled; ignore junk.
	}
	$packed = @inet_pton($ip);
	if ($packed === false || $packed === '') {
		return false;
	}
	$ranges = nw3_azure_ranges();
	$fam = (strlen($packed) === 16) ? 'v6' : 'v4';
	return nw3_ip_in_packed_ranges($packed, $ranges[$fam]);
}

/**
 * Client IP for the Azure check: REMOTE_ADDR only, plus a local-dev override.
 * @param bool $isLocal
 * @return string
 */
function nw3_azure_client_ip($isLocal) {
	if ($isLocal && !empty($_SERVER['HTTP_X_NW3_TEST_IP'])) {
		$cand = $_SERVER['HTTP_X_NW3_TEST_IP'];
		if (filter_var($cand, FILTER_VALIDATE_IP)) {
			return $cand;
		}
	}
	return isset($_SERVER['REMOTE_ADDR']) ? (string)$_SERVER['REMOTE_ADDR'] : '';
}

/**
 * Exit with 403 if the client is in a blocked Azure range.
 * @param bool $isLocal
 * @return void
 */
function nw3_azure_maybe_block($isLocal) {
	$ip = nw3_azure_client_ip($isLocal);
	if ($ip === '' || !nw3_ip_is_azure($ip)) {
		return;
	}
	while (ob_get_level() > 0) {
		ob_end_clean();
	}
	http_response_code(403);
	header('Content-Type: text/plain; charset=utf-8');
	header('Cache-Control: no-store');
	header('Connection: close');
	echo "Forbidden\n";
	exit;
}
