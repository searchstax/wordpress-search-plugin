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

$query = '';
if ( isset($_GET['searchQuery']) ) {
    $query = $_GET['searchQuery'];
}

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="container">
    <div>
        <form action="">
            <input type="text" name="searchQuery" value="<?php $query; ?>" />
            <input type="submit" value="Search" />
        </form>
    </div>
    <?php 
        $token = get_option('searchstax_serverless_token_read');
        $select_api = get_option('searchstax_serverless_api_select');
        if ( $query != '' && $token != '' && $select_api != '' ) {

            $url = $select_api . '?q=body:' . $query . '&wt=json&indent=true';
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
                // echo var_dump($json);
                echo '<div>Results:' . $json['response']['numFound'] . '</div>';
                foreach ( $json['response']['docs'] as $doc ) {
                    echo '<div>';
                    echo '<h3>' . $doc['title'][0] . '</h3>';
                    echo '<div>' . $doc['body'][0] . '</div>';
                    if ( array_key_exists('url', $doc) ) {
                        echo '<div><a href="' . $doc['url'][0] . '">' . $doc['url'][0] . '</a></div>';
                    }
                    echo '</div>';
                }
                //echo '<pre>' . var_dump($json);
            }

        }

    ?>
</div>