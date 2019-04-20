<?php
/**
 * @package DigitalBoard
 */

class Pixabay {
	static $instance = null;
	var $data = 0;
	var $key = "";
	var $api_url = 'https://pixabay.com/api/';
	var $cat = 'nature';
	var $order = 'latest';
	var $transient_key = 'pixabay';
	var $cache = false;
	var $expire = 3600;

	static function get_instance( $key='' ) {
		if ( ! self::$instance ) {
			self::$instance = new self();
			if ( $key ) {
				self::$instance->key = $key;
			}
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

	function get_result( $query ) {
		if ( $this->valid_cache( $query ) ) {
			return $this->cache['data'];
		}

		if ( ! $this->key ) {
			return;
		}

		$url = "{$this->api_url}?q={$query}&orientation=horizontal&key={$this->key}&cat={$this->cat}&order={$this->order}";
		$data = wp_remote_retrieve_body( wp_remote_get( $url ) );
		$data = json_decode( $data, true );

		if ( ! $data ) {
			return;
		}

		$hit = $data['hits'][0];
		$this->set_cache( $query, $hit );
		return $hit;
	}

	function get_image( $query ) {
		$w = $this->get_result( $query );
		if ( ! $w )
			return;

		return $w['largeImageURL'];
	}

	function get_image_credit() {
		$w = $this->get_cache();
		if ( ! $w )
			return;

		$name = $w['user'];
		return "Photo by $name on Pixabay";
	}

}
