 <?php
 print "START";
 //error_reporting(E_ALL);
//echo "Content:<pre>";
//system("convert -version");
//system("convert jpggroundcam.jpg -compress JPEG -quality 1 im.jpg");
//system("animate *.jpg -debug all");

//var_dump(ini_get('SMTP'));
function urlToArray($url, $timeout = 5) {
	$ctx = stream_context_create( array( 'http'=> array('timeout' => $timeout) ) );
	return file($url, false, $ctx);
}
//echo "</pre>";

// $ext_dat_file = urlToArray('http://weather.stevenjamesgray.com/realtime.txt');
// $ext_dat = $ext_dat_file[0];
// $dat_fields = explode(" ", $ext_dat);
// $temp = $dat_fields[2];
// $humi = $dat_fields[3];

//var_dump($dat_fields);

mail("blr@nw3weather.co.uk", "test php app mail", "this is just a test / hello world");

print "END";
?> 