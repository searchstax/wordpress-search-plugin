<?php
/*
 * Plugin Name: Paulund WP List Table Example
 * Description: An example of how to use the WP_List_Table class to display data in your WordPress Admin area
 * Plugin URI: http://www.paulund.co.uk
 * Author: Paul Underwood
 * Author URI: http://www.paulund.co.uk
 * Version: 1.0
 * License: GPL2
 */

if(is_admin()) {
    new Searchstax_Serverless_Admin_Index_Table();
}

class Searchstax_Serverless_Admin_Index_Table {
    public function __construct() {

    }

    /**
     * Display the list table page
     *
     * @return Void
     */
    public function list_table_page() {
        $indexed_items = new Indexed_Items_Table();
        $indexed_items->prepare_items();
        ?>
            <div class="wrap">
                <h2>Indexed Items</h2>
                <div class="tablenav">
                    <span>
                        <button id="searchstax_serverless_index_content_now" class="button">
                            Index All Content
                            <div id="searchstax_serverless_index_loader">
                                <div class="searchstax_serverless_loader"></div>
                            </div>
                        </button>
                    </span>
                    <span>
                        <button id="searchstax_serverless_delete_items" class="button">
                            Delete All Content from Index
                            <div id="searchstax_serverless_delete_loader">
                                <div class="searchstax_serverless_loader"></div>
                            </div>
                        </button>
                    </span>
                </div>
                <div id="searchstax_serverless_index_status_message"></div>
                <div id="searchstax_serverless_indexed_status_message"></div>
                <div id="searchstax_serverless_delete_status_message"></div>
                <?php $indexed_items->display(); ?>
            </div>
        <?php
    }
}

if( !class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class Indexed_Items_Table extends WP_List_Table {
    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public $index_item_count;
    public $index_row_count;
    public $index_start_page;

    public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $this->index_row_count = 10;
        $this->index_start_page = $this->get_pagenum();

        $data = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );

        $this->set_pagination_args( array(
            'total_items' => $this->index_item_count,
            'per_page'    => $this->index_row_count
        ) );

        //$data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns() {
        $columns = array(
            'title' => 'Title',
            'url' => 'URL',
            'post_date' => 'Post Date'
        );

        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns() {
        $columns = array(
            'id' => 'ID'
        );

        return $columns;
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns() {
        return array(
            'title' => array('title', false),
            'url' => array('url', false)
        );
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data() {
        $data = array();

        $token = get_option('searchstax_serverless_token_read');
        $select_api = get_option('searchstax_serverless_api_select');

        if ( $token != '' && $select_api != '' ) {
            $url = $select_api . '?q=*:*';
            $url .= '&fl=id,title,thumbnail,url,summary,post_type,categories,tags,post_date';
            $url .= '&start=' . ($this->index_start_page - 1) * $this->index_row_count;
            $url .= '&rows=' . $this->index_row_count;
            if(!empty($_GET['orderby'])) {
                $url .= '&sort=' . $_GET['orderby'];
                if(!empty($_GET['order'])) {
                    $url .= ' ' . $_GET['order'];
                }
                else {
                    $url .= ' asc';
                }
            }
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
                //$return['status'] = 'failed';
                //$return['data'] = $json['message'];
            }
            elseif ( $json != null && isset($json['response']) ) {
                $this->index_item_count = $json['response']['numFound'];
                foreach ( $json['response']['docs'] as $this_doc) {
                    $data[] = array(
                        'id' => $this_doc['id'],
                        'title' => $this_doc['title'],
                        'url' => $this_doc['url'],
                        'post_date' => $this_doc['post_date'][0]
                    );
                }
            }
        }
        return $data;
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name ) {
        switch( $column_name ) {
            case 'id':
            case 'title':
                return $item[ $column_name ];
            case 'post_date':
                return wp_date( get_option( 'date_format' ) , strtotime($item[ $column_name ]));
            case 'url':
                return '<a href="' . $item[ $column_name ] . '" target="_blank">' . $item[ $column_name ] . '</a>';

            default:
                return print_r( $item, true ) ;
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b ) {
        // Set defaults
        $orderby = 'title';
        $order = 'asc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby'])) {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order'])) {
            $order = $_GET['order'];
        }

        $result = strcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc') {
            return $result;
        }

        return -$result;
    }
    public function no_items() {

        $token = get_option('searchstax_serverless_token_read');
        $select_api = get_option('searchstax_serverless_api_select');

        if ( $token != '' && $select_api != '' ) {
            _e( 'No items have been indexed' );
        }
        else {
            _e( 'Please enter account info to get started' );
        }
    }
}
?>