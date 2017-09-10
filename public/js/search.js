function search(page) {
	var query_val = $("#review-keywords").val();
	var languages_selected = $("#review-language").val();
	var search_in_closed = $("#review-can-be-closed").prop('checked');

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
				  window.search((result.reviews.current_page-1));
				});
			}

			if(result.reviews.next_page_url != null) {
				$("#next-a").click(function() {
					window.search((result.reviews.current_page+1));
				});
			}

	},'json')
	.fail(function() {
		window.displayPopup('snackbar-error','Error while searching. Please try again',4000);
	})
}
$(document).ready(function() {
	$('#review-language').select2({placeholder: "Languages"});
})