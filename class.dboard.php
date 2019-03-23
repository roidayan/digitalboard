<?php
/**
 * @package DigitalBoard
 */

class DigitalBoard {
	private static $initiated = false;
	private static $settings;
	private static $weather_provider;
	private static $image_provider;

	public static function init() {
		if ( ! self::$initiated ) {
			self::$initiated = true;
		}

		self::$settings = new DigitalBoard_Settings_API;

		$openweathermap_key = self::$settings->get_option( 'openweathermap_key', 'dboard_basic' );
		$openweathermap_loc = self::$settings->get_option( 'openweathermap_loc', 'dboard_basic' );
		$openweathermap_lang = self::$settings->get_option( 'openweathermap_lang', 'dboard_basic' );
		$pixabay_key = self::$settings->get_option( 'pixabay_key', 'dboard_basic' );

		self::$weather_provider = OpenWeatherMap::get_instance( $openweathermap_key,
									$openweathermap_loc,
									$openweathermap_lang );
		self::$image_provider = Pixabay::get_instance( $pixabay_key );
		self::init_hooks();
	}

	static function init_hooks() {
		add_action( 'init', array( 'DigitalBoard', 'create_post_types' ) );
		add_action( 'widgets_init', array( 'DigitalBoard', 'widgets_init' ) );
		add_action( 'wp_enqueue_scripts', array( 'DigitalBoard', 'enqueue_scripts' ), 20 );
		add_filter( 'heartbeat2_settings', array( 'DigitalBoard', 'heartbeat_settings' ) );
		add_filter( 'heartbeat2_received', array( 'DigitalBoard', 'heartbeat_received' ), 10, 2 );
		add_action( 'admin_init', array( 'DigitalBoard', 'admin_init' ) );
		add_action( 'admin_menu', array( 'DigitalBoard', 'admin_menu' ) );
	}

	static function admin_init() {
		self::$settings->set_sections( self::get_settings_sections() );
		self::$settings->set_fields( self::get_settings_fields() );
		self::$settings->admin_init();
	}

	static function admin_menu() {
		add_options_page( 'Digital Board', 'Digital Board',
				  'manage_options', 'digital_board_settings',
			array( 'DigitalBoard', 'plugin_page' ) );
	}

	static function plugin_page() {
		echo '<div class="wrap">';
		self::$settings->show_navigation();
		self::$settings->show_forms();
		echo '</div>';
	}

	static function get_settings_sections() {
		$sections = array(
			array(
				'id' => 'dboard_basic',
				'title' => __( 'General Settings' ),
			),
		);

		return $sections;
	}

	static function callback_weather_demo() {
		$w = self::$weather_provider->get_weather();
		if ( $w['cod'] != 200 )
			print $w['message'];
		else
			print $w['name'].", ".$w['weather'][0]['description'];
	}

	static function get_settings_fields() {
		$fields = array(
			'dboard_basic' => array(
				array(
					'name' => 'openweathermap_key',
					'type' => 'text',
					'label' => __( 'OpenWeahterMap API key' ),
				),
				array(
					'name' => 'openweathermap_loc',
					'type' => 'text',
					'label' => __( 'OpenWeahterMap location' ),
					'default' => 'Tel Aviv,IL',
				),
				array(
					'name' => 'openweathermap_lang',
					'type' => 'text',
					'label' => __( 'OpenWeahterMap language' ),
					'default' => 'he',
				),
				array(
					'name' => '',
					'label' => __( 'Weather' ),
					'callback' => array( 'DigitalBoard', 'callback_weather_demo' ),
				),
				array(
					'name' => 'pixabay_key',
					'type' => 'text',
					'label' => __( 'Pixabay API key' ),
				),
				array(
					'name' => 'rss_feed_label',
					'type' => 'text',
					'label' => __( 'RSS feed label' ),
				),
				array(
					'name' => 'rss_feed',
					'type' => 'text',
					'label' => __( 'RSS feed' ),
				),
			),
		);

		return $fields;
	}

	static function heartbeat_settings( $settings ) {
		global $pagenow;
		// minimize the change to index.php as the frontend.
		if ( $pagenow != 'index.php' ) {
			return $settings;
		}
		$settings['interval'] = 300;
		return $settings;
	}

	static function get_page_version( $page_id ) {
		$mod = get_the_modified_time( 'U', $page_id );
		// TODO
		// 1.check msgs mod time?
		// 2.check widgets mod time?
		// 3.detect new/removed widget?
		// 4.event for 1,2,3 will set refresh for next time?
		// refresh once a day anyway? too long waiting. we want scheduled msgs later.
		return $mod;
	}

