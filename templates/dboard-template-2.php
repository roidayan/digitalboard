<?php
/*
 * Template Name: Digital Board Template 2
 * Template Post Type: dboard_screen
 *
 * @package DigitalBoard
 */

function tpl_scripts() {
	wp_enqueue_style( 'animate',
		plugins_url( 'animate.min.css', __FILE__ ),
		array(), '3.7.2' );

	wp_enqueue_style( 'dboard-page-style',
		plugins_url( 'dboard-template-2.css', __FILE__ ),
		array(), '1.0.0' );

	if ( is_rtl() ) {
		wp_enqueue_style( 'dboard-page-style-rtl',
			plugins_url('dboard-template-1-rtl.css', __FILE__),
			array(), '1.0.0' );
	}
}

add_action( 'wp_enqueue_scripts', array( 'DigitalBoard', 'enqueue_scripts' ), 20 );
add_action( 'wp_enqueue_scripts', 'tpl_scripts', 20 );

function get_post_by_slug($slug) {
	$args = array(
		'name'           => $slug,
		'post_type'      => DBOARD_MSG_POST_TYPE,
		'post_status'    => 'publish',
		'posts_per_page' => 1
	);
	$my_posts = get_posts( $args );
	return array_shift($my_posts);
}

function get_post_thumbnail($post_id) {
	return wp_get_attachment_url( get_post_thumbnail_id( $post_id ), 'thumbnail' );
}

function show_holiday_page( $class ) {
	$h = Hebcal::get_instance();
	$items = $h->calendar_today('holiday','major');

	if (!$items)
		return;

	$item = $items[0];
	$slug = basename($item['link']);
	$post = get_post_by_slug($slug);
	if ($post)
		$img = get_post_thumbnail($post->ID);
	else
		$img = "";
	$title = $item['title'];
	$content = "";

	/*
	 * major
	 * rosh-hashana
	 * yom-kippur
	 * sukkot
	 * shmini-atzeret
	 * simchat-torah
	 * chanukah
	 * purim
	 * pesach
	 * shavuot
	 * tisha-bav
	 */

	echo "<div class=\"$class\" data-img=\"$img\">";
	echo "<h3>$title</h3>";
	echo $content;
	echo "</div>";
}

?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="google" content="notranslate">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php
wp_head();

function show_weather() {
	$weather = OpenWeatherMap::get_instance();
	echo '<div class="weather-line">';
 	echo "<span class=\"icon\"><img src=\"".$weather->get_weather_icon()."\"/></span>";
	echo "<span class=\"temp\">".$weather->get_weather_temp()."</span>";
	echo "<span class=\"desc\">".$weather->get_weather_desc()."</span>";
	echo "</div>";
}

$bg_img = DigitalBoard::get_background_image();
?>

<script>
	var pagenow = <?php the_ID(); ?>;
	jQuery(document).ready(function( $ ) {
		cycle_single_msgs();
		refresh_at_midnight();
	});
</script>
</head>
<body <?php body_class(); ?>>

<header>
<?php
	show_weather();
?>
  <div class="date"><?php echo DigitalBoard::get_current_date(); ?></div>
  <div class="clock" id="clock1"></div>
</header>

<div class="parent-container" style="background-image: url('<?php echo $bg_img; ?>')">
<div class="container" style="background-image: url('<?php echo $bg_img; ?>')">
  <div class="background-image-credit"><?php echo DigitalBoard::get_background_image_credit(); ?></div>
  <div class="wrapper">
    <div class="sidebar-msgs">
	<?php
	DigitalBoard::show_msgs( "msg widget hide" );
	show_holiday_page( "msg widget hide" );
	?>
    </div> <!-- /sidebar-msgs -->
    <div class="sidebar">
<?php if ( is_active_sidebar( DigitalBoard::get_sidebar_id() ) ) : ?>
	<div id="secondary" class="sidebar-container">
	  <div class="widget-area">
	<?php dynamic_sidebar( DigitalBoard::get_sidebar_id() ); ?>
 	  </div>
	</div>
<?php endif; ?>
    </div> <!-- /sidebar -->
  </div> <!-- /wrapper -->
</div> <!-- /container -->
</div> <!-- /parent-container -->

<?php
//	echo "<footer></footer>";
?>

</body>
<?php wp_footer(); ?>
</html>
