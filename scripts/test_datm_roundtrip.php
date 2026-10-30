<?php
/**
 * datm CSV round-trip test (CLI). Exit 0 = pass.
 *   php scripts/test_datm_roundtrip.php [dir-with-datm-copies]
 * Uses a temp copy; never writes to the given directory.
 */
error_reporting(E_ALL);
$site = __DIR__ . '/../site/';
require_once $site . 'compat.php';
require_once $site . 'cron/DatmWriter.php';

$fail = 0;
function check($ok, $msg) {
	global $fail;
	echo ($ok ? 'ok   ' : 'FAIL ') . $msg . "\n";
	if (!$ok) { $fail++; }
}
/** Rewrite every line the way the admin pages do: read, edit nothing, write back. */
function rewrite_once(array $lines) {
	$out = array();
	foreach ($lines as $i => $line) {
		$out[] = $i === 0 ? $line : DatmWriter::formatLine(DatmWriter::parseLine($line));
	}
	return $out;
}

$header = implode(',', DatmWriter::datmHeaders()) . "\n";
$fixture = array(
	$header,
	"6.6,3,u,1,0.5,,,,\"am rn? pm sun\",,,1,2.5,0\n",
	"0,6,u,,,,,,blr,,,1,11.5,\"\\n\"\n",
	"8,0,u,,,,,,-,,\"\"\"sample note; more\"\"\",1,20,0\n",
	"0,5,u,2,,,,,a comment with spaces,,,1,4,0\n",
);

// 1. Fields come back unquoted and stable.
$f = DatmWriter::parseLine($fixture[1]);
check($f[8] === 'am rn? pm sun', 'quoted comment parses to its text');
$f = DatmWriter::parseLine($fixture[3]);
check($f[10] === 'sample note; more', 'already-doubled comment parses to its text');
check(count(DatmWriter::parseLine($fixture[4])) === 14, 'unquoted row keeps 14 fields');

// 2. A round trip is idempotent (old code grew quotes 2n+1 per save).
$once = rewrite_once($fixture);
$twice = rewrite_once($once);
$many = $once;
for ($k = 0; $k < 20; $k++) { $many = rewrite_once($many); }
check($once === $twice && $once === $many, '1, 2 and 21 rewrites give identical lines');
check(strpos(implode('', $many), '"') === false, 'no quotes after rewrites');

// 3. Input commas / quotes / newlines cannot break the row.
$line = DatmWriter::formatLine(array('1', '0', 'u', '', '', '', '', '', "a, \"b\"\nc", '', '', '1', '5', '0'));
check(substr_count($line, ',') === 13 && substr_count($line, "\n") === 1, 'user text cannot add fields or lines');

// 4. Optional: real copies of the data files.
if (isset($argv[1])) {
	$src = rtrim($argv[1], '/') . '/';
	$tmp = sys_get_temp_dir() . '/datmtest' . getmypid() . '/';
	@mkdir($tmp);
	foreach (glob($src . 'datm*.csv') as $path) {
		$name = basename($path);
		copy($path, $tmp . $name);
		$lines = file($tmp . $name);
		$a = rewrite_once($lines);
		$b = rewrite_once($a);
		check($a === $b, "$name: round trip idempotent (" . filesize($path) . " B -> " . strlen(implode('', $a)) . " B)");
		check(count($a) === count($lines), "$name: same line count");
		unlink($tmp . $name);
	}
	@rmdir($tmp);
}

echo $fail ? "$fail FAILED\n" : "all passed\n";
exit($fail ? 1 : 0);
