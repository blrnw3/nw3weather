<?php
define('ROOT','/tmp/');
require dirname(__FILE__) . '/../../../site/SunLog.php';
$cases=json_decode(file_get_contents('/tmp/pt_cases.json'),true); $bad=0; $n=0;
foreach($cases as $c){ $rows=array(); foreach($c['rows'] as $r){ $rows[]=array('w'=>(float)$r[0],'pw'=>(float)$r[1],'v'=>$r[2]===null?null:(int)$r[2]); }
 $p=SunLog::partialTotal($rows); $e=$c['exp']; $n++;
 if($e['sun_h']===null){ if($p!==null){$bad++;} continue; }
 if($p===null || abs($p['hours']-$e['sun_h'])>0.011 || abs($p['unknown']-$e['unknown_h'])>0.011 || $p['partial']!==$e['partial'] || abs($p['coverage']-$e['coverage'])>0.001){$bad++; if($bad<4){echo json_encode(array($p,$e)),"\n";}}
}
echo "cases $n mismatches $bad\n";
