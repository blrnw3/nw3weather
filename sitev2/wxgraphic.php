<?php
/******************************************************************************
* wxgraphic.php v6.3
*
* Weather graphic generator
* Copyright (C) 2007 Anole Computer Services, LLC
* scripts@anolecomputer.com
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*
*******************************************************************************
* This script generates an image in gif, png, or jpg
* format created from a user supplied background image and data file.
*
* The following assumptions are made in this script:
* 1) background image for default named "default.gif", "default.png", or "default.jpeg"
* 2) background image for banner named "banner.gif", "banner.png", "banner.jpeg"
* 3) background image for the big banner named "banner_big.gif", "banner_big.png", or "banner_big.jpeg"
* 4) background image for avatar named "avatar.gif", "avatar.png", or "avatar.jpeg"
* 5) A text data file in the following format:
* time,date,temp,heatindex,wind chill,humidity,dew point,barometer,barometer trend,wind speed,wind direction,todays rain
* OR for VWS with current conditions static-images:
* time,date,temp,heatindex,wind chill,humidity,dew point,barometer,barometer trend,wind speed,wind direction,todays rain,currentconditions,sunrise,sunset
* OR
* Weather Display clientraw.txt data file
*
* Script configuration is contained in the file config.txt. Modify the various
* paramenters in config.txt to suit your needs.
* Please review the included README.TXT file for more details on usage,
* configuration, restrictions and functionality
*******************************************************************************/

//get the configuration file
require "./config.txt";

// see if the data file is a WD clientraw.txt file and set
// the WD flag for those specific routines to be executed.
if (preg_match('|clientraw.txt|', $data_file_path)){
$use_wd_clientraw = '1';
} //end if

// get the data file. If we don't get it the first time we'll make two
// more attempts then output an image that indicates we don't have any
// data.
// This is not very elegant but it seems to work better with less problems
// than a while loop.
@$dataraw = file_get_contents($data_file_path);
if (empty ($dataraw)) {
	sleep(2);
	@$dataraw = file_get_contents($data_file_path);
} // end if
if (empty ($dataraw)) {
	sleep(2);
	@$dataraw = file_get_contents($data_file_path);
} // end if
if (empty ($dataraw)) {
nodataimage("Data Currently", "Unavailable");
} // end if

// clean up any blank lines
$dataraw = trim($dataraw);
$dataraw = preg_replace("/[\r\n]+[\s\t]*[\r\n]+/","\n",$dataraw);

// if we're using the WD clientraw.txt we have a lot of work to do to make it
// pretty. So here we go:

// put the clientraw.txt data into an array and define the data points
$data = explode(" ", $dataraw);
// clean up and define
$baromtrend = "$data[50]";
$winddir = "$data[3]";
(float)$wind = round("$data[1]",$wind_prec);
(float)$gust = round("$data[140]",$wind_prec);
(float)$temp = "$data[4]";
(float)$mintemp = "$data[47]";
(float)$maxtemp = "$data[46]";
(float)$humidity = "$data[5]";
(float)$barom = "$data[6]";
(float)$raintoday = "$data[7]";
(float)$rainmonth = "$data[8]";
(float)$windchill = "$data[44]";
(float)$dewpt = "$data[72]";
(float)$heatindex = "$data[112]";

$time = sprintf ("%02d:%02d", $data[29], $data[30]);
$date = date('d/m/y', mktime(0,0,0,$data[36],$data[35],$data[141]));

// convert windspeeds
// only necessary if we want MPH or KPH
switch (TRUE) {
	case ($wind_conv == 'KPH'):
	$wind = KTStoKPH($wind, $wind_prec);
	$gust = KTStoKPH($gust, $wind_prec);
	break;
	case ($wind_conv == 'MPH'):
	$wind = KTStoMPH($wind, $wind_prec);
	$gust = KTStoMPH($gust, $wind_prec);
	break;
	default:
	$wind = round($wind, $wind_prec);
	$gust = round($gust, $wind_prec);
} // end switch

// convert the barometric pressure
// only necessary if we want in/Hg
if ($barom_conv == 'INCHES') {
	$barom = sprintf("%01.2f", MBtoINCHES($barom, $barom_prec));
} // end if
else {
		$barom = round($barom, $barom_prec);
} // end else

