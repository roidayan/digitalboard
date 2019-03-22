<?php
/**
 * Heartbeat2 based on Wordpress heartbeat.
 *
 * @package DigitalBoard
 */

class Heartbeat2 {
	static $instance = null;

	static function init() {
		self::get_instance();
	}

	static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	function __construct() {
		add_action( 'wp_ajax_heartbeat2', array( $this, 'ajax_heartbeat2_handler' ) );
		add_action( 'wp_ajax_nopriv_heartbeat2', array( $this, 'ajax_heartbeat2_nopriv_handler' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 1 );
	}

	function ajax_heartbeat2_handler() {
		// relogin invalidates the nonce. so why check it?
		$result = check_ajax_referer( 'heartbeat2', '_nonce', false );

		$data = array();

		if ( is_array( $_POST['data'] ) ) {
			$data = array_merge( $data, $_POST['data'] ); 
		}

		$response = array();
		$response = apply_filters( 'heartbeat2_received', $response, $data );

		if ( $result === false || $result == 2 ) {
			// result 2 means nonce is about to expire.
			$response['heartbeat_nonce'] = wp_create_nonce( 'heartbeat2' );
		}

		echo wp_json_encode( $response );
		wp_die();
	}

	function ajax_heartbeat2_nopriv_handler() {
		return $this->ajax_heartbeat2_handler();
	}

	function enqueue_scripts() {
		wp_register_script( 'heartbeat2',
				    plugins_url( '/heartbeat2.js', __FILE__ ),
				    array( 'jquery' ), '1.0.0', true );

		$nonce = wp_create_nonce( 'heartbeat2' );

		$settings = apply_filters( 'heartbeat2_settings', array() );
		$settings = array_merge( array( 'ajaxurl' => admin_url( 'admin-ajax.php' ),
						'nonce'   => $nonce,
					 ), $settings );

		wp_localize_script( 'heartbeat2', 'heartbeat2Settings', $settings );
	}
}
