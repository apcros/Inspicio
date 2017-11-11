function updateSettings() {
	var jsonObj = {
		settings: []
	};
	$(":input[name^='setting_']").each(function(index) {
		var setting = $(this);
		//TODO : Handle cases where setting is NOT a boolean
		jsonObj.settings.push({
			key: setting.attr("id"),
			value: setting.is(":checked"),
		});
	});

	$.post(window.location.origin+"/ajax/settings", jsonObj, function(data) {
		if(data.success) {
			Materialize.toast(data.message,4000, "green");
		} else {
			Materialize.toast("Error "+data.message, 4000, "red");
		}

	})
	.fail(function() {
		Materialize.toast("Error while executing the request",4000, "red");
	});
}

