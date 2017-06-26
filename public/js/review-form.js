function extractFromRepositoryVals(repoStr) {
	var selectValues = repoStr.split(",");
	var keys = selectValues[0].split("/");

	return {
		accountId: selectValues[1],
		owner: keys[0],
		slug: keys[1]
	};
}

function autoPopulatePRTitle() {
	var selectData = $("#pull_request").select2('data');
	var prTitle = selectData[0].text;

	var repoStr = $("#repository").val();
	var repoVals = extractFromRepositoryVals(repoStr);

	/* We don't want to replace the user title
	 with an empty one */
	if(prTitle !== "") {
		$("#title").val(repoVals.slug+" - "+prTitle);
	}
}

function loadOpenPullRequests(owner, repo, accountId) {
	$("#repository").attr('disabled', true);

	$.getJSON('/ajax/reviews/pulls/'+owner+'/'+repo+'/'+accountId, function (data) {
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

function loadBranches(owner, repo, accountId) {
	$("#repository").attr('disabled', true);
	$.getJSON('/ajax/reviews/branches/'+owner+'/'+repo+'/'+accountId, function (data) {
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

	loadOpenPullRequests(vals.owner, vals.slug, vals.accountId);
	loadBranches(vals.owner, vals.slug, vals.accountId);
});

$("#pull_request").on("select2:select",function (e) {
	autoPopulatePRTitle();
});
