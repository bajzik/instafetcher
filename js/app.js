jQuery.noConflict();
(function($) {
	$(function() {
		$(document).ready(function() {

			var initLoadMoreButton = function() {

				$(document).on('click', 'a.load-more-trigger', function() {

					var loadMoreButton = $(this);
					var form = $(this).closest('.jbif__content').find('form[name="load-more-helper"]');
					var data = form.serializeArray();
					var spinner = loadMoreButton.closest('.jbif__content').find('a .spinner');

					spinner.css({'display': 'inline-block'});

					$.ajax({
						url: JBIF.rest_url + JBIF.get_images_route,
						type: 'POST',
						data: data,
						success: function(data) {

							var success = data.success;

							if (success) {

								var html = data.html;

								loadMoreButton.closest('.jbif__content').find('.jbif__loadmore--results').append(html);

								form.find('input[name="current_page"]').val(data.current_page);

								var hideLoadMore = data.hide_load_more;

								if (hideLoadMore) {
									loadMoreButton.hide();
								}

								spinner.hide();

							}

						}
					});

				});

			};

			var equalizeProfileColumns = function() {


				setTimeout(function() {

					if ($('.jbif__container--user-detail-image').length > 0 && $('.jbif__container--user-detail.mobile').length === 0) {

						var imageColumn = $('.jbif__container--user-detail-image');
						var textColumn = $('.jbif__container--user-detail-info');

						imageColumn.height(textColumn.height() + 60);
					}
				}, 200);

			};

			var initFetchUserNameButton = function() {

				$(document).on('click', 'a.username-fetch-trigger', function() {

					var spinner = $('.spinner');

					var form = $(this).closest('.row').find('form[name="username-fetch-helper"]');
					var data = form.serializeArray();

					spinner.css({'display': 'inline-block'});

					$.ajax({
						url: JBIF.rest_url + JBIF.get_userdata_route,
						type: 'POST',
						data: data,
						success: function(data) {

							spinner.hide();

							$('a.username-fetch-trigger').replaceWith('<span class="message">' + data.message + '</span>');

							if (data.success) {

								setTimeout(function() {
									location.reload();
								}, 3000);

							}

						}
					});

				});

			};

			initLoadMoreButton();
			initFetchUserNameButton();
			equalizeProfileColumns();

		});
	});
})(jQuery);
