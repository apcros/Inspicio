function reviewAction(id, endpoint) {

	$.post(window.location.origin+endpoint, function(data) {
		if(data.success) {
			Materialize.toast(data.message,4000,'green');
			loadActions();
		} else {
			Materialize.toast('Error : '+data.message, 4000, 'red');
		}
	})
	.fail(function() {
		Materialize.toast('Unexpected error',4000, 'red');
	});
}
function approveReview(id) {
	reviewAction(id, "/ajax/reviews/"+id+"/approve");
}

function followReview(id) {
	reviewAction(id,"/ajax/reviews/"+id+"/track";
}

function unfollowReview(id) {
	reviewAction(id,"/ajax/reviews/"+id+"/untrack");
}

function closeReview(id) {
	showModalConfirm('Closing review','You are about to close your review, are you sure ?', function() {
		reviewAction(id,"/ajax/reviews/"+id+"/close")
	});
}
function reopenReview(id) {
	reviewAction(id,"/ajax/reviews/"+id+"/reopen")
}

function loadActions() {
	var review_id = $("#review-id").val();
	$.get(window.location.origin+'/api/reviews/'+review_id+'/permissions', function(data) {
		if(data.success) {
			updateOrCreateVue('reviewsactions','#review-actions', 'permissions', data.message);
		} else {
			Materialize.toast('snackbar-error', 'Failed to load review actions '+data.message,4000, 'red');
		}
	})
}

$(document).ready(function() {
	loadActions();
})