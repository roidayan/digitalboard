<?php
/**
 * MetaBox for screen audio
 *
 * @package DigitalBoard
 */

class MB_ScreenAudio {
	var $FOR_POST_TYPE = DBOARD_SCREEN_POST_TYPE;
	var $box_id;
	var $box_label;

	static function get_instance() {
		return new MB_ScreenAudio();
	}

	function __construct() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	function admin_init() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
		$this->meta_key     = "_selected_{$this->FOR_POST_TYPE}_audio";
		$this->box_id       = "select-{$this->FOR_POST_TYPE}-metabox";
		$this->box_label    = __( "Select Audio" );
	}

	function add_meta_boxes() {
		add_meta_box(
			$this->box_id,
			$this->box_label,
			array( $this, 'render_meta_box' ),
			$this->FOR_POST_TYPE,
			'side'
		);
	}

	function render_meta_box( $post ) {
		$post_audio = get_post_meta( $post->ID, 'post_audio', true );
		?>
		<label id="post-label-audio" for="post_audio">Audio</label>
		<input type="text" name="post_audio" id="post_audio" value="<?=$post_audio; ?>" autocomplete="off">
		<?php
	}

	function save_post( $post_id, $post ) {
		if ( $post->post_type != $this->FOR_POST_TYPE ) {
			return;
		}

		if ( ! isset( $_POST['post_audio'] ) ) {
			delete_post_meta( $post_id, 'post_audio' );
			return;
		}

		$value = esc_url_raw( $_POST['post_audio'] );
		update_post_meta( $post_id, 'post_audio', $value );
	}
}

add_action( 'init', array( 'MB_ScreenAudio', 'get_instance' ) );
