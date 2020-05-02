<?php
/**
 * @package DigitalBoard
 */

function return_7200( $seconds ) {
	return 7200;
}

class DigitalBoard {
	private static $initiated = false;
	private static $settings;
	private static $weather_provider;
	private static $image_provider;
	private static $last_rss_item;
	private static $meta_key_use_image_provider = '_dboard_use_image_provider';


	public static function load_text_domain() {
		load_plugin_textdomain( DBOARD_TD, false, DBOARD_PLUGIN_RELPATH . '/translation' );
	}

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

	public static function get_setting( $key, $section ) {
		return self::$settings->get_option($key, $section);
	}

	static function init_hooks() {
		add_action( 'init', array( 'DigitalBoard', 'create_post_types' ) );
		add_action( 'plugins_loaded', array( 'DigitalBoard', 'load_text_domain' ) );
		add_action( 'widgets_init', array( 'DigitalBoard', 'widgets_init' ) );
		add_filter( 'heartbeat2_settings', array( 'DigitalBoard', 'heartbeat_settings' ) );
		add_filter( 'heartbeat2_received', array( 'DigitalBoard', 'heartbeat_received' ), 10, 2 );
		add_action( 'admin_init', array( 'DigitalBoard', 'admin_init' ) );
		add_action( 'admin_menu', array( 'DigitalBoard', 'admin_menu' ) );
		add_filter( 'admin_post_thumbnail_html', array( 'DigitalBoard', 'add_featured_image_display_settings' ), 10, 2 );
		add_action( 'save_post', array( 'DigitalBoard', 'save_post' ), 10, 2 );
	}

	static function save_post( $post_id, $post ) {
		$field_id = self::$meta_key_use_image_provider;

		if ( $post->post_type == DBOARD_SCREEN_POST_TYPE ) {
			if ( isset( $_POST[$field_id] ) ) {
				update_post_meta( $post_id, $field_id, 1 );
			} else {
				delete_post_meta( $post_id, $field_id );
			}
		}
	}

	static function add_featured_image_display_settings( $content, $post_id ) {
		if ( get_post_type( $post_id ) != DBOARD_SCREEN_POST_TYPE ) {
			return $content;
		}

		$field_id    = self::$meta_key_use_image_provider;
		$field_value = esc_attr( get_post_meta( $post_id, $field_id, true ) );
		$field_text  = esc_html__( 'Use image provider', DBOARD_TD );
		$field_state = checked( $field_value, 1, false);

		$field_label = sprintf(
			'<p><label for="%1$s"><input type="checkbox" name="%1$s" id="%1$s" value="%2$s" %3$s> %4$s</label></p>',
			$field_id, $field_value, $field_state, $field_text
		);

		return $content .= $field_label;
	}

	static function admin_init() {
		self::$settings->set_sections( self::get_settings_sections() );
		self::$settings->set_fields( self::get_settings_fields() );
		self::$settings->admin_init();
	}

