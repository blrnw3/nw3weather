// For keeping track of the server datetime
var $time = $('#constants-time').val() * 1000;
var $browser_time_offset = $time - (new Date().getTime());
function time() {
	return ((new Date().getTime()) + $browser_time_offset) / 1000;
}
function date() {
	return new Date(time());
}
