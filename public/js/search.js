function search(page, trigger_loading = true) {
	var query_val = $("#review-keywords").val();
	var languages_selected = $("#review-language").val();
	var search_in_closed = $("#review-can-be-closed").prop('checked');
	var button_state;

	if(trigger_loading) {
		button_state = window.startLoading("#start-search-btn");
	}
	

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

			window.updateOrCreateVue('searchreviews','#reviews-search-result', 'reviews', result.reviews);
			$("#previous-a").off();
			$("#next-a").off();

			if(result.reviews.prev_page_url != null){
				$( "#previous-a" ).click(function() {
					var previousBtnState = window.startLoading("#previous-a");
					var btnState = window.startLoading("#start-search-btn");
					window.search((result.reviews.current_page-1),false);
					window.stopLoading("#previous-a", previousBtnState);
					window.stopLoading("#start-search-btn",btnState);
				});
			}

			if(result.reviews.next_page_url != null) {
				$("#next-a").click(function() {
					var nextBtnState = window.startLoading("#next-a");
					var btnState = window.startLoading("#start-search-btn");
					window.search((result.reviews.current_page+1),false);
					window.stopLoading("#next-a", nextBtnState);
					window.stopLoading("#start-search-btn",btnState);
				});
			}

	},'json')
	.fail(function() {
		Materialize.toast("Error while searching. Please try again", 4000, "red");
	})
	.always(function() {
		if(trigger_loading) {
			window.stopLoading("#start-search-btn",button_state);
		}
	})
}
$(document).ready(function() {
	$('#review-language').select2({placeholder: "Language(s)"});
})