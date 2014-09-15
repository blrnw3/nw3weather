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
