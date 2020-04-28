<?php
/**
 * @package DigitalBoard
 */
/*
Plugin Name: Digital Board
Plugin URI: https://roidayan.com
Description: Digital board for lobbies for displaying messages and ads to tenants and guests.
Version: 1.0.0
Author: Roi Dayan
Author URI: https://roidayan.com
License: GPLv2 or later
Text Domain: digital-board
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

if ( !function_exists( 'add_action' ) ) {
	echo "Hi there! I'm just a plugin, not much I can do when called directly.";
	exit;
}

define( 'DBOARD_TD', 'digital-board' );
define( 'DBOARD_PLUGIN_RELPATH', dirname( plugin_basename( __FILE__ ) ) );
define( 'DBOARD_MSG_POST_TYPE', 'dboard_msg' );
define( 'DBOARD_SCREEN_POST_TYPE', 'dboard_screen' );
define( 'DBOARD_SOUL_POST_TYPE', 'dboard_soul' );
define( 'DBOARD_SIDEBAR', 'sidebar-dboard' );

require_once( 'class.dboard.php' );
require_once( 'class.heartbeat2.php' );
require_once( 'class.settings.php' );
require_once( 'class.pixabay.php' );
require_once( 'class.openweathermap.php' );
require_once( 'checklist-pages.php' );
require_once( 'pagetemplater.php' );
require_once( 'mb-msgs.php' );
require_once( 'mb-memorial-day.php' );
require_once( 'shortcodes.php' );

Heartbeat2::init();
DigitalBoard::init();
