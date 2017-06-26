function reviewAction(id, endpoint, success_callback) {
	$("#review-action").attr("disabled",true);
	$.post(window.location.origin+endpoint, function(data) {
		if(data.success) {
			displayPopup('snackbar-success',data.message,4000);
			success_callback();
		} else {
			$("#review-action").attr("disabled",false);
			displayPopup('snackbar-error', 'Error '+data.message, 4000);
		}
	})
	.fail(function() {
		$("#review-action").attr("disabled",false);
		displayPopup('snackbar-error','Error while executing the request',4000);
	});
}
function approveReview(id) {
	reviewAction(id, "/ajax/reviews/"+id+"/approve",function() {
		$("#review-approve").html('Approved');
		$("#review-approve").attr("disabled",true);
	});
}

function followReview(id) {
	reviewAction(id,"/ajax/reviews/"+id+"/track",function() {
		$("#review-approve").attr("disabled",false);

		$("#review-follow").attr("disabled",false);
		$("#review-follow").attr("onclick", "unfollowReview('"+id+"');");
		$("#review-follow").removeClass("btn-info");
		$("#review-follow").addClass("btn-danger");
		$("#review-follow").html("Unfollow this review");
	});
}

function unfollowReview(id) {
	reviewAction(id,"/ajax/reviews/"+id+"/untrack",function() {
		$("#review-follow").attr("onclick", "followReview('"+id+"');");
		$("#review-follow").html("Follow this review");
		$("#review-follow").attr("disabled",false);
		$("#review-follow").removeClass("btn-danger");
		$("#review-follow").addClass("btn-info");
		$("#review-approve").attr("disabled",false);
	});
}

function closeReview(id) {
	showModalConfirm('Closing review','You are about to close your review, are you sure ?', function() {
		reviewAction(id,"/ajax/reviews/"+id+"/close",function() {
			$("#review-close-"+id).html("Re-open");
			$("#review-edit-"+id).attr("disabled",true);
			$("#review-edit-"+id).attr("href","#");
			$("#review-close-"+id).attr("onclick","reopenReview('"+id+"');");
		})
	});
}
function reopenReview(id) {
	reviewAction(id,"/ajax/reviews/"+id+"/reopen",function() {
			$("#review-close-"+id).html("Close");
			$("#review-edit-"+id).attr("disabled",false);
			$("#review-edit-"+id).attr("href","/reviews/"+id+"/edit");
			$("#review-close-"+id).attr("onclick","closeReview('"+id+"');");
		})
}