(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
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

		function get_search_results(params = {start: 0, post_type: '', category: '', tag: ''}) {

			const urlParams = new URLSearchParams(window.location.search);
			var query = $('#searchstax_serverless_dynamic_search_input').val();
			if (query == '' && urlParams.get('searchQuery')) {
				query = urlParams.get('searchQuery');
				$('#searchstax_serverless_dynamic_search_input').val(query);
			}
			if ($('#searchstax_serverless_dynamic_fixed_search_query').length > 0) {
				query = $('#searchstax_serverless_dynamic_fixed_search_query').val();
			}
			
			if (urlParams.get('start')) {
				params.start = urlParams.get('start');
			}

			if (query != '') {
				$('#searchstax_serverless_dynamic_label').css('display','none');
				$('#searchstax_serverless_dynamic_loader').css('display','inline-block');
				$('html,body').animate({scrollTop: 0},'slow');
				$.ajax({
					url: frontend_wp_ajax.ajax_url,
					type: 'POST',
					data: {
						'action': 'get_search_results',
						'post_id': $('#searchstax_serverless_dynamic_post_id').val(),
						'q': query,
						'post_type': params.post_type,
						'category': params.category,
						'tag': params.tag,
						'searchStart': params.start
					},
					success: function(data) {
						var response = JSON.parse(data);

						$('#searchstax_serverless_dynamic_results').empty();
	    				$.each(response['data']['docs'], function(i, doc) {
	    					//var fd = new Date(doc['post_date'][0]);
					        var div= $('<div>').addClass('searchstax_serverless_result');

					        if (doc['thumbnail']) {
					        	div.append(
					        		$('<div>').addClass('searchstax_serverless_thumbnail_frame')
					        			.append(
					        				$('<img>')
					        				.addClass('searchstax_serverless_thumbnail')
					        				.attr('src', doc['thumbnail'][0])
					        			)
					        	);
					        }

						    div.append(
					            $('<div>')
					        		.addClass('searchstax_serverless_snippet')
				        			.append(
				        				$('<h4>')
				        					.append(
				        						$('<a>')
					        						.addClass('searchstax_serverless_result_link')
					        						.attr('href', doc['url'])
					        						.text(doc['title'])
					        				),
				        				$('<div>').text(doc['summary'] ? doc['summary'][0] : ''),
				        				$('<div>')
				        					.append($('<a>')
				        						.addClass('searchstax_serverless_result_link')
				        						.attr('href', doc['url'])
				        						.text(doc['url']))
				        			)
					        );
					        $('#searchstax_serverless_dynamic_results').append(div);
					     });

	    				var resultCount = 'Showing <strong>' + (Number(params.start) + 1) + ' - ';
	                    if ((params.start + response['config']) > response['data']['numFound']) {
	                        resultCount += response['data']['numFound'];
	                    }
	                    else {
	                        resultCount += (Number(params.start) + Number(response['config']));
	                    }
	                    resultCount += '</strong> of <strong>' + response['data']['numFound'] + '</strong>';
						
						if ($('#searchstax_serverless_dynamic_fixed_search_query').length > 0) {
							resultCount += ' results for <strong>"' + query + '"</strong>';
						}

	    				$('#searchstax_serverless_dynamic_search_count').html(resultCount);

						if (response['data']['numFound'] > 0) {
					        $('#searchstax_serverless_result_dynamic_pagination').empty();
					        $('#searchstax_serverless_result_dynamic_pagination').append(
					        	$('<button>')
					        		.text('Previous')
					        		.addClass('button')
					        		.click(function() {
										get_search_results({start: Number(params.start) - Number(response['config'])})
					        		}),
					        	$('<div>')
					        		.text('Page ' + (Math.ceil(params.start / response['config']) + 1) + ' of ' + Math.ceil(response['data']['numFound'] / response['config'])),
					            $('<button>')
					            	.text('Next')
					            	.addClass('button')
					            	.click(function() {
										get_search_results({start: Number(params.start) + Number(response['config'])})
					            	})
					        );
						}
						$('#searchstax_serverless_dynamic_search_facets').empty();
						if (response['facet_counts'] && response['facet_counts']['facet_fields']) {
							if (response['facet_counts']['facet_fields']['post_type']) {
								var postTypeFacet = $('<div>')
									.addClass('searchstax_serverless_facet')
									.append($('<h4>').text('Content Type'));
								for(let i = 0; i < response['facet_counts']['facet_fields']['post_type'].length; i += 2) {
									postTypeFacet.append(
										$('<div>')
											.addClass('searchstax_serverless_facet')
											.append(
											$('<a>').text(
												response['facet_counts']['facet_fields']['post_type'][i] + ' ('
												+ response['facet_counts']['facet_fields']['post_type'][i + 1] + ')')
												.addClass('searchstax_serverless_facet_link')
												.attr('href','#')
												.click(function(event) {
													event.preventDefault();
													get_search_results({start: 0, post_type: response['facet_counts']['facet_fields']['post_type'][i]})
												})
										)
									);
								}
								$('#searchstax_serverless_dynamic_search_facets').append(postTypeFacet);
							}
							if (response['facet_counts']['facet_fields']['categories']) {
								var categoryFacet = $('<div>')
									.addClass('searchstax_serverless_facet')
									.append($('<h4>').text('Categories'));
								for(let i = 0; i < response['facet_counts']['facet_fields']['categories'].length; i += 2) {
									categoryFacet.append(
										$('<div>')
											.addClass('searchstax_serverless_facet')
											.append(
											$('<a>').text(
												response['facet_counts']['facet_fields']['categories'][i] + ' ('
												+ response['facet_counts']['facet_fields']['categories'][i + 1] + ')')
												.addClass('searchstax_serverless_facet_link')
												.attr('href','#')
												.click(function(event) {
													event.preventDefault();
													get_search_results({start: 0, category: response['facet_counts']['facet_fields']['categories'][i]})
												})
										)
									);
								}
								$('#searchstax_serverless_dynamic_search_facets').append(categoryFacet);
							}
							if (response['facet_counts']['facet_fields']['tags']) {
								var tagFacet = $('<div>')
									.addClass('searchstax_serverless_facet')
									.append($('<h4>').text('Tags'));
								for(let i = 0; i < response['facet_counts']['facet_fields']['tags'].length; i += 2) {
									tagFacet.append(
										$('<div>')
											.addClass('searchstax_serverless_facet')
											.append(
											$('<a>').text(
												response['facet_counts']['facet_fields']['tags'][i] + ' ('
												+ response['facet_counts']['facet_fields']['tags'][i + 1] + ')')
												.addClass('searchstax_serverless_facet_link')
												.attr('href','#')
												.click(function(event) {
													event.preventDefault();
													get_search_results({start: 0, tag: response['facet_counts']['facet_fields']['tags'][i]})
												})
										)
									);
								}
								$('#searchstax_serverless_dynamic_search_facets').append(tagFacet);
							}
						}
						$('#searchstax_serverless_dynamic_loader').css('display','none');
						$('#searchstax_serverless_dynamic_label').css('display','inline-block');
					},
					error: function(errorThrown) {
						$('#searchstax_serverless_dynamic_status_message').text('WordPress plugin error');
						$('#searchstax_serverless_dynamic_loader').css('display','none');
						$('#searchstax_serverless_dynamic_label').css('display','inline-block');
					}
				});
			}
		}

		if ( $('#searchstax_serverless_dynamic_search_results').length > 0 ) {
			get_search_results();
		}


		$('#searchstax_serverless_dynamic_search_submit').click(function() {
			get_search_results();
		});
	});

})( jQuery );
