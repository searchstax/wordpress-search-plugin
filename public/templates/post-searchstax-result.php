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

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="container">
    <div>
        <form action="">
            <div class="searchstax_serverless_search_bar">
                <input class="searchstax_serverless_search_input" type="text" name="searchQuery" value="<?php $query; ?>" />
                <input class="searchstax_serverless_search_submit" type="submit" value="Search" />
            </div>
        </form>
    </div>
    <?php 
        $token = get_option('searchstax_serverless_token_read');
        $select_api = get_option('searchstax_serverless_api_select');
        if ( $query != '' && $token != '' && $select_api != '' ) {

            $url = $select_api . '?q=body:*' . $query . '*';
            if ( count($selected_post_types) > 0 ) {
                $url .= '&fq=post_type:(' . join(' OR ', $selected_post_types) . ')';
            }
            if ( count($selected_categories) > 0 ) {
                $url .= '&fq=categories:(' . join(' OR ', $selected_categories) . ')';
            }
            if ( count($selected_tags) > 0 ) {
                $url .= '&fq=tags:(' . join(' OR ', $selected_tags) . ')';
            }
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
                echo '<div>Showing <strong>' . $json['response']['numFound'] . '</strong> results for <strong>"' . $query . '"</strong></div>';
                echo '<div class="searchstax_serverless_facet">';
                foreach ( $categories as $category => $count) {
                    echo '<div class="searchstax_serverless_facet">';
                    echo $category . ' (' . $count . ')';
                    echo '</div>';
                }
                echo '</div><div class="searchstax_serverless_facet">';
                foreach ( $tags as $tag => $count) {
                    echo '<div class="searchstax_serverless_facet">';
                    echo $tag . ' (' . $count . ')';
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
                    echo '<h3>' . $doc['title'][0] . '</h3>';
                    echo '<div>' . $doc['body'][0] . '</div>';
                    if ( array_key_exists('url', $doc) ) {
                        echo '<div><a href="' . $doc['url'][0] . '">' . $doc['url'][0] . '</a></div>';
                    }
                    if ( array_key_exists('categories', $doc) ) {
                        echo '<div>';
                        echo '<strong>Categories</strong>';
                        foreach ( $doc['categories'] as $category ) {
                            echo $category;
                        }
                        echo '</div>';
                    }
                    if ( array_key_exists('tags', $doc) ) {
                        echo '<div>';
                        echo '<strong>Tags</strong>';
                        foreach ( $doc['tags'] as $tag ) {
                            echo $tag;
                        }
                        echo '</div>';
                    }
                    echo '</div>';
                }
                echo '</div>';
                echo '</div>';
                //echo '<pre>' . var_dump($json);
            }

        }

    ?>
</div>
<?php get_footer(); ?>