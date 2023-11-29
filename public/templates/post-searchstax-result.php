<?php

/*
 *
 * @link       https://www.searchstax.com
 * @since      1.0.0
 *
 * @package    Searchstax_Search
 * @subpackage Searchstax_Search/public/partials
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

get_header();

$post = $GLOBALS['post'];
$meta = get_post_meta($post->ID);

$show_search_bar = get_post_meta($post->ID, 'search_bar', true);
$fixed_search_query = get_post_meta($post->ID, 'fixed_search_query', true);

if ( $meta['search_config'][0] == 'config_static' ) {

	$selected_post_types = get_post_meta($post->ID, 'search_result_post_types', true);
	$selected_categories = get_post_meta($post->ID, 'search_result_post_categories', true);
	$selected_tags = get_post_meta($post->ID, 'search_result_post_tags', true);

	$query = '';
	if ( isset($_GET['searchQuery']) ) {
		$query = $_GET['searchQuery'];
	}
	$start = 0;
	if ( isset($_GET['searchStart']) ) {
		$start = $_GET['searchStart'];
	}
	if ( isset($_GET['post_type']) ) {
		$selected_post_types = [$_GET['post_type']];
	}
	if ( isset($_GET['category']) ) {
		$selected_categories = [$_GET['category']];
	}
	if ( isset($_GET['tag']) ) {
		$selected_tags = [$_GET['tag']];
	}

	?>
	<div class="searchstax_search_container">
		<?php
			if( $show_search_bar != 'fixed_search' ) {
			?>
				<div>
					<form action="">
						<div class="searchstax_search_search_bar">
							<input class="searchstax_search_search_input" type="text" name="searchQuery" value="<?php echo $query; ?>" autocomplete="off" />
							<input class="searchstax_search_search_submit" type="submit" value="Search" />
						</div>
					</form>
				</div>
			<?php 
			}
			else {
				$query = $fixed_search_query;
			}
			$token = get_option('searchstax_search_token_read');
			$select_api = get_option('searchstax_search_api_select');
			if ( $query != '' && $token != '' && $select_api != '' ) {

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
					echo 'Error';
					echo $json['message'];
				}
				else {
					$post_types = array();
					$categories = array();
					$tags = array();

					if ( array_key_exists('categories', $json['facet_counts']['facet_fields'] ) ) {
						$cats = $json['facet_counts']['facet_fields']['categories'];
						for ( $i = 0; $i < count($cats); $i +=2 ) {
							$categories[ $cats[$i] ] = $cats[$i + 1];
						}
					}

					if ( array_key_exists('tags', $json['facet_counts']['facet_fields'] ) ) {
						$tag = $json['facet_counts']['facet_fields']['tags'];
						for ( $i = 0; $i < count($tag); $i +=2 ) {
							$tags[ $tag[$i] ] = $tag[$i + 1];
						}
					}

					if ( array_key_exists('post_type', $json['facet_counts']['facet_fields'] ) ) {
						$types = $json['facet_counts']['facet_fields']['post_type'];
						for ( $i = 0; $i < count($types); $i +=2 ) {
							$post_types[ $types[$i] ] = $types[$i + 1];
						}
					}

					ksort($post_types);
					ksort($categories);
					ksort($tags);
					echo '<div>Showing <strong>' . ($start + 1) . ' - ';
					if ( ($start + $meta['search_result_count'][0]) > $json['response']['numFound']) {
						echo $json['response']['numFound'];
					}
					else {
						echo $start + $meta['search_result_count'][0];
					}
					echo '</strong> of <strong>' . $json['response']['numFound'] . '</strong>';
					if ( $show_search_bar != 'fixed_search' ) {
						echo ' results for <strong>"' . $query . '"</strong>';
					}
					echo '</div>';
					echo '<div class="searchstax_search_search_results">';
					echo '<div class="searchstax_search_search_facets">';
					echo '<div class="searchstax_search_facet">';
					echo '<h4>Content Type</h4>';
					if ( count($post_types) > 10) {
						echo '<input id="searchstax_search_post_type_expander" class="searchstax_search_toggle" type="checkbox">';
						echo '<label for="searchstax_search_post_type_expander" class="searchstax_search_toggle_label">More</label>';
					}
					echo '<div class="searchstax_search_facet_container">';
					foreach ( $post_types as $post_type => $count) {
						echo '<div class="searchstax_search_facet">';
						echo '<form>';
						echo '<input type="hidden" name="searchQuery" value="' . $query . '">';
						echo '<input type="hidden" name="post_type" value="' . $post_type . '">';
						echo '<a href="#" class="searchstax_search_facet_link" onClick="parentNode.submit();">' . $post_type . ' (' . $count . ')</a>';
						echo '</form>';
						echo '</div>';
					}
					echo '</div>';
					echo '</div>';
					echo '<div class="searchstax_search_facet">';
					echo '<h4>Categories</h4>';
					if ( count($categories) > 10) {
						echo '<input id="searchstax_search_category_expander" class="searchstax_search_toggle" type="checkbox">';
						echo '<label for="searchstax_search_category_expander" class="searchstax_search_toggle_label">More</label>';
					}
					echo '<div class="searchstax_search_facet_container">';
					foreach ( $categories as $category => $count) {
						echo '<div class="searchstax_search_facet">';
						echo '<form>';
						echo '<input type="hidden" name="searchQuery" value="' . $query . '">';
						echo '<input type="hidden" name="category" value="' . $category . '">';
						echo '<a href="#" class="searchstax_search_facet_link" onClick="parentNode.submit();">' . $category . ' (' . $count . ')</a>';
						echo '</form>';
						echo '</div>';
					}
					echo '</div>';
					echo '</div>';
					echo '<div class="searchstax_search_facet">';
					echo '<h4>Tags</h4>';
					if ( count($tags) > 10) {
						echo '<input id="searchstax_search_tags_expander" class="searchstax_search_toggle" type="checkbox">';
						echo '<label for="searchstax_search_tags_expander" class="searchstax_search_toggle_label">More</label>';
					}
					echo '<div class="searchstax_search_facet_container">';
					foreach ( $tags as $tag => $count) {
						echo '<div class="searchstax_search_facet">';
						echo '<form>';
						echo '<input type="hidden" name="searchQuery" value="' . $query . '">';
						echo '<input type="hidden" name="tag" value="' . $tag . '">';
						echo '<a href="#" class="searchstax_search_facet_link" onClick="parentNode.submit();">' . $tag . ' (' . $count . ')</a>';
						echo '</form>';
						echo '</div>';
					}
					echo '</div>';
					echo '</div>';
					echo '</div>';
					echo '<div class="searchstax_search_results">';
					if ($meta['search_display'][0] == 'display_grid') {
						echo '<div class="searchstax_search_grid">';
					}
					if ($meta['search_display'][0] == 'display_inline') {
						echo '<div class="searchstax_search_inline">';
					}
					foreach ( $json['response']['docs'] as $doc ) {
						echo '<div class="searchstax_search_result">';
						if ( array_key_exists('thumbnail', $doc) && $doc['thumbnail'] !== 'false') {
							echo '<div class="searchstax_search_thumbnail_frame">';
							echo '<img class="searchstax_search_thumbnail" src="' . $doc['thumbnail'] . '">';
							echo '</div>';
						}
						echo '<div class="searchstax_search_snippet">';
						echo '<h4><a href="' . $doc['url'] . '" class="searchstax_search_result_link">' . $doc['title'] . '</a></h4>';
						echo '<div>' . $doc['summary'] . '</div>';
						if ( array_key_exists('url', $doc) ) {
							echo '<div><a href="' . $doc['url'] . '">' . $doc['url'] . '</a></div>';
						}
						echo '</div>';
						echo '</div>';
					}
					echo '</div>';
					echo '</div>';
					echo '</div>';
					echo '<div class="searchstax_search_result_pagination">';
					echo '<form>';
					echo '<input type="hidden" name="searchQuery" value="' . $query . '">';
					echo '<input type="hidden" name="searchStart" value="' . ($start - $meta['search_result_count'][0]) . '">';
					echo '<input type="submit" value="Previous"';
					if ( $start == 0 ) {
						echo ' disabled="true"';
					}
					echo '>';
					echo '</form>';
					echo 'Page ' . (ceil($start / $meta['search_result_count'][0]) + 1) . ' of ' . ceil($json['response']['numFound'] / $meta['search_result_count'][0]);
					echo '<form>';
					echo '<input type="hidden" name="searchQuery" value="' . $query . '">';
					echo '<input type="hidden" name="searchStart" value="' . ($start + $meta['search_result_count'][0]) . '">';
					echo '<input type="submit" value="Next"';
					if ( ($start + $meta['search_result_count'][0]) > $json['response']['numFound'] ) {
						echo ' disabled="true"';
					}
					echo '>';
					echo '</form>';
					echo '</div>';
					echo '</div>';
				}
			}
	echo '</div>';
}
if ( $meta['search_config'][0] == 'config_dynamic' ) {
	?>
		<div class="searchstax_search_container">
			<?php if( $show_search_bar != 'fixed_search' ) { ?>
				<div>
					<div class="searchstax_search_search_bar">
						<input id="searchstax_search_dynamic_search_input" class="searchstax_search_search_input" type="text" name="searchQuery" autocomplete="off" />
						<button id="searchstax_search_dynamic_search_submit" class="searchstax_search_search_submit" type="submit">
							<div id="searchstax_search_dynamic_label">
								Search
							</div>
							<div id="searchstax_search_dynamic_loader">
								<div class="searchstax_search_loader"></div>
							</div>
						</button>
					</div>
				</div>
			<?php
				}
				else {
					echo '<input id="searchstax_search_dynamic_fixed_search_query" type="hidden" value="' . $fixed_search_query . '" />';
				}
			?>
			<div id="searchstax_search_dynamic_status_message"></div>
			<div id="searchstax_search_dynamic_search_count"></div>
			<input id="searchstax_search_dynamic_post_id" type="hidden" value="<?php echo $post->ID ?>" />
			<div class="searchstax_search_search_results" id="searchstax_search_dynamic_search_results">
				<div id="searchstax_search_dynamic_search_facets" class="searchstax_search_search_facets"></div>
				<div class="searchstax_search_results">
					<?php
					if ($meta['search_display'][0] == 'display_grid') {
						echo '<div id="searchstax_search_dynamic_results" class="searchstax_search_grid"></div>';
					}
					if ($meta['search_display'][0] == 'display_inline') {
						echo '<div id="searchstax_search_dynamic_results" class="searchstax_search_inline"></div>';
					}
					?>
				</div>
			</div>
			<div id="searchstax_search_result_dynamic_pagination" class="searchstax_search_result_pagination"></div>
		</div>
	<?php
}

get_footer();

?>