#!/usr/bin/env php
<?php
// Local convenience wrapper; production runs /var/www/html/migrate_runtime_layout.php.
$repoRoot = dirname(__DIR__);
if(!getenv('NW3_ROOT')) {
	putenv('NW3_ROOT=' . $repoRoot . '/site');
}
require($repoRoot . '/site/migrate_runtime_layout.php');
