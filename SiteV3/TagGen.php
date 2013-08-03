<?php

// ################## Rain Tags #################### //
$rawRn = '$yestrn, $rainweek, $rain31, $rain365, $monthrnF, $yearrnF, $seasonrnF, $drywetdays,
		$raindays_monthF, $raindays_yearF, $day_rain_last_year,
		$raintodmonthago, $raintodayearago, $drywet, $maxhourrn, $maxrainratehr,
		$mrecorddailyrain, $mrecorddailyhrmax, $mrecorddailyrate, $mrecwetlength, $mrecdrylength,
		$yrecorddailyrain, $yrecorddailyhrmax, $yrecorddailyrate, $yrecwetlength, $yrecdrylength,
		$yrmonthrn_max, $yrmonthrn_min, $yrndays_max, $yrndays_min,
		$maxhourrnyest, $maxrainrateyest, $maxhourrnt, $maxrainratetime,
		$mrecorddailyraindate, $mrecorddailyhrmaxdate, $mrecorddailyratedate, $mrecwetlengthdate, $mrecdrylengthdate,
		$yrecorddailyraindate, $yrecorddailyhrmaxdate, $yrecorddailyratedate, $yrecwetlengthdate, $yrecdrylengthdate,
		$yrmonthrn_maxdate, $yrmonthrn_mindate, $yrndays_maxdate, $yrndays_mindate,
		$maxhourrnyesttime,$maxrainrateyesttime,
		$maxrainD, $maxhrmaxD, $maxrateD,
		$maxrainMD, $maxhrmaxMD, $maxrateMD, $recwetlength_curr, $recdrylength_curr, $monthrn_max_curr,
		$monthrn_min_curr, $rndays_max_curr, $rndays_min_curr,
		$recorddailyrain, $recorddailyhrmax, $recorddailyrate, $recwetlength, $recdrylength, $monthrn_max, $monthrn_min,
		$rndays_max, $rndays_min, $maxrainDdate, $maxhrmaxDdate, $maxrateDdate,
		$maxrainMDdate, $maxhrmaxMDdate, $maxrateMDdate, $recwetlength_currdate, $recdrylength_currdate,
		$monthrn_max_currdate, $monthrn_min_currdate, $rndays_max_currdate, $rndays_min_currdate,
		$recorddailyraindate, $recorddailyhrmaxdate, $recorddailyratedate, $recwetlengthdate,
		$recdrylengthdate, $monthrn_maxdate, $monthrn_mindate, $rndays_maxdate, $rndays_mindate,
		$wettestyr, $driestyr, $wettest31, $driest31, $wettest365, $driest365,
		$wettestyrdate, $driestyrdate, $wettest31date, $driest31date, $wettest365date, $driest365date,
		$ranknum, $ranknumM, $lymrain, $lymrain1, $lymrain2,

		$wettest_day, $wettest, $wettest_dayM, $wettestM, $raintots, $driestM, $driest_dayM';

$tagsRn = explode(',', preg_replace('/\s+/','',$rawRn));

echo "<pre>'";

foreach($tagsRn as $tag) {
	echo $tag . " = '.var_export($tag, true).';\r\n";
}
echo "'</pre><br />";


// ################## Temperature Tags #################### //
$rawTp = '$avtempsincemidnight, $nighttimeMin, $daytimeMax, $nighttimeMinT, $daytimeMaxT,
		$tdatday, $tdatdaydate, $tdatdayanom,
		$tdatyest, $tdatyestdate, $tdatyestanom,
		$tdat, $tdatMM, $tdatSS, $tdatSSanom,
		$hrsfrostmidnight, $daysTminL0C, $daysTminyearL0C, $last24houravtemp';

$tagsTp = explode(',', preg_replace('/\s+/','',$rawTp));

echo "<pre>\$output =
'";

foreach($tagsTp as $tag) {
	echo $tag . " = '.var_export($tag, true).';\r\n";
}
echo "';</pre><br />";

// $raw = '$yestrn, $rainweek, $rain31, $rain365, $monthrnF, $yearrnF, $seasonrnF, $drywetdays,
// 		 $raindays_monthF, $raindays_yearF, $day_rain_last_year,
// $raintodmonthago, $raintodayearago, $drywet, $maxhourrn, $maxrainratehr,
// 		$mrecorddailyrain, $mrecorddailyhrmax, $mrecorddailyrate, $mrecwetlength, $mrecdrylength,
// 		$yrecorddailyrain, $yrecorddailyhrmax, $yrecorddailyrate, $yrecwetlength, $yrecdrylength,
// 		 $yrmonthrn_max, $yrmonthrn_min, $yrndays_max, $yrndays_min,
// 		 $maxhourrnyest, $maxrainrateyest, $maxhourrnt, $maxrainratetime,
// 		$mrecorddailyraindate, $mrecorddailyhrmaxdate, $mrecorddailyratedate, $mrecwetlengthdate, $mrecdrylengthdate,
// 		$yrecorddailyraindate, $yrecorddailyhrmaxdate, $yrecorddailyratedate, $yrecwetlengthdate, $yrecdrylengthdate,
// 		 $yrmonthrn_maxdate, $yrmonthrn_mindate, $yrndays_maxdate, $yrndays_mindate,
// 		$maxhourrnyesttime,$maxrainrateyesttime,
// 		$maxrainD, $maxhrmaxD, $maxrateD,
// 		$maxrainMD, $maxhrmaxMD, $maxrateMD, $recwetlength_curr, $recdrylength_curr, $monthrn_max_curr,
// 		$monthrn_min_curr, $rndays_max_curr, $rndays_min_curr,
// 		$recorddailyrain, $recorddailyhrmax, $recorddailyrate, $recwetlength, $recdrylength, $monthrn_max, $monthrn_min,
// 		$rndays_max, $rndays_min, $maxrainDdate, $maxhrmaxDdate, $maxrateDdate,
// 		$maxrainMDdate, $maxhrmaxMDdate, $maxrateMDdate, $recwetlength_currdate, $recdrylength_currdate,
// 		 $monthrn_max_currdate, $monthrn_min_currdate, $rndays_max_currdate, $rndays_min_currdate,
// 		$recorddailyraindate, $recorddailyhrmaxdate, $recorddailyratedate, $recwetlengthdate,
// 		 $recdrylengthdate, $monthrn_maxdate, $monthrn_mindate, $rndays_maxdate, $rndays_mindate,
// 		$wettestyr, $driestyr, $wettest31, $driest31, $wettest365, $driest365, $record24hrrain,
// 		$wettestyrdate, $driestyrdate, $wettest31date, $driest31date, $wettest365date, $driest365date,
// 		$ranknum, $ranknumM, $lymrain, $lymrain1, $lymrain2';

// $rawArrays = '$wettest_day, $wettest, $wettest_dayM, $wettestM, $raintots, $driestM, $driest_dayM';


// $tags = explode(',', preg_replace('/\s+/','',$raw));
// $tagsArrays = explode(',', preg_replace('/\s+/','',$rawArrays));

// echo "<pre>'";
// foreach($tags as $tag) {
// 	echo $tag . " = \''.".$tag . ".'\';\r\n";
// }

// echo "\r\n";

// foreach($tagsArrays as $tagArray) {
// 	echo $tagArray . " = '.var_export($tagArray, true).';\r\n";
// }
// echo "</pre>";


?>