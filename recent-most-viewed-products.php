<?php
/**
 *	Plugin Name: Recent - Most Viewed Products 
 *	Description: Display Recently viewed and Most viewed products through shortcodes.
 *	Version: 1.0.0
 *	Text Domain: recent-most-viewed-products
 *
 *  @package RecentMostViewedProducts
*/

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'RM_VIEWED_PLUGIN_VER' ) ) {
    define( 'RM_VIEWED_PLUGIN_VER', '1.0.0' );
}

if ( ! defined( 'RM_VIEWED_PLUGIN_DIR_PATH' ) ) {
    define( 'RM_VIEWED_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'RM_VIEWED_PLUGIN_DIR_URL' ) ) {
    define( 'RM_VIEWED_PLUGIN_DIR_URL',  __FILE__ );
}


// Include the main WC COG class.
include_once RM_VIEWED_PLUGIN_DIR_PATH . '/includes/class-rm-viewed-core-functions.php' ;
