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
			var html = "";
			console.log(result);
			$.each(result.reviews.data, function(key, val) {
				html += '<li class="list-group-item">\
				<a href="/reviews/'+val.id+'/view" class="btn btn-info pull-right" target="_blank">View</a>\
				<h4 class="list-group-item-heading">'+val.name+'</h4>\
				<p><span class="label label-primary">'+val.language+'</span></p>\
				<p><span class="label label-default"> Author : '+val.author+'</span></p>\
				</li>';
			});
			html += '<ul class="pagination">';
			if(result.reviews.prev_page_url != null){
				html += "<li><a onclick='search("+(result.reviews.current_page-1)+");' >Previous</a></li>";
			}else {
				html += "<li class='disabled'><a href='#' aria-label='Next'>Previous</a></li>";
			}
				html += "<li class='active'><a href='#'>"+result.reviews.current_page+"</a></li>";
			if(result.reviews.next_page_url != null) {
				html += "<li><a onclick='search("+(result.reviews.current_page+1)+");' >Next</a></li>";
			} else {
				html += "<li class='disabled'><a href='#' aria-label='Next'>Next</a></li>";
			}
			$('#reviews-list').html(html);
	},'json')
	.fail(function() {
		$('#reviews-list').html('<div class="alert alert-danger"><b>Error while searching. Please try again</div>');
	})
}
$(document).ready(function() {
	$('#review-language').select2({placeholder: "Languages"});
})