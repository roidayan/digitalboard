<?php

class Pixabay {
	static $instance = null;
	var $data = 0;
	var $key = "";
	var $min_time = 3600; // minimum time for refresh for the same query.
	var $last_query = '';
	var $last_refresh = 0;
	var $api_url = 'https://pixabay.com/api/';
	var $cat = 'nature';
	var $order = 'latest';

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
		return $this->data;
	}

	function set_cache( $query, $data ) {
		$this->last_refresh = time();
		$this->last_query = $query;
		$this->data = $data;
	}

	function valid_cache( $query ) {
		return ( $query == $this->last_query && time() - $this->last_refresh < $this->min_time);
	}

	function get_result( $query ) {
		if ( $this->valid_cache( $query ) ) {
			return $this->get_cache();
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
