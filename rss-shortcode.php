<?php
/**
 * @package DigitalBoard
 */


class RSS_SC {
	public static function init() {
		add_shortcode( 'rss-sc', array( 'RSS_SC', 'shortcode' ) );
		wp_register_script( 'rss-sc-script',
							plugins_url( 'rss-sc.js', __FILE__ ),
							array( 'jquery' ), '1.0.0', true );
		wp_register_style( 'rss-sc-style',
						   plugins_url( 'rss-sc.css', __FILE__ ),
						   array(), '1.0.0' );
		add_filter( 'heartbeat2_received', array( 'RSS_SC', 'heartbeat_received' ), 10, 2 );
	}

	static function heartbeat_received( $response, $data ) {
		if ( empty( $data['dboard'] ) || empty( $data['dboard']['screen_id'] ) ) {
			return $response;
		}

		$response['rss-sc'] = array();
		foreach( $data['rss-sc'] as $attr ) {
			$attr['items'] = self::get_rss_feed( $attr );
			$response['rss-sc'][] = $attr;
		}

		return $response;
	}

	static function shortcode( $atts ) {
		extract(shortcode_atts( array(
				'url' => '',
				'items'	=> 10,
				'html_decode' => false,
		), $atts ));

		if ( empty( $url ) ) {
			return;
		}

		wp_enqueue_style( 'animate' );
		wp_enqueue_style( 'rss-sc-style' );
		wp_enqueue_script( 'rss-sc-script' );

		/* uniqid() is only based micro seconds so to increase chance of uniq use str_shuffle() */
		$id = 'rss'.str_shuffle(uniqid());

		return '<div class="rss-news" data-id="'.$id.'" data-url="'.$url.'" data-items="'.$items.'" data-html_decode="'.$html_decode.'"></div>';
	}

	static function get_rss_feed( $attr ) {
		$url = $attr['url'];
		$items = $attr['items'];
		$rss = DigitalBoard::my_fetch_feed( $url );

		if ( is_wp_error( $rss ) ) {
			return false;
		}

		$html_decode = isset( $attr['html_decode'] );

		$maxitems = $rss->get_item_quantity( $items );
		$rss_items = $rss->get_items( 0, $maxitems );
		$out = array();

		foreach ( $rss_items as $item ) {
			$tmp = $item->get_title();
			if ( $html_decode ) {
				$tmp = htmlspecialchars_decode( $tmp );
			}
			$out[] = esc_html( $tmp );
		}

		return $out;
	}
}

add_action( 'init', array( 'RSS_SC', 'init' ) );
