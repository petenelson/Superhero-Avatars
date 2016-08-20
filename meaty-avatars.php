<?php
/*
 * Plugin Name: Meaty Avatars
 * Plugin URI: #
 * Author: Pete Nelson
 * Author URI: https://twitter.com/GunGeekATX
 * Description: Replace avatars with images of meat
 * License: WTFPL
 * Text Domain: meaty-avatars
 * Version: 1.1
 */


if ( !defined( 'ABSPATH' ) ) exit( 'restricted access' );

define( 'MEATY_AVATARS_PLUGIN_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );

require_once 'includes/class-meaty-avatars.php';

if ( class_exists( 'Meaty_Avatars' ) ) {
	$meaty_avatars = new Meaty_Avatars();
	add_action( 'plugins_loaded', array( $meaty_avatars, 'plugins_loaded' ) );
}
