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

			if(result.reviews.prev_page_url != null){
				$( "#previous-a" ).click(function() {
				  var previous_btn_state = window.startLoading("#previous-a");
				  var btn_state = window.startLoading("#start-search-btn");
				  window.search((result.reviews.current_page-1),false);
				  window.stopLoading("#previous-a", previous_btn_state);
				  window.stopLoading("#start-search-btn",btn_state);
				});
			}

			if(result.reviews.next_page_url != null) {
				$("#next-a").click(function() {
					var next_btn_state = window.startLoading("#next-a");
					var btn_state = window.startLoading("#start-search-btn");
					window.search((result.reviews.current_page+1),false);
					window.stopLoading("#next-a", next_btn_state);
					window.stopLoading("#start-search-btn",btn_state);
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