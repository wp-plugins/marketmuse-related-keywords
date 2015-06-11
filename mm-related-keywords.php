<?php
/**
 * MarketMuse Related Keywords
 *
 * Get keyword suggestions using Market Muse API.
 *
 * @package   MM_Related_Keywords
 * @author    Javier Villanueva <javier@vivwebsolutions.com>
 * @copyright 2014 ViV Web Solutions
 *
 * @wordpress-plugin
 * Plugin Name:       MarketMuse Related Keywords
 * Description:       Get keyword suggestions using Market Muse API.
 * Version:           1.3.0
 * Author:            ViV Web Solutions
 * Author URI:        http://vivwebsolutions.com/
 * Text Domain:       mm-related-keywords
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'public/class-mm-related-keywords.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
register_activation_hook( __FILE__, array( 'MM_Related_Keywords', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'MM_Related_Keywords', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'MM_Related_Keywords', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if ( is_admin() ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-mm-related-keywords-admin.php' );
	add_action( 'plugins_loaded', array( 'MM_Related_Keywords_Admin', 'get_instance' ) );

}
