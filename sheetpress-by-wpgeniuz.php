<?php

/**
 * @link              wpgeniuz.com
 * @package           Sheetpress
 *
 * @wordpress-plugin
 * Plugin Name:       SheetPress
 * Plugin URI:        wpgeniuz.com/docs/
 * Description:       SheetPress Connects Google sheets with WordPress to manage category, tags and SEO Yoast Meta data to make things easy.
 * Version:           1.1
 * Author:            wpgeniuz
 * Author URI:        wpgeniuz.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sheetpress-by-wpgeniuz
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'SHEETPRESS_PLUGIN_NAME_VERSION', '1.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-sheetpress-by-wpgeniuz-activator.php
 */
function sheetpress_activation_hook() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sheetpress-by-wpgeniuz-activator.php';
	Sheetpress_By_Wpgeniuz_Activator::sheetpress_activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-sheetpress-by-wpgeniuz-deactivator.php
 */
function sheetpress_deactivation_hook() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sheetpress-by-wpgeniuz-deactivator.php';
	Sheetpress_By_Wpgeniuz_Deactivator::sheetpress_deactivate();
}

register_activation_hook( __FILE__, 'sheetpress_activation_hook' );
register_deactivation_hook( __FILE__, 'sheetpress_deactivation_hook' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-sheetpress-by-wpgeniuz.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0
 */
function sheetpress_init() {
	
	// Integration_freemius
	if(is_admin())
	require_once( 'includes/sheetpress_freemius.php' );
	
	$plugin = new Sheetpress_By_Wpgeniuz();
	$plugin->sheetpress_run();

}
sheetpress_init();
