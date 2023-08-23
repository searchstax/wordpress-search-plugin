<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.searchstax.com
 * @since      1.0.0
 *
 * @package    Searchstax_Search
 * @subpackage Searchstax_Search/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Searchstax_Search
 * @subpackage Searchstax_Search/public
 * @author     Your Name <email@example.com>
 */
class Searchstax_Search_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/searchstax-search-public.css', array(), $this->version, 'all' );

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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/searchstax-search-public.js', array( 'jquery' ), $this->version, false );

		wp_localize_script( $this->plugin_name, 'frontend_wp_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'_nonce' => wp_create_nonce( 'searchstax-search' ),
		) );
	}

	public function add_search_template( $template ) {
		/*
		 * Custom hook to add/edit a search result page
		 */

		if ( get_post_type() == 'searchstax-result' ) {
			return plugin_dir_path( dirname( __FILE__ ) ) . 'public/templates/post-searchstax-result.php';
		}
		return $template;
	}

	public function add_search_intercept( $query ) {
		/*
		 * Custom hook to intercept ?s=* requests
		 */

		$selected_search_page = get_option('searchstax_search_site_search');
		if ( !is_admin() && $selected_search_page !== '' && isset($query->query['s']) ) {
			wp_redirect('searchstax-result/' . $selected_search_page . '?searchQuery=' . $query->query['s']);
			exit;
			die();
		}
	}

	public function get_search_results() {
		/*
		 * Handle AJAX request for getting search results
		 *
		 * This is currently  duplicate of the admin function needed for separate public/admin enqueueing
		 */
		
		$return = array();
		$return['status'] = 'none';
		$return['data'] = array();

		$token = get_option('searchstax_search_token_read');
		$select_api = get_option('searchstax_search_api_select');

		$query = $_POST['q'];
		$post_ID = $_POST['post_id'];
			
		$show_search_bar = get_post_meta($post_ID, 'search_bar', true);
		$fixed_search_query = get_post_meta($post_ID, 'fixed_search_query', true);

		$return['query'] = $fixed_search_query;

		if( $show_search_bar == 'fixed_search' ) {
			$query = $fixed_search_query;
		}

		if ( $query != '' && $post_ID != '' && $token != '' && $select_api != '' ) {
		
			$selected_post_types = get_post_meta($post_ID, 'search_result_post_types', true);
			$selected_categories = get_post_meta($post_ID, 'search_result_post_categories', true);
			$selected_tags = get_post_meta($post_ID, 'search_result_post_tags', true);

			$start = 0;
			if ( isset($_POST['searchStart']) ) {
				$start = $_POST['searchStart'];
			}
			if ( isset($_POST['post_type']) && $_POST['post_type'] != '') {
				$selected_post_types = [$_POST['post_type']];
			}
			if ( isset($_POST['category']) && $_POST['category'] != '' ) {
				$selected_categories = [$_POST['category']];
			}
			if ( isset($_POST['tag']) && $_POST['tag'] != '' ) {
				$selected_tags = [$_POST['tag']];
			}
			$meta = get_post_meta($post_ID);

			$url = $select_api . '?q=(body:*' . $query . '* OR title:*' . $query . '*)';
			if ( count($selected_post_types) > 0 ) {
				$url .= '&fq=post_type:("' . join('" OR "', $selected_post_types) . '")';
			}
			if ( count($selected_categories) > 0 ) {
				$url .= '&fq=categories:("' . join('" OR "', $selected_categories) . '")';
			}
			if ( count($selected_tags) > 0 ) {
				$url .= '&fq=tags:("' . join('" OR "', $selected_tags) . '")';
			}
			$url .= '&fl=id,title,thumbnail,url,summary,post_type,categories,tags';
			$url .= '&start=' . $start;
			$url .= '&rows=' . $meta['search_result_count'][0];
			$url .= '&facet=true';
			$url .= '&facet.mincount=1';
			$url .= '&facet.field=categories';
			$url .= '&facet.field=tags';
			$url .= '&facet.field=post_type';
			$url .= '&f.categories.facet.sort=index';
			$url .= '&f.tags.facet.sort=index';
			$url .= '&f.post_type.facet.sort=index';
			$url .= '&wt=json';
			$args = array(
				'headers' => array(
					'Authorization' => 'Token ' . $token
				)
			);

			$response = wp_remote_get( $url, $args );
			$body = wp_remote_retrieve_body( $response );
			$json = json_decode( $body, true );
			
			if (isset($json['message'])) {
				$return['status'] = 'failed';
				$return['data'] = $json['message'];
			}
			elseif ( $json != null && isset($json['response']) ) {
				$return['status'] = 'success';
				$return['data'] = $json['response'];
				$return['config'] = $meta['search_result_count'][0];
				$return['facet_counts'] = $json['facet_counts'];
				$return['url'] = $url;
			}
			else {
				$return['status'] = 'failed';
				$return['data'] = 'Unable to connect';
			}
		}
		else {
			$return['status'] = 'failed';
			$return['data'] = 'Please enter all account info';
		}

		wp_reset_query();
		die( json_encode( $return ) );
	}

}
