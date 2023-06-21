<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.searchstax.com
 * @since      0.1.0
 *
 * @package    Searchstax_Serverless
 * @subpackage Searchstax_Serverless/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

require_once ABSPATH . 'wp-admin/admin-header.php';
$post = $GLOBALS['post'];
$meta = get_post_meta($post->ID);
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
    <h1>Configure Search Results Page</h1>
    <div>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="search_result_edit">
            <input type="hidden" name="search_page_id" value="<?php if ( isset($post->ID) ) { echo $post->ID; } ?>">
            <div>
                <h2>Name</h2>
                <input type="text" name="search_title" value="<?php 
                if ( isset($post) ) {
                    echo $post->post_title;
                }
                ?>" />
            </div>
            <div>
                <h2>Display</h2>
                <div>
                    <input type="radio" value="display_inline" name="search_display" <?php if ( isset($meta['search_display']) && $meta['search_display'][0] == "display_inline") { echo 'checked'; } ?>/>
                    <label for="display_inline">Inline</label>
                </div>
                <div>
                    <input type="radio" value="display_grid" name="search_display"  <?php if ( isset($meta['search_display']) && $meta['search_display'][0] == "display_grid") { echo 'checked'; } ?>/>
                    <label for="display_inline">Grid</label>
                </div>
            </div>
            <div>
                <h2>Result Count</h2>
                <select name="search_result_count">
                    <option value="10" <?php if ( isset($meta['search_result_count']) && $meta['search_result_count'][0] == "10") { echo 'selected'; } ?>>10 per page</option>
                    <option value="30"<?php if ( isset($meta['search_result_count']) && $meta['search_result_count'][0] == "30") { echo ' selected'; } ?>>30 per page</option>
                    <option value="50"<?php if ( isset($meta['search_result_count']) && $meta['search_result_count'][0] == "50") { echo ' selected'; } ?>>50 per page</option>
                </select>
            <input type="hidden" name="search_status" value="publish" />
            <?php submit_button(); ?>
        </form>
    </div>
</div>
