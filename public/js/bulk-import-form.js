function loadPrs() {
	$.getJSON("/ajax/reviews/available-for-import", function (data) {
		if(data.success === 1) {
			var repositories = data.message.repositories;
			var points = data.message.points;

			updateOrCreateVue("prs_to_import","#available_prs", "data", {is_loading: false, repositories: repositories, points: points});

			$("#import-btn").attr("disabled", false);

		} else {
			updateOrCreateVue("prs_to_import","#available_prs", "data", {is_loading: false, error_message: "An error ocurred when loading your available pull requests : "+data.message});
		}

	}).fail(function() {
		updateOrCreateVue("prs_to_import","#available_prs", "data", {is_loading: false, error_message: "An ufnknown error ocurred when loading your available pull requests"});
	});
}

$(document).ready(function() {
	updateOrCreateVue("prs_to_import","#available_prs", "data", {is_loading: true}, function() {
			$("#prs_selected").select2({
				placeholder: "Available PRs to import",
				maximumSelectionLength: available_vues["prs_to_import"].data.points
			});
	});
	loadPrs();
});