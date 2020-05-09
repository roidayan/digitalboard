/**
 * Digital Board
 *
 * @package DigitalBoard
 *
 */

jQuery(document).ready(function( $ ) {
	cycle_rss_sc_items();
});

function cycle_rss_sc_items() {
	var display_time = 10000;
	const delay = 1000; // delay for the animation.
	const selector = ".rss-news";
	const sel_label = ".rss-news-label";

	var container = jQuery(selector);
	var label = jQuery(sel_label);
	var active = container.find("li.active");
	var next = active.next();

	if (next.length == 0)
		next = container.find("li").first();

	label.removeClass( 'fadeIn' ).addClass( 'animated fadeOut' );
	setTimeout(function(){
		active.removeClass("active");
		next.addClass("active");
		label.html( next.html() );
		label.removeClass( 'fadeOut' ).show().addClass( 'animated fadeIn' );
		if (container.find("li").length > 1)
			setTimeout(cycle_rss_sc_items, display_time + delay);
	}, delay);
}
