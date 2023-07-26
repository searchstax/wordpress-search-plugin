<?php

/*
 * Template Name: My custom view
 * Template Post Type: searchstax-result
 *
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://www.searchstax.com
 * @since      0.1.0
 *
 * @package    Searchstax_Serverless
 * @subpackage Searchstax_Serverless/public/partials
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

//require_once ABSPATH . 'wp-blog-header.php';
get_header();

$post = $GLOBALS['post'];
$meta = get_post_meta($post->ID);

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
if ( isset($_GET['category']) ) {
    $selected_categories = [$_GET['category']];
}
if ( isset($_GET['tag']) ) {
    $selected_tags = [$_GET['tag']];
}

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="container">
    <div>
        <form action="">
            <div class="searchstax_serverless_search_bar">
                <input class="searchstax_serverless_search_input" type="text" name="searchQuery" value="<?php $query; ?>" autocomplete="off" />
                <input class="searchstax_serverless_search_submit" type="submit" value="Search" />
            </div>
        </form>
    </div>
    <?php 
        $token = get_option('searchstax_serverless_token_read');
        $select_api = get_option('searchstax_serverless_api_select');
        if ( $query != '' && $token != '' && $select_api != '' ) {

            $url = $select_api . '?q=(body:*' . $query . '* OR title:*' . $query . '*)';
            if ( count($selected_post_types) > 0 ) {
                $url .= '&fq=post_type:(' . join(' OR ', $selected_post_types) . ')';
            }
            if ( count($selected_categories) > 0 ) {
                $url .= '&fq=categories:(' . join(' OR ', $selected_categories) . ')';
            }
            if ( count($selected_tags) > 0 ) {
                $url .= '&fq=tags:(' . join(' OR ', $selected_tags) . ')';
            }
            $url .= '&start=' . $start;
            $url .= '&rows=' . $meta['search_result_count'][0];
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
                $categories = array();
                $tags = array();
                foreach ( $json['response']['docs'] as $doc ) {
                    if ( array_key_exists('categories', $doc) ) {
                        foreach ( $doc['categories'] as $category ) {
                            if ( array_key_exists( $category, $categories) ) {
                                $categories[$category] = $categories[$category] + 1;
                            }
                            else {
                                $categories[$category] = 1;
                            }
                        }
                    }
                    if ( array_key_exists('tags', $doc) ) {
                        foreach ( $doc['tags'] as $tag ) {
                            if ( array_key_exists( $tag, $tags) ) {
                                $tags[$tag] = $tags[$tag] + 1;
                            }
                            else {
                                $tags[$tag] = 1;
                            }
                        }
                    }
                }
                echo '<div>Showing <strong>' . ($start + 1) . ' - ';
                if ( ($start + $meta['search_result_count'][0]) > $json['response']['numFound']) {
                    echo $json['response']['numFound'];
                }
                else {
                    echo $start + $meta['search_result_count'][0];
                }
                echo '</strong> of <strong>' . $json['response']['numFound'] . '</strong> results for <strong>"' . $query . '"</strong></div>';
                echo '<div class="searchstax_serverless_facet">';
                foreach ( $categories as $category => $count) {
                    echo '<div class="searchstax_serverless_facet">';
                    echo '<form>';
                    echo '<input type="hidden" name="searchQuery" value="' . $query . '">';
                    echo '<input type="hidden" name="category" value="' . $category . '">';
                    echo '<a href="#" class="searchstax_serverless_facet_link" onClick="parentNode.submit();">' . $category . ' (' . $count . ')</a>';
                    echo '</form>';
                    echo '</div>';
                }
                echo '</div><div class="searchstax_serverless_facet">';
                foreach ( $tags as $tag => $count) {
                    echo '<div class="searchstax_serverless_facet">';
                    echo '<form>';
                    echo '<input type="hidden" name="searchQuery" value="' . $query . '">';
                    echo '<input type="hidden" name="tag" value="' . $tag . '">';
                    echo '<a href="#" class="searchstax_serverless_facet_link" onClick="parentNode.submit();">' . $tag . ' (' . $count . ')</a>';
                    echo '</form>';
                    echo '</div>';
                }
                echo '</div>';
                echo '<div class="searchstax_serverless_results">';
                if ($meta['search_display'][0] == 'display_grid') {
                    echo '<div class="searchstax_serverless_grid">';
                }
                if ($meta['search_display'][0] == 'display_inline') {
                    echo '<div class="searchstax_serverless_inline">';
                }
                foreach ( $json['response']['docs'] as $doc ) {
                    echo '<div class="searchstax_serverless_result">';
                    if ( array_key_exists('thumbnail', $doc) && $doc['thumbnail'][0] !== 'false') {
                        echo '<div class="searchstax_serverless_thumbnail_frame">';
                        echo '<img class="searchstax_serverless_thumbnail" src="' . $doc['thumbnail'][0] . '">';
                        echo '</div>';
                    }
                    echo '<div class="searchstax_serverless_snippet">';
                    echo '<h3><a href="' . $doc['url'][0] . '" class="searchstax_serverless_result_link">' . $doc['title'][0] . '</a></h3>';
                    if ( array_key_exists('summary', $doc) ) {
                        echo '<div>' . $doc['summary'][0] . '</div>';
                    }
                    else {
                        echo '<div>' . substr( wp_strip_all_tags( $doc['body'][0], true ), 0, 300 ) . '</div>';
                    }
                    if ( array_key_exists('url', $doc) ) {
                        echo '<div><a href="' . $doc['url'][0] . '">' . $doc['url'][0] . '</a></div>';
                    }
                    if ( array_key_exists('categories', $doc) ) {
                        echo '<div class="searchstax_serverless_result_sublink">';
                        echo '<strong>Categories</strong>';
                        foreach ( $doc['categories'] as $category ) {
                            echo '<form class="searchstax_serverless_inline_form">';
                            echo '<input type="hidden" name="searchQuery" value="' . $query . '">';
                            echo '<input type="hidden" name="category" value="' . $category . '">';
                            echo '<a href="#" class="searchstax_serverless_category_link" onClick="parentNode.submit();">' . $category . '</a>';
                            echo '</form>';
                        }
                        echo '</div>';
                    }
                    if ( array_key_exists('tags', $doc) ) {
                        echo '<div class="searchstax_serverless_result_sublink">';
                        echo '<strong>Tags</strong>';
                        foreach ( $doc['tags'] as $tag ) {
                            echo '<form class="searchstax_serverless_inline_form">';
                            echo '<input type="hidden" name="searchQuery" value="' . $query . '">';
                            echo '<input type="hidden" name="tag" value="' . $tag . '">';
                            echo '<a href="#" class="searchstax_serverless_tag_link" onClick="parentNode.submit();">' . $tag . '</a>';
                            echo '</form>';
                        }
                        echo '</div>';
                    }
                    echo '</div>';
                    echo '</div>';
                }
                echo '</div>';
                echo '<div class="searchstax_serverless_result_pagination">';
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
                //echo '<pre>' . var_dump($json);
            }

        }

    ?>
</div>
<?php get_footer(); ?>