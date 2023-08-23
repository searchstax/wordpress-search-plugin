<?php

/**
 * Provide a admin area view for the plugin
 *
 * @link       https://www.searchstax.com
 * @since      1.0.0
 *
 * @package    Searchstax_Search
 * @subpackage Searchstax_Search/admin/partials
 */

$created_search_pages = get_posts([
	'post_type' => 'searchstax-result',
	'numberposts' => -1
]);
$selected_search_page = get_option('searchstax_search_site_search');

$token = get_option('searchstax_search_token_read');
$select_api = get_option('searchstax_search_api_select');
$write_token = get_option('searchstax_search_token_write');
$update_api = get_option('searchstax_search_api_update');

$apiAvailable = 0;

if ( $token != '' && $select_api != '' && $write_token != '' && $update_api != '' ) {
	$apiAvailable = 1;
}

?>
<div class="wrap">
	<h1>SearchStax Search Options</h1>
	<a href="https://www.searchstax.com" target="_blank"><span class="searchstax_search_powered"></span></a>
	<div>
		<div class="nav-tab-wrapper">
			<input id="searchstax_search_api_available" type="hidden" value="<?php echo $apiAvailable ?>" />
			<?php if ( $apiAvailable == 1) { ?>
				<button id="searchstax_search_index_tab" class="nav-tab nav-tab-active">Search Index</button>
				<button id="searchstax_search_sitesearch_tab" class="nav-tab">Site-wide Search</button>
				<button id="searchstax_search_account_tab" class="nav-tab">Account</button>
			<?php } else { ?>
				<button id="searchstax_search_index_tab" class="nav-tab">Search Index</button>
				<button id="searchstax_search_sitesearch_tab" class="nav-tab">Site-wide Search</button>
				<button id="searchstax_search_account_tab" class="nav-tab nav-tab-active">Account</button>
			<?php } ?>
		</div>
		<div class="searchstax_search_option_frame">
			<div id="searchstax_search_index" class="searchstax_search_tab_visible">
				<div>
					<?php
						include_once 'searchstax-search-admin-index-table.php';
						$table = new Searchstax_Search_Admin_Index_Table();
						$table->list_table_page();
					?>
				</div>
			</div>
			<form method="post" action="options.php">
				<?php settings_fields( 'searchstax_search_account' ); ?>
				<?php do_settings_sections( 'searchstax_search_account' ); ?>
				<div id="searchstax_search_account" class="searchstax_search_tab">
					<h3>SearchStax Search Account Info</h3>
					<button id="searchstax_search_get_indexed_items" class="button" type="button">
						Check Index
						<div id="searchstax_search_indexed_loader">
							<div class="searchstax_search_loader"></div>
						</div>
					</button>
					<div id="searchstax_search_indexed_status_message"></div>
					<p>Enter your account info to start indexing your WordPress pages and posts</p>
					<p>Don't have SearchStax Search account? <a href="https://www.searchstax.com/managed-solr/search/" target="_blank">Sign up now</a></p>
					<div>
						<h3>Read</h3>
						<p>Public token for fetching search results</p>
					</div>
					<div>
						<h4>Read-Only Token</h4>
						<input type="text" name="searchstax_search_token_read" value="<?php echo esc_attr( get_option('searchstax_search_token_read') ); ?>" size=50 />
					</div>
					<div>
						<h4>Select API URL</h4>
						<input type="text" name="searchstax_search_api_select" value="<?php echo esc_attr( get_option('searchstax_search_api_select') ); ?>" size=50 />
					</div>
					<div>
						<h3>Write/Update</h3>
						<p>Admin Read/Write token for adding and updating documents</p>
					</div>
					<div>
						<h4>Write Token</h4>
						<input type="text" name="searchstax_search_token_write" value="<?php echo esc_attr( get_option('searchstax_search_token_write') ); ?>" size=50 />
					</div>
					<div>
						<h4>Update API URL</h4>
						<input type="text" name="searchstax_search_api_update" value="<?php echo esc_attr( get_option('searchstax_search_api_update') ); ?>" size=50 />
					</div>
					<?php submit_button(); ?>
				</div>
				<div id="searchstax_search_sitesearch" class="searchstax_search_tab">
					<div>
						<h3>Site Search Page</h3>
						<p>Select a search result page for site-wide searches.</p>
						<p>Any URLs that include <code>?s=</code> will show search results with this page.</p>
						<select name="searchstax_search_site_search">
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