<?php 
// If first day of year/season, default to the previous year
	if (( date("n") == $season_start) AND (date("j") == 1) AND ($show_today != true)) {
		$year = date("Y")-1;
	}
	else { $year = date("Y"); }

// Build an array of years available assumming data is available from the first year thru the current year.
$years_available[0] = $year;
$max = $year - $first_year_of_data;
for ( $i = 0; $i < $max ; $i ++ ) {	$years_available[$i+1] = (string)($year - $i-1); }

//Adjust with passed variables
if(isset($_COOKIE['year'])) { $year = intval($_COOKIE['year']); }
if(isset($_GET['year'])) { $year = intval($_GET['year']); }
?>