	static function admin_menu() {
		add_options_page(
			__( 'Digital Board', DBOARD_TD ),
			__( 'Digital Board', DBOARD_TD ),
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
			array(
				'id' => 'dboard_soul',
				'title' => __( 'Soul Settings', DBOARD_TD ),
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

	static function callback_pixabay_demo() {
		$w = self::$image_provider->get_result( 'test' );
		if ( empty( $w ) ) {
			print self::$image_provider->get_last_resp_code();
		} else {
			_e('Passed');
		}
	}

	static function callback_rss_feed_demo() {
		$w = self::get_rss_feed();
		if ( !$w ) {
			_e('Failed');
		} else {
			_e('Passed');
		}
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
					'name' => 'weather_demo',
					'label' => __( 'Status' ),
					'callback' => array( 'DigitalBoard', 'callback_weather_demo' ),
				),
				array(
					'name' => 'pixabay_key',
					'type' => 'text',
					'label' => __( 'Pixabay API key' ),
				),
				array(
					'name' => 'pixabay_demo',
					'label' => __( 'Status' ),
					'callback' => array( 'DigitalBoard', 'callback_pixabay_demo' ),
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
				array(
					'name' => 'rss_feed_demo',
					'label' => __( 'Status' ),
					'callback' => array( 'DigitalBoard', 'callback_rss_feed_demo' ),
				),
				array(
					'name' => 'hb_interval',
					'type' => 'number',
					'min' => 30,
					'max' => 900,
					'placeholder' => 300,
					'label' => __( 'Heartbeat interval', DBOARD_TD ),
					'desc' => __( 'In seconds', DBOARD_TD ),
				),
				array(
					'name' => 'cycle_msgs_interval',
					'type' => 'number',
					'min' => 1,
					'max' => 1000,
					'placeholder' => 5,
					'label' => __( 'Cycle messages interval', DBOARD_TD ),
					'desc' => __( 'In seconds', DBOARD_TD ),
				),
				array(
					'name' => 'cb_create_holiday_msgs',
					'type' => 'checkbox',
					'label' => __('Create holiday messages', DBOARD_TD ),
					'sanitize_callback' => array( 'DigitalBoard', 'sanitize_create_holiday_msgs' ),
				),
			),
			'dboard_soul' => array(
				array(
					'name' => 'bg_img',
					'type' => 'image',
					'label' => __('Featured Image'),
				),
			),
		);

		return $fields;
	}

	static function get_post_by_slug($slug) {
		$args = array(
			'name'           => $slug,
			'post_type'      => DBOARD_MSG_POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => 1
		);
		$my_posts = get_posts( $args );
		return array_shift($my_posts);
	}

	static function register_holiday_msgs() {
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

		$count = 0;
		foreach($tags as $tag) {
			$post = self::get_post_by_slug($tag);
			if ($post)
				continue;

			$my_post = array(
				'post_type'     => DBOARD_MSG_POST_TYPE,
				'post_title'    => $tag,
			);
			wp_insert_post( $my_post );
			$count++;
		}
		return $count;
	}

	static function sanitize_create_holiday_msgs($value) {
		if ($value == 'on') {
			self::register_holiday_msgs();
			add_settings_error( 'cb_create_holiday_msgs', 'cbhm',
					    __("Added holiday msgs"),
					    'updated' );
		}
		return 'off';
	}

	static function heartbeat_settings( $settings ) {
		global $pagenow;
		// minimize the change to index.php as the frontend.
		if ( $pagenow != 'index.php' ) {
			return $settings;
		}

		$interval = self::$settings->get_option( 'hb_interval', 'dboard_basic' );
		if ( empty( $interval ) || $interval < 30 || $interval >  900 ) {
			$interval = 300;
		}

		$settings['interval'] = $interval;
		return $settings;
	}

	static function get_page_version() {
		$page_id = get_the_ID();
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
		if ( empty( $data['dboard'] ) || empty( $data['dboard']['screen_id'] ) ) {
			return $response;
		}

		global $post;
		$post = get_post( $data['dboard']['screen_id'] );
		setup_postdata( $post );

		if ( ! empty( $data['dboard']['last_rss_item'] ) ) {
			self::$last_rss_item = $data['dboard']['last_rss_item'];
		}

		$weather = self::$weather_provider;
		$page_version = self::get_page_version();
		$news_ticker = self::get_news_ticker();

		$interval = self::$settings->get_option( 'cycle_msgs_interval', 'dboard_basic' );
		if ( empty( $interval ) || $interval < 1 || $interval >  1000 ) {
			$interval = 5;
		}

		$response['dboard'] = array(
			'weather_name' => $weather->get_weather_name(),
			'weather_desc' => $weather->get_weather_desc(),
			'weather_temp' => $weather->get_weather_temp(),
			'weather_icon' => $weather->get_weather_icon(),
			'current_date' => self::get_current_date(),
			'background_image' => self::get_background_image(),
			'background_image_credit' => self::get_background_image_credit(),
			'page_version' => $page_version,
			'news_ticker' => $news_ticker,
			'last_rss_item' => self::$last_rss_item,
			'cycle_msgs_interval' => $interval,
		);

		wp_reset_postdata();
		return $response;
	}

	/**
	 * Enqueue this from templates
	 */
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
	}

	static function create_post_types() {
		self::create_msg_post_type();
		self::create_screen_post_type();
		self::create_soul_post_type();
		add_filter( 'manage_'.DBOARD_SOUL_POST_TYPE.'_posts_columns',
			    array( 'DigitalBoard', 'manage_soul_columns' ) );
		add_action( 'manage_'.DBOARD_SOUL_POST_TYPE.'_posts_custom_column',
			    array( 'DigitalBoard', 'manage_soul_custom_column' ), 10, 2 );
	}

	static function manage_soul_columns($columns) {
		 return array(
			 'cb' => '<input type="checkbox" />',
			 'title' => __('Name'),
			 'memorial-day' => __( 'Memorial Day', DBOARD_TD ),
			 'memorial-day-next' => __( 'Next Memorial Day', DBOARD_TD ),
		 );
	}

	static function manage_soul_custom_column($column_name, $post_id) {
		if ($column_name == 'memorial-day') {
			$meta_key     = "soul_memorial_day";
			$memorial_day = get_post_meta( $post_id, $meta_key, true );
			if ( ! empty( $memorial_day ) ) {
				print $memorial_day;
				$greg = h2g($memorial_day);
				if (!$greg) {
					print "<br>" . __( 'Invalid memorial day', DBOARD_TD );
				}
			}
		} else if ($column_name == "memorial-day-next") {
			$meta_key = "soul_memorial_day_next";
			$date = get_post_meta( $post_id, $meta_key, true );
			print $date;
		}
	}

	static function create_soul_post_type() {
		register_post_type( DBOARD_SOUL_POST_TYPE,
			array(
				'labels' => array(
					'name' => __( 'Souls', DBOARD_TD ),
					'singular_name' => __( 'Soul', DBOARD_TD ),
					'new_item'      => __( 'New Soul', DBOARD_TD ),
					'add_new'       => __( 'New Soul', DBOARD_TD ),
					'add_new_item'  => __( 'New Soul', DBOARD_TD ),
				),
				'public' => true,
				'has_archive' => true,
				'menu_icon' => 'dashicons-format-aside',
				'supports' => array('title', 'editor'),
			)
		);
	}

