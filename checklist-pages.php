<?php
/**
 * @package DigitalBoard
 */

/* https://core.trac.wordpress.org/ticket/20167 */
function wp1_checklist_pages($args = '') {
	$defaults = array('depth' => 0, 'name' => 'check', 'checked' => 0, 'echo' => 1, 'post_type' => 'page');

	$r = wp_parse_args( $args, $defaults );

	$pages = get_pages( $r );
	$output = '';

	// Back-compat with old system where both id and name were based on $name argument
	if ( empty( $r['id'] ) ) {
		$r['id'] = $r['name'];
	}

	if ( empty($pages) ) {
		$output = $r['show_option_none'];
	} else {
		$output = walk_page_checklist_tree($pages, $r['depth'], $r);
	}

	if ( $r['echo'] )
		echo $output;

	return $output;
}


/**
 * Retrieve HTML checklist of pages for page list.
 *
 * @uses Walker_Page_Checklist to create  checklist of pages.
 * @since 
 */
function walk_page_checklist_tree() {
	$args = func_get_args();
	if ( empty($args[2]['walker']) ) // the user's options are the third parameter
		$walker = new Walker_Page_Checklist;
	else
		$walker = $args[2]['walker'];

	return call_user_func_array(array(&$walker, 'walk'), $args);
}

/**
 * Creates a checklist of pages.
 *
 * @package WordPress
 * @since 
 * @uses Walker
 */
class Walker_Page_Checklist extends Walker {
	var $db_fields = array ('parent' => 'post_parent', 'id' => 'ID');

	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent<ul class='children'>\n";
	}
	function end_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}	

	function start_el(&$output, $page, $depth = 0, $args = array(), $current_object_id = 0) {
		$output .= "\t<li class=\"level-$depth\">".
			"<label class=\"selectit\">".
			"<input type=\"checkbox\" name=\"${args['name']}[]\" value=\"$page->ID\"";

		if ( is_array( $args['checked'] ) ) {
			$output .= checked( in_array( $page->ID, $args['checked'] ), true, false );
		}

		$output .= '>';
		$title = apply_filters( 'list_pages', $page->post_title, $page );
		$output .= esc_html( $title );
	}
	function end_el( &$output, $category, $depth = 0, $args = array() ) {
		$output .= "</label></li>\n";
	}
}
