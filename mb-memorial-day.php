<?php
/**
 * MetaBox for memorial day
 * Description: hebrew date input box
 *
 * @package DigitalBoard
 */

require_once("lib/converter.php");


class MB_SoulMemorialDay {
	var $FOR_POST_TYPE = DBOARD_SOUL_POST_TYPE;
	var $box_id;
	var $box_label;
	var $field_name;
	var $meta_key;
	var $meta_next_date;

	static function get_instance() {
		return new self();
	}

	function __construct() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	function admin_init() {
		$this->box_id       = "metabox-soul-memorial-day";
		$this->box_label    = __( 'Memorial Day' );
		$this->field_name   = "soul_memorial_day";
		$this->meta_key     = "soul_memorial_day";
		$this->meta_next_date  = "soul_memorial_day_next";
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
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
		echo "</table>";
		if ($value) {
			$greg = h2g($value);
			if ($greg) {
				print date("Y-m-d", strtotime($greg));
				$next = h2g_next($value);
				print "<br>Next: ".date("Y-m-d", $next);
			} else {
				print __('Invalid date');
			}
		}
	}

	function save_post_next_date( $post_id, $hebdate ) {
		$next = h2g_next($hebdate);
		if ($next) {
			$next = date("Y-m-d", $next);
			update_post_meta( $post_id, $this->meta_next_date, $next );
		} else {
			delete_post_meta( $post_id, $this->meta_next_date );
		}
	}

	function save_post( $post_id, $post ) {
		if ( $post->post_type == $this->FOR_POST_TYPE ) {
			if ( isset( $_POST[$this->field_name] ) ) {
				$v = esc_attr( $_POST[$this->field_name] );
				update_post_meta( $post_id, $this->meta_key, $v );
				$this->save_post_next_date( $post_id, $v );
			} else {
				delete_post_meta( $post_id, $this->meta_key );
				delete_post_meta( $post_id, $this->meta_next_date );
			}
		}
	}
}

add_action( 'init', array( 'MB_SoulMemorialDay', 'get_instance' ) );
