/**
 * Digital Board
 *
 * @package DigitalBoard
 *
 */

jQuery(document).ready(function( $ ) {
	dboard_update_time();
	setInterval(dboard_update_time, 1000);
	dboard_set_next_midnight_event();

}).on( 'heartbeat2-send', function ( event, data ) {
	data['dboard'] = {
		"hello": 1,
		"screen_id": pagenow,
	};

}).on( 'heartbeat2-tick', function ( event, data ) {
	dboard_refresh_data( data );
}).on( 'midnight', function() {
	window.heartbeat2.connectNow();
});

var dboard_page_version = 0;

function dboard_refresh_data( data ) {
	if ( ! data.dboard ) {
		console.log("no data");
		return;
	}

	if ( dboard_page_version && dboard_page_version != data.dboard.page_version ) {
		console.log("force reload");
		location.reload();
		return;
	}

	dboard_page_version = data.dboard.page_version;

	jQuery( '.weather-line .temp' ).html( data.dboard.weather_temp );
	jQuery( '.weather-line .desc' ).html( data.dboard.weather_desc );
	jQuery( '.weather-line .icon img' ).attr( 'src', data.dboard.weather_icon );
	jQuery( '.container' ).attr( 'background-image', data.dboard.background_image );
	jQuery( '.background-image-credit' ).html( data.dboard.background_image_credit );
	jQuery( 'header .date').html( data.dboard.current_date );
}

function dboard_set_next_midnight_event() {
	var today = new Date();
	var tommorow = new Date( today.getFullYear(), today.getMonth(), today.getDate() + 1 );
	var timeToMidnight = tommorow - today;

	setTimeout(function(){
		dboard_set_next_midnight_event();
		jQuery(document).trigger( 'midnight' );
	}, timeToMidnight);
}

function addZero(i) {
	if (i < 10) {
		i = "0" + i;
	}
	return i;
}

function dboard_update_time() {
	var d = new Date();
	var s = addZero(d.getSeconds());
	var m = addZero(d.getMinutes());
	var h = addZero(d.getHours());
	var span = document.getElementById("clock1");
	span.textContent = h + ":" + m + ":" + s;
}
