<script type="text/javascript">
//<![CDATA[
var updateable = true;

var timeServer;
var timeWD;

var wxvars = new [];
var wxvarsNew = new [];

$(document).ready(function() {
	timeWD = $("#WDtime").val();
	timeServer = $("#Servertime").val();
//	updater();
});

function refreshImage(id) {
	var patt = new RegExp("currid=[0-9]+");
	var url = $("#"+id).attr('src');
	var match = patt.exec(url);
	$("#"+id).attr( 'src', url.replace(match, "currid="+ timePHP) );
}

var currImage = true;
function imgSwap(id) {
	var num1 = (currImage ? 1 : 3) + id -1;
	var num2 = (!currImage ? 1 : 3) + id -1;
	$("#graph"+id).attr( 'src', $("#graph"+id).attr('src').replace('Graph'+num1, 'Graph'+num2) );
	currImage = !currImage;
}

var currCam = true;
function camChange() {
	var num1 = currCam ? '' : 'g';
	var num2 = !currCam ? '' : 'g';
	$("#cam").attr( 'src', $("#cam").attr('src').replace('curr'+num1, 'curr'+num2) );
	currCam = !currCam;
}

var cnt = 0;
function newify() {
	wxvars = JSON.parse( $("#newData").val() );
//	console.log($("#newData").val());
	 $.ajax({
       url: "ajax/wx-body.php",
       dataType: "html",
       cache: false,
       success: function ( data, textStatus, jqXHR ) {
				$("#lol").html(data);
				timeWD = $("#WDtime").val();
				timeServer = $("#Servertime").val();
//				console.log($("#newData").val());
				wxvarsNew = JSON.parse( $("#newData").val() );

				for(var i = 0; i < wxvars.length; i++) {
					if(wxvars[i] !== wxvarsNew[i]) {
						var colour = (wxvars[i] > wxvarsNew[i]) ? 'red' : 'green';
						$("#var"+i).attr("style", "color:" + colour);
					} else {
						$("#var"+i).attr("style", "color:black");
					}
				}

				cnt++;
			}
		});
}

var autoupdateCount = 0;
var totalCnt = 1;
function updater() {
	timeServer++;
	var elapsedSeconds = timeServer - timeWD - 1;
	var target = document.getElementById('elapsedTime');

	if(elapsedSeconds > 99) {
		elapsedSeconds = '>99';
		target.style.color = 'red';
	} else {
		target.style.color = (elapsedSeconds < 5) ? 'green' : 'black';
	}
	var message = (elapsedSeconds < 0 || elapsedSeconds > 9999) ? '' : ' - ' + elapsedSeconds + ' s ago';

	target.innerHTML = message;

	if(updateable && totalCnt % 20 === 0) {
		if(autoupdateCount < 30) {
			newify();
			autoupdateCount++;
		} else {
			$("#info").html(" - AutoUpdates paused. Click to resume");
		}
	}

	if(dateJS.getMinutes() % 5 === 0 && dateJS.getSeconds() === 10) {
		refreshImage('graph1');
		refreshImage('graph2');
		console.log("refreshing images");
	}

	totalCnt++;
	setTimeout('updater()', 1000);
}

function resume() {
	autoupdateCount = 0;
	$("#info").html("");
	newify();
}
function pause() {
	var pr = updateable ? 'Resume' : 'Pause';
	var colr = !updateable ? '3a4' : 'a34';
	$("#pauser").html(pr +" live updates");
	$("#pauser").attr("style", "color:#" + colr);
	updateable = !updateable;
	if(updateable) {
		resume();
	}
}
//]]>
</script>
<style type="text/css">
	#pauser {
		font-size:90%;
		border-bottom: 1px dotted;
	}
</style>


<h1>Hampstead nw3, London - Current Weather</h1>

<div>
<table width="99%" cellpadding="2" cellspacing="0" align="center" border="0" rules="none">
<tr class="rowdark">
<td width="25%" align="center"><b><span style="color:#610B0B">Weather Report</span></b>
<br /><br />
<?php //decode WD cond, using metar too
echo "<b>{$this->m->condition()}</b>; ". acronym("Raw METAR: ". $this->m->metar, $this->m->cloud());
?> </td>

<td width="30%" rowspan="3" align="center"><b><span title="Clickable!" onclick="camChange();" style="color:#336666">Weathercam</span></b>
<br /><br />
<?php $img = 'currcam_small.jpg'; ?>
<a href="wx11.php">
	<img id="cam" name="refresh" border="0" src="<?php echo $camImg; ?>" title="Click to enlarge" alt="Web cam" width="236" height="177" /></a>
<br />
<a href="wx2.php" title="Full webcam image and timelapses">See more</a>

</td>
<td width="45%" rowspan="3" align="center">
<?php
if($imperial) {
	$img1 = 'graphdayA.php?type1=temp&amp;type2=rain&amp;ts=12&amp;x=400&amp;y=160&amp;nofooter';
	$img2 = 'graphdayA.php?type1=hum&amp;type2=dew&amp;ts=12&amp;x=400&amp;y=160';
	$click = '';
} else {
	$timeID = date('dmYHi');
	$img1 = '/mainGraph1.png?reqid='. $timeID;
	$img2 = '/mainGraph2.png?reqid='. $timeID;
	$click = $metric ? 'title="You can click, but the units will be mph!"' : 'title="Click to change graph variables" ';
}
echo '<img '.$click.'id="graph1" src="'.$img1.'&amp;currid='. time().'" alt="Last 12-hours weather" onclick="imgSwap(1);" width="400" height="160" />
	  <img '.$click.'id="graph2" src="'.$img2.'&amp;currid='. time().'" alt="Last 12-hours weather" onclick="imgSwap(2);" width="400" height="160" />';
?>
</td>
</tr>

<tr class="rowdark">
<td><span style="color:rgb(243,242,235)">-</span></td></tr>
<tr class="rowdark">
<td align="center"><b><span style="color:#6A4EC6">Local Forecast</span></b>
<br /><br />