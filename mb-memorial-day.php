<?php
/**
 * MetaBox for memorial day
 * Description: hebrew date input box
 *
 * @package DigitalBoard
 */

require_once("lib/converter.php");


class MB_SoulMemorialDay {
	static $instance = null;
	var $FOR_POST_TYPE = DBOARD_SOUL_POST_TYPE;
	var $box_id;
	var $box_label;
	var $field_name;
	var $meta_key        = "soul_memorial_day";
	var $meta_next_date  = "soul_memorial_day_next";
	var $meta_year       = "soul_memorial_day_year";

	static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	function __construct() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'soul_memorial_daily_event', array( $this, 'soul_memorial_daily' ) );
		if (!wp_next_scheduled('soul_memorial_daily_event')) {
			wp_schedule_event(time(), 'hourly', 'soul_memorial_daily_event');
		}
	}

	function admin_init() {
		$this->box_id       = "metabox-soul-memorial-day";
		$this->box_label    = __( 'Memorial Day', DBOARD_TD );
		$this->field_name   = "soul_memorial_day";
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
	}

	function soul_memorial_daily() {
		$posts = $this->query_old_dates();
		foreach($posts as $post) {
			$v = get_post_meta( $post->ID, $this->meta_key, true );
			if (!$v)
				continue;
			$this->save_post_next_date( $post->ID, $v );
		}
	}

	function add_meta_boxes() {
		add_meta_box(
			$this->box_id,
			$this->box_label,
			array( $this, 'content' ),
			$this->FOR_POST_TYPE,
			'normal'
		);
	}

	function content( $post ) {
		$value = get_post_meta( $post->ID, $this->meta_key, true );
		$id = "memorial-day";
		$text = __( 'Date' );
		echo "<table class=\"form-table\">";
		echo "<tr>";
		echo "<th><label for=\"$id\">$text</label></th>";
		echo "<td><input id=\"$id\" name=\"{$this->field_name}\" class=\"regular-text\" value=\"$value\"></input></td>";
		echo "</tr>";
		echo "<tr>";
		$next = false;
		if ($value) {
			$greg = h2g($value);
			if ($greg) {
				$next = h2g_next($value);
			}
			if ( $next ) {
				echo "<th>" . __( 'Next Memorial Day', DBOARD_TD ) . "</th>";
				echo "<td>$next</td>";
			} else {
				echo "<div class=\"error\">" . __( 'Invalid memorial day', DBOARD_TD ) . "</div>";
			}
		}
		echo "</tr>";
		echo "</table>";
	}

	function save_post_next_date( $post_id, $hebdate ) {
		$next = h2g_next($hebdate);
		if ($next) {
			update_post_meta( $post_id, $this->meta_next_date, $next );
		} else {
			delete_post_meta( $post_id, $this->meta_next_date );
		}
	}

	function save_post_memorial_year( $post_id, $hebdate ) {
		$year = h2g_get_year($hebdate);
		if ($year) {
			update_post_meta( $post_id, $this->meta_year, $year );
		} else {
			delete_post_meta( $post_id, $this->meta_year );
		}
	}

	function save_post( $post_id, $post ) {
		if ( $post->post_type == $this->FOR_POST_TYPE ) {
			if ( isset( $_POST[$this->field_name] ) ) {
				$v = sanitize_text_field( $_POST[$this->field_name] );
				update_post_meta( $post_id, $this->meta_key, $v );
				$this->save_post_next_date( $post_id, $v );
				$this->save_post_memorial_year( $post_id, $v );
			} else {
				delete_post_meta( $post_id, $this->meta_key );
				delete_post_meta( $post_id, $this->meta_next_date );
				delete_post_meta( $post_id, $this->meta_year );
			}
		}
	}

	function query_next_dates() {
		$current = current_time('timestamp');
		$today = date( 'Y-m-d', $current );
		$this_year = date( 'Y', $current );
		$maxdate = strtotime( '+7 day', $current );
		$maxdate = date( 'Y-m-d', $maxdate );
		$args = array(
			'post_type'  => $this->FOR_POST_TYPE,
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key'     => $this->meta_year,
					'type'    => 'YEAR',
					'value'   => $this_year,
					'compare' => '=',
				),
				array(
					'relation' => 'AND',
					array(
						'key'     => $this->meta_next_date,
						'type'    => 'DATETIME',
						'value'   => $today,
						'compare' => '>=',
					),
					array(
						'key'     => $this->meta_next_date,
						'type'    => 'DATETIME',
						'value'   => $maxdate,
						'compare' => '<=',
					),
				),
			),
		);
		return get_posts( $args );
	}

	function query_old_dates() {
		$today = date( 'Y-m-d', current_time('timestamp') );
		$args = array(
			'post_type'  => $this->FOR_POST_TYPE,
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key'     => $this->meta_next_date,
					'type'    => 'DATETIME',
					'value'   => $today,
					'compare' => '<',
				),
				array(
					'key'     => $this->meta_next_date,
					'value'   => '',
					'compare' => 'NOT EXISTS',
				),
			),
		);
		return get_posts( $args );
	}
}

add_action( 'init', array( 'MB_SoulMemorialDay', 'get_instance' ) );
