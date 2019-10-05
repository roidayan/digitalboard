<?php

class Hebcal {
	static $instance = null;
	var $hebcal_remote_url = 'http://www.hebcal.com/hebcal/';
	var $shabbat_remote_url = 'http://www.hebcal.com/shabbat/';
	var $date_converter_remote_url = 'http://www.hebcal.com/converter/';
	var $lg = 'h';
	var $geonameid = '293397'; // Tel Aviv, Israel

	static function get_instance() {
		if ( ! self::$instance )
			self::$instance = new self();

		return self::$instance;
	}

	function get_resource($url, $params) {
		$query = http_build_query($params, NULL, '&', PHP_QUERY_RFC3986);
		$url = "$url&$query";

		if (function_exists('wp_remote_get')) {
			$r = wp_remote_get($url);
			$data = wp_remote_retrieve_body($r);
		} else {
			$data = file_get_contents($url);
		}

		if ($data)
			$data = json_decode( $data, true );

		return $data;
	}

	function calendar($month='x') {
		/**
		 * Jewish calendar
		 * https://www.hebcal.com/home/195/jewish-calendar-rest-api
		 */
		$cal_remote_url = $this->hebcal_remote_url . '?maj=on&min=on&mod=on&nx=on&ss=off&s=on&mf=on&c=off&D=on&d=on&i=on';

		$params = array(
		    'v'     => '1',
		    'cfg'   => 'json',
		    'year'  => 'now',
		    'lg'    => $this->lg,
		    'month' => $month, // 'x' for entire year.
		);

		return $this->get_resource($cal_remote_url, $params);
	}

	function calendar_today() {
		$data = $this->calendar( date('m') );
		// item date might contain time so need to parse it
		$today = date('Y-m-d');
		$items = array();

		foreach( $data['items'] as $item ) {
			if ($item['date'] == $today) {
				$items[] = $item;
			}
		}

		return $items;
	}

	function candles($month='x') {
		/**
		 * Candle lighting times
		 */
		$candle_remote_url = $this->hebcal_remote_url . '?c=on&s=on&nx=off&m=50&D=off&d=off';

		$params = array(
			'v'         => '1',
			'cfg'       => 'json',
			'lg'        => $this->lg,
			'year'      => 'now',
			'month'     => $month,
			'geo'       => 'geoname',
			'geonameid' => $this->geonameid,
		);

		return $this->get_resource($candle_remote_url, $params);
	}

	function shabbat() {
		/**
		 * Shabbat times
		 * https://www.hebcal.com/home/197/shabbat-times-rest-api
		 */
		$shabbat_remote_url = $this->shabbat_remote_url . '?';

		$params = array(
			'cfg'       => 'json',
			'lg'        => $this->lg,
			'm'         => 50,
			'geo'       => 'geoname',
			'geonameid' => $this->geonameid,

		);

		return $this->get_resource($shabbat_remote_url, $params);
	}

	function date_convert($gy, $gm, $gd) {
		/**
		 * Hebrew date converter
		 * https://www.hebcal.com/home/219/hebrew-date-converter-rest-api
		 */
		$remote_url = $this->date_converter_remote_url . '?';

		$params = array(
			'cfg' => 'json',
			'g2h' => 1,
			'gy' => $gy,
			'gm' => $gm,
			'gd' => $gd,
		);

		return $this->get_resource($remote_url, $params);
	}
/* end class */
}
