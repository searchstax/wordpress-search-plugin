<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.searchstax.com
 * @since      0.1.0
 *
 * @package    Searchstax_Serverless
 * @subpackage Searchstax_Serverless/includes
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
 * @since      0.1.0
 * @package    Searchstax_Serverless
 * @subpackage Searchstax_Serverless/includes
 * @author     Your Name <email@example.com>
 */
class Searchstax_Serverless {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      Plugin_Name_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.1.0
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
		if ( defined( 'SEARCHSTAX_SERVERLESS_VERSION' ) ) {
			$this->version = SEARCHSTAX_SERVERLESS_VERSION;
		} else {
			$this->version = '0.1.0';
		}
		$this->plugin_name = 'searchstax-serverless';

	   	add_action( 'init', array( $this, 'register_search_page' ) );

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Plugin_Name_Loader. Orchestrates the hooks of the plugin.
	 * - Plugin_Name_i18n. Defines internationalization functionality.
	 * - Plugin_Name_Admin. Defines all hooks for the admin area.
	 * - Plugin_Name_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-searchstax-serverless-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-searchstax-serverless-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-searchstax-serverless-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-searchstax-serverless-public.php';

		$this->loader = new Searchstax_Serverless_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Plugin_Name_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Searchstax_Serverless_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Searchstax_Serverless_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		
		$this->loader->add_action( 'publish_to_draft', $plugin_admin, 'post_delete_hook', 10, 1 );
		$this->loader->add_action( 'publish_to_trash', $plugin_admin, 'post_delete_hook', 10, 1 );
		$this->loader->add_action( 'post_updated', $plugin_admin, 'post_edit_hook', 10, 3 );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_options_page' );
		$this->loader->add_filter( 'replace_editor', $plugin_admin, 'search_result_editor' );

		$this->loader->add_action( 'wp_ajax_index_content_now', $plugin_admin, 'index_content_now' );
		$this->loader->add_action( 'wp_ajax_get_indexed_items', $plugin_admin, 'get_indexed_items' );

		$this->loader->add_action( 'admin_post_search_result_edit', $plugin_admin, 'edit_search_result' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Searchstax_Serverless_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		//$this->loader->add_filter( 'the_content', $plugin_public, 'print_page' );

		$this->loader->add_filter( 'template_include', $plugin_public, 'add_search_template' );

		$this->loader->add_filter( 'parse_query', $plugin_public, 'add_search_intercept' );
	}

	public function register_search_page() {

	    $args = array(
	    	'label' => __('SearchStax Search', 'searchstax_serverless'),
	    	'description' => __('SearchStax Page', 'searchstax_serverless'),
	    	'labels' => array(
				'name' => __('Search Result Pages', 'searchstax_serverless'),
				'singular_name' => __('SearchStax Result', 'searchstax_serverless'),
			),
			'show_in_menu' => true,
	        'public' => true,
	        'supports' => array('title', 'thumbnail'), 
	        'has_archive' => false,
	        'template' => locate_template('single-searchstax-result'),
	        'menu_icon' => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48c3ZnIGlkPSJiIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4NS42MjYiIGhlaWdodD0iOTUuMjY3IiB2aWV3Qm94PSIwIDAgODUuNjI2IDk1LjI2NyI+PGRlZnM+PHN0eWxlPi5ke2ZpbGw6I2ViMzUwMDt9PC9zdHlsZT48L2RlZnM+PGcgaWQ9ImMiPjxnPjxwYXRoIGNsYXNzPSJkIiBkPSJNLjM4OSw2OS40NzVsOS40NTYtNC44ODVjLjE4My0uMDk0LC40MDEtLjA5LC41OCwuMDEybDMyLjQ1NywxOC40ODdjLjE4NywuMTA3LC40MTcsLjEwNiwuNjA0LS4wMDFsMzIuMDk0LTE4LjQ2N2MuMTk0LS4xMTIsLjQzNC0uMTA4LC42MjQsLjAxMWw4LjU2Nyw1LjMyN2MuMzg5LC4yNDIsLjM4MSwuODEtLjAxNCwxLjA0MWwtNDEuMjY3LDI0LjE4NGMtLjE4OSwuMTExLS40MjMsLjExMS0uNjEyLC4wMDFMLjM2Myw3MC41NDJjLS40MTYtLjI0MS0uNDAxLS44NDYsLjAyNi0xLjA2NloiLz48cGF0aCBjbGFzcz0iZCIgZD0iTS4zMjksNDcuMTEybDkuNTA0LTUuMzU3Yy4xODktLjEwNiwuNDE5LS4xMDQsLjYwNiwuMDA1bDMxLjc2OCwxOC42MzRjLjE4OSwuMTExLC40MjIsLjExMSwuNjEyLC4wMDJsMzIuMjY4LTE4LjYzN2MuMTg4LS4xMDksLjMwNC0uMzA5LC4zMDQtLjUyN3YtOC41MDFjMC0uNDcxLS41MTItLjc2My0uOTE3LS41MjRsLTMwLjk4NSwxOC4yN2MtLjE4OSwuMTEyLS40MjQsLjExMi0uNjE0LC4wMDJMLjMwMywyNS44MDRjLS40MDMtLjIzMy0uNDA1LS44MTQtLjAwNC0xLjA1TDQyLjIxMSwuMDg0Yy4xODctLjExLC40MTgtLjExMiwuNjA3LS4wMDZsMjUuMzM0LDE0LjI5NmMuMzk2LC4yMjMsLjQxNiwuNzg2LC4wMzcsMS4wMzZsLTguNzUsNS43OTRjLS4xOTUsLjEyOS0uNDQ3LC4xMzUtLjY0NywuMDE1bC0xNS45NjEtOS41MjhjLS4xOTUtLjExNy0uNDQtLjExNC0uNjMzLC4wMDZsLTIwLjk4MiwxMy4wNThjLS4zODYsLjI0LS4zODEsLjgwNCwuMDA5LDEuMDM4bDIwLjk4MiwxMi41ODFjLjE4OSwuMTEzLC40MjQsLjExNiwuNjE1LC4wMDZsMzIuMjc1LTE4LjQ3N2MuMTgzLS4xMDUsLjQwNy0uMTA3LC41OTItLjAwN2w5LjYxOCw1LjIxMmMuMTk2LC4xMDYsLjMxOCwuMzExLC4zMTgsLjUzNXYyMS42NDVjMCwuMjE0LS4xMTIsLjQxMi0uMjk2LC41MjJsLTQxLjgzOCwyNS4wMTVjLS4xOSwuMTEzLS40MjYsLjExNS0uNjE3LC4wMDRMLjMyMyw0OC4xNjhjLS40MDctLjIzNi0uNDAzLS44MjUsLjAwNi0xLjA1NloiLz48L2c+PC9nPjwvc3ZnPg=='
	    );

	    register_post_type('searchstax-result', $args);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    0.1.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Plugin_Name_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
