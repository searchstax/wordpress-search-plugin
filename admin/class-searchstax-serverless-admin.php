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

	public function sanitize_token( $input )
	{
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
		wp_localize_script( $this->searchstax_serverless, $this->searchstax_serverless , array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}

	public function add_options_page() {
	
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Searchstax Serverless Settings', 'searchstax-serverless' ),
			__( 'Searchstax Serverless', 'searchstax-serverless' ),
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
	}

	public function search_result_editor() {
		if ( get_post_type() == 'searchstax-result' ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/searchstax-serverless-admin-editor.php';
			return true;
		}
		return false;
	}

	public function list_items() {
		$token = get_option('searchstax_serverless_token_read');
		$select_api = get_option('searchstax_serverless_api_select');

		echo '<h2>Index Status</h2>';

		if ( $token != '' && $select_api != '' ) {
			$this->index_content();

			$url = $select_api . '?q=*:*&wt=json&indent=true';
			$args = array(
			    'headers' => array(
			        'Authorization' => 'Token ' . $token
			    )
			);

			$response = wp_remote_get( $url, $args );
			$body = wp_remote_retrieve_body( $response );
			$json = json_decode( $body, true );
			
			if (isset($json['message'])) {
				echo 'Error';
				echo $json['message'];
			}
			else {
				echo 'Indexed Documents:' . $json['response']['numFound'] . '<br />';
				foreach ( $json['response']['docs'] as $doc ) {
					echo $doc['id'];
					if( isset($doc['title'][0])) {
						echo ' - ' . $doc['title'][0];
					}
					echo '<br />';
				}
			}
		}
		else {
			echo 'Please enter all account info';
		}
	}

	public function index_content() {
		$write_token = get_option('searchstax_serverless_token_write');
		$update_api = get_option('searchstax_serverless_api_update');

		if ( $write_token != '' && $update_api != '') {
			$posts = get_posts();
			$post_batch = array();
			foreach ( $posts as $post ) {
				if ( $post->post_status == "publish") {
					echo '<div>Adding post "' . $post->post_title . '"</div>';
					$solrDoc = array();
					
					$solrDoc['id'] = 'post_' . $post->ID;
					$solrDoc['title'] = $post->post_title;
					$solrDoc['summary'] = $post->post_excerpt;
					$solrDoc['body'] = $post->post_content;
					$solrDoc['url'] = $post->guid;
					//echo json_encode($solrDoc);

					$post_batch[] = $solrDoc;
				}
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
				echo 'Successfully added posts';
			}

			$pages = get_pages();
			$page_batch = array();
			foreach ( $pages as $page ) {
				if ( $page->post_status == "publish" ) {
					echo '<div>Adding page "' . $page->post_title . '" </div>';
					$solrDoc = array();
					
					$solrDoc['id'] = 'page_' . $page->ID;
					$solrDoc['title'] = $page->post_title;
					$solrDoc['summary'] = $page->post_excerpt;
					$solrDoc['body'] = $page->post_content;
					$solrDoc['url'] = $page->guid;
					//echo json_encode($solrDoc);

					$page_batch[] = $solrDoc;
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
				echo 'Successfully added pages';
			}
		}
	}

	public function edit_search_result() {

		if (isset($_POST['search_status']) && $_POST['search_status'] == 'publish' ) {
			if ( isset($_POST['search_page_id']) ) {
				wp_update_post(array (
					'ID' => $_POST['search_page_id'],
					'post_title' => $_POST['search_title'],
				));
				$post_id = $_POST['search_page_id'];
				update_post_meta($post_id, 'search_display', $_POST['search_display']);
				update_post_meta($post_id, 'search_result_count', $_POST['search_result_count']);
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
			}
			wp_redirect(admin_url('edit.php?post_type=searchstax-result'));
			exit;
			die();
		}

	}

	public function post_edit_hook($post_id, $post, $update) {

		$write_token = get_option('searchstax_serverless_token_write');
		$update_api = get_option('searchstax_serverless_api_update');

		if ( $write_token != '' && $update_api != '' && ($post->post_type == 'page' || $post->post_type == 'post') ) {
			$post = get_post($post_id);
			$solrDoc = array();
			
			$solrDoc['id'] = $post->post_type . '_' . $post->ID;
			$solrDoc['title'] = $post->post_title;
			$solrDoc['summary'] = $post->post_excerpt;
			$solrDoc['body'] = $post->post_content;
			$solrDoc['url'] = $post->guid;

			$url = $update_api . '?commit=true';
			$args = array(
				'body' => json_encode([$solrDoc]),
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

	/**
	 * Render the options page for plugin
	 *
	 * @since  0.1.0
	 */

	public function display_options_page() {
		?>
			<div class="wrap">
				<h1>SearchStax Serverless Options</h1>
				<div>
					<div>
						<form method="post" action="options.php">
							<?php settings_fields( 'searchstax_serverless_account' ); ?>
							<?php do_settings_sections( 'searchstax_serverless_account' ); ?>
							<table class="form-table">
								<tr valign="top">
									<th scope="row" colspan="2">
										<h3>Read</h3>
									</th>
								</tr>
								<tr valign="top">
									<td colspan="2"><p>Public token for fetching search results</p></td>
								</tr>
								<tr valign="top">
									<th scope="row">Read-Only Token</th>
									<td><input type="text" name="searchstax_serverless_token_read" value="<?php echo esc_attr( get_option('searchstax_serverless_token_read') ); ?>" /></td>
								</tr>
								<tr valign="top">
									<th scope="row">Select API</th>
									<td><input type="text" name="searchstax_serverless_api_select" value="<?php echo esc_attr( get_option('searchstax_serverless_api_select') ); ?>" /></td>
								</tr>
								<tr valign="top">
									<th scope="row" colspan="2">
										<h3>Write/Update</h3>
									</th>
								</tr>
								<tr valign="top">
									<td colspan="2"><p>Admin Read/Write token for adding and updating documents</p></td>
								</tr>
								<tr valign="top">
									<th scope="row">Write Token</th>
									<td><input type="text" name="searchstax_serverless_token_write" value="<?php echo esc_attr( get_option('searchstax_serverless_token_write') ); ?>" /></td>
								</tr>
								<tr valign="top">
								<th scope="row">Update API</th>
								<td><input type="text" name="searchstax_serverless_api_update" value="<?php echo esc_attr( get_option('searchstax_serverless_api_update') ); ?>" /></td>
								</tr>
							</table>
							<?php submit_button(); ?>
						</form>
					</div>
					<div>
						<?php $this->list_items(); ?>
					</div>
				</div>
			</div>
		<?php
	}

}