	static function heartbeat_received( $response, $data ) {
		if ( empty( $data['dboard'] ) || empty( $data['dboard']['screen_id'] ) )
			return $response;

		$weather = self::$weather_provider;
		$page_version = self::get_page_version( $data['dboard']['screen_id'] );

		$response['dboard'] = array(
			'weather_name' => $weather->get_weather_name(),
			'weather_desc' => $weather->get_weather_desc(),
			'weather_temp' => $weather->get_weather_temp(),
			'weather_icon' => $weather->get_weather_icon(),
			'current_date' => self::get_current_date(),
			'background_image' => self::get_background_image(),
			'background_image_credit' => self::get_background_image_credit(),
			'page_version' => $page_version,
		);

		return $response;
	}

	static function enqueue_scripts() {
		wp_enqueue_script( 'heartbeat2' );

		wp_enqueue_script( 'news-ticker',
			 plugins_url( 'news-ticker/breaking-news-ticker.min.js', __FILE__ ),
			array( 'jquery' ), '1.0.0' );

		wp_enqueue_style( 'news-ticker',
			plugins_url( 'news-ticker/breaking-news-ticker.css', __FILE__ ),
			array(), '1.0.0' );

		wp_enqueue_script( 'dboard-screen-script',
			plugins_url( 'screen.js', __FILE__ ),
			array( 'jquery' ), '1.0.0', true );

		wp_enqueue_style( 'dboard-page-style',
			plugins_url( 'templates/dboard-template-1.css', __FILE__ ),
			array(), '1.0.0' );

		if ( is_rtl() ) {
			wp_enqueue_style( 'dboard-page-style-rtl',
				plugins_url('templates/dboard-template-1-rtl.css', __FILE__),
				array(), '1.0.0' );
		}
	}

	static function create_post_types() {
		self::create_msg_post_type();
		self::create_screen_post_type();
	}

	static function create_msg_post_type() {
		register_post_type( DBOARD_MSG_POST_TYPE,
			array(
				'labels' => array(
					'name' => __( 'Message' ),
					'singular_name' => __( 'Message' )
				),
				'public' => true,
				'has_archive' => true,
				'menu_icon' => 'dashicons-format-aside',
			)
		);
	}

	static function create_screen_post_type() {
		register_post_type( DBOARD_SCREEN_POST_TYPE,
			array(
				'labels' => array(
					'name' => __( 'Screen' ),
					'singular_name' => __( 'Screen' )
				),
				'public' => true,
				'has_archive' => true,
				'menu_icon' => 'dashicons-desktop',
				'supports' => array('title', 'thumbnail'),
			)
		);
	}

	static function show_msgs() {
		$post_id = get_the_ID();
    		$meta_key = "_selected_" . DBOARD_MSG_POST_TYPE;
	  	$selected_post_id = get_post_meta( $post_id, $meta_key, true );
		if ( ! is_array( $selected_post_id ) ) {
			return;
		}
		$first = $selected_post_id[0];
		global $post;
		$post = get_post($first);
		setup_postdata( $post );
		the_title( '<h3>', '</h3>' );
		the_content();
		wp_reset_postdata( $post );
	}

	static function widgets_init() {
		register_sidebar( array(
			'name' => __( 'Digital Board Sidebar' ),
			'id' => DBOARD_SIDEBAR,
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3>',
			'after_title'   => '</h3>',
		) );
	}

	static function get_current_date() {
		return date_i18n( get_option( 'date_format' ) );
	}

	static function get_background_image() {
		$weather = self::$weather_provider;
		$img = self::$image_provider->get_image( $weather->get_weather_name() );

		if ( empty( $img ) ) {
			$img = wp_get_attachment_url( get_post_thumbnail_id( get_the_ID() ), 'thumbnail' );
		}

		return $img;
	}

	static function get_background_image_credit() {
		return self::$image_provider->get_image_credit();
	}

	static function get_rss_feed_label() {
		return self::$settings->get_option( 'rss_feed_label', 'dboard_basic' );
	}

	static function get_rss_feed() {
		$url = self::$settings->get_option( 'rss_feed', 'dboard_basic' );
		$rss = fetch_feed( $url );
		$maxitems = 0;
		$out = array();

		if ( ! is_wp_error( $rss ) ) {
			$maxitems = $rss->get_item_quantity( 5 );
			$rss_items = $rss->get_items( 0, $maxitems );
			foreach ( $rss_items as $item ) {
				$out[] = $item->get_title();
			}
		}

		return $out;
	}
}
