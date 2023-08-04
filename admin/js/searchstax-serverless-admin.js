(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	$(document).ready(function() {
		/*
		 * Ugly jQuery for tabs
		 */

		$('#searchstax_serverless_account_tab').click(function() {
			$('#searchstax_serverless_account').show();
			$('#searchstax_serverless_index').hide();
			$('#searchstax_serverless_sitesearch').hide();

			$('#searchstax_serverless_index_tab').removeClass('nav-tab-active');
			$('#searchstax_serverless_sitesearch_tab').removeClass('nav-tab-active');
			$('#searchstax_serverless_account_tab').addClass('nav-tab-active');
		});
		
		$('#searchstax_serverless_index_tab').click(function() {
			$('#searchstax_serverless_account').hide();
			$('#searchstax_serverless_index').show();
			$('#searchstax_serverless_sitesearch').hide();

			$('#searchstax_serverless_index_tab').addClass('nav-tab-active');
			$('#searchstax_serverless_sitesearch_tab').removeClass('nav-tab-active');
			$('#searchstax_serverless_account_tab').removeClass('nav-tab-active');
		});
		
		$('#searchstax_serverless_sitesearch_tab').click(function() {
			$('#searchstax_serverless_account').hide();
			$('#searchstax_serverless_index').hide();
			$('#searchstax_serverless_sitesearch').show();

			$('#searchstax_serverless_index_tab').removeClass('nav-tab-active');
			$('#searchstax_serverless_sitesearch_tab').addClass('nav-tab-active');
			$('#searchstax_serverless_account_tab').removeClass('nav-tab-active');
		});

		function searchstax_serverless_get_index_items_solr() {
			$('#searchstax_serverless_indexed_loader').css('display','inline-block');
			$.ajax({
				url: wp_ajax.ajax_url,
				type: 'POST',
				data: {
					'action': 'get_indexed_items'
				},
				success: function(data) {
					var response = JSON.parse(data);
					$('#searchstax_serverless_indexed_status_message').text('Indexed (' + response['data']['numFound'] + ')');
					$('#searchstax_serverless_indexed_loader').css('display','none');

					$('#searchstax_serverless_indexed_list').empty();

					var indexed_items = $('<table>').addClass('wp-list-table widefat fixed striped');
			        indexed_items.append($('<thead>').append(
			            $('<th>').text('Type'),
			            $('<th>').text('Title'),
			            $('<th>').text('URL'),
			            $('<th>').text('Date')
			        ));
					indexed_items.append($('<tbody>'));
    				$.each(response['data']['docs'], function(i, doc) {
    					var fd = new Date(doc['post_date'][0]);
				        var $tr = $('<tr>').append(
				            $('<td>').text(doc['post_type'][0]),
				            $('<td>').text(doc['title'][0]),
				            $('<td>').html('<a href=' + doc['url'][0] + ' target="_blank">' + doc['url'][0] + '</a>'),
				            $('<td>').text(fd.getMonth() + '/' + fd.getDate() + '/' + fd.getFullYear())
				        );
				        indexed_items.append($tr);
				     });

					if ( response['data']['numFound'] > 0 ) {
				        indexed_items.append($('<tfoot>').append(
				            $('<th>').append($('<button>').text('Back').addClass('button')),
				            $('<th>').text(''),
				            $('<th>').text(''),
				            $('<th>').append($('<button>').text('Next').addClass('button'))
				        ));
					}

					$('#searchstax_serverless_indexed_list').append(indexed_items);
				},
				error: function(errorThrown) {
					$('#searchstax_serverless_indexed_status_message').text('WordPress plugin error');
					$('#searchstax_serverless_indexed_loader').css('display','none');
				}
			});
		}

		$('#searchstax_serverless_get_indexed_items').click(function( event ) {
			event.preventDefault();
			searchstax_serverless_get_index_items_solr();
		});

		$('#searchstax_serverless_index_content_now').click(function( event ) {
			event.preventDefault();
			$('#searchstax_serverless_index_loader').css('display','inline-block');
			$.ajax({
				url: wp_ajax.ajax_url,
				type: 'POST',
				data: {
					'action': 'index_content_now'
				},
				timeout: 100000,
				success: function(data) {
					var response = JSON.parse(data);
					$('#searchstax_serverless_index_loader').css('display','none');
					if ( response['status'] === 'success' ) {
						$('#searchstax_serverless_index_status_message').text('Indexing complete');
						setTimeout(function() {
							location.reload();
						}, 500);
					}
					else {
						$('#searchstax_serverless_index_status_message').text(response['status']);
						setTimeout(function() {
							location.reload();
						}, 5000);
					}
				},
				error: function(errorThrown) {
					$('#searchstax_serverless_index_status_message').text('WordPress plugin error');
					$('#searchstax_serverless_index_loader').css('display','none');
				}
			});
		});

		$('#searchstax_serverless_delete_items').click(function( event ) {
			event.preventDefault();
			$('#searchstax_serverless_delete_loader').css('display','inline-block');
			$.ajax({
				url: wp_ajax.ajax_url,
				type: 'POST',
				data: {
					'action': 'delete_indexed_items'
				},
				timeout: 100000,
				success: function(data) {
					var response = JSON.parse(data);
					$('#searchstax_serverless_index_status_message').text('All items removed from index');
					$('#searchstax_serverless_delete_loader').css('display','none');
					setTimeout(function() {
						location.reload();
					}, 5000);
				},
				error: function(errorThrown) {
					$('#searchstax_serverless_delete_status_message').text('WordPress plugin error');
					$('#searchstax_serverless_delete_loader').css('display','none');
				}
			});
			return false;
		});
	});

})( jQuery );
