function displayPopup(style, text, duration) {
	var options = {
		content: text,
		style: style,
		timeout: duration
	}
	var snackbar = $.snackbar(options);
}

function showModalConfirm(title,text, callback_confirmed) {
	$("#modal-confirm").remove();
	var modal_html =
	'<div id="modal-confirm" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog">\
	  <div class="modal-dialog modal-sm" role="document">\
	    <div class="modal-content">\
	    <div class="modal-body">\
	      <h3>'+title+'</h3>\
	      <p>'+text+'</p>\
	      </div>\
		<div class="modal-footer">\
	      <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
	      <button id="modal-confirm-btn" type="button" class="btn btn-primary">Confirm</button>\
	    </div>\
	    </div>\
	  </div>\
	</div>';
	$('body').append(modal_html);
	$('#modal-confirm-btn').click(function() {
		callback_confirmed();
		$("#modal-confirm").modal('hide');
	});
	$("#modal-confirm").modal('show');
}