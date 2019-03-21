<?php

class Unsplash {
	static $instance = null;
	var $data = 0;
	var $key = "";
	var $min_time = 3600; // minimum time for refresh for the same query.
	var $last_query = '';
	var $last_refresh = 0;
	var $width = 1080;
	var $height = 768;

	static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	function get_cache() {
		return $this->data;
	}

	function use_cache( $query ) {
		return ( $query == $this->last_query && time() - $this->last_refresh < $this->min_time);
	}

	function get_result( $query ) {
		if ( $this->use_cache( $query ) ) {
			return $this->data;
		}

		if ( ! $this->key ) {
			return;
		}

		$url = "https://api.unsplash.com/photos/random?query={$query}&client_id={$this->key}&w={$this->width}&h={$this->height}&fit=max";
		$data = wp_remote_retrieve_body( wp_remote_get( $url ) );
		$data = json_decode( $data, true );

		if ( ! $data ) {
			return;
		}

		$this->data = $data;
		$this->last_refresh = time();
		$this->last_query = $query;
		return $data;
	}

	function get_image( $query ) {
		$w = $this->get_result( $query );
		if ( ! $w )
			return;

		$img = $w['urls']['custom'];
		return $img;
	}

	function get_image_credit() {
		$w = $this->get_cache();
		if ( ! $w )
			return;

		$name = $w['user']['name'];
		return "Photo by $name on Unsplash";
	}

}
