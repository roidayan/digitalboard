<?php
/**
 * @package DigitalBoard
 */

require_once("lib/hebcal.php");
require_once("lib/converter.php");

function get_hebcal() {
	return Hebcal::get_instance();
}


class SunTimesShortcodes {
	static $sc_defaults = array(
		'latitude'  => 32.08088,
		'longitude' => 34.78057,
	);

	static function sunrise( $atts ) {
		$a = shortcode_atts( array(
			'foo' => 'something',
			'bar' => 'something else',
		), $atts, NULL );

		$offset = get_option('gmt_offset');

		return date_sunrise(time(), SUNFUNCS_RET_STRING, self::$sc_defaults['latitude'], self::$sc_defaults['longitude'], 90, $offset);
	}

	static function sunset( $atts ) {
		$a = shortcode_atts( array(
			'foo' => 'something',
			'bar' => 'something else',
		), $atts, NULL );

		$offset = get_option('gmt_offset');

		return date_sunset(time(), SUNFUNCS_RET_STRING, self::$sc_defaults['latitude'], self::$sc_defaults['longitude'], 90, $offset);
	}
}

define('Sun', 'ראשון');
define('Mon', 'שני');
define('Tue', 'שלישי');
define('Wed', 'רביעי');
define('Thu', 'חמישי');
define('Fri', 'שישי');
define('Sat', 'שבת');
define('Day', 'יום');

function heb_day_map($day) {
	$dayMap = array(Sun, Mon, Tue, Wed, Thu, Fri, Sat);
	return $dayMap[$day];
}

function sc_format_date($date) {
	$time = strtotime($date);
	$format = 'l, j בF Y';
	return date_i18n( $format, $time );
}


class HebcalShortcodes {
	static function dayname() {
		$h = date_i18n( 'l' );
		return $h;
	}

	static function hebdate() {
		return g2h_today();
	}

	static function get_item_by_category($items, $category, $today=false) {
		if (!$items)
			return false;

		if ($today)
			$today1 = date( 'Y-m-d', current_time('timestamp') );

		foreach( $items as $item ) {
			if ($item['category'] == $category) {
				if (!$today || $today1 == $item['date'])
					return $item;
			}
		}
		return false;
	}

	static function parashat() {
		$h = get_hebcal();
		$data = $h->shabbat();
		$item = self::get_item_by_category($data['items'], 'parashat');
		if ( empty( $item ) )
			$item = self::get_item_by_category($data['items'], 'holiday', true);
		return $item['title'];
	}

	static function candles() {
		$h = get_hebcal();
		$data = $h->shabbat();
		$item = self::get_item_by_category($data['items'], 'candles');
		$date2 = sc_format_date( $item['date'] );
		return $item['title'] . " $date2";
	}

	static function havdalah() {
		$h = get_hebcal();
		$data = $h->shabbat();
		$item = self::get_item_by_category($data['items'], 'havdalah');
		$date2 = sc_format_date( $item['date'] );
		return $item['title'] . " $date2";
	}
}


add_shortcode( 'sunrise', array( 'SunTimesShortcodes', 'sunrise' ) );
add_shortcode( 'sunset', array( 'SunTimesShortcodes', 'sunset' ) );
add_shortcode( 'hebday', array( 'HebcalShortcodes', 'dayname' ) );
add_shortcode( 'hebdate', array( 'HebcalShortcodes', 'hebdate' ) );
add_shortcode( 'parashat', array( 'HebcalShortcodes', 'parashat' ) );
add_shortcode( 'candles', array( 'HebcalShortcodes', 'candles' ) );
add_shortcode( 'havdalah', array( 'HebcalShortcodes', 'havdalah' ) );


function dboard_shortcodes_help() {
	$a = array(
		'[sunrise]'  => __('Sunrise time', DBOARD_TD),
		'[sunset]'   => __('Sunset time', DBOARD_TD),
		'[hebday]'   => __('Hebrew day name', DBOARD_TD),
		'[hebdate]'  => __('Hebrew date', DBOARD_TD),
		'[parashat]' => __('Parashat hashavua', DBOARD_TD),
		'[candles]'  => __('Candle light time', DBOARD_TD),
		'[havdalah]' => __('Havdalah', DBOARD_TD),
	);
	$help = "<h2>".__('Available shortcodes:', DBOARD_TD)."</h2><br>";
	$help .= "<table>";
	foreach($a as $k => $v) {
		$help .= "<tr><td>$k</td><td>$v</td></tr>";
	}
	$help .= "</table>";
	return $help;
}
