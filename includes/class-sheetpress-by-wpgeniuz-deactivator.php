<?php

/**
 * Fired during plugin deactivation
 *
 * @link       wpgeniuz.com
 * @since      1.0.0
 *
 * @package    Sheetpress_By_Wpgeniuz
 * @subpackage Sheetpress_By_Wpgeniuz/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Sheetpress_By_Wpgeniuz
 * @subpackage Sheetpress_By_Wpgeniuz/includes
 * @author     Wpgeniuz <info@wpgeniuz.com>
 */
class Sheetpress_By_Wpgeniuz_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function sheetpress_deactivate() {
		    delete_option('fs_accounts');
	}

}
