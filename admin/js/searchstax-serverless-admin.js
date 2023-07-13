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

		$('#searchstax_serverless_account_tab').click(function() {
			$('#searchstax_serverless_account').show();
			$('#searchstax_serverless_index').hide();
			$('#searchstax_serverless_sitesearch').hide();

		});
		
		$('#searchstax_serverless_index_tab').click(function() {
			$('#searchstax_serverless_account').hide();
			$('#searchstax_serverless_index').show();
			$('#searchstax_serverless_sitesearch').hide();

		});
		
		$('#searchstax_serverless_sitesearch_tab').click(function() {
			$('#searchstax_serverless_account').hide();
			$('#searchstax_serverless_index').hide();
			$('#searchstax_serverless_sitesearch').show();
		});

		$('#searchstax_serverless_check_server_status').click(function( event ) {
			event.preventDefault();
			$('#searchstax_serverless_status_loader').css('display','inline-block');
			$.ajax({
				url: wp_ajax.ajax_url,
				type: 'POST',
				data: {
					'action': 'check_api_status'
				},
				success: function(data) {
					var response = JSON.parse(data);
					$('#searchstax_serverless_server_status_message').text(response['data']);
					$('#searchstax_serverless_status_loader').css('display','none');
				},
				error: function(errorThrown) {
					$('#searchstax_serverless_server_status_message').text('WordPress plugin error');
					$('#searchstax_serverless_status_loader').css('display','none');
				}
			});
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
				success: function(data) {
					var response = JSON.parse(data);
					$('#searchstax_serverless_index_status_message').html(response['data']['pages'] + '<br>' + response['data']['posts']);
					$('#searchstax_serverless_index_loader').css('display','none');
				},
				error: function(errorThrown) {
					$('#searchstax_serverless_index_status_message').text('WordPress plugin error');
					$('#searchstax_serverless_index_loader').css('display','none');
				}
			});
		});
	});

})( jQuery );
