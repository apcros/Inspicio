function addSkill() {
	$("#review-action").attr('disabled', true);
	var skill_id = $('#skill').val();
	var skill_name = $('#skill option:selected').text();
	var level_id = $('#level').val();
	var level_name = $('#level option:selected').text();

	$.post(window.location.origin+"/ajax/account/skills", {skill: skill_id, level: level_id}, function(data) {
		if(data.success) {
			displayPopup('snackbar-success', data.message, 4000);
			$('#skill_list').append('<tr><td>'+skill_name+'</td><td>'+level_name+'</td></tr>');
		} else {
			displayPopup('snackbar-error', 'Error '+data.message, 4000);
		}

	})
	.fail(function() {
		displayPopup('snackbar-error', 'Error while executing the request', 4000);
		$("#review-action").attr('disabled', false);
	});

}

function deleteSkill(id) {
	showModalConfirm('Deleting skill','You are about to delete this skill, are you sure ?', function() {
		$.post(window.location.origin+"/ajax/account/skills/"+id+"/delete", function(data) {
			if(data.success) {
				displayPopup('snackbar-success',data.message,5000);
				$("#skill-"+id).remove();
			} else {
				displayPopup('snackbar-error','Error: '+data.message,5000);
			}
		})
		.fail(function() {
			displayPopup('snackbar-error', 'Error while executing the request', 5000);
		})
	});

}