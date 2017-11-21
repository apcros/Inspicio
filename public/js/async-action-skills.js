function addSkill() {
	var btn_status = window.startLoading("#new_skill_btn");

	var skill_id = $('#skill').val();
	var skill_name = $('#skill option:selected').text();
	var level_id = $('#level').val();
	var level_name = $('#level option:selected').text();

	$.post(window.location.origin+"/ajax/account/skills", {skill: skill_id, level: level_id}, function(data) {
		if(data.success) {
			Materialize.toast(data.message, 4000, "green");
			loadSkills();
		} else {
			Materialize.toast(data.message, 4000, "red");
		}

	})
	.fail(function() {
		Materialize.toast("Unexpected error while executing the request", 4000, "red");
	})
	.always(function() {
		window.stopLoading("#new_skill_btn",btn_status);
	});

}

function deleteSkill(id) {
	showModalConfirm('Deleting skill','You are about to delete this skill, are you sure ?', function() {
		$.post(window.location.origin+"/ajax/account/skills/"+id+"/delete", function(data) {
			if(data.success) {
				Materialize.toast(data.message,5000,"green");
				window.loadSkills();
			} else {
				Materialize.toast("Error: "+data.message,5000,"red");
			}
		})
		.fail(function() {
			Materialize.toast("Unexpected error while executing the request",5000,"red");
		})
	});

}

function loadSkills() {
	$.get(window.location.origin+"/ajax/account/skills", function(data) {
		if(data.success) {
			window.updateOrCreateVue("skillslist","#skills-list", "skills", data.skills);
		} else {
			Materialize.toast("Failed to load skills : "+data.message, 5000, "red");
		}
	})
	.fail(function() {
		Materialize.toast("Unexpected error while loading skills", 5000, "red");
	});
}

$(document).ready(function() {
	window.loadSkills();
});