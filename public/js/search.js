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
			$.each(result.reviews.data, function(key, val) {
				html += '<li class="list-group-item">\
				<a href="/reviews/'+val.id+'/view" class="btn btn-info pull-right" target="_blank">View</a>\
				<h4 class="list-group-item-heading">'+val.name+'</h4>\
				<p><span class="label label-primary">'+val.language+'</span></p>\
				<p><span class="label label-default"> Author : '+val.author+'</span></p>\
				</li>';
			});
			/* TODO : Move this horror to VueJS
			   This is really really awful, but no need in doing something clean as this will be ditched for VueJS soon
				09/09/2017 (Putting the date so I can't get away with leaving this 10 years in the code...)
			 */
			html += '<ul class="pagination">';
			var previous_bind = false;
			var next_bind = false;
			if(result.reviews.prev_page_url != null){
				html += "<li><a href='#' id='previous-a'>Previous</a></li>";
				previous_bind = true;
			}else {
				html += "<li class='disabled'><a href='#' aria-label='Next'>Previous</a></li>";
			}
				html += "<li class='active'><a href='#'>"+result.reviews.current_page+"</a></li>";
			if(result.reviews.next_page_url != null) {
				html += "<li><a href='#' id='next-a'>Next</a></li>";
				next_bind = true;
			} else {
				html += "<li class='disabled'><a href='#' aria-label='Next'>Next</a></li>";

			}
			$('#reviews-list').html(html);

			if(previous_bind){
				$( "#previous-a" ).click(function() {
				  search((result.reviews.current_page-1));
				});
			}

			if(next_bind) {
				$("#next-a").click(function() {
					search((result.reviews.current_page+1));
				});
			}

	},'json')
	.fail(function() {
		$('#reviews-list').html('<div class="alert alert-danger"><b>Error while searching. Please try again</div>');
	})
}
$(document).ready(function() {
	$('#review-language').select2({placeholder: "Languages"});
})