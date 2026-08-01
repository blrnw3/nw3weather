var updateable = true;

var timeServer;
var timeWD;

var ids = ['temp', 'rain', 'wind', 'humi', 'pres', 'dewp'];
var wxvars;
var wxvars_old;

$(document).ready(function() {
	update_ui(JSON.parse($('#init_live').html()));
	setInterval(updater, 1000);

	$('#livewx_main').find('caption').click(resume);
});

function refreshImage(id) {
	var patt = new RegExp('currid=[0-9]+');
	var url = $('#'+id).attr('src');
	var match = patt.exec(url);
	$('#'+id).attr( 'src', url.replace(match, 'currid='+ time()) );
}

var currImage = true;
function imgSwap(id) {
	var num1 = (currImage ? 1 : 3) + id -1;
	var num2 = (!currImage ? 1 : 3) + id -1;
	$('#graph'+id).attr( 'src', $('#graph'+id).attr('src').replace('Graph'+num1, 'Graph'+num2) );
	currImage = !currImage;
}

var currCam = true;
function camChange() {
	var num1 = currCam ? '' : 'g';
	var num2 = !currCam ? '' : 'g';
	$('#cam').attr( 'src', $('#cam').attr('src').replace('curr'+num1, 'curr'+num2) );
	currCam = !currCam;
}

var cnt = 0;
function newify() {
	wxvars_old = wxvars;
	//TODO: no looky no ajax
	 $.ajax({
       url: 'api/latest/live',
       dataType: 'json',
       cache: false,
       success: function (data) {
			update_ui(data);
			cnt++;
		}
	});
}

function update_ui(data) {
	timeWD = data.exec_stats.data_updated;
	wxvars = data.response;

	$('#data_recorded_at').text(wxvars.misc.last_updated_pretty);

	for(var i in ids) {
		var wxname = ids[i];
		var wx = wxvars[wxname];
		// Now
		$('#'+ wxname +'_now').html(ui_getter.now(wx, wxname));
		// Extremes
		var extremes = ui_getter.extreme(wx, wxname);
		$('#'+ wxname +'_extreme').html(extremes[0] +'<br />'+ extremes[1]);
		// Rates
		var rates = ui_getter.rate(wx, wxname);
		$('#'+ wxname +'_rate').html(rates[0] +'<br />'+ rates[1]);
		// Mean
		$('#'+ wxname +'_mean').html(ui_getter.mean(wx, wxname));
	}

	var feel_extreme = ' (Daily ';
	if(wxvars.temp.min.val - wxvars.feel.min.val < Math.abs(wxvars.temp.max.val - wxvars.feel.max.val)) {
		feel_extreme += 'Max: '+ wxvars.feel.max.val;
	} else {
		feel_extreme += 'Min: '+ wxvars.feel.min.val;
	}
	feel_extreme += ')';

	$('#today_temp_range').html(wxvars.misc.today_temp_range);
	$('#feels_like').html(wxvars.feel.now + feel_extreme);
	$('#10m_wind').html(wxvars.misc['10m_wind'] +' '+ wxvars.misc['10m_wdir']);
	$('#hr_rain').html(wxvars.rain.rate_hr);
	$('#month_rn').html(wxvars.misc.month_rn);
	$('#last_rn').html('<span class="soft_tip" title="'+ wxvars.misc.last_rn +'">'+
		wxvars.misc.last_rn_pretty +'</span>');

	for(var id in wxvars_old || []) {
		if(wxvars_old[id].now !== wxvars[id].now) {
			var colour = (wxvars_old[id].now > wxvars[id].now) ? 'red' : 'green';
			$('#var'+ id).attr('style', 'color:' + colour);
		} else {
			$('#var'+ id).attr('style', 'color:black');
		}
	}
	updater();
}

var ui_getter = {
	now: function(wx, wxname) {
		if(wxname === 'wind') {
			return '<span class="now_val">'+ wx.now +' '+ wxvars.wdir.now + '</span>' +
				'<br />Gusting to <span class="now_val">'+ wxvars.gust.now + '</span>';
		}
		return '<span id="var'+ wxname +'" class="now_val">'+ wx.now + '</span>' +
			'<img class="now_trend" src="static/img/icons/'+ wx.trend +'.png" alt="trend" />';
	},
	extreme: function(wx, wxname) {
		if(wxname === 'wind') {
			return [
				'<span class="extreme_label">Max Spd:</span> '+ wx.max.val,
				'<span class="extreme_label">Max Gst</span> '+ wxvars.gust.max.val
			];
		}
		if(wxname === 'rain') {
			return [
				'<span class="extreme_label">Rate:</span> '+ wxvars.rate.now,
				'<span class="extreme_label">Last 10:</span> '+ wxvars.misc.rn_last_10
			];
		}
		return [
			'<span class="extreme_label">Max:</span> '+ wx.max.val +' at '+ wx.max.dt,
			'<span class="extreme_label">Min:</span> '+ wx.min.val +' at '+ wx.min.dt
		];
	},
	rate: function(wx, wxname) {
		if(wxname === 'wind') {
			return [
				'<span class="extreme_label">Max Hr Gust:</span> '+ wxvars.misc.max_hr_gust,
				'<span class="extreme_label"><a href="beaufort">Bft:</a></span> '+ wxvars.misc.bft_wind_now
			];
		}
		if(wxname === 'rain') {
			return [
				'<span class="extreme_label">Max Rate:</span> '+ wxvars.rate.max.val,
				'<span class="extreme_label">Max Hour:</span> '+ wxvars.misc.max_hour_rn
			];
		}
		return [
			wx.rate_hr +' /hr',
			wx.rate_24hr +' /hr'
		];
	},
	mean: function(wx, wxname) {
		if(wxname === 'wind') {
			return wx.mean +'<br />'+ wxvars.wdir.mean;
		}
		return wx.mean;
	}
};

var autoupdateCount = 0;
var totalCnt = 1;
function updater() {
	timeServer = time();
	var elapsedSeconds = Math.round(timeServer - timeWD);
	var target = $('#elapsed_time')[0];

	if(elapsedSeconds > 99) {
		elapsedSeconds = '>99';
		target.style.color = 'red';
	} else {
		target.style.color = (elapsedSeconds < 5) ? 'green' : 'black';
	}
	var message = (elapsedSeconds < 0 || elapsedSeconds > 9999) ? '' : elapsedSeconds;

	target.innerHTML = message;

	if(updateable && totalCnt % 20 === 0) {
		if(autoupdateCount < 30) {
			newify();
			autoupdateCount++;
		} else {
			$('#autopause_info').html(' - AutoUpdates paused. Click to resume');
		}
	}

	server_datetime = date();
	if(server_datetime.getMinutes() % 5 === 0 && server_datetime.getSeconds() === 10) {
		refreshImage('graph1');
		refreshImage('graph2');
//		console.log('refreshing images');
	}

	totalCnt++;
}

function resume() {
	autoupdateCount = 0;
	$('#autopause_info').empty();
	newify();
}
function pause() {
	var pr = updateable ? 'Resume' : 'Pause';
	var colr = !updateable ? '3a4' : 'a34';
	$('#pauser').html(pr +' live updates');
	$('#pauser').attr('style', 'color:#' + colr);
	updateable = !updateable;
	if(updateable) {
		resume();
	}
}