// convert the daily rainfall
// only necessary if we want inches
if ($rain_conv == 'INCHES') {
	$raintoday = sprintf("%01.2f", MMtoINCHES($raintoday, $rain_prec));
} // end if
else {
		$raintoday = sprintf('%01.1f',$raintoday);
} // end else

// figure out a text value for compass direction
switch (TRUE) {
	case (($winddir >= 349) and ($winddir <= 360)):
	$winddir = 'N';
	break;
	case (($winddir >= 0) and ($winddir <= 11)):
	$winddir = 'N';
	break;
	case (($winddir > 11) and ($winddir <= 34)):
	$winddir = 'NNE';
	break;
	case (($winddir > 34) and ($winddir <= 56)):
	$winddir = 'NE';
	break;
	case (($winddir > 56) and ($winddir <= 78)):
	$winddir = 'ENE';
	break;
	case (($winddir > 78) and ($winddir <= 101)):
	$winddir = 'E';
	break;
	case (($winddir > 101) and ($winddir <= 124)):
	$winddir = 'ESE';
	break;
	case (($winddir > 124) and ($winddir <= 146)):
	$winddir = 'SE';
	break;
	case (($winddir > 146) and ($winddir <= 169)):
	$winddir = 'SSE';
	break;
	case (($winddir > 169) and ($winddir <= 191)):
	$winddir = 'S';
	break;
	case (($winddir > 191) and ($winddir <= 214)):
	$winddir = 'SSW';
	break;
	case (($winddir > 214) and ($winddir <= 236)):
	$winddir = 'SW';
	break;
	case (($winddir > 236) and ($winddir <= 259)):
	$winddir = 'WSW';
	break;
	case (($winddir > 259) and ($winddir <= 281)):
	$winddir = 'W';
	break;
	case (($winddir > 281) and ($winddir <= 304)):
	$winddir = 'WNW';
	break;
	case (($winddir > 304) and ($winddir <= 326)):
	$winddir = 'NW';
	break;
	case (($winddir > 326) and ($winddir <= 349)):
	$winddir = 'NNW';
	break;
} // end switch

// figure out a text value for barometric pressure trend
settype($baromtrend, "float");
switch (TRUE) {
	case (($baromtrend >= -1.1) and ($baromtrend <= 1.1)):
		$baromtrendwords = "Steady";
	break;
	case (($baromtrend > 1.1) and ($baromtrend <= 2)):
		$baromtrendwords = "Rising";
	break;
	case ($baromtrend > 2):
		$baromtrendwords = "Rising Rapidly";
	break;
	case (($baromtrend < -1.1) and ($baromtrend >= -2)):
		$baromtrendwords = "Falling";
	break;
	case ($baromtrend < -2):
		$baromtrendwords = "Falling Rapidly";
	break;
} // end switch

