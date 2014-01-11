var $browser_time_offset = new Date().getTime();
var $time = 0;

function setup() {
	$time = $('#constants-time').val() * 1000;
	$browser_time_offset = $time - $browser_time_offset;
}

function time() {
	return new Date().getTime() + $browser_time_offset;
}
function date() {
	return new Date(time());
}

$(document).ready(function(){
	setup();
});