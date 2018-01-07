function displayPopup(style, text, duration) {
	var options = {
		content: text,
		style: style,
		timeout: duration
	}
	var snackbar = $.snackbar(options);
}

function showModalConfirm(title, text, callback_confirmed, modalName = "confirm_modal") {
	window.updateOrCreateVue(modalName,"#"+modalName+"_vue", "modal", {
		title: title,
		text: text
	});
	$("#"+modalName).modal();
	$("#"+modalName+"_btn").off();
	$("#"+modalName+"_btn").click(function() {
		callback_confirmed();
		$("#"+modalName).modal("close");
	});
	$("#"+modalName).modal("open");
}