// CURRENT CONDITIONS static-images FOR clientraw.txt
// create array for static-images. There are 35 possible values in clientraw.txt
// It would be simpler to do this with array() but to make it easier to
// modify each element is defined individually. Each index [#] corresponds
// to the value provided in clientraw.txt
$icon_array[0] = "./static-images/day_clear.$image_format";            // imagesunny.visible
$icon_array[1] = "./static-images/night_clear.$image_format";          // imageclearnight.visible
$icon_array[2] = "./static-images/day_partly_cloudy.$image_format";    // imagecloudy.visible
$icon_array[3] = "./static-images/day_partly_cloudy.$image_format";    // imagecloudy2.visible
$icon_array[4] = "./static-images/night_partly_cloudy.$image_format";  // imagecloudynight.visible
$icon_array[5] = "./static-images/day_clear.$image_format";            // imagedry.visible
$icon_array[6] = "./static-images/fog.$image_format";                  // imagefog.visible
$icon_array[7] = "./static-images/haze.$image_format";                 // imagehaze.visible
$icon_array[8] = "./static-images/day_heavy_rain.$image_format";       // imageheavyrain.visible
$icon_array[9] = "./static-images/day_mostly_sunny.$image_format";     // imagemainlyfine.visible
$icon_array[10] = "./static-images/mist.$image_format";                // imagemist.visible
$icon_array[11] = "./static-images/fog.$image_format";                 // imagenightfog.visible
$icon_array[12] = "./static-images/night_heavy_rain.$image_format";    // imagenightheavyrain.visible
$icon_array[13] = "./static-images/night_cloudy.$image_format";        // imagenightovercast.visible
$icon_array[14] = "./static-images/night_rain.$image_format";          // imagenightrain.visible
$icon_array[15] = "./static-images/night_light_rain.$image_format";    // imagenightshowers.visible
$icon_array[16] = "./static-images/night_snow.$image_format";          // imagenightsnow.visible
$icon_array[17] = "./static-images/night_tstorm.$image_format";        // imagenightthunder.visible
$icon_array[18] = "./static-images/day_cloudy.$image_format";          // imageovercast.visible
$icon_array[19] = "./static-images/day_partly_cloudy.$image_format";   // imagepartlycloudy.visible
$icon_array[20] = "./static-images/day_rain.$image_format";            // imagerain.visible
$icon_array[21] = "./static-images/day_rain.$image_format";            // imagerain2.visible
$icon_array[22] = "./static-images/day_light_rain.$image_format";      // imageshowers2.visible
$icon_array[23] = "./static-images/sleet.$image_format";               // imagesleet.visible
$icon_array[24] = "./static-images/sleet.$image_format";               // imagesleetshowers.visible
$icon_array[25] = "./static-images/snow.$image_format";                // imagesnow.visible
$icon_array[26] = "./static-images/snow.$image_format";                // imagesnowmelt.visible
$icon_array[27] = "./static-images/snow.$image_format";                // imagesnowshowers2.visible
$icon_array[28] = "./static-images/day_clear.$image_format";           // imagesunny.visible
$icon_array[29] = "./static-images/day_tstorm.$image_format";          // imagethundershowers.visible
$icon_array[30] = "./static-images/day_tstorm.$image_format";          // imagethundershowers2.visible
$icon_array[31] = "./static-images/day_tstorm.$image_format";          // imagethunderstorms.visible
$icon_array[32] = "./static-images/tornado.$image_format";             // imagetornado.visible
$icon_array[33] = "./static-images/windy.$image_format";               // imagewindy.visible
$icon_array[34] = "./static-images/day_partly_cloudy.$image_format";   // stopped rainning
// that's all the WD clientraw.txt specific stuff

//
// Change wind value to "Calm" if the speed is 0
switch (TRUE) {
case ($wind == 0):
	$winds = "Calm";
break;
case (($wind > 0) and ($_REQUEST["type"] == "banner")):
	$winds = "$winddir @ $wind$wind_units";
break;
case (($wind > 0) and ($_REQUEST["type"] == "banner_big")):
	$winds = "$winddir @ $wind$wind_units";
break;
case (($wind > 0) and ($_REQUEST["type"] == "avatar")):
	$winds = "$winddir @ $wind$wind_units";
break;
case (($wind > 0) and (empty($_REQUEST["type"]))):
	$winds = "$winddir @ $wind";
break;
} // end switch

// make the current conditions icon image
if ($curr_cond_icon == 'yes') {
	switch (TRUE) {
		case ($image_format == 'gif') :
			$ccicon = imagecreatefromgif($icon_array[$data[48]]);
			$ccicon2 = imagecreatefromgif("./static-images/rising.gif");
		break;
		case ($image_format == 'png') :
			$ccicon = imagecreatefrompng($icon_array[$data[48]]);
		break;
		case ($image_format == 'jpeg') :
			$ccicon = imagecreatefromjpeg($icon_array[$data[48]]);
		break;
	} // end switch

// create a truecolor image to avoid any pallete problems
// if we couldn't get the value we'll default to clear
$ccx = imagesx($ccicon); $ccy = imagesy($ccicon);
$ccx2 = imagesx($ccicon2); $ccy2 = imagesy($ccicon2);
$icon = imagecreatetruecolor($ccx,$ccy);
$icon2 = imagecreatetruecolor($ccx2,$ccy2);
imagecopy($icon, $ccicon,0,0,0,0,$ccx,$ccy); imagedestroy($ccicon);
imagecopy($icon2, $ccicon2,0,0,0,0,$ccx2,$ccy2); imagedestroy($ccicon2);
} // end if

// create the base image
create_base_image();

