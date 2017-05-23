function displayPopup(style, text, duration) {
	var options = {
		content: text,
		style: style,
		timeout: duration
	}
	var snackbar = $.snackbar(options);
}