<?php
/**
 * @package DigitalBoard
 */

class OpenWeatherMap {
	static $instance = null;
	/* default key from wp-cloudy plugin */
	var $key = "46c433f6ba7dd4d29d5718dac3d7f035";
	var $loc = 'US';
	var $lang = 'en';
	var $data = 0;
	var $transient_key = 'openweathermap';
	var $cache = false;
	var $expire = 1800;

	static function get_instance( $key='', $loc='', $lang='' ) {
		if ( ! self::$instance ) {
			self::$instance = new self();
			if ( $key ) {
				self::$instance->key = $key;
			}
			self::$instance->loc = $loc;
			self::$instance->lang = $lang;
		}

		return self::$instance;
	}

	function get_cache() {
		$this->cache = get_transient( $this->transient_key );
	}

	function set_cache( $query, $data ) {
		$this->cache = array(
			'last_refresh' => time(),
			'last_query'   => $query,
			'data'         => $data,
		);

		set_transient( $this->transient_key, $this->cache, $this->expire );
	}

	function valid_cache( $query ) {
		$this->get_cache();
		return ( $this->cache && $this->cache['last_query'] == $query );
	}

	function get_weather() {
		$query = $this->loc . $this->lang;

		if ( $this->valid_cache( $query ) ) {
			return $this->cache['data'];
		}

		if ( ! $this->key ) {
			return;
		}

		$weather_current_url = "https://api.openweathermap.org/data/2.5/weather?q={$this->loc}&mode=json&units=metric&lang={$this->lang}&appid={$this->key}";
		$weather_data = wp_remote_retrieve_body( wp_remote_get( $weather_current_url ) );
		$weather_current = json_decode( $weather_data, true );

		if ( empty($weather_current['cod']) || $weather_current['cod'] != 200 ) {
			return $weather_current;
		}

		$this->data = $weather_current;
		$this->set_cache( $query, $weather_current );
		return $weather_current;
	}

	function get_weather_icon() {
		$w = $this->get_weather();
		if ( ! $w )
			return;

		$d = $w['weather'][0]['icon'];
		return 'http://openweathermap.org/img/w/'.$d.'.png';
	}

	function get_weather_temp() {
		$w = $this->get_weather();
		if ( ! $w )
			return;
		return round($w['main']['temp']);
	}

	function get_weather_name() {
		$w = $this->get_weather();
		if ( ! $w )
			return;

		return $w['weather'][0]['main'];
	}

	function get_weather_desc() {
		$w = $this->get_weather();
		if ( ! $w )
			return;

		return $w['weather'][0]['description'];
	}
}
