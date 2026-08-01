$(document).ready(function() {
	$('.chart_changer').change(changeChart);
});

/**
* Changes chart based on dynamic input
* @author &copy; Ben Lee-Rodgers, nw3weather, April 2013
*/
function changeChart() {
   var extras = '';
   var len;

   var type = $("#type").val();
   var yr = $("#year").val();
   var wxvar = $("#wxvar").val();

   if(type == 31) {
	   $("#lengthM").hide();
	   $("#lengthD").show();
	   len = $("#lengthD").val();

	   if(yr > 0) {
		   $("#month").show();
		   $("#lengthD").val(31);
		   $("#lengthD").prop('disabled', 'disabled');
		   extras += '&year='+ yr;
		   extras += '&month='+ $("#month").val();
	   } else {
		   $("#month").hide();
		   $("#lengthD").prop('disabled', false);
	   }
   } else {
	   $("#lengthD").hide();
	   $("#month").hide();
	   $("#lengthM").show();
	   len = $("#lengthM").val();

	   extras += '&mmm=' + type;

	   if(yr > 0) {
		   extras += '&year='+ yr;
		   $("#lengthM").val(12);
		   $("#lengthM").prop('disabled', 'disabled');
	   } else {
		   $("#lengthM").prop('disabled', false);
	   }
   }

   extras += '&length=' + len;

	type = (type == 31) ? 'daily' : 'monthly';
   $("#chart").attr('src', '../graph/' + type + '/'+ wxvar + '?x=845&y=450' + extras);
   $("#heading").text('Daily Data Charts - ' + $("#wxvar option:selected").text());
}