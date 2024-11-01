<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       wpgeniuz.com
 * @since      1.0.0
 *
 * @package    Sheetpress_By_Wpgeniuz
 * @subpackage Sheetpress_By_Wpgeniuz/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Sheetpress_By_Wpgeniuz
 * @subpackage Sheetpress_By_Wpgeniuz/includes
 * @author     Wpgeniuz <info@wpgeniuz.com>
 */
class Sheetpress_By_Wpgeniuz_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function sheetpress_load_plugin_textdomain() {

		load_plugin_textdomain(
			'sheetpress-by-wpgeniuz',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
