<?php
// The hand-written annual reports now live on the annual report page, one year at a
// time (annual-reports/YYYY.php). This old page redirects to the latest of them;
// a repyear.php#report-YYYY link keeps its fragment and wxhistyear.php opens that year.
$years = array_map(function ($f) { return (int)basename($f, '.php'); }, glob(__DIR__ . '/annual-reports/[0-9][0-9][0-9][0-9].php') ?: []);
$year = isset($_GET['year']) ? intval($_GET['year']) : ($years ? max($years) : 0);
header('Location: /wxhistyear.php' . ($year ? '?year=' . $year : ''), true, 301);
exit;
