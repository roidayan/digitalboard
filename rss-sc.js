/**
 * Digital Board
 *
 * @package DigitalBoard
 *
 */

var rss_sc_timer = null;
var rss_sc_data = {};

jQuery(document).ready(function( $ ) {
	//cycle_rss_sc_items();
}).on( 'heartbeat2-send', function ( event, data ) {
	data['rss-sc'] = rss_sc_get_urls();
}).on( 'heartbeat2-tick', function ( event, data ) {
	if ( ! data['rss-sc'] )
		return;

	rss_sc_refresh( data['rss-sc'] );
	if ( ! rss_sc_timer )
		cycle_rss_sc_items();
});

function rss_sc_refresh(data) {
	for (var i = 0; i < data.length; i++) {
		var item = data[i];
		rss_sc_data[item['id']] = item['items'];
	}
	console.log('rss data', rss_sc_data);
}

function rss_sc_get_urls() {
	var data = [];
	jQuery('.rss-news').each(function() {
		data.push(jQuery(this).data());
	});
	return data;
}

function cycle_rss_sc_items() {
	const display_time = 10000;
	const delay = 1000; // delay for the animation.

	jQuery(".rss-news").each(function() {
		cycle_rss_sc_single( jQuery(this), delay );
	});

	rss_sc_timer = setTimeout(cycle_rss_sc_items, display_time + delay);
}

function cycle_rss_sc_single( container, delay ) {
	var id = container.data('id');
	var active = container.data('active');
	if ( ! active )
		active = 0;
	var next = active+1;
	var data = rss_sc_data[id];

	if ( ! data )
		return;

	if (next >= data.length)
		next = 0;

	container.removeClass( 'fadeIn' ).addClass( 'animated fadeOut' );
	setTimeout(function(){
		container.data( 'active', next );
		container.html( data[next] );
		container.removeClass( 'fadeOut' ).show().addClass( 'animated fadeIn' );
	}, delay);
}
