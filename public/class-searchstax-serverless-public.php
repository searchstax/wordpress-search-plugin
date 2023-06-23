<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.searchstax.com
 * @since      0.1.0
 *
 * @package    Searchstax_Serverless
 * @subpackage Searchstax_Serverless/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Searchstax_Serverless
 * @subpackage Searchstax_Serverless/public
 * @author     Your Name <email@example.com>
 */
class Searchstax_Serverless_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/searchstax-serverless-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/searchstax-serverless-public.js', array( 'jquery' ), $this->version, false );

	}

	public function add_search_template( $template ) {
		if ( get_post_type() == 'searchstax-result' ) {
			return plugin_dir_path( dirname( __FILE__ ) ) . 'public/templates/post-searchstax-result.php';
			//return locate_template('post-searchstax-result.php');
		}
		return $template;
	}

	public function add_search_intercept( $query ) {
		/*
		 * Custom hook to intercept ?s=* requests
		 */

		/*
		if ( isset($query->query['s']) ) {
			wp_redirect('fart');
			exit;
			die();
			// it's a search!
		}
		*/
	}

}
