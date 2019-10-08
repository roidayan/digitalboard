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
	init_news_ticker();

}).on( 'heartbeat2-send', function ( event, data ) {
	data['dboard'] = {
		"hello": 1,
		"screen_id": pagenow,
		"last_rss_item": dboard_last_rss_item,
	};

}).on( 'heartbeat2-tick', function ( event, data ) {
	dboard_refresh_data( data );
}).on( 'midnight', function() {
	if (dboard_refresh_at_midnight) {
		console.log('refresh at midnight')
		location.reload();
		return;
	}
	window.heartbeat2.connectNow();
});

var dboard_page_version = 0;
var dboard_last_rss_item = '';
var dboard_refresh_at_midnight = false;


function refresh_at_midnight() {
	dboard_refresh_at_midnight = true;
}

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
	dboard_last_rss_item = data.dboard.last_rss_item;

	jQuery( '.weather-line .temp' ).html( data.dboard.weather_temp );
	jQuery( '.weather-line .desc' ).html( data.dboard.weather_desc );
	jQuery( '.weather-line .icon img' ).attr( 'src', data.dboard.weather_icon );
	jQuery( 'header .date').html( data.dboard.current_date );

	if (data.dboard.background_image)
		dboard_set_background_image( '.container',
					     data.dboard.background_image,
					     data.dboard.background_image_credit );

	if ( data.dboard.news_ticker ) {
		jQuery('.breaking-news-ticker').replaceWith( data.dboard.news_ticker );
		init_news_ticker();
	}
}

function dboard_set_background_image( selector, img, credit ) {
	var url = 'url("'+img+'")';
	var container = jQuery( selector );
	var cur = container.css( 'background-image' );

	if ( cur != url ) {
		if (img) {
			jQuery('<img/>').attr('src', img).on('load', function(){
				jQuery(this).remove();
				container.css('background-image', url);
				jQuery('.background-image-credit').html( credit );
			});
		} else {
			container.css('background-image', '');
			jQuery('.background-image-credit').html('');
		}
	}
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

function init_news_ticker() {
	var dir = jQuery('html').attr('dir');

	jQuery('.breaking-news-ticker').breakingNews({
		effect: 'scroll',
		direction: dir,
		height: '50px',
		fontSize: '1.5em'});
}

function cycle_single_msgs() {
	const display_time = 5000;
	const delay = 1000; // delay for the animation.
	const selector = ".msg-container";

	container = jQuery(selector);
	active = jQuery(".msg.active");
	next = active.next();

	if (next.length == 0)
		next = jQuery(".msg").first();

	active.removeClass( 'fadeIn' ).addClass( 'animated fadeOut' );
	container.removeClass( 'fadeIn' ).addClass( 'animated fadeOut' );
	setTimeout(function(){
		active.removeClass("active");
		next.addClass("active");
		next.removeClass( 'fadeOut' ).show().addClass( 'animated fadeIn' );
		container.removeClass( 'fadeOut' ).addClass( 'animated fadeIn' );
		dboard_set_background_image( selector, next.data().img );

		setTimeout(cycle_single_msgs, display_time + delay);
	}, delay);
}
