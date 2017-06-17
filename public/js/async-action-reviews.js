function followReview(id) {
	$("#review-action").attr('disabled', true);
	$.post(window.location.origin+"/ajax/reviews/"+id+"/track", function(data) {
		if(data.success) {
			displayPopup('snackbar-success', data.message, 4000);
			$("#review-action").attr('onclick', "approveReview('"+id+"');");
			$("#review-action").html('Approve');
		} else {
			displayPopup('snackbar-error', 'Error '+data.message, 4000);
		}

	})
	.fail(function(data) {
		displayPopup('snackbar-error', 'Error while executing the request', 4000);
	})
	.always(function() {
		$("#review-action").attr('disabled', false);
	})
}

function approveReview(id) {
	$("#review-action").attr('disabled', true);
	$.post(window.location.origin+"/ajax/reviews/"+id+"/approve", function(data) {
		if(data.success) {
			displayPopup('snackbar-success', data.message, 4000);
			$("#review-action").html('Approved');
		} else {
			$("#review-action").attr("disabled", false);
			displayPopup('snackbar-error', 'Error '+data.message, 4000);
		}

	})
	.fail(function() {
		displayPopup('snackbar-error', 'Error while executing the request', 4000);
		$("#review-action").attr('disabled', false);
	});
}

function closeReview(id) {
	showModalConfirm('Closing review','You are about to close your review, are you sure ?', function() {
		$.post(window.location.origin+"/ajax/reviews/"+id+"/close", function(data) {
			if(data.success) {
				displayPopup('snackbar-success',data.message,5000);
				$("#review-close-"+id).remove();
				$("#review-close").remove(); //Just to avoid having to put the id on the button on the detailed view
			} else {
				displayPopup('snackbar-error','Error: '+data.message,5000);
			}
		})
		.fail(function() {
			displayPopup('snackbar-error', 'Error while executing the request', 5000);
		})
	});
}