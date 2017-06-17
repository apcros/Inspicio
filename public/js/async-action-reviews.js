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
		$("#review-action").html('Approved');
	});
}

function followReview(id) {
	reviewAction(id,"/ajax/reviews/"+id+"/track",function() {
		$("#review-action").attr("onclick", "approveReview('"+id+"');");
		$("#review-action").html("Approve");
		$("#review-action").attr("disabled",false);
	});
}

function unfollowReview(id) {
	reviewAction(id,"/ajax/reviews/"+id+"/untrack",function() {
		$("#review-action").attr("onclick", "followReview('"+id+"');");
		$("#review-action").html("Follow this review");
		$("#review-unfollow").remove();
		$("#review-action").attr("disabled",false);
	});
}

function closeReview(id) {
	showModalConfirm('Closing review','You are about to close your review, are you sure ?', function() {
		reviewAction(id,"/ajax/reviews/"+id+"/close",function() {
			$("#review-close-"+id).html("Re-open");
			$("#review-close-"+id).attr("onclick","reopenReview('"+id+"');");
		})
	});
}
function reopenReview(id) {
	reviewAction(id,"/ajax/reviews/"+id+"/reopen",function() {
			$("#review-close-"+id).html("Close");
			$("#review-close-"+id).attr("onclick","closeReview('"+id+"');");
		})
}