<?php
/**
 * MetaBox for memorial day
 * Description: hebrew date input box
 *
 * @package DigitalBoard
 */

class MB_SoulMemorialDay {
	var $FOR_POST_TYPE = DBOARD_SOUL_POST_TYPE;
	var $box_id;
	var $box_label;
	var $meta_key;
	var $field_name;

	static function get_instance() {
		return new self();
	}

	function __construct() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	function admin_init() {
		$this->box_id       = "metabox-soul-memorial-day";
		$this->box_label    = __( 'Memorial Day' );
		$this->meta_key     = "soul_memorial_day";
		$this->field_name   = "soul_memorial_day";
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
//		$strip = 'אבגדהוזחטיכלמנסעפצקרשת ';
//		echo preg_replace('/[^'.$strip.']/', "", $value);
	}

	function save_post( $post_id, $post ) {
		if ( $post->post_type == $this->FOR_POST_TYPE ) {
			if ( isset( $_POST[$this->field_name] ) ) {
				$v = esc_attr( $_POST[$this->field_name] );
				update_post_meta( $post_id, $this->meta_key, $v );
			} else {
				delete_post_meta( $post_id, $this->meta_key );
			}
		}
	}
}

add_action( 'init', array( 'MB_SoulMemorialDay', 'get_instance' ) );