// create a truecolor image to deal avoid any pallete problems
$basex = imagesx($baseimg);
$basey = imagesy($baseimg);
$img = imagecreatetruecolor($basex,$basey);
imagecopy($img,$baseimg,0,0,0,0,$basex,$basey);
imagedestroy($baseimg);

// define the colors with function define_colors() from config.txt
// this has to be done after the image is created
define_colors();

// now let's create the image
switch (TRUE){
case ($type == "banner"):
	// put the icon on the background
	if (isset($icon)) {
		imagecopyresampled($img, $icon, $banner_icon_x, $banner_icon_y, 0, 0, 25, 25, 25, 25);
		imagecopyresampled($img, $icon2, $banner_icon2_x, $banner_icon2_y, 0, 0, 15, 15, 15, 15);
	} // end if
	// write the text onto the 468X60 banner
	write_banner();
break;
case ($type == "banner_big"):
	if (isset($icon)) {
		imagecopyresampled($img, $icon, $banner_big_icon_x, $banner_big_icon_y, 0, 0, 25, 25, 25, 25);
	} // end if
	// write the text onto the 500X80 banner
	write_banner_big();
break;
case ($type == "avatar"):
	if (isset($icon)) {
		imagecopyresampled($img, $icon, $avatar_icon_x, $avatar_icon_y, 0, 0, 25, 25, 25, 25);
	} // end if
	// write the text onto the avatar
	write_avatar();
break;
case empty($type):
	if (isset($icon)) {
		imagecopyresampled($img, $icon, $default_icon_x, $default_icon_y, 0, 0, 25, 25, 25, 25);
		imagecopyresampled($img, $icon2, $default_icon2_x, $default_icon2_y, 0, 0, 10, 10, 10, 10);
	} // end if
	// write the text onto the default image
	write_default();
break;
} // end switch

// send header to browser
switch (TRUE) {
case ($image_format == 'gif') :
	header('Content-type: image/gif');
	imagegif($img);
break;
case ($image_format == 'png') :
	header('Content-Type: image/png');
	imagepng($img);
break;
case ($image_format == 'jpeg') :
	header('Content-type: image/jpeg');
	imagejpeg($img);
break;
} // end switch

// get rid of the image since we don't need it in memory any more.
// don't need any memory leaks taking down the server
imagedestroy($img);
if (isset($icon)){
imagedestroy($icon); imagedestroy($icon2);
} // end if

/************ Function Definitions *************/

// imagecenteredtext() : text centering function for image creation
// centers on provided x, y coordinates
// you must pass all parameters even if you aren't using them.
// $x = x coordinate where the text will be centered
// $y = y coordinate where the text will be centered
// $text = the text to be written
// $size = font size for built-in GD fonts (1,2,3,4, or 5)
// $ttfsize = font size for ttf fonts. Use just like you would in a word processor
// $color = color as defined in the allocate colors section below
// $angle = for ttf fonts, determines the angle for the text.
function imagecenteredtext($x, $y, $text, $size, $ttfsize, $color, $angle) {
global $font_file, $img ;
// if FreeType is not supported OR $font_file is set to none
// we'll use the GD default fonts
$gdinf = gd_info();
if (($gdinf["FreeType Support"] == 0) or ($font_file == "none")) {
	$x -= (imagefontwidth($size) * strlen($text)) / 2;
	$y -= (imagefontheight($size)) / 2;
	imagestring($img, $size, $x, $y - 3, $text, $color);
} // end if

// otherwise we'll use the truetype font defined in $font_file
else {
	$box = imagettfbbox ($ttfsize, $angle, $font_file, $text);
	$x -= ($box[2] - $box[0]) / 2;
	$y -= ($box[3] - $box[1]) / 2;
	if ($anti_alias == 'off'){
		imagettftext ($img, $ttfsize, $angle, $x, $y, -$color, $font_file, $text);
	} // end if
	else {
		imagettftext ($img, $ttfsize, $angle, $x, $y, $color, $font_file, $text);
	} // end else
} // end else

} // end function imagecenteredtext

