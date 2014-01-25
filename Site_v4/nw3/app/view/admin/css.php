<?php
use nw3\app\model\Variable;

foreach (Variable::$daily as $var_name => $var) {
	echo ".$var_name {color:{$var['colour']}}\n";
}

?>

<!--.temp {
	color: #DF7401;
}
.rain {
	color: #3567EF;
}
.sun {
	color: #99910A;
}
.snow {
	color: #68acbd
}-->
