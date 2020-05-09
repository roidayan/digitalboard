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
	}

	static function shortcode( $atts ) {
		$a = shortcode_atts( array(
				'url' => '',
				'items'	=> 10
		), $atts );

		if ( empty( $a['url'] ) ) {
			return;
		}

		wp_enqueue_style( 'animate' );
		wp_enqueue_style( 'rss-sc-style' );
		wp_enqueue_script( 'rss-sc-script' );

		$items = self::get_rss_feed( $a['url'], $a['items'] );
		$out = self::parse_items( $items );
		return $out;
	}

	static function parse_items( $items ) {
		$tmp = '';
		foreach( $items as $item ) {
			$tmp .= '<li>'.$item.'</li>';
		}
		$tmp = '<ul>'.$tmp.'</ul>';
		$label = '<div class="rss-news-label"></div>';
		return '<div class="rss-news">'.$label.$tmp.'</div>';
	}

	static function get_rss_feed( $url, $items ) {
		$rss = DigitalBoard::my_fetch_feed( $url );

		if ( is_wp_error( $rss ) ) {
			return false;
		}

		$maxitems = $rss->get_item_quantity( $items );
		$rss_items = $rss->get_items( 0, $maxitems );
		$out = array();

		foreach ( $rss_items as $item ) {
			$out[] = $item->get_title();
		}

		return $out;
	}
}

add_action( 'init', array( 'RSS_SC', 'init' ) );
