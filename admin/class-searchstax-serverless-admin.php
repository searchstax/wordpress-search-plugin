<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.searchstax.com
 * @since      0.1.0
 *
 * @package    Searchstax_Serverless
 * @subpackage Searchstax_Serverless/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Searchstax_Serverless
 * @subpackage Searchstax_Serverless/admin
 * @author     Your Name <email@example.com>
 */
class Searchstax_Serverless_Admin {

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
	 * @since    0.1.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $searchstax_serverless, $version ) {

		$this->searchstax_serverless = $searchstax_serverless;
		$this->version = $version;
	}

	public function sanitize_token( $input ) {
		return $input;
			/*
		$new_input = array();
		if( isset( $input['id_number'] ) )
			$new_input['id_number'] = absint( $input['id_number'] );

		if( isset( $input['title'] ) )
			$new_input['title'] = sanitize_text_field( $input['title'] );

		return $new_input;
		*/
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    0.1.0
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

		wp_enqueue_style( $this->searchstax_serverless, plugin_dir_url( __FILE__ ) . 'css/searchstax-serverless-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    0.1.0
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

		wp_enqueue_script( $this->searchstax_serverless, plugin_dir_url( __FILE__ ) . 'js/searchstax-serverless-admin.js', array( 'jquery' ), $this->version, false );

	    wp_localize_script( $this->searchstax_serverless, 'wp_ajax', array(
	        'ajax_url' => admin_url( 'admin-ajax.php' ),
	        '_nonce' => wp_create_nonce( 'searchstax-serverless' ),
	    ) );
	}

	public function add_options_page() {
		/*
		 * Set the display function for our 'Settings' page and register the plugin settings
		 */

		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'SearchStax Serverless Settings', 'searchstax-serverless' ),
			__( 'SearchStax Serverless', 'searchstax-serverless' ),
			'manage_options',
			'searchstax_serverless_settings',
			array( $this, 'display_options_page' )
		);

		register_setting(
			'searchstax_serverless_account',
			'searchstax_serverless_token_read',
			array( $this, 'sanitize_token' )
		);

		register_setting(
			'searchstax_serverless_account',
			'searchstax_serverless_token_write',
			array( $this, 'sanitize_token' )
		);

		register_setting(
			'searchstax_serverless_account',
			'searchstax_serverless_api_select',
			array( $this, 'sanitize_token' )
		);

		register_setting(
			'searchstax_serverless_account',
			'searchstax_serverless_api_update',
			array( $this, 'sanitize_token' )
		);

		register_setting(
			'searchstax_serverless_account',
			'searchstax_serverless_site_search',
			array( $this, 'sanitize_token' )
		);
	}

	public function search_result_editor() {
		/*
		 * Override the default WP Post editor and insert our own for our custom search page post type
		 */

		if ( get_post_type() == 'searchstax-result' ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/searchstax-serverless-admin-editor.php';
			return true;
		}
		return false;
	}

	public function index_content_now() {
		/*
		 * Handle AJAX request to index all WordPress content
		 */

        //check_ajax_referer('check_api_status', 'nonce');

	    $return = array();
	    $return['status'] = 'none';
	    $return['data'] = array();

		$write_token = get_option('searchstax_serverless_token_write');
		$update_api = get_option('searchstax_serverless_api_update');

		if ( $write_token != '' && $update_api != '') {
			$post_batch = array();
			$posts = get_posts([
			  'post_status' => 'publish',
			  'numberposts' => -1
			]);
			foreach ( $posts as $post ) {
				$post_batch[] = $this->post_to_solr($post, $post->post_type . '_' . $post->ID);
			}
			
			$posts = get_posts([
			  'post_type' => 'custom',
			  'post_status' => 'publish',
			  'numberposts' => -1
			]);
			foreach ( $posts as $post ) {
				$post_batch[] = $this->post_to_solr($post, $post->post_type . '_' . $post->ID);
			}
			$url = $update_api . '?commit=true';
			$args = array(
				'body' => json_encode($post_batch),
			    'headers' => array(
			        'Authorization' => 'Token ' . $write_token,
			        'Content-type' => 'application/json'
			    )
			);

			$response = wp_remote_post( $url, $args );

			if ( $response['response']['code'] == 200) {
				$return['status'] = 'success';
				$return['data']['posts'] = 'Successfully indexed ' . count($post_batch) . ' posts';
			}
			else {
				$return['status'] = 'failed';
				$return['data'] = 'Unable to connect';
			}

			$pages = get_pages();
			$page_batch = array();
			foreach ( $pages as $page ) {
				if ( $page->post_status == "publish" ) {
					$page_batch[] = $this->post_to_solr($page, 'page_' . $page->ID);
				}
			}

			$url = $update_api . '?commit=true';
			$args = array(
				'body' => json_encode($page_batch),
			    'headers' => array(
			        'Authorization' => 'Token ' . $write_token,
			        'Content-type' => 'application/json'
			    )
			);

			$response = wp_remote_post( $url, $args );
			
			if ( $response['response']['code'] == 200) {
				$return['status'] = 'success';
				$return['data']['pages'] = 'Successfully indexed ' . count($page_batch) . ' pages';
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

	public function get_indexed_items() {
		/*
		 * Handle AJAX request for getting indexed items
		 */

        //check_ajax_referer('check_api_status', 'nonce');

	    $return = array();
	    $return['status'] = 'none';
	    $return['data'] = array();

		$token = get_option('searchstax_serverless_token_read');
		$select_api = get_option('searchstax_serverless_api_select');

		if ( $token != '' && $select_api != '' ) {
			$url = $select_api . '?q=*:*&rows=10&wt=json&indent=true';
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

        //check_ajax_referer('check_api_status', 'nonce');

	    $return = array();
	    $return['status'] = 'none';
	    $return['data'] = array();

		$write_token = get_option('searchstax_serverless_token_write');
		$update_api = get_option('searchstax_serverless_api_update');

		if ( $write_token != '' && $update_api != '' ) {
			
			$url = $update_api . '?commit=true';
			$args = array(
				'body' => '{"delete": {"query": "*:*"}}',
			    'headers' => array(
			        'Authorization' => 'Token ' . $write_token,
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

	    wp_reset_query();
	    die( json_encode( $return ) );
	}

	public function post_to_solr( $post, $solr_id ) {
		/*
		 * Create the JSON object to submit to Solr from a WordPress post
		 */

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
		$solrDoc['summary'] = $post->post_excerpt;
		$solrDoc['body'] = $post->post_content;
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
				update_post_meta($post_id, 'search_display', $_POST['search_display']);
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
				add_post_meta($post_id, 'search_display', $_POST['search_display'], true);
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

		$write_token = get_option('searchstax_serverless_token_write');
		$update_api = get_option('searchstax_serverless_api_update');

		if ( $write_token != '' && $update_api != '' && ($post->post_type == 'page' || $post->post_type == 'post') ) {
			$post = get_post($post_id);

			$url = $update_api . '?commit=true';
			$args = array(
				'body' => json_encode([$this->post_to_solr($post, $post->post_type . '_' . $post->ID)]),
			    'headers' => array(
			        'Authorization' => 'Token ' . $write_token,
			        'Content-type' => 'application/json'
			    )
			);

			$response = wp_remote_post( $url, $args );
			
			if ( $response['response']['code'] == 200) {
				//echo 'Successfully added pages';
			}
		}
	}

	public function post_delete_hook( $post ) {
		/*
		 * Custom hook to deindex posts when they are deleted
		 * Need to break loop to call Solr API and then redirect back to post_type editor
		 */

		$write_token = get_option('searchstax_serverless_token_write');
		$update_api = get_option('searchstax_serverless_api_update');

		if ( $write_token != '' && $update_api != '' && ($post->post_type == 'page' || $post->post_type == 'post') ) {
			$url = $update_api . '?commit=true';
			$args = array(
				'body' => '{"delete":"' . $post->post_type . '_' . $post->ID . '"}',
			    'headers' => array(
			        'Authorization' => 'Token ' . $write_token,
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
	 * @since  0.1.0
	 */

	public function display_options_page() {
		$created_search_pages = get_posts([
			'post_type' => 'searchstax-result',
  			'numberposts' => -1
		]);
		$selected_search_page = get_option('searchstax_serverless_site_search');
		$active_tab = 'account';
		if ( isset($_GET[ 'tab' ]) ) {
			$active_tab = $_GET[ 'tab' ];
		}
		?>
			<div class="wrap">
				<h1>SearchStax Serverless Options</h1>
				<a href="https://www.searchstax.com" target="_blank"><span class="searchstax_serverless_powered"></span></a>
				<div>
					<div class="nav-tab-wrapper">
						<button id="searchstax_serverless_index_tab" class="nav-tab nav-tab-active">Search Index</button>
						<button id="searchstax_serverless_sitesearch_tab" class="nav-tab">Site-wide Search</button>
						<button id="searchstax_serverless_account_tab" class="nav-tab">Account</button>
					</div>
					<div class="searchstax_serverless_option_frame">
						<div id="searchstax_serverless_index" class="searchstax_serverless_tab_visible">
							<div>
								<?php
    								include_once 'partials/searchstax-serverless-admin-index-table.php';
									$table = new Searchstax_Serverless_Admin_Index_Table();
									$table->list_table_page();
								?>
							</div>
						</div>
						<form method="post" action="options.php">
							<?php settings_fields( 'searchstax_serverless_account' ); ?>
							<?php do_settings_sections( 'searchstax_serverless_account' ); ?>
							<div id="searchstax_serverless_account" class="searchstax_serverless_tab">
								<h3>SearchStax Serverless Account Info</h3>
								<p>Enter your account info to start indexing your WordPress pages and posts</p>
								<p>Don't have SearchStax Serverless account? <a href="https://www.searchstax.com/managed-solr/serverless/" target="_blank">Sign up now</a></p>
								<div>
									<h3>Read</h3>
									<p>Public token for fetching search results</p>
								</div>
								<div>
									<h4>Read-Only Token</h4>
									<input type="text" name="searchstax_serverless_token_read" value="<?php echo esc_attr( get_option('searchstax_serverless_token_read') ); ?>" />
								</div>
								<div>
									<h4>Select API</h4>
									<input type="text" name="searchstax_serverless_api_select" value="<?php echo esc_attr( get_option('searchstax_serverless_api_select') ); ?>" />
								</div>
								<div>
									<h3>Write/Update</h3>
									<p>Admin Read/Write token for adding and updating documents</p>
								</div>
								<div>
									<h4>Write Token</h4>
									<input type="text" name="searchstax_serverless_token_write" value="<?php echo esc_attr( get_option('searchstax_serverless_token_write') ); ?>" />
								</div>
								<div>
									<h4>Update API</h4>
									<input type="text" name="searchstax_serverless_api_update" value="<?php echo esc_attr( get_option('searchstax_serverless_api_update') ); ?>" />
								</div>
								<?php submit_button(); ?>
							</div>
							<div id="searchstax_serverless_sitesearch" class="searchstax_serverless_tab">
								<div>
									<h3>Site Search Page</h3>
									<p>Select a search result page for site-wide searches.</p>
									<p>Any URLs that include <code>?s=</code> will show search results with this page.</p>
									<select name="searchstax_serverless_site_search">
										<option value="">None</option>
										<?php
											foreach ( $created_search_pages as $this_page ) {
												echo '<option value="' . $this_page->post_name . '"';
												if ( $selected_search_page == $this_page->post_name ) {
													echo ' selected';
												}
												echo '>' . $this_page->post_title . '</option>';
											}
										?>
									</select>
									<?php submit_button(); ?>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		<?php
	}

}
