<?php
/**
 * MetaBox for selecting msgs
 * Description: <a target="_blank" href="http://wordpress.stackexchange.com/q/85107/89">WPSE 85107</a>
 *
 * @package DigitalBoard
 */

class WPSE_85107 {
	var $FOR_POST_TYPE = DBOARD_SCREEN_POST_TYPE;
	var $SELECT_POST_TYPE = DBOARD_MSG_POST_TYPE;
	var $box_id;
	var $box_label;
	var $field_id;
	var $field_label;
	var $field_name;
	var $meta_key;

	static function get_instance() {
		return new WPSE_85107();
	}

	function __construct() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	function admin_init() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
		$this->meta_key     = "_selected_{$this->SELECT_POST_TYPE}";
		$this->box_id       = "select-{$this->SELECT_POST_TYPE}-metabox";
		$this->field_id     = "selected-{$this->SELECT_POST_TYPE}";
		$this->field_name   = "selected_{$this->SELECT_POST_TYPE}";
		$this->box_label    = __( "Messages", DBOARD_TD );
		$this->field_label  = __( "No Messages", DBOARD_TD );
	}

	function add_meta_boxes() {
		add_meta_box(
			$this->box_id,
			$this->box_label,
			array( $this, 'select_box' ),
			$this->FOR_POST_TYPE,
			'side'
		);
	}

	function select_box( $post ) {
		global $wp_post_types;
		$selected_post_id = get_post_meta( $post->ID, $this->meta_key, true );
		$save_hierarchical = $wp_post_types[$this->SELECT_POST_TYPE]->hierarchical;
		$wp_post_types[$this->SELECT_POST_TYPE]->hierarchical = true;
		wp1_checklist_pages( array(
			'id' => $this->field_id,
			'name' => $this->field_name,
			'checked' => empty( $selected_post_id ) ? 0 : $selected_post_id,
			'post_type' => $this->SELECT_POST_TYPE,
			'show_option_none' => $this->field_label,
			'sort_column' => 'post_title',
		));
		$wp_post_types[$this->SELECT_POST_TYPE]->hierarchical = $save_hierarchical;
	}

	function save_post( $post_id, $post ) {
		if ( $post->post_type != $this->FOR_POST_TYPE ) {
			return;
		}

		if ( ! isset( $_POST[$this->field_name] ) ) {
			delete_post_meta( $post_id, $this->meta_key );
			return;
		}

		$sanitizedValues = array_filter( $_POST[$this->field_name], 'ctype_digit' );
		update_post_meta( $post_id, $this->meta_key, $sanitizedValues );
	}
}

add_action( 'init', array( 'WPSE_85107', 'get_instance' ) );
