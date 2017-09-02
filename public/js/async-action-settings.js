function updateSettings() {
	var jsonObj = {
		settings: []
	};
	$(":input[name^='setting_']").each(function(index) {
		var setting = $(this);
		jsonObj.settings.push({
			key: setting.attr("id"),
			value: setting.is(":checked"),
		});
	});

	console.log(jsonObj);
	
	$.post(window.location.origin+"/ajax/settings", jsonObj, function(data) {
		if(data.success) {
			displayPopup("snackbar-success", data.message, 4000);
		} else {
			displayPopup("snackbar-error", "Error "+data.message, 4000);
		}

	})
	.fail(function() {
		displayPopup("snackbar-error", "Error while executing the request", 4000);
	});
}