// create_base_image() : creates the base image based on on the "type"
// passed to the script.
function create_base_image() {
global $image_format, $baseimg, $type;

// set the type of graphic we're going to output based on the type parm
if (isset($_REQUEST["type"])) {
	$type = $_REQUEST["type"];
} // end if

// generate the base image
if (isset($type)) {
	switch (TRUE) {
		case ($image_format == 'gif') :
			$baseimg = imagecreatefromgif("$type.$image_format");
		break;
		case ($image_format == 'png') :
			$baseimg = imagecreatefrompng("$type.$image_format");
		break;
		case ($image_format == 'jpeg') :
			$baseimg = imagecreatefromjpeg("$type.$image_format");
		break;
	} // end switch
} // end if
else {
	switch (TRUE) {
		case ($image_format == 'gif') :
			$baseimg = imagecreatefromgif("default.$image_format");
		break;
		case ($image_format == 'png') :
			$baseimg = imagecreatefrompng("default.$image_format");
		break;
		case ($image_format == 'jpeg') :
			$baseimg = imagecreatefromjpeg("default.$image_format");
		break;
	} // end switch
} // end else
} // end function create_base_image

// nodataimage() : Default image to output if we can't get the data file
// text to write to image:
// no_data_text1: top line of text
// no_data_text2: bottom line of text
function nodataimage ($no_data_text1, $no_data_text2) {
global $baseimg, $font_file, $image_format, $img, $type, $color1, $color2, $color3, $color4, $color5;

// create the base image
create_base_image();

// create a truecolor image to deal avoid any pallete problems
$basex = imagesx($baseimg);
$basey = imagesy($baseimg);
$img = imagecreatetruecolor($basex,$basey);
imagecopy($img,$baseimg,0,0,0,0,$basex,$basey);
imagedestroy($baseimg);

// define the colors with function define_colors() from config.txt
// this has to be done after the image is created
define_colors();

//write the text onto the image
switch (TRUE) {
	case empty($_REQUEST["type"]):
		imagecenteredtext(75, 65, "$no_data_text1", 5, 12, $color1, 0);
		imagecenteredtext(75, 85, "$no_data_text2", 5, 12, $color1, 0);
	break;
	case ($_REQUEST["type"] == "banner"):
		imagecenteredtext(234, 35, "$no_data_text1 $no_data_text2", 5, 12, $color1, 0);
	break;
	case ($_REQUEST["type"] == "banner_big"):
		imagecenteredtext(250, 45, "$no_data_text1 $no_data_text2", 5, 12, $color1, 0);
	break;
} // end switch

// send header to browser
switch (TRUE) {
	case ($image_format == 'gif') :
		header('Content-type: image/gif');
		imagegif($img);
	break;
	case ($image_format == 'png') :
		header('Content-Type: image/png');
		imagepng($img);
	break;
	case ($image_format == 'jpeg') :
		header('Content-type: image/jpeg');
		imagejpeg($img);
	break;
} // end switch

// get rid of the image since we don't need it in memory any more.
// don't need any memory leaks taking down the server
imagedestroy($img);

// exit the script
exit;

} // end function nodataimage

// CtoF: converts degrees Celcius to degress Farenheight
function CtoF($value, $precision) {
global $temp_prec;
return round($value = ((1.8* $value) + 32),$precision);
} // end function C_to_F

// KTStoKPH: converts knots to KPH
function KTStoKPH($value, $precision) {
global $wind_prec;
return round($value = (1.852 * $value),$precision);
} // end function KTStoKPH

// KTStoMPH: converts knots to MPH
function KTStoMPH($value, $precision) {
global $wind_prec;
return round($value = (1.1508 * $value),$precision);
} // end function KTStoMPH

// MBtoINCHES: converts mb to in/Hg
function MBtoINCHES($value, $precision) {
global $barom_prec;
return round($value = ($value / 33.86388158),$precision);
} // end function MBtoINCHES

// MMtoINCHES: converts mm to inches
function MMtoINCHES($value, $precision) {
global $rain_prec;
return round($value = (.0393700787 * $value),$precision);
} // end function MMtoINCHES

/************* End of Function Definitions ****************/
/*if($_SERVER['REMOTE_ADDR']  == '131.111.131.12' && $type == 'banner') {
	$flogip = fopen("testlog2.csv","a");
	fputcsv($flogip,array($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], date('d/m/Y'), date('H:i')));
	fclose($flogip);
}*/
?>