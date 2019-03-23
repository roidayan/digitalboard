<?php
/*
 * Template Name: Digital Board Template 1
 * Template Post Type: dboard_screen
 *
 * @package DigitalBoard
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
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

$img = DigitalBoard::get_background_image();
?>

<style>
	.container {
		background-image: url("<?php echo $img; ?>");
	}
</style>

<script>
	var pagenow = <?php the_ID(); ?>;
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

<div class="container">
  <div class="background-image-credit"><?php echo DigitalBoard::get_background_image_credit(); ?></div>
  <div class="wrapper">
    <div class="msg">
<?php
	DigitalBoard::show_msgs();
?>
    </div> <!-- /msg -->
    <div class="sidebar">
<?php if ( is_active_sidebar( DBOARD_SIDEBAR ) ) : ?>
	<div id="secondary" class="sidebar-container">
	  <div class="widget-area">
	<?php dynamic_sidebar( DBOARD_SIDEBAR ); ?>
 	  </div>
	</div>
<?php endif; ?>
    </div> <!-- /sidebar -->
  </div> <!-- /wrapper -->
</div> <!-- /container -->

<?php
	$news = DigitalBoard::get_news_ticker();
	if ( $news ) {
		echo "<footer>$news</footer>";
	}
?>

</body>
<?php wp_footer(); ?>
</html>
