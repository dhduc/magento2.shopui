requirejs(['algoliaBundle'], function(algoliaBundle) {
	algoliaBundle.$(function ($) {
		window.transformHit = function (hit, price_key) {
			if (Array.isArray(hit.categories))
				hit.categories = hit.categories.join(', ');
			
			if (hit._highlightResult.categories_without_path && Array.isArray(hit.categories_without_path)) {
				hit.categories_without_path = $.map(hit._highlightResult.categories_without_path, function (category) {
					return category.value;
				});
				
				hit.categories_without_path = hit.categories_without_path.join(', ');
			}
			
			if (Array.isArray(hit.color)) {
				var colors = [];
				
				$.each(hit._highlightResult.color, function (i, color) {
					if (color.matchLevel === 'none') {
						return;
					}
					colors.push(color.value);
				});
				
				colors = colors.join(', ');
				
				hit._highlightResult.color = {value: colors};
			}
			else {
				if (hit._highlightResult.color && hit._highlightResult.color.matchLevel === 'none') {
					hit._highlightResult.color = {value: ''};
				}
			}
			
			if (hit._highlightResult.color && hit._highlightResult.color.value && hit.categories_without_path) {
				if (hit.categories_without_path.indexOf('<em>') === -1 && hit._highlightResult.color.value.indexOf('<em>') !== -1) {
					hit.categories_without_path = '';
				}
			}
			
			if (Array.isArray(hit._highlightResult.name))
				hit._highlightResult.name = hit._highlightResult.name[0];
			
			if (Array.isArray(hit.price))
				hit.price = hit.price[0];
			
			if (hit['price'] !== undefined && price_key !== '.' + algoliaConfig.currencyCode + '.default' && hit['price'][algoliaConfig.currencyCode][price_key.substr(1) + '_formated'] !== hit['price'][algoliaConfig.currencyCode]['default_formated']) {
				hit['price'][algoliaConfig.currencyCode][price_key.substr(1) + '_original_formated'] = hit['price'][algoliaConfig.currencyCode]['default_formated'];
			}
			
			// Add to cart parameters
			var action = algoliaConfig.instant.addToCartParams.action + 'product/' + hit.objectID + '/';
			
			hit.addToCart = {
				'action': action,
				'uenc': AlgoliaBase64.mageEncode(action),
				'formKey': algoliaConfig.instant.addToCartParams.formKey
			};
			
			return hit;
		};
		
		window.getAutocompleteSource = function (section, algolia_client, $, i) {
			if (section.hitsPerPage <= 0)
				return null;
			
			var options = {
				hitsPerPage: section.hitsPerPage,
				analyticsTags: 'autocomplete'
			};
			
			var source;
			
			if (section.name === "products") {
				options.facets = ['categories.level0'];
				options.numericFilters = 'visibility_search=1';
				
				source =  {
					source: $.fn.autocomplete.sources.hits(algolia_client.initIndex(algoliaConfig.indexName + "_" + section.name), options),
					name: section.name,
					templates: {
						empty: function (query) {
							var template = '<div class="aa-no-results-products">' +
								'<div class="title">' + algoliaConfig.translations.noProducts + ' "' + $("<div>").text(query.query).html() + '"</div>';
							
							var suggestions = [];
							
							if (algoliaConfig.showSuggestionsOnNoResultsPage && algoliaConfig.popularQueries.length > 0) {
								$.each(algoliaConfig.popularQueries.slice(0, Math.min(3, algoliaConfig.popularQueries.length)), function (i, query) {
									query = $('<div>').html(query).text(); // Avoid xss
									suggestions.push('<a href="' + algoliaConfig.baseUrl + '/catalogsearch/result/?q=' + encodeURIComponent(query) + '">' + query + '</a>');
								});
								
								template +=     '<div class="suggestions"><div>' + algoliaConfig.translations.popularQueries + '</div>';
								template +=        '<div>' + suggestions.join(', ') + '</div>';
								template +=     '</div>';
							}
							
							template +=         '<div class="see-all">' + (suggestions.length > 0 ? algoliaConfig.translations.or + ' ' : '') + '<a href="' + algoliaConfig.baseUrl + '/catalogsearch/result/?q=__empty__">' + algoliaConfig.translations.seeAll + '</a></div>' +
							'</div>';
							
							return template;
						},
						suggestion: function (hit) {
							hit = transformHit(hit, algoliaConfig.priceKey)
							hit.displayKey = hit.displayKey || hit.name;
							return algoliaConfig.autocomplete.templates[section.name].render(hit);
						}
					}
				};
			}
			else if (section.name === "categories" || section.name === "pages")
			{
				if (section.name === "categories" && algoliaConfig.show_cats_not_included_in_navigation == false) {
					options.numericFilters = 'include_in_menu=1';
				}
				
				source =  {
					source: $.fn.autocomplete.sources.hits(algolia_client.initIndex(algoliaConfig.indexName + "_" + section.name), options),
					name: i,
					templates: {
						empty: '<div class="aa-no-results">' + algoliaConfig.translations.noResults + '</div>',
						suggestion: function (hit) {
							if (section.name === 'categories') {
								hit.displayKey = hit.path;
							}
							
							if (hit._snippetResult && hit._snippetResult.content && hit._snippetResult.content.value.length > 0) {
								hit.content = hit._snippetResult.content.value;
								
								if (hit.content.charAt(0).toUpperCase() !== hit.content.charAt(0)) {
									hit.content = '&#8230; ' + hit.content;
								}
								
								if ($.inArray(hit.content.charAt(hit.content.length - 1), ['.', '!', '?'])) {
									hit.content = hit.content + ' &#8230;';
								}
								
								if (hit.content.indexOf('<em>') === -1) {
									hit.content = '';
								}
							}
							
							hit.displayKey = hit.displayKey || hit.name;
							return algoliaConfig.autocomplete.templates[section.name].render(hit);
						}
					}
				};
			}
			else if (section.name === "suggestions")
			{
				/// popular queries/suggestions
				var suggestions_index = algolia_client.initIndex(algoliaConfig.indexName + "_suggestions");
				var products_index = algolia_client.initIndex(algoliaConfig.indexName + "_products");
				
				source = {
					source: $.fn.autocomplete.sources.popularIn(suggestions_index, {
						hitsPerPage: section.hitsPerPage
					}, {
						source: 'query',
						index: products_index,
						facets: ['categories.level0'],
						hitsPerPage: 0,
						typoTolerance: false,
						maxValuesPerFacet: 1,
						analytics: false
					}, {
						includeAll: true,
						allTitle: algoliaConfig.translations.allDepartments
					}),
					displayKey: 'query',
					name: section.name,
					templates: {
						suggestion: function (hit) {
							if (hit.facet) {
								hit.category = hit.facet.value;
							}
							
							if (hit.facet && hit.facet.value !== 'All departments') {
								hit.url = algoliaConfig.baseUrl + '/catalogsearch/result/?q=' + hit.query + '#q=' + hit.query + '&hFR[categories.level0][0]=' + encodeURIComponent(hit.category) + '&idx=' + algoliaConfig.indexName + '_products';
							} else {
								hit.url = algoliaConfig.baseUrl + '/catalogsearch/result/?q=' + hit.query;
							}
							return algoliaConfig.autocomplete.templates.suggestions.render(hit);
						}
					}
				};
			} else {
				/** If is not products, categories, pages or suggestions, it's additional section **/
				var index = algolia_client.initIndex(algoliaConfig.indexName + "_section_" + section.name);
				
				source = {
					source: $.fn.autocomplete.sources.hits(index, {
						hitsPerPage: section.hitsPerPage,
						analyticsTags: 'autocomplete'
					}),
					displayKey: 'value',
					name: i,
					templates: {
						suggestion: function (hit) {
							hit.url = algoliaConfig.baseUrl + '/catalogsearch/result/?q=' + hit.value + '&refinement_key=' + section.name;
							return algoliaConfig.autocomplete.templates.additionalSection.render(hit);
						}
					}
				};
			}
			
			if (section.name === 'products') {
				source.templates.footer = function (query, content) {
					var keys = [];
					for (var key in content.facets['categories.level0']) {
						var url = algoliaConfig.baseUrl + '/catalogsearch/result/?q=' + encodeURIComponent(query.query) + '#q=' + encodeURIComponent(query.query) + '&hFR[categories.level0][0]=' + encodeURIComponent(key) + '&idx=' + algoliaConfig.indexName + '_products';
						keys.push({
							key: key,
							value: content.facets['categories.level0'][key],
							url: url
						});
					}
					
					keys.sort(function (a, b) {
						return b.value - a.value;
					});
					
					var ors = '';
					
					if (keys.length > 0) {
						ors += '<span><a href="' + keys[0].url + '">' + keys[0].key + '</a></span>';
					}
					
					if (keys.length > 1) {
						ors += ', <span><a href="' + keys[1].url + '">' + keys[1].key + '</a></span>';
					}
					
					var allUrl = algoliaConfig.baseUrl + '/catalogsearch/result/?q=' + encodeURIComponent(query.query);
					var returnFooter = '<div id="autocomplete-products-footer">' + algoliaConfig.translations.seeIn + ' <span><a href="' + allUrl +  '">' + algoliaConfig.translations.allDepartments + '</a></span> (' + content.nbHits + ')';
					
					if(ors && algoliaConfig.instant.enabled) {
						returnFooter += ' ' + algoliaConfig.translations.orIn + ' ' + ors;
					}
					
					returnFooter += '</div>';
					
					return returnFooter;
				}
			}
			
			if (section.name !== 'suggestions' && section.name !== 'products') {
				source.templates.header = '<div class="category">' + (section.label ? section.label : section.name) + '</div>';
			}
			
			return source;
		};
		
		window.fixAutocompleteCssHeight = function () {
			if ($(document).width() > 768) {
				$(".other-sections").css('min-height', '0');
				$(".aa-dataset-products").css('min-height', '0');
				var height = Math.max($(".other-sections").outerHeight(), $(".aa-dataset-products").outerHeight());
				$(".aa-dataset-products").css('min-height', height);
			}
		};
		
		window.fixAutocompleteCssSticky = function (menu) {
			var dropdown_menu = $('#algolia-autocomplete-container .aa-dropdown-menu');
			var autocomplete_container = $('#algolia-autocomplete-container');
			autocomplete_container.removeClass('reverse');
			
			/** Reset computation **/
			dropdown_menu.css('top', '0px');
			
			/** Stick menu vertically to the input **/
			var targetOffset = Math.round(menu.offset().top + menu.outerHeight());
			var currentOffset = Math.round(autocomplete_container.offset().top);
			
			dropdown_menu.css('top', (targetOffset - currentOffset) + 'px');
			
			if (menu.offset().left + menu.outerWidth() / 2 > $(document).width() / 2) {
				/** Stick menu horizontally align on right to the input **/
				dropdown_menu.css('right', '0px');
				dropdown_menu.css('left', 'auto');
				
				var targetOffset = Math.round(menu.offset().left + menu.outerWidth());
				var currentOffset = Math.round(autocomplete_container.offset().left + autocomplete_container.outerWidth());
				
				dropdown_menu.css('right', (currentOffset - targetOffset) + 'px');
			}
			else {
				/** Stick menu horizontally align on left to the input **/
				dropdown_menu.css('left', 'auto');
				dropdown_menu.css('right', '0px');
				autocomplete_container.addClass('reverse');
				
				var targetOffset = Math.round(menu.offset().left);
				var currentOffset = Math.round(autocomplete_container.offset().left);
				
				dropdown_menu.css('left', (targetOffset - currentOffset) + 'px');
			}
		};
		
		$(algoliaConfig.autocomplete.selector).each(function () {
			$(this).closest('form').submit(function (e) {
				var query = $(this).find(algoliaConfig.autocomplete.selector).val();
				
				if (algoliaConfig.instant.enabled && query == '')
					query = '__empty__';
				
				window.location = $(this).attr('action') + '?q=' + query;
				
				return false;
			});
		});
		
		function handleInputCrossAutocomplete(input) {
			if (input.val().length > 0) {
				input.closest('#algolia-searchbox').find('.clear-query-autocomplete').show();
				input.closest('#algolia-searchbox').find('.magnifying-glass').hide();
			}
			else {
				input.closest('#algolia-searchbox').find('.clear-query-autocomplete').hide();
				input.closest('#algolia-searchbox').find('.magnifying-glass').show();
			}
		}
		
		window.focusInstantSearchBar = function (search, instant_search_bar) {
			if ($(window).width() > 992) {
				instant_search_bar.focusWithoutScrolling();
				if (algoliaConfig.autofocus === false) {
					instant_search_bar.focus().val('');
				}
			}
			instant_search_bar.val(search.helper.state.query);
		};
		
		window.handleInputCrossInstant = function (input) {
			if (input.val().length > 0) {
				input.closest('#instant-search-box').find('.clear-query-instant').show();
			}
			else {
				input.closest('#instant-search-box').find('.clear-query-instant').hide();
			}
		};
		
		window.createISWidgetContainer = function (attributeName) {
			var div = document.createElement('div');
			div.className = 'is-widget-container-' + attributeName.split('.').join('_');
			
			return div;
		};
		
		var instant_selector = !algoliaConfig.autocomplete.enabled ? ".algolia-search-input" : "#instant-search-bar";
		
		$(document).on('input', algoliaConfig.autocomplete.selector, function () {
			handleInputCrossAutocomplete($(this));
		});
		
		$(document).on('input', instant_selector, function () {
			handleInputCrossInstant($(this));
		});
		
		$(document).on('click', '.clear-query-instant', function () {
			var input = $(this).closest('#instant-search-box').find('input');
			input.val('');
			input.get(0).dispatchEvent(new Event('input'));
			handleInputCrossInstant(input);
		});
		
		$(document).on('click', '.clear-query-autocomplete', function () {
			var input = $(this).closest('#algolia-searchbox').find('input');
			input.val('');
			
			if(!algoliaConfig.autocomplete.enabled && algoliaConfig.instant.enabled) {
				input.get(0).dispatchEvent(new Event('input'));
			}
			
			handleInputCrossAutocomplete(input);
		});
		
		/** Handle small screen **/
		$('body').on('click', '#refine-toggle', function () {
			$('#instant-search-facets-container').toggleClass('hidden-sm').toggleClass('hidden-xs');
			if ($(this).html()[0] === '+')
				$(this).html('- ' + algoliaConfig.translations.refine);
			else
				$(this).html('+ ' + algoliaConfig.translations.refine);
		});
		
		$.fn.focusWithoutScrolling = function(){
			var x = window.scrollX, y = window.scrollY;
			this.focus();
			window.scrollTo(x, y);
		};
	});
});