	static function create_msg_post_type() {
		register_post_type( DBOARD_MSG_POST_TYPE,
			array(
				'labels' => array(
					'name' => __( 'Messages', DBOARD_TD ),
					'singular_name' => __( 'Message', DBOARD_TD ),
					'new_item'      => __( 'New Message', DBOARD_TD ),
					'add_new'       => __( 'New Message', DBOARD_TD ),
					'add_new_item'  => __( 'New Message', DBOARD_TD ),
				),
				'public' => true,
				'has_archive' => true,
				'menu_icon' => 'dashicons-format-aside',
				'supports' => array('title', 'editor', 'thumbnail'),
			)
		);
	}

	static function create_screen_post_type() {
		register_post_type( DBOARD_SCREEN_POST_TYPE,
			array(
				'labels' => array(
					'name' => __( 'Screens', DBOARD_TD ),
					'singular_name' => __( 'Screen', DBOARD_TD ),
					'new_item'      => __( 'New Screen', DBOARD_TD ),
					'add_new'       => __( 'New Screen', DBOARD_TD ),
					'add_new_item'  => __( 'New Screen', DBOARD_TD ),
				),
				'public' => true,
				'has_archive' => true,
				'menu_icon' => 'dashicons-desktop',
				'supports' => array('title', 'thumbnail'),
			)
		);
	}

	static function show_msgs( $class ) {
		$post_id = get_the_ID();
    		$meta_key = "_selected_" . DBOARD_MSG_POST_TYPE;
		$selected_post_id = get_post_meta( $post_id, $meta_key, true );

		if ( ! is_array( $selected_post_id ) ) {
			return;
		}

		global $post;
		foreach( $selected_post_id as $p ) {
			$post = get_post( $p );
			setup_postdata( $post );
			$img = self::get_background_image();
			echo "<div class=\"$class\" data-img=\"$img\">";
			the_content();
			echo "</div>";
		}
		wp_reset_postdata();
	}

	static function get_sidebar_id( $post=0 ) {
		$post = get_post( $post );
		return DBOARD_SIDEBAR.'-'.$post->post_name;
	}

	static function widgets_init() {
		$posts = get_posts(array(
			'posts_per_page' => -1,
			'post_type' => DBOARD_SCREEN_POST_TYPE,
		));

		foreach( $posts as $p ) {
			if ( empty( $p->post_name  ) ) {
				continue;
			}

			register_sidebar( array(
				'name' => $p->post_name,
				'id' => self::get_sidebar_id( $p ),
				'before_widget' => '<div id="%1$s" class="widget %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3>',
				'after_title'   => '</h3>',
			) );
		}
	}

	static function get_current_date() {
		return date_i18n( get_option( 'date_format' ) );
	}

	static function use_image_provider() {
		$field_id = self::$meta_key_use_image_provider;
		return esc_attr( get_post_meta( get_the_ID(), $field_id, true ) );
	}

	static function get_background_image() {
		$img = '';

		if ( self::use_image_provider() ) {
			$weather = self::$weather_provider;
			$img = self::$image_provider->get_image( $weather->get_weather_name() );
		}

		if ( empty( $img ) ) {
			$img = wp_get_attachment_url( get_post_thumbnail_id( get_the_ID() ), 'thumbnail' );
		}

		return $img ? : '';
	}

	static function get_background_image_credit() {
		if ( self::use_image_provider() ) {
			return self::$image_provider->get_image_credit();
		}
	}

	static function get_rss_feed_label() {
		return self::$settings->get_option( 'rss_feed_label', 'dboard_basic' );
	}

	static function fetch_feed( $url ) {
		add_filter( 'wp_feed_cache_transient_lifetime' , 'return_7200' );
		$feed = fetch_feed( $url );
		remove_filter( 'wp_feed_cache_transient_lifetime' , 'return_7200' );
		return $feed;
	}

	static function get_rss_feed() {
		$url = self::$settings->get_option( 'rss_feed', 'dboard_basic' );
		$rss = self::fetch_feed( $url );

		if ( is_wp_error( $rss ) ) {
			return false;
		}

		$maxitems = $rss->get_item_quantity( 5 );
		$rss_items = $rss->get_items( 0, $maxitems );
		$last_date = $rss_items[0]->get_date();

		if ( $last_date == self::$last_rss_item ) {
			return;
		}

		self::$last_rss_item = $last_date;
		$out = array();

		foreach ( $rss_items as $item ) {
			$out[] = $item->get_title();
		}

		return $out;
	}

	static function get_news_ticker() {
		$news = self::get_rss_feed();
		if ( ! $news ) {
			return;
		}

		$tmp = '';
		foreach( $news as $item ) {
			$tmp .= '<li><span class="bn-seperator bn-news-dot"></span>'.$item.'</li>';
		}
		$tmp = '<div class="bn-news"><ul>'.$tmp.'</ul></div>';
		$label = '<div class="bn-label">'.DigitalBoard::get_rss_feed_label().'</div>';
		return '<div class="breaking-news-ticker">'.$label.$tmp.'</div>';
	}
}
