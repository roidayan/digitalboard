<?php
/**
 * @package DigitalBoard
 */
/*
Plugin Name: Digital Board
Plugin URI: https://roidayan.com
Description: Digital board for lobbies for displaying messages and ads to tenants and guests.
Version: 0.1.0
Author: Roi Dayan
Author URI: https://roidayan.com
License: GPLv2 or later
Text Domain: dboard
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
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'DBOARD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DBOARD_MSG_POST_TYPE', 'dboard_msg' );
define( 'DBOARD_SCREEN_POST_TYPE', 'dboard_screen' );
define( 'DBOARD_SIDEBAR', 'sidebar-dboard' );

require_once( DBOARD_PLUGIN_DIR . 'class.dboard.php' );
require_once( DBOARD_PLUGIN_DIR . 'class.heartbeat2.php' );
require_once( DBOARD_PLUGIN_DIR . 'class.settings.php' );
require_once( DBOARD_PLUGIN_DIR . 'class.pixabay.php' );
require_once( DBOARD_PLUGIN_DIR . 'class.openweathermap.php' );
require_once( DBOARD_PLUGIN_DIR . 'checklist-pages.php' );
require_once( DBOARD_PLUGIN_DIR . 'pagetemplater.php' );
require_once( DBOARD_PLUGIN_DIR . 'mb-msgs.php' );

Heartbeat2::init();
DigitalBoard::init();