// Taken from Magento's tools.js - not included on frontend, only in backend
var AlgoliaBase64 = {
	// private property
	_keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
	//'+/=', '-_,'
	// public method for encoding
	encode: function (input) {
		var output = "";
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		var i = 0;
		
		if( typeof window.btoa === "function" ){
			return window.btoa(input);
		}
		
		input = AlgoliaBase64._utf8_encode(input);
		
		while (i < input.length) {
			
			chr1 = input.charCodeAt(i++);
			chr2 = input.charCodeAt(i++);
			chr3 = input.charCodeAt(i++);
			
			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;
			
			if (isNaN(chr2)) {
				enc3 = enc4 = 64;
			} else if (isNaN(chr3)) {
				enc4 = 64;
			}
			output = output +
				this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
				this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);
		}
		
		return output;
	},
	
	// public method for decoding
	decode: function (input) {
		var output = "";
		var chr1, chr2, chr3;
		var enc1, enc2, enc3, enc4;
		var i = 0;
		
		if( typeof window.atob === "function" ){
			return window.atob(input);
		}
		
		input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
		
		while (i < input.length) {
			
			enc1 = this._keyStr.indexOf(input.charAt(i++));
			enc2 = this._keyStr.indexOf(input.charAt(i++));
			enc3 = this._keyStr.indexOf(input.charAt(i++));
			enc4 = this._keyStr.indexOf(input.charAt(i++));
			
			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;
			
			output = output + String.fromCharCode(chr1);
			
			if (enc3 != 64) {
				output = output + String.fromCharCode(chr2);
			}
			if (enc4 != 64) {
				output = output + String.fromCharCode(chr3);
			}
		}
		output = AlgoliaBase64._utf8_decode(output);
		return output;
	},
	
	mageEncode: function(input){
		return this.encode(input).replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, ',');
	},
	
	mageDecode: function(output){
		output = output.replace(/\-/g, '+').replace(/_/g, '/').replace(/,/g, '=');
		return this.decode(output);
	},
	
	idEncode: function(input){
		return this.encode(input).replace(/\+/g, ':').replace(/\//g, '_').replace(/=/g, '-');
	},
	
	idDecode: function(output){
		output = output.replace(/\-/g, '=').replace(/_/g, '/').replace(/\:/g, '\+');
		return this.decode(output);
	},
	
	// private method for UTF-8 encoding
	_utf8_encode : function (string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";
		
		for (var n = 0; n < string.length; n++) {
			
			var c = string.charCodeAt(n);
			
			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}
		}
		return utftext;
	},
	
	// private method for UTF-8 decoding
	_utf8_decode : function (utftext) {
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;
		
		while ( i < utftext.length ) {
			
			c = utftext.charCodeAt(i);
			
			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			}
			else if((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			}
			else {
				c2 = utftext.charCodeAt(i+1);
				c3 = utftext.charCodeAt(i+2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}
		}
		return string;
	}
};