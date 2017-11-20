function displayPopup(style, text, duration) {
	var options = {
		content: text,
		style: style,
		timeout: duration
	}
	var snackbar = $.snackbar(options);
}

function showModalConfirm(title, text, callback_confirmed, modal_name = "confirm_modal") {
	window.updateOrCreateVue(modal_name,'#'+modal_name+"_vue", 'modal', {
		title: title,
		text: text
	});
	$("#"+modal_name).modal();
	$("#"+modal_name+"_btn").off();
	$("#"+modal_name+"_btn").click(function() {
		callback_confirmed();
		$("#"+modal_name).modal('close');
	});
	$("#"+modal_name).modal('open');
}
