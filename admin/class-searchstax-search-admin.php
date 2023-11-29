<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.searchstax.com
 * @since      1.0.0
 *
 * @package    Searchstax_Search
 * @subpackage Searchstax_Search/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Searchstax_Search
 * @subpackage Searchstax_Search/admin
 * @author     Your Name <email@example.com>
 */
class Searchstax_Search_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/searchstax-search-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/searchstax-search-admin.js', array( 'jquery' ), $this->version, false );

		wp_localize_script( $this->plugin_name, 'wp_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'searchstax-search' ),
		) );
	}

	public function add_options_page() {
		/*
		 * Set the display function for the 'Settings' page and register the plugin settings
		 */

		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'SearchStax Search Settings', 'searchstax-search' ),
			__( 'SearchStax Search', 'searchstax-search' ),
			'manage_options',
			'searchstax_search_settings',
			array( $this, 'display_options_page' )
		);

		register_setting( 'searchstax_search_account', 'searchstax_search_token_read', array( $this, 'sanitize_token' ) );
		register_setting( 'searchstax_search_account', 'searchstax_search_token_write', array( $this, 'sanitize_token' ) );
		register_setting( 'searchstax_search_account', 'searchstax_search_api_select', array( $this, 'sanitize_url' ) );
		register_setting( 'searchstax_search_account', 'searchstax_search_api_update', array( $this, 'sanitize_url' ) );
		register_setting( 'searchstax_search_account', 'searchstax_search_site_search', array( $this, 'sanitize_token' ) );
	}

	public function get_read_api() {
		$api = array();
		$api['available'] = false;

		$read_token = get_option('searchstax_search_token_read');
		$select_api = get_option('searchstax_search_api_select');

		if ( $read_token != '' && $select_api != '' ) {
			$api['available'] = true;
			$api['token'] = $read_token;
			$api['url'] = $select_api;
		}
		return $api;
	}

	public function get_write_api() {
		/*
		 * Check if API is available to write
		 *
		 * Grab POST vars when available (for any add/update option hooks)
		 */
		$api = array();
		$api['available'] = false;

		$write_token = get_option('searchstax_search_token_write');
		$update_api = get_option('searchstax_search_api_update');

		if (isset($_POST['searchstax_search_token_write']) && $token = $_POST['searchstax_search_token_write'] != '') {
			if(isset($_POST['searchstax_search_api_update']) && $url = $_POST['searchstax_search_api_update'] != '') {
				$write_token = $_POST['searchstax_search_token_write'];
				$update_api = $_POST['searchstax_search_api_update'];
			}
		}

		if ( is_admin() && $write_token != '' && $update_api != '' ) {
			$api['available'] = true;
			$api['token'] = $write_token;
			$api['url'] = $update_api;
		}
		return $api;
	}

	public function sanitize_token( $input ) {
		return sanitize_text_field( trim( $input ) );
	}

	public function sanitize_url( $input ) {
		return filter_var( trim( $input ), FILTER_SANITIZE_URL );
	}

	public function search_result_editor() {
		/*
		 * Override the default WP Post editor and insert our own for our custom search page post type
		 */

		if ( get_post_type() == 'searchstax-result' ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/searchstax-search-admin-editor.php';
			return true;
		}
		return false;
	}

	public function update_schema() {
		/*
		 * Update Solr schema for WordPress fields
		 */

		$api = $this->get_write_api();

		if ( $api['available'] ) {
			$url = str_replace("update", "schema", $api['url']);
			$args = array(
				'body' => '{
					"add-field": { 
						"name": "title", "type": "string", "indexed": true, "required": false, "stored": true, "multiValued": false
					},
					"add-field": {
						"name": "url", "type": "string", "indexed": true, "required": false, "stored": true, "multiValued": false
					},
					"add-field": {
						"name": "body", "type": "string", "indexed": true, "required": false, "stored": true, "multiValued": false
					},
					"add-field": {
						"name": "summary", "type": "string", "indexed": true, "required": false, "stored": true, "multiValued": false
					},
					"add-field": {
						"name": "thumbnail", "type": "string", "indexed": true, "required": false, "stored": true, "multiValued": false
					},
					"add-field": {
						"name": "tags", "type": "string", "indexed": true, "required": false, "stored": true, "multiValued": true
					},
					"add-field": {
						"name": "categories", "type": "string", "indexed": true, "required": false, "stored": true, "multiValued": true
					},
					"add-field": {
						"name": "post_type", "type": "string", "indexed": true, "required": false, "stored": true, "multiValued": true
					}
				}',
				'headers' => array(
					'Authorization' => 'Token ' . $api['token'],
					'Content-type' => 'application/json'
				)
			);

			$response = wp_remote_post( $url, $args );
			$body = wp_remote_retrieve_body( $response );
			$json = json_decode( $body, true );
			return $json;
		}
	}

	public function push_to_solr( $docs ) {
		$return = array();
		$return['status'] = 'none';
		$return['data'] = array();

		$api = $this->get_write_api();

		if ( $api['available'] ) {
			$url = $api['url'] . '?commit=true';
			$args = array(
				'body' => json_encode( $docs ),
				'headers' => array(
					'Authorization' => 'Token ' . $api['token'],
					'Content-type' => 'application/json'
				)
			);

			$response = wp_remote_post( $url, $args );

			if ( $response['response']['code'] == 200) {
				$return['status'] = 'success';
				$doc_ids = array();
				foreach ( $docs as $doc ) {
					$doc_ids[] = $doc['id'];
				}
				$return['data'] = $doc_ids;
			}
			else {
				$return['status'] = 'failed';
				$return['data'] = 'Unable to connect';
			}
		}
		return $return;
	}

	public function index_content_now() {
		/*
		 * Handle AJAX request to index all WordPress content
		 */

		$return = array();
		$return['status'] = 'none';
		$return['data'] = array();

		if ( wp_verify_nonce( $_POST['nonce'], 'searchstax-search' ) ) {
			$api = $this->get_write_api();

			if ( $api['available'] ) {
				$this->update_schema();
				
				$post_batch = array();

				$posts = get_posts(['post_status' => 'publish', 'numberposts' => -1]);
				$custom_posts = get_posts(['post_type' => 'custom', 'post_status' => 'publish', 'numberposts' => -1]);
				$pages = get_pages(['post_type' => 'page', 'post_status' => 'publish', 'numberposts' => -1]);

				foreach ( $posts as $post ) {
					$post_batch[] = $this->post_to_solr_json($post, $post->post_type . '_' . $post->ID);
				}

				foreach ( $custom_posts as $post ) {
					$post_batch[] = $this->post_to_solr_json($post, $post->post_type . '_' . $post->ID);
				}

				foreach ( $pages as $post ) {
					$post_batch[] = $this->post_to_solr_json($post, $post->post_type . '_' . $post->ID);
				}

				$batch_size = 20;
				$pages = ceil(count($post_batch) / $batch_size);
				$timeout = time() + 30;
				$delay = 250;
				$data = array();

				for ( $i = 0; $i < $pages; $i++ ) {
					$status = $this->push_to_solr( array_slice( $post_batch, $i * $batch_size, $batch_size ) );

					if ( $status['status'] == 'success' ) {
						$data = array_merge( $data, $status['data'] );
					}
					else {
						$return['status'] = $status['status'];
						break;
					}

					if ( time() > $timeout ) {
						$return['status'] = 'timeout';
						break;
					}
					set_time_limit(20);
					usleep( $delay );
				}

				if ( $return['status'] == 'none' ) {
					$return['status'] = 'success';
					$return['data']['posts'] = 'Successfully indexed ' . count($data) . ' items';
				}
			}
			else {
				$return['status'] = 'failed';
				$return['data'] = 'Please enter all account info';
			}
		}
		else {
			$return['status'] = 'failed';
			$return['data'] = 'WordPress error';
		}

		wp_reset_query();
		die( json_encode( $return ) );
	}

	public function get_indexed_items() {
		/*
		 * Handle AJAX request for getting indexed items
		 */
		$return = array();
		$return['status'] = 'none';
		$return['data'] = array();

		if ( wp_verify_nonce( $_POST['nonce'], 'searchstax-search' ) ) {
			$api = $this->get_read_api();

			if ( $api['available'] ) {
				$url = $api['url'] . '?q=*:*&rows=0&wt=json';
				$args = array(
					'headers' => array(
						'Authorization' => 'Token ' . $api['token']
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
		}
		else {
			$return['status'] = 'failed';
			$return['data'] = 'WordPress error';
		}

		wp_reset_query();
		die( json_encode( $return ) );
	}

	public function get_search_results_admin() {
		/*
		 * Handle AJAX request for getting search results
		 *
		 * This is currently  duplicate of the public function needed for separate public/admin enqueueing
		 */

		//check_ajax_referer('check_api_status', 'nonce');

		$return = array();
		$return['status'] = 'none';
		$return['data'] = array();

		$api = $this->get_read_api();

		$query = $_POST['q'];
		$post_ID = $_POST['post_id'];
			
		$show_search_bar = get_post_meta($post_ID, 'search_bar', true);
		$fixed_search_query = get_post_meta($post_ID, 'fixed_search_query', true);

		$return['query'] = $fixed_search_query;

		if( $show_search_bar == 'fixed_search' ) {
			$query = $fixed_search_query;
		}

		if ( $query != '' && $post_ID != '' && $api['available'] ) {
		
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

			$url = $api['url'] . '?q=(body:*' . $query . '* OR title:*' . $query . '*)';
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
					'Authorization' => 'Token ' . $api['token']
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

	public function delete_indexed_items() {
		/*
		 * Handle AJAX request for deleting indexed items
		 */

		$return = array();
		$return['status'] = 'none';
		$return['data'] = array();

		if ( wp_verify_nonce( $_POST['nonce'], 'searchstax-search' ) ) {
			$api = $this->get_write_api();

			if ( $api['available'] ) {
				$url = $api['url'] . '?commit=true';
				$args = array(
					'body' => '{"delete": {"query": "*:*"}}',
					'headers' => array(
						'Authorization' => 'Token ' . $api['token'],
						'Content-type' => 'application/json'
					)
				);

				$response = wp_remote_post( $url, $args );
				$body = wp_remote_retrieve_body( $response );
				$json = json_decode( $body, true );
				
				if (isset($json['message'])) {
					$return['status'] = 'failed';
					$return['data'] = $json['message'];
				}
				elseif ( $json != null && isset($json['responseHeader']) && $json['responseHeader']['status'] == 0 ) {
					$return['status'] = 'success';
					$return['data'] = $json;
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
		}
		else {
			$return['status'] = 'failed';
			$return['data'] = 'WordPress error';
		}

		wp_reset_query();
		die( json_encode( $return ) );
	}

	public function post_to_solr_json( $post, $solr_id ) {
		/*
		 * Create the JSON object to submit to Solr from a WordPress post
		 */

		$max_doc_size = 100000;

		$post_categories = wp_get_post_categories($post->ID);
		$categories = array();
		foreach ( $post_categories as $this_category) {
			$category = get_category($this_category);
			$categories[] = $category->name;
		}

		$post_tags = wp_get_post_tags($post->ID);
		$tags = array();
		foreach ( $post_tags as $this_tag) {
			$tags[] = $this_tag->name;
		}

		$solrDoc = array();
		
		$solrDoc['id'] = $solr_id;
		$solrDoc['title'] = $post->post_title;
		$solrDoc['summary'] = '';
		if( $post->post_excerpt != '' ) {
			$solrDoc['summary'] = $post->post_excerpt;
		}
		else {
			$solrDoc['summary'] = substr( wp_strip_all_tags( $post->post_content, true ), 0, 300 );
		}
		$solrDoc['body'] = substr( $post->post_content, 0, $max_doc_size );
		$solrDoc['thumbnail'] = wp_get_attachment_url( get_post_thumbnail_id($post->ID), 'thumbnail' );
		$solrDoc['guid'] = $post->guid;
		$solrDoc['url'] = get_permalink($post);
		$solrDoc['post_date'] = $post->post_date;
		$solrDoc['post_type'] = $post->post_type;
		$solrDoc['post_author'] = $post->post_author;
		$solrDoc['categories'] = $categories;
		$solrDoc['tags'] = $tags;

		return $solrDoc;
	}

	public function edit_search_result() {
		/*
		 * Custom action for creating and editing a SearchStax search result page
		 */

		if (isset($_POST['search_status']) && $_POST['search_status'] == 'publish' ) {
			if ( isset($_POST['search_page_id']) && get_post_status($_POST['search_page_id']) == 'publish') {
				wp_update_post(array (
					'ID' => $_POST['search_page_id'],
					'post_title' => $_POST['search_title'],
				));
				$post_id = $_POST['search_page_id'];
				update_post_meta($post_id, 'search_config', $_POST['search_config']);
				update_post_meta($post_id, 'search_display', $_POST['search_display']);
				update_post_meta($post_id, 'search_bar', $_POST['search_bar']);
				update_post_meta($post_id, 'fixed_search_query', $_POST['fixed_search_query']);
				update_post_meta($post_id, 'search_result_count', $_POST['search_result_count']);
				if ( isset($_POST['search_result_post_types']) ) {
					update_post_meta($post_id, 'search_result_post_types', $_POST['search_result_post_types']);
				}
				else {
					update_post_meta($post_id, 'search_result_post_types', array());
				}
				if ( isset($_POST['search_result_post_categories']) ) {
					update_post_meta($post_id, 'search_result_post_categories', $_POST['search_result_post_categories']);
				}
				else {
					update_post_meta($post_id, 'search_result_post_categories', array());
				}
				if ( isset($_POST['search_result_post_tags']) ) {
					update_post_meta($post_id, 'search_result_post_tags', $_POST['search_result_post_tags']);
				}
				else {
					update_post_meta($post_id, 'search_result_post_tags', array());
				}
			}
			else {
				$post_id = wp_insert_post(array (
					'post_type' => 'searchstax-result',
					'post_title' => $_POST['search_title'],
					'post_content' => '',
					'post_status' => 'publish',
					'comment_status' => 'closed',
					'ping_status' => 'closed',
				));
				add_post_meta($post_id, 'search_config', $_POST['search_config'], true);
				add_post_meta($post_id, 'search_display', $_POST['search_display'], true);
				add_post_meta($post_id, 'search_bar', $_POST['search_bar']);
				add_post_meta($post_id, 'fixed_search_query', $_POST['fixed_search_query']);
				add_post_meta($post_id, 'search_result_count', $_POST['search_result_count'], true);
				if ( isset($_POST['search_result_post_types']) ) {
					add_post_meta($post_id, 'search_result_post_types', $_POST['search_result_post_types']);
				}
				else {
					add_post_meta($post_id, 'search_result_post_types', array());
				}
				if ( isset($_POST['search_result_post_categories']) ) {
					add_post_meta($post_id, 'search_result_post_categories', $_POST['search_result_post_categories']);
				}
				else {
					add_post_meta($post_id, 'search_result_post_categories', array());
				}
				if ( isset($_POST['search_result_post_tags']) ) {
					add_post_meta($post_id, 'search_result_post_tags', $_POST['search_result_post_tags']);
				}
				else {
					add_post_meta($post_id, 'search_result_post_tags', array());
			}
			}
			wp_redirect(admin_url('edit.php?post_type=searchstax-result'));
			exit;
			die();
		}
	}

	public function post_edit_hook($post_id, $post, $update) {
		/*
		 * Custom hook to add or update a Solr doc when a post or page is published
		 */

		$api = $this->get_write_api();

		if ( $api['available'] && ($post->post_type == 'page' || $post->post_type == 'post') ) {
			$post = get_post($post_id);
			$status = $this->push_to_solr( [$post] );
			
			if ( $status['status'] == 'success' ) {
				//echo 'Successfully added pages';
			}
		}
	}

	public function post_delete_hook( $post ) {
		/*
		 * Custom hook to deindex posts when they are deleted
		 * Need to break loop to call Solr API and then redirect back to post_type editor
		 */

		$api = $this->get_write_api();

		if ( $api['available'] && ($post->post_type == 'page' || $post->post_type == 'post') ) {
			$url = $api['url'] . '?commit=true';
			$args = array(
				'body' => '{"delete":"' . $post->post_type . '_' . $post->ID . '"}',
				'headers' => array(
					'Authorization' => 'Token ' . $api['token'],
					'Content-type' => 'application/json'
				)
			);

			$response = wp_remote_post( $url, $args );
			
			if ( $response['response']['code'] == 200) {
				//echo 'Successfully added pages';
			}
			wp_redirect(admin_url('edit.php?post_type=' . $post->post_type));
			exit;
			die();
		}
	}

	/**
	 * Render the options page for plugin
	 *
	 * @since  1.0.0
	 */

	public function display_options_page() {
		require_once plugin_dir_path( __FILE__ ) . 'partials/searchstax-search-admin-display.php';
	}
}
