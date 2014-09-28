$(document).ready(function(){
	arrow_init();
});
function arrow_init() {
	$('#data_report_header').find('.arrow').click(function() {
		var is_next = $(this).hasClass('next');
		var _select = $(this).parent().find('select')[0];
		if(is_next) {
			_select.selectedIndex = (_select.selectedIndex + 1) % _select.length;
		} else {
			_select.selectedIndex = (_select.selectedIndex === 0) ? _select.length-1 : _select.selectedIndex - 1;
		}
		$('#data_report_header').find('form').submit();
	});
}