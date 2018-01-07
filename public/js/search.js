function search(page) {
	var query_val = $("#review-keywords").val();
	var languages_selected = $("#review-language").val();
	var search_in_closed = $("#review-can-be-closed").prop('checked');
	var button_state = window.startLoading("#start-search-btn");

	

	$.post(window.location.origin+"/api/reviews/search",
		{
			filters: {
				query: query_val,
				languages: languages_selected,
				include_closed: search_in_closed
			},
			page: page
		}
		,function(result) {

			var previous_handler = function () {
				window.search((result.reviews.current_page-1));
			};

			var next_handler = function() {
				window.search((result.reviews.current_page+1));
			};

			$("#previous-a").unbind();
			$("#next-a").unbind();

			window.updateOrCreateVue('searchreviews','#reviews-search-result', 'reviews', result.reviews);

			if(result.reviews.prev_page_url != null){
				$( "#previous-a" ).bind("click", previous_handler);
			}

			if(result.reviews.next_page_url != null) {
				$("#next-a").bind("click", next_handler);
			}

	},'json')
	.fail(function() {
		Materialize.toast("Error while searching. Please try again", 4000, "red");
	})
	.always(function() {
		window.stopLoading("#start-search-btn",button_state);
	})
}
$(document).ready(function() {
	$('#review-language').select2({placeholder: "Language(s)"});
})