<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       wpgeniuz.com
 * @since      1.0.0
 *
 * @package    Sheetpress_By_Wpgeniuz
 * @subpackage Sheetpress_By_Wpgeniuz/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Sheetpress_By_Wpgeniuz
 * @subpackage Sheetpress_By_Wpgeniuz/includes
 * @author     Wpgeniuz <info@wpgeniuz.com>
 */
class Sheetpress_By_Wpgeniuz {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Sheetpress_By_Wpgeniuz_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'SHEETPRESS_PLUGIN_NAME_VERSION' ) ) {
			$this->version = SHEETPRESS_PLUGIN_NAME_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'sheetpress-by-wpgeniuz';

		$this->sheetpress_load_dependencies();
		$this->sheetpress_set_locale();
		$this->sheetpress_define_admin_hooks();
		$this->sheetpress_define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Sheetpress_By_Wpgeniuz_Loader. Orchestrates the hooks of the plugin.
	 * - Sheetpress_By_Wpgeniuz_i18n. Defines internationalization functionality.
	 * - Sheetpress_By_Wpgeniuz_Admin. Defines all hooks for the admin area.
	 * - Sheetpress_By_Wpgeniuz_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function sheetpress_load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sheetpress-by-wpgeniuz-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sheetpress-by-wpgeniuz-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-sheetpress-by-wpgeniuz-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-sheetpress-by-wpgeniuz-public.php';

		$this->loader = new Sheetpress_By_Wpgeniuz_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Sheetpress_By_Wpgeniuz_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function sheetpress_set_locale() {

		$plugin_i18n = new Sheetpress_By_Wpgeniuz_i18n();

		$this->loader->sheetpress_add_action( 'plugins_loaded', $plugin_i18n, 'sheetpress_load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function sheetpress_define_admin_hooks() {

		$plugin_admin = new Sheetpress_By_Wpgeniuz_Admin( $this->sheetpress_get_plugin_name(), $this->sheetpress_get_version() );

		$this->loader->sheetpress_add_action( 'admin_enqueue_scripts', $plugin_admin, 'sheetpress_enqueue_styles' );
		$this->loader->sheetpress_add_action( 'admin_enqueue_scripts', $plugin_admin, 'sheetpress_enqueue_scripts' );
		$this->loader->sheetpress_add_action( 'admin_menu', $plugin_admin, 'sheetpress_action_add_menu' );
		$this->loader->sheetpress_add_action( 'admin_post_auth_google', $plugin_admin, 'sheetpress_form_auth_google_function' );
		$this->loader->sheetpress_add_action( 'admin_post_manual_sync', $plugin_admin, 'sheetpress_form_manual_sync_function' );


	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function sheetpress_define_public_hooks() {

		$plugin_public = new Sheetpress_By_Wpgeniuz_Public( $this->sheetpress_get_plugin_name(), $this->sheetpress_get_version() );

		$this->loader->sheetpress_add_action( 'wp_enqueue_scripts', $plugin_public, 'sheetpress_enqueue_styles' );
		$this->loader->sheetpress_add_action( 'wp_enqueue_scripts', $plugin_public, 'sheetpress_enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function sheetpress_run() {
		$this->loader->sheetpress_run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function sheetpress_get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Sheetpress_By_Wpgeniuz_Loader    Orchestrates the hooks of the plugin.
	 */
	public function sheetpress_get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function sheetpress_get_version() {
		return $this->version;
	}

}
