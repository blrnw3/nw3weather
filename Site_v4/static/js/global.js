var $browser_time_offset = new Date().getTime();
var $time = 0;

function setup() {
	$time = $('#constants-time');
	$browser_time_offset = $time - $browser_time_offset;
}

function time() {
	return new Date().getTime() + $browser_time_offset;
}