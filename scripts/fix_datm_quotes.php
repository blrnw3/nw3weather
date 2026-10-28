<?php
/**
 * One-off: collapse stacked CSV quotes in datmYYYY.csv comment fields.
 *
 * Older admin saves re-quoted already-quoted fields, so each save doubled the
 * quotes (1, 3, 7, 15 ...). Only lines holding a doubled quote ("") are
 * rewritten, with the same parse/format the admin pages now use; every other
 * line is left byte-for-byte as it is. Files are rewritten in place (owner and
 * mode kept) after a copy is saved to the backup dir.
 *
 * Usage (docroot = directory containing datmYYYY.csv):
 *   php fix_datm_quotes.php /var/www/html                 # dry run: report only
 *   php fix_datm_quotes.php /var/www/html --apply [--backup-dir=DIR]
 *
 * Safe to re-run: a fixed file has no doubled quotes, so it is skipped.
 */
error_reporting(E_ALL);
require_once __DIR__ . '/../site/cron/DatmWriter.php';

$root = isset($argv[1]) && substr($argv[1], 0, 2) !== '--'
	? rtrim($argv[1], '/') . '/'
	: __DIR__ . '/../site/';
$apply = in_array('--apply', $argv, true);
$backupDir = (getenv('HOME') ?: '/tmp') . '/nw3-datm-backup';
foreach ($argv as $arg) {
	if (strpos($arg, '--backup-dir=') === 0) {
		$backupDir = rtrim(substr($arg, 13), '/');
	}
}

$files = array();
foreach (glob($root . 'datm*.csv') as $path) {
	if (preg_match('/^datm(\d{4})\.csv$/', basename($path))) {
		$files[] = $path;
	}
}
sort($files); // current year last, so the cron re-serialises after all edits

$stamp = date('Ymd-His');
$bad = 0;
$changedFiles = 0;
foreach ($files as $path) {
	$raw = file_get_contents($path);
	if ($raw === false || strpos($raw, '""') === false) {
		continue;
	}
	$lines = preg_split('/(?<=\n)/', $raw, -1, PREG_SPLIT_NO_EMPTY);
	$changed = 0;
	foreach ($lines as $i => $line) {
		if ($i === 0 || strpos($line, '""') === false) {
			continue;
		}
		$before = count(explode(',', rtrim($line, "\r\n")));
		$fields = DatmWriter::parseLine($line);
		$eol = substr($line, -2) === "\r\n" ? "\r\n" : "\n";
		$new = rtrim(DatmWriter::formatLine($fields), "\n") . $eol;
		if (count($fields) !== $before || strpos($new, '"') !== false) {
			echo "  REFUSE line " . ($i + 1) . ": field count or quotes changed\n";
			$bad++;
			continue 2;
		}
		$text = array();
		foreach ($fields as $f) {
			if (preg_match('/[a-z]{2}/i', $f) && $f !== 'blr') {
				$text[] = $f;
			}
		}
		echo '  line ' . ($i + 1) . ': ' . strlen($line) . ' -> ' . strlen($new) . ' B  ' . implode(' | ', $text) . "\n";
		$lines[$i] = $new;
		$changed++;
	}
	$out = implode('', $lines);
	echo basename($path) . ": $changed lines, " . strlen($raw) . ' -> ' . strlen($out) . " B\n";
	if (!$apply || $changed === 0) {
		continue;
	}
	if (!is_dir($backupDir) && !mkdir($backupDir, 0700, true)) {
		fwrite(STDERR, "cannot create $backupDir\n");
		exit(1);
	}
	$backup = $backupDir . '/' . basename($path) . '.' . $stamp;
	if (!copy($path, $backup) || filesize($backup) !== strlen($raw)) {
		fwrite(STDERR, "backup failed for $path\n");
		exit(1);
	}
	$outLines = preg_split('/(?<=\n)/', $out, -1, PREG_SPLIT_NO_EMPTY);
	if (!DatmWriter::writeLines($path, $outLines)) {
		fwrite(STDERR, "write failed for $path (backup: $backup)\n");
		exit(1);
	}
	clearstatcache(true, $path);
	echo "  written; backup $backup\n";
	$changedFiles++;
}
echo $apply ? "applied to $changedFiles file(s)\n" : "dry run; add --apply to write\n";
exit($bad ? 2 : 0);
