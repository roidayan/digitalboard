<?php

class Hebcal {
	static $instance = null;
	var $hebcal_remote_url = 'https://www.hebcal.com/hebcal/';
	var $shabbat_remote_url = 'https://www.hebcal.com/shabbat/';
	var $date_converter_remote_url = 'https://www.hebcal.com/converter/';
	var $lg = '';
	var $geonameid = '293397'; // Tel Aviv, Israel

	static function get_instance() {
		if ( ! self::$instance )
			self::$instance = new self();

		return self::$instance;
	}

	function __construct($lg='') {
		if ($lg) {
			$this->lg = $lg;
		} else if (function_exists('get_locale')) {
			$a = get_locale();
			if ($a == 'he_IL')
				$this->lg = 'h';
		}
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

		if (!$data)
			return false;

		return json_decode( $data, true );
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

	function calendar_today( $category='', $subcat='' ) {
		$data = $this->calendar( date('m') );
		if (!$data)
			return false;
		// item date might contain time so need to parse it
		$today = date( 'Y-m-d', current_time('timestamp') );
		$items = array();

		foreach( $data['items'] as $item ) {
			if ($item['date'] == $today) {
				if ((!$category || ($category && $category == $item['category'])) &&
				    (!$subcat || (array_key_exists('subcat', $item) &&
						  $subcat == $item['subcat']))) {
					$items[] = $item;
				}
			}
		}

		return $items;
	}

	function calendar_today_major_holiday() {
		$tags = [
			'rosh-hashana',
			'yom-kippur',
			'sukkot',
			'shmini-atzeret',
			'simchat-torah',
			'chanukah',
			'purim',
			'pesach',
			'shavuot',
			'tisha-bav',
		];
		$allitems = $this->calendar_today('holiday');
		if (!$allitems)
			return false;
		$items = array();
		foreach( $allitems as $item ) {
			$link = $item['link'];
			foreach( $tags as $tag ) {
				if (strpos($link, $tag) !== false) {
					$item['tag'] = $tag;
					$items[] = $item;
					break;
				}
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
