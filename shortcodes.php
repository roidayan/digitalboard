<?php
/**
 * @package DigitalBoard
 */

require_once("lib/hebcal.php");
$hebcal = new Hebcal();

function get_hebcal() {
	global $hebcal;
	return $hebcal;
}


class SunTimesShortcodes {
	static $sc_defaults = array(
		'latitude'  => 32.08088,
		'longitude' => 34.78057,
		'tzid'      => 'Asia/Jerusalem',
		'offset'    => 2,
	);

	static function get_timezone_offset( $tzid ) {
		//$tz = new DateTimeZone( $tzid );
		//return $tz->getOffset( new DateTime() );
		return self::$sc_defaults['offset'];
	}

	static function sunrise( $atts ) {
		$a = shortcode_atts( array(
			'foo' => 'something',
			'bar' => 'something else',
		), $atts, NULL );

		$offset = self::get_timezone_offset( self::$sc_defaults['tzid'] );

		return date_sunrise(time(), SUNFUNCS_RET_STRING, self::$sc_defaults['latitude'], self::$sc_defaults['longitude'], 90, $offset);
	}

	static function sunset( $atts ) {
		$a = shortcode_atts( array(
			'foo' => 'something',
			'bar' => 'something else',
		), $atts, NULL );

		$offset = self::get_timezone_offset( self::$sc_defaults['tzid'] );

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

class HebcalShortcodes {
	static function dayname() {
		$dayMap = array(Sun, Mon, Tue, Wed, Thu, Fri, Sat);
		$d = intval( date('w') );
		$h = $dayMap[$d];
		return 'יום ' . $h;
	}

	static function hebdate() {
		$h = get_hebcal();
		$gy = date('Y');
		$gm = date('m');
		$gd = date('d');
		return $h->date_convert($gy, $gm, $gd)['hebrew'];
	}

	static function parashat() {
		$h = get_hebcal();
		$items = $h->calendar_today();
		foreach( $items as $item ) {
			if ($item['category'] == 'parashat') {
				return $item['hebrew'];
			}
		}
		return '';
	}
}


add_shortcode( 'sunrise', array( 'SunTimesShortcodes', 'sunrise' ) );
add_shortcode( 'sunset', array( 'SunTimesShortcodes', 'sunset' ) );
add_shortcode( 'hebday', array( 'HebcalShortcodes', 'dayname' ) );
add_shortcode( 'hebdate', array( 'HebcalShortcodes', 'hebdate' ) );
add_shortcode( 'parashat', array( 'HebcalShortcodes', 'parashat' ) );
