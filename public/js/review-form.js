function loadOpenPullRequests(owner, repo, account_id) {
	$("#repository").attr('disabled', true);

	$.getJSON('/ajax/reviews/pulls/'+owner+'/'+repo+'/'+account_id, function (data) {
		var html ='';

		$.each(data, function (key, val) {

			html += "<option value='"+val.url+"'>"+val.name+"</option>";
		});
		$("#pull_request").html(html);
		autoPopulatePRTitle();
	}).always(function() {
		$("#repository").attr('disabled', false);
	});
}

function loadBranches(owner, repo, account_id) {
	$("#repository").attr('disabled', true);
	$.getJSON('/ajax/reviews/branches/'+owner+'/'+repo+'/'+account_id, function (data) {
		var html ='';

		$.each(data, function (key, val) {
			html += "<option value='"+val.name+"'>"+val.name+"</option>";
		});
		$("#head_branch").html(html);
		$("#base_branch").html(html);

	}).always(function() {
		$("#repository").attr('disabled', false);
	});
}

function extractFromRepositoryVals(repo_str) {
	var select_values = repo_str.split(',');
	var keys = select_values[0].split('/');

	return {
		account_id: select_values[1],
		repo_owner: keys[0],
		repo_slug: keys[1]
	}
}

function autoPopulatePRTitle() {
	var pr_title = $("#pull_request").text();
	var current_title = $("#title").val();
	var repo_str = $("#repository").val();
	var repo_vals = extractFromRepositoryVals(repo_str);

	/* We don't want to replace the user title
	 and we don't want to populate if it's empty*/
	if(pr_title != "" && current_title == "") {
		$("#title").val(repo_vals.repo_slug+" - "+pr_title);
	}
}
$(document).ready(function(){
	$('#repository').select2({placeholder: "Select a repository"});
	$('#pull_request').select2({placeholder: "Select an open pull request"});
	$('#language').select2({placeholder: "Select a language"});
	$('#base_branch').select2();
	$('#head_branch').select2();
	tinymce.init({ selector:"textarea" });
});

$('#new_pull_request').change(function() {
	var check = $(this).prop('checked');
	$("#pull_request").attr('disabled',check);
	$("#new_pull_request_branches_select").attr('hidden',!check);
});

$("#repository").on("select2:select", function (e) { 
	var vals = extractFromRepositoryVals(e.params.data.id);

	loadOpenPullRequests(vals.repo_owner, vals.repo_slug, vals.account_id);
	loadBranches(vals.repo_owner, vals.repo_slug, vals.account_id);
});

$("#pull_request").on("select2:select",function (e) {
	autoPopulatePRTitle();
})
