(function( $ ) {
	'use strict';
	$(document).ready(function() {

		function get_search_results(params = {start: 0, post_type: '', category: '', tag: ''}) {

			const urlParams = new URLSearchParams(window.location.search);
			var query = $('#searchstax_search_dynamic_search_input').val();
			if (query == '' && urlParams.get('searchQuery')) {
				query = urlParams.get('searchQuery');
				$('#searchstax_search_dynamic_search_input').val(query);
			}
			if ($('#searchstax_search_dynamic_fixed_search_query').length > 0) {
				query = $('#searchstax_search_dynamic_fixed_search_query').val();
			}
			
			if (urlParams.get('start')) {
				params.start = urlParams.get('start');
			}

			if (query != '') {
				$('#searchstax_search_dynamic_label').css('display','none');
				$('#searchstax_search_dynamic_loader').css('display','inline-block');
				$('html,body').animate({scrollTop: 0},'slow');
				$.ajax({
					url: frontend_wp_ajax.ajax_url,
					type: 'POST',
					data: {
						'action': 'get_search_results',
						'post_id': $('#searchstax_search_dynamic_post_id').val(),
						'q': query,
						'post_type': params.post_type,
						'category': params.category,
						'tag': params.tag,
						'searchStart': params.start
					},
					success: function(data) {
						var response = JSON.parse(data);

						$('#searchstax_search_dynamic_results').empty();
						$.each(response['data']['docs'], function(i, doc) {
							var div= $('<div>').addClass('searchstax_search_result');

							if (doc['thumbnail']) {
								div.append(
									$('<div>').addClass('searchstax_search_thumbnail_frame')
										.append(
											$('<img>')
											.addClass('searchstax_search_thumbnail')
											.attr('src', doc['thumbnail'][0])
										)
								);
							}
							div.append(
								$('<div>')
									.addClass('searchstax_search_snippet')
									.append(
										$('<h4>')
											.append(
												$('<a>')
													.addClass('searchstax_search_result_link')
													.attr('href', doc['url'])
													.text(doc['title'])
											),
										$('<div>').text(doc['summary'] ? doc['summary'][0] : ''),
										$('<div>')
											.append($('<a>')
												.addClass('searchstax_search_result_link')
												.attr('href', doc['url'])
												.text(doc['url']))
									)
							);
							$('#searchstax_search_dynamic_results').append(div);
						 });

						var resultCount = 'Showing <strong>' + (Number(params.start) + 1) + ' - ';
						if ((Number(params.start) + Number(response['config'])) > Number(response['data']['numFound'])) {
							resultCount += response['data']['numFound'];
						}
						else {
							resultCount += (Number(params.start) + Number(response['config']));
						}
						resultCount += '</strong> of <strong>' + response['data']['numFound'] + '</strong>';
						
						if ($('#searchstax_search_dynamic_fixed_search_query').length > 0) {
							resultCount += ' results for <strong>"' + query + '"</strong>';
						}

						$('#searchstax_search_dynamic_search_count').html(resultCount);

						if (response['data']['numFound'] > 0) {
							$('#searchstax_search_result_dynamic_pagination').empty();
							$('#searchstax_search_result_dynamic_pagination').append(
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
						$('#searchstax_search_dynamic_search_facets').empty();
						if (response['facet_counts'] && response['facet_counts']['facet_fields']) {
							if (response['facet_counts']['facet_fields']['post_type']) {
								var postTypeFacet = $('<div>')
									.addClass('searchstax_search_facet')
									.append($('<h4>').text('Content Type'));
								var postTypeFacetContainer = $('<div>').addClass('searchstax_search_facet_container');
								if (response['facet_counts']['facet_fields']['post_type'].length > 10) {
									postTypeFacet.append($('<input id="searchstax_search_post_type_expander" class="searchstax_search_toggle" type="checkbox">'));
									postTypeFacet.append($('<label for="searchstax_search_post_type_expander" class="searchstax_search_toggle_label">More</label>'));
								}
								for(let i = 0; i < response['facet_counts']['facet_fields']['post_type'].length; i += 2) {
									postTypeFacetContainer.append(
										$('<div>')
											.addClass('searchstax_search_facet')
											.append(
											$('<a>').text(
												response['facet_counts']['facet_fields']['post_type'][i] + ' ('
												+ response['facet_counts']['facet_fields']['post_type'][i + 1] + ')')
												.addClass('searchstax_search_facet_link')
												.attr('href','#')
												.click(function(event) {
													event.preventDefault();
													get_search_results({start: 0, post_type: response['facet_counts']['facet_fields']['post_type'][i]})
												})
										)
									);
								}
								postTypeFacet.append(postTypeFacetContainer);
								$('#searchstax_search_dynamic_search_facets').append(postTypeFacet);
							}
							if (response['facet_counts']['facet_fields']['categories']) {
								var categoryFacet = $('<div>')
									.addClass('searchstax_search_facet')
									.append($('<h4>').text('Categories'));
								var categoryFacetContainer = $('<div>').addClass('searchstax_search_facet_container');
								if (response['facet_counts']['facet_fields']['categories'].length > 10) {
									categoryFacet.append($('<input id="searchstax_search_category_expander" class="searchstax_search_toggle" type="checkbox">'));
									categoryFacet.append($('<label for="searchstax_search_category_expander" class="searchstax_search_toggle_label">More</label>'));
								}
								for(let i = 0; i < response['facet_counts']['facet_fields']['categories'].length; i += 2) {
									categoryFacetContainer.append(
										$('<div>')
											.addClass('searchstax_search_facet')
											.append(
											$('<a>').text(
												response['facet_counts']['facet_fields']['categories'][i] + ' ('
												+ response['facet_counts']['facet_fields']['categories'][i + 1] + ')')
												.addClass('searchstax_search_facet_link')
												.attr('href','#')
												.click(function(event) {
													event.preventDefault();
													get_search_results({start: 0, category: response['facet_counts']['facet_fields']['categories'][i]})
												})
										)
									);
								}
								categoryFacet.append(categoryFacetContainer);
								$('#searchstax_search_dynamic_search_facets').append(categoryFacet);
							}
							if (response['facet_counts']['facet_fields']['tags']) {
								var tagFacet = $('<div>')
									.addClass('searchstax_search_facet')
									.append($('<h4>').text('Tags'));
								var tagFacetContainer = $('<div>').addClass('searchstax_search_facet_container');
								if (response['facet_counts']['facet_fields']['tags'].length > 10) {
									tagFacet.append($('<input id="searchstax_search_tag_expander" class="searchstax_search_toggle" type="checkbox">'));
									tagFacet.append($('<label for="searchstax_search_tag_expander" class="searchstax_search_toggle_label">More</label>'));
								}
								for(let i = 0; i < response['facet_counts']['facet_fields']['tags'].length; i += 2) {
									tagFacetContainer.append(
										$('<div>')
											.addClass('searchstax_search_facet')
											.append(
											$('<a>').text(
												response['facet_counts']['facet_fields']['tags'][i] + ' ('
												+ response['facet_counts']['facet_fields']['tags'][i + 1] + ')')
												.addClass('searchstax_search_facet_link')
												.attr('href','#')
												.click(function(event) {
													event.preventDefault();
													get_search_results({start: 0, tag: response['facet_counts']['facet_fields']['tags'][i]})
												})
										)
									);
								}
								tagFacet.append(tagFacetContainer);
								$('#searchstax_search_dynamic_search_facets').append(tagFacet);
							}
						}
						$('#searchstax_search_dynamic_loader').css('display','none');
						$('#searchstax_search_dynamic_label').css('display','inline-block');
					},
					error: function(errorThrown) {
						$('#searchstax_search_dynamic_status_message').text('WordPress plugin error');
						$('#searchstax_search_dynamic_loader').css('display','none');
						$('#searchstax_search_dynamic_label').css('display','inline-block');
					}
				});
			}
		}

		if ( $('#searchstax_search_dynamic_search_results').length > 0 ) {
			get_search_results();
		}

		$('#searchstax_search_dynamic_search_submit').click(function() {
			get_search_results();
		});
	});
})( jQuery );
