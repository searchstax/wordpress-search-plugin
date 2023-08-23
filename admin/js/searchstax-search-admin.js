(function( $ ) {
	'use strict';
	$(document).ready(function() {
		/*
		 * Ugly jQuery for tabs
		 */

		$('#searchstax_search_account_tab').click(function() {
			$('#searchstax_search_account').show();
			$('#searchstax_search_index').hide();
			$('#searchstax_search_sitesearch').hide();

			$('#searchstax_search_index_tab').removeClass('nav-tab-active');
			$('#searchstax_search_sitesearch_tab').removeClass('nav-tab-active');
			$('#searchstax_search_account_tab').addClass('nav-tab-active');
		});
		
		$('#searchstax_search_index_tab').click(function() {
			$('#searchstax_search_account').hide();
			$('#searchstax_search_index').show();
			$('#searchstax_search_sitesearch').hide();

			$('#searchstax_search_index_tab').addClass('nav-tab-active');
			$('#searchstax_search_sitesearch_tab').removeClass('nav-tab-active');
			$('#searchstax_search_account_tab').removeClass('nav-tab-active');
		});
		
		$('#searchstax_search_sitesearch_tab').click(function() {
			$('#searchstax_search_account').hide();
			$('#searchstax_search_index').hide();
			$('#searchstax_search_sitesearch').show();

			$('#searchstax_search_index_tab').removeClass('nav-tab-active');
			$('#searchstax_search_sitesearch_tab').addClass('nav-tab-active');
			$('#searchstax_search_account_tab').removeClass('nav-tab-active');
		});

		if ( $('#searchstax_search_api_available').val() === '0' ) {
			$('#searchstax_search_account').show();
			$('#searchstax_search_index').hide();
			$('#searchstax_search_sitesearch').hide();
		}

		function searchstax_search_get_index_items_solr() {
			$('#searchstax_search_indexed_loader').css('display','inline-block');
			$.ajax({
				url: wp_ajax.ajax_url,
				type: 'POST',
				data: {
					'action': 'get_indexed_items',
					'nonce': wp_ajax.nonce
				},
				success: function(data) {
					var response = JSON.parse(data);
					$('#searchstax_search_indexed_loader').css('display','none');
					if ( response['status'] === 'success' ) {
						$('#searchstax_search_indexed_status_message').text('Indexed (' + response['data']['numFound'] + ')');
					}
					else {
						$('#searchstax_search_indexed_status_message').text(response['data']);
					}
				},
				error: function(errorThrown) {
					$('#searchstax_search_indexed_status_message').text('WordPress plugin error');
					$('#searchstax_search_indexed_loader').css('display','none');
				}
			});
		}

		$('#searchstax_search_get_indexed_items').click(function( event ) {
			event.preventDefault();
			searchstax_search_get_index_items_solr();
		});

		$('#searchstax_search_reload_config').click(function( event ) {
			event.preventDefault();
			$('#searchstax_search_reload_config_loader').css('display','inline-block');
			$.ajax({
				url: wp_ajax.ajax_url,
				type: 'POST',
				data: {
					'action': 'reload_config',
					'nonce': wp_ajax.nonce
				},
				timeout: 100000,
				success: function(data) {
					var response = JSON.parse(data);
					$('#searchstax_search_reload_config_loader').css('display','none');
					if ( response['status'] === 'success' ) {
						$('#searchstax_search_reload_config_message').text('Update complete');
					}
					else {
						$('#searchstax_search_reload_config_message').text(response['status']);
					}
				},
				error: function(errorThrown) {
					$('#searchstax_search_reload_config_message').text('WordPress plugin error');
					$('#searchstax_search_reload_config_loader').css('display','none');
				}
			});
		});

		$('#searchstax_search_index_content_now').click(function( event ) {
			event.preventDefault();
			$('#searchstax_search_index_loader').css('display','inline-block');
			$.ajax({
				url: wp_ajax.ajax_url,
				type: 'POST',
				data: {
					'action': 'index_content_now',
					'nonce': wp_ajax.nonce
				},
				timeout: 100000,
				success: function(data) {
					var response = JSON.parse(data);
					$('#searchstax_search_index_loader').css('display','none');
					if ( response['status'] === 'success' ) {
						$('#searchstax_search_index_status_message').text('Indexing complete');
						setTimeout(function() {
							location.reload();
						}, 500);
					}
					else {
						$('#searchstax_search_index_status_message').text(response['status']);
						setTimeout(function() {
							location.reload();
						}, 5000);
					}
				},
				error: function(errorThrown) {
					$('#searchstax_search_index_status_message').text('WordPress plugin error');
					$('#searchstax_search_index_loader').css('display','none');
				}
			});
		});

		$('#searchstax_search_delete_items').click(function( event ) {
			event.preventDefault();
			$('#searchstax_search_delete_loader').css('display','inline-block');
			$.ajax({
				url: wp_ajax.ajax_url,
				type: 'POST',
				data: {
					'action': 'delete_indexed_items',
					'nonce': wp_ajax.nonce
				},
				timeout: 100000,
				success: function(data) {
					var response = JSON.parse(data);
					$('#searchstax_search_index_status_message').text('All items removed from index');
					$('#searchstax_search_delete_loader').css('display','none');
					setTimeout(function() {
						location.reload();
					}, 5000);
				},
				error: function(errorThrown) {
					$('#searchstax_search_delete_status_message').text('WordPress plugin error');
					$('#searchstax_search_delete_loader').css('display','none');
				}
			});
			return false;
		});
	});
})( jQuery );
