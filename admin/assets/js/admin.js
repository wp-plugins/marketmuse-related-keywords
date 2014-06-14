/* global MM_Settings, tinyMCE */
(function ( $ ) {
	"use strict";

	$(function () {
		// Cache most used elements
		var $results      = $('#mm-results'),
			$errorArea    = $('#mm-error'),
			$inputField   = $('#mm-keywords'),
			$submitButton = $('#mm-keywords-submit'),
			keywords      = [],
			inputKeywords = [];

		if ( MM_Settings.keywords ) {
			keywords = $.parseJSON(MM_Settings.keywords);
		}

		window.mmManageKeywords = function(ed) {
			ed.on('keyup', function () {
				countKeyWords(ed.getContent());
			});
		};

		// Resize keyword box automatically
		$('#mm-keyword-tags').tagit({
			singleField: true,
			allowSpaces: true,
			singleFieldNode: $inputField
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
			for (var i = keywords.length - 1; i >= 0; i--) {
				var regex = new RegExp(keywords[i], 'gi'),
					count = content.match(regex);

				// If keyword is present update count and add checkmar class,
				// if not remove the class and display a dash
				var $countCell = $('td[data-mm-keyword="' + keywords[i] + '"]');

				if (count) {
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
				} else {
					$countCell.html('&mdash;');
					$countCell.removeClass('mm-checkmark');
				}
			}
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
			results +=     '<th>' + MM_Settings.headingTopics + '</th>';
			results +=     '<th data-toggle="tooltip" data-placement="top" title="' + MM_Settings.headingTooltip + '">' + MM_Settings.headingFrequency + ' <span class="dashicons dashicons-editor-help"></span></th>';
			results += '</thead>';

			results += '<tbody>';

			$.each(keywords, function (index, value) {
				// Set stripe css class in odd rows
				odd = (i++ % 2 === 0) ? 'class="mm-odd"' : '';

				results += '<tr ' + odd + '>';
				results +=     '<td>' + value + '</td>';
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

			// Make sure at least 2 keywords are present
			if (inputKeywords.length < 2) {
				$errorArea.html(
					'<p class="mm-error">' +
						'<small>' + MM_Settings.errorMsg + '</small>' +
					'</p>'
				);
			} else {
				$errorArea.html('');
			}

			// Disable input button
			disableButton(true);

			// Turn array into comma-sparated words
			var searchQuery = inputKeywords.join(',');

			// Fetch keywords from external server via AJAX
			var request = $.post(
				'http://prod.marketmuse.co:8000/api/search',
				{
					search_query: searchQuery
				},
				function (response) {
					$.each(response.data, function (index, value) {
						keywords.push(value.term);
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

		$('.tagit-new input').on('keypress', function (e) {
			if (e.which === 13) {
				e.preventDefault();
				fetchResults();
			}
		});

});

}(jQuery));