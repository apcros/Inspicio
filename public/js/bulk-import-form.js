function displayLoader() {
	$("#async_loading").html("<div class='progress'><div class='progress-bar progress-bar-striped active' role='progressbar' style='width: 100%'><b>Loading available pull requests..</b></div></div>");
}

function loadPrs() {
	$.getJSON("/ajax/reviews/available-for-import", function (data) {
		var html = "<select class='form-control' name='prs_selected' id='prs_selected' multiple='multiple'>";

		console.log(data);
		
		if(data.success == 1) {
			var repositories = data.message.repositories;
			var points = data.message.points;
			$.each(repositories, function (key_repo, repository) {
				$.each(repository.pull_requests, function (key_pr, pull_request) {
					html += "<option value='"+pull_request.url+"'>"+pull_request.name+" (<b>"+repository.object.name+"</b>) </option>";
				});
				
			});
			html += "</select>";

			$("#async_loading").html(html);
			$("#import-btn").attr("disabled", true);
			//TODO take in consideration the number of points you currently have
			$("#prs_selected").select2({
				placeholder: "Available PRs to import",
				maximumSelectionLength: points
			});
		} else {
			$("#async_loading").html("<div class='alert alert-danger'>An error ocurred when loading your available pull requests : "+data.message+"</div>");
		}

	}).fail(function() {
		$("#async_loading").html("<div class='alert alert-danger'>An unknown error ocurred when loading your available pull requests</div>");
	});
}

$(document).ready(function() {
	displayLoader();
	loadPrs();
});