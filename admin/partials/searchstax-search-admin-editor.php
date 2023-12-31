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

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

require_once ABSPATH . 'wp-admin/admin-header.php';

$post = $GLOBALS['post'];
$meta = get_post_meta($post->ID);
$post_types = get_post_types();
$categories = get_categories(array(
	'orderby' => 'name'
));
$tags = get_tags(array(
	'orderby' => 'name'
));

sort($post_types);

$selected_post_types = array();
$selected_categories = array();
$selected_tags = array();

if ( get_post_meta($post->ID, 'search_result_post_types', true) != '' ) {
	$selected_post_types = get_post_meta($post->ID, 'search_result_post_types', true);
}
if ( get_post_meta($post->ID, 'search_result_post_categories', true) != '' ) {
	$selected_categories = get_post_meta($post->ID, 'search_result_post_categories', true);
}
if ( get_post_meta($post->ID, 'search_result_post_tags', true) != '' ) {
	$selected_tags = get_post_meta($post->ID, 'search_result_post_tags', true);
}

$unindexable_types = array(
	'attachment',
	'revision',
	'nav_menu_item',
	'custom_css',
	'customize_changeset',
	'oembed_cache',
	'user_request',
	'wp_block',
	'wp_template',
	'wp_template_part',
	'wp_global_styles',
	'wp_navigation',
	'searchstax-result'
);
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
	<h1>Configure Search Results Page</h1>
	<div>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="search_result_edit">
			<input type="hidden" name="search_page_id" value="<?php if ( isset($post->ID) ) { echo $post->ID; } ?>">
			<input type="hidden" name="search_status" value="publish" />
			<div>
				<h2>Name</h2>
				<input type="text" name="search_title" value="<?php 
				if ( isset($post) ) {
					echo $post->post_title;
				}
				?>" />
			</div>
			<div>
				<h2>Result Config</h2>
				<div>
					<input type="radio" value="config_static" name="search_config" <?php if ( (isset($meta['search_config']) && $meta['search_config'][0] == "config_static") || !isset($meta['search_config']) ) { echo 'checked'; } ?>/>
					<label for="config_static">Static</label>
				</div>
				<div>
					<input type="radio" value="config_dynamic" name="search_config"  <?php if ( isset($meta['search_config']) && $meta['search_config'][0] == "config_dynamic") { echo 'checked'; } ?>/>
					<label for="config_dynamic">AJAX/Dynamic</label>
				</div>
			</div>
			<div>
				<h2>Display Results</h2>
				<div>
					<input type="radio" value="display_inline" name="search_display" <?php if ( (isset($meta['search_display']) && $meta['search_display'][0] == "display_inline") || !isset($meta['search_display']) ) { echo 'checked'; } ?>/>
					<label for="display_inline">Inline</label>
				</div>
				<div>
					<input type="radio" value="display_grid" name="search_display"  <?php if ( isset($meta['search_display']) && $meta['search_display'][0] == "display_grid") { echo 'checked'; } ?>/>
					<label for="display_inline">Grid</label>
				</div>
			</div>
			<div>
				<h2>Search Bar</h2>
				<div>
					<input type="radio" value="user_search" name="search_bar" <?php if ( (isset($meta['search_bar']) && $meta['search_bar'][0] == "user_search") || !isset($meta['search_bar']) ) { echo 'checked'; } ?>/>
					<label for="user_search">User Search Query (Show Search Bar)</label>
				</div>
				<div>
					<input type="radio" value="fixed_search" name="search_bar"  <?php if ( isset($meta['search_bar']) && $meta['search_bar'][0] == "fixed_search") { echo 'checked'; } ?>/>
					<label for="fixed_search">Fixed Search Query (Search Bar Hidden)</label>
				</div>
				<div>
					<input type="text" name="fixed_search_query" value="<?php 
					if ( isset($meta['fixed_search_query']) ) {
						echo $meta['fixed_search_query'][0];
					}
					?>"/>
					<p>Use <code>*</code> to show all results
				</div>
			</div>
			<div>
				<h2>Result Count</h2>
				<select name="search_result_count">
					<option value="10" <?php if ( isset($meta['search_result_count']) && $meta['search_result_count'][0] == "10") { echo 'selected'; } ?>>10 per page</option>
					<option value="30"<?php if ( isset($meta['search_result_count']) && $meta['search_result_count'][0] == "30") { echo ' selected'; } ?>>30 per page</option>
					<option value="50"<?php if ( isset($meta['search_result_count']) && $meta['search_result_count'][0] == "50") { echo ' selected'; } ?>>50 per page</option>
				</select>
			</div>
			<div>
				<h2>Only Show Post Types</h2>
				<div class="searchstax_search_scroll_container">
				<?php
					foreach ( $post_types as $index => $this_post ) {
						if ( !in_array($this_post, $unindexable_types) ) {
							echo '<div>';
							echo '<input type="checkbox" value="' . $this_post . '" name="search_result_post_types[' . $index . ']"';
							if ( isset($selected_post_types) && in_array($this_post, $selected_post_types) ) {
								echo ' checked';
							}
							echo '><label for="' . $this_post . '">' . ucfirst($this_post) . '</label>';
							echo '</div>';
						}
					}
				?>
				</div>
			</div>
			<div>
				<h2>Only Show Categories</h2>
				<div class="searchstax_search_scroll_container">
				<?php
					foreach ( $categories as $index => $this_category ) {
						echo '<div>';
						echo '<input type="checkbox" value="' . $this_category->name . '" name="search_result_post_categories[' . $index . ']"';
							if ( isset($selected_categories) && in_array($this_category->name, $selected_categories) ) {
								echo ' checked';
							}
						echo '><label for="' . $this_category->name . '">' . $this_category->name . '</label>';
						echo '</div>';
					}
				?>
				</div>
			</div>
			<div>
				<h2>Only Show Tags</h2>
				<div class="searchstax_search_scroll_container">
				<?php
					foreach ( $tags as $index => $this_tag ) {
						echo '<div>';
						echo '<input type="checkbox" value="' . $this_tag->name . '" name="search_result_post_tags[' . $index . ']"';
							if ( isset($selected_tags) && in_array($this_tag->name, $selected_tags) ) {
								echo ' checked';
							}
						echo '><label for="' . $this_tag->name . '">' . $this_tag->name . '</label>';
						echo '</div>';
					}
				?>
				</div>
			</div>
			<?php submit_button(); ?>
		</form>
	</div>
</div>
