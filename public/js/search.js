function search() {
	var query_val = $("#review-keywords").val();
	var languages_selected = $("#review-language").val();

	$.post(window.location.origin+"/api/reviews/search",
		{
			filters: {
				query: query_val,
				languages: languages_selected
			},
		}
		,function(data) {
			var html = "";
			$.each(data.reviews, function(key, val) {
				html += '<li class="list-group-item">\
				<a href="/reviews/'+val.id+'/view" class="btn btn-info pull-right" target="_blank">View</a>\
				<h4 class="list-group-item-heading">'+val.name+'</h4>\
				<p><span class="label label-primary">'+val.language+'</span></p>\
				<p><span class="label label-default"> Author : '+val.author+'</span></p>\
				</li>';
			});
			$('#reviews-list').html(html);
	},'json')
	.fail(function(data) {
		$('#reviews-list').html('<div class="alert alert-danger"><b>Error while searching. Please try again</div>');
	})
}
$(document).ready(function() {
	$('#review-language').select2({placeholder: "Languages"});
})