<?php
/**
 * One-off: reset a non-numeric last ("spare") datm field to 0.
 *
 * DatmWriter::write used to append the literal text \n (CSV-quoted) as the spare field instead of 0. Only
 * lines whose last field is not an integer are rewritten (spare -> 0, every other field untouched); all other
 * lines stay byte-for-byte. The file is rewritten in place (owner and mode kept) after a backup copy.
 *
 * Usage (docroot = directory containing datmYYYY.csv):
 *   php fix_datm_spare.php /var/www/html [--year=2026]                  # dry run: report only
 *   php fix_datm_spare.php /var/www/html [--year=2026] --apply [--backup-dir=DIR]
 *
 * Safe to re-run: a fixed file has no such lines.
 */
error_reporting(E_ALL);
require_once __DIR__ . '/../site/cron/DatmWriter.php';

$root = isset($argv[1]) && !str_starts_with($argv[1], '--') ? rtrim($argv[1], '/') . '/' : __DIR__ . '/../site/';
$apply = in_array('--apply', $argv, true);
$year = date('Y');
$backupDir = (getenv('HOME') ?: '/tmp') . '/nw3-datm-backup';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--backup-dir=')) { $backupDir = rtrim(substr($arg, 13), '/'); }
	if (str_starts_with($arg, '--year=')) { $year = (int)substr($arg, 7); }
}
$path = $root . 'datm' . $year . '.csv';
$raw = file_get_contents($path);
if ($raw === false) { fwrite(STDERR, "cannot read $path\n"); exit(1); }

$lines = preg_split('/(?<=\n)/', $raw, -1, PREG_SPLIT_NO_EMPTY);
$cols = count(DatmWriter::datmHeaders());
$changed = 0;
foreach ($lines as $i => $line) {
	if ($i === 0) { continue; }
	$fields = DatmWriter::parseLine($line);
	$last = count($fields) - 1;
	if (count($fields) !== $cols) {
		echo '  SKIP line ' . ($i + 1) . ': ' . count($fields) . " fields\n";
		continue;
	}
	if (preg_match('/^-?\d+$/', $fields[$last])) { continue; }
	$day = date('Y-m-d', mktime(0, 0, 0, 1, $i, $year));
	$fields[$last] = '0';
	$new = DatmWriter::formatLine($fields);
	echo '  line ' . ($i + 1) . " ($day): " . rtrim($line) . '  ->  ' . rtrim($new) . "\n";
	$lines[$i] = $new;
	$changed++;
}
echo basename($path) . ": $changed line(s) to fix\n";
if (!$apply || $changed === 0) {
	echo $apply ? "nothing to do\n" : "dry run; add --apply to write\n";
	exit(0);
}
if (!is_dir($backupDir) && !mkdir($backupDir, 0700, true)) { fwrite(STDERR, "cannot create $backupDir\n"); exit(1); }
$backup = $backupDir . '/' . basename($path) . '.' . date('Ymd-His');
if (!copy($path, $backup) || filesize($backup) !== strlen($raw)) { fwrite(STDERR, "backup failed\n"); exit(1); }
if (!DatmWriter::writeLines($path, $lines)) { fwrite(STDERR, "write failed (backup: $backup)\n"); exit(1); }
echo "written; backup $backup\n";
