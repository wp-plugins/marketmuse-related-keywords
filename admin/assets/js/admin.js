/* global MM_Settings, tinyMCE */
(function ( $ ) {
	"use strict";

	$(function () {
		// Cache most used elements
		var $results      = $('#mm-results'),
			$inputField   = $('#mm-keywords'),
			$submitButton = $('#mm-keywords-submit'),
			keywords      = [],
			inputKeywords = [],
			keywordsExist;

		if ( MM_Settings.keywords ) {
			keywords = $.parseJSON(MM_Settings.keywords);
		}

		window.mmManageKeywords = function(ed) {
			ed.on('keyup', function () {
				countKeyWords(ed.getContent());
			});
		};

		// Enable keyword count on HTML text editor
		$('.wp-editor-area').on('keyup', function() {
			countKeyWords($(this).val());
		});

		// Enable tooptip support
		$results.tooltip({
			selector: '[data-toggle="tooltip"]',
			container: 'body'
		});

		/**
		 * Update keyword count
		 *
		 * @param  String content
		 * @return void
		 */
		function countKeyWords(content) {
			keywordsExist = 0;

			for (var i = keywords.length - 1; i >= 0; i--) {
				var regex = new RegExp(keywords[i], 'gi'),
					count = content.match(regex);

				// If keyword is present update count and add checkmar class,
				// if not remove the class and display a dash
				var $countCell = $('td[data-mm-keyword="' + keywords[i] + '"]');

				if (count) {
					if (count.length > 0) {
						keywordsExist++;
					}
					// If there are less than 8 occurrences display a checkmark with the count
					// else change the count color and remove checkmark
					if (count.length > 0 && count.length <= 8) {
						if (!$countCell.hasClass('mm-checkmark')) {
							$countCell.addClass('mm-checkmark');
						}

						$countCell.removeClass('mm-warning');
						$countCell.html('&#x2713; ' + count.length);
					} else if (count.length > 8) {
						if (!$countCell.hasClass('mm-warning')) {
							$countCell.addClass('mm-warning');
						}

						$countCell.removeClass('mm-checkmark');
						$countCell.html(count.length);
					}

					if (keywords[i] === keywords[0]) {
						$('#mm-focus-count').text(count.length);
						$('#mm-focus-percent').text(((count.length / (tinyMCE.activeEditor.getContent({format : 'text'}).replace(/['";:,.?¿\-!¡]+/g, '').match(/\S+/g) || []).length) * 100).toFixed(0));
					}
				} else {
					$countCell.html('&mdash;');
					$countCell.removeClass('mm-checkmark');
				}
			}

			$('#mm-keywords-exist').text(keywordsExist);
		}

		/**
		 * Render results table with keywords
		 *
		 * @param  array response
		 * @return void
		 */
		function renderResults() {
			var results = '<table class="mm-results-table">',
				odd     = '',
				i       = 0;

			results += '<thead>';
			results += '<tr>';
			results +=     '<td colspan="2">' + MM_Settings.focusKeywordCount + ': <span id="mm-focus-count">0</span> (<span id="mm-focus-percent">0</span>%)</td>';
			results += '</tr>';
			results += '<tr>';
			results +=     '<td colspan="2">' + MM_Settings.relatedTopics + ': <span id="mm-keywords-exist">0</span> / <span id="mm-keywords-total">' + keywords.length + '</span></td>';
			results += '</tr>';
			results += '<tr>';
			results +=     '<th>' + MM_Settings.headingTopics + '</th>';
			results +=     '<th data-toggle="tooltip" data-placement="top" title="' + MM_Settings.headingTooltip + '">' + MM_Settings.headingFrequency + ' <span class="dashicons dashicons-editor-help"></span></th>';
			results += '</tr>';
			results += '</thead>';

			results += '<tbody>';

			$.each(keywords, function (index, value) {
				// Set stripe css class in odd rows
				odd = (i++ % 2 === 0) ? 'class="mm-odd"' : '';

				results += '<tr ' + odd + '>';
				results +=     '<td>' + $('<div/>').text(value).html() + '</td>';
				results +=     '<td class="mm-count" data-mm-keyword="' + value + '">&mdash;</td>';
				results += '</tr>';
			});

			results += '</tbody>';

			results += '</table>';

			results += '<input type="hidden" name="mm-keyword-list" value="' + keywords.join(',') + '">';

			$results.html(results);
		}

		/**
		 * Toggle submit button state
		 *
		 * @param  bool state
		 * @return void
		 */
		function disableButton(state) {
			$submitButton.prop('disabled', state);

			if (state === true) {
				$submitButton.val(MM_Settings.buttonFetching);
			} else {
				$submitButton.val(MM_Settings.buttonSubmit);
			}
		}

		/**
		 * Fetch keywords with AJAX
		 *
		 * @return void
		 */
		function fetchResults() {
			// Split keywords by commas
			var queryString = $inputField.val().split(',');

			// Reset input keywords
			keywords = [];
			inputKeywords = [];

			// Trim whitespace in keywords
			$.each(queryString, function (index, value) {
				var trimmedvalue = value.trim();

				if (trimmedvalue) {
					inputKeywords.push(trimmedvalue);
				}
			});

			// Disable input button
			disableButton(true);

			// Turn array into comma-sparated words
			var searchQuery = inputKeywords.join(',');

			// Fetch keywords from external server via AJAX
			var request = $.post(
				'http://prod.marketmuse.com:8000/api/search_with_data',
				{
					search_query: searchQuery,
					// pass user_key specified in 3scale
					user_key: '8cce15356351679e4d4ee1461c803d06',
					// pass public token if present
					public_token: MM_Settings.settings.public_token
				},
				function (response) {
					$.each(response.data, function (index, value) {
						// If the public token is invalid the 'attractiveness'
						// attribute will return as -1 so filter out those
						if (value.attractiveness !== -1) {
							keywords.push(value.term);
						}
					});

					// Insert input keywords into results
					keywords = $.merge(inputKeywords, keywords);

					// Remove duplicates
					keywords = $.unique(keywords);
				},
				'json'
			);

			// Render keywords if AJAX request is successful
			request.done(renderResults);

			// Update keyword count
			request.done(function () {
				countKeyWords(tinyMCE.get('content').getContent());
			});

			// Enable submit button when AJAX request finishes
			request.complete(function () {
				disableButton(false);
			});
		}

		// Fetch results on button click and when pressing "Enter" key
		$submitButton.on('click', fetchResults);

		$inputField.on('keypress', function (e) {
			if (e.which === 13) {
				e.preventDefault();
				fetchResults();
			}
		});

});

}(jQuery));
