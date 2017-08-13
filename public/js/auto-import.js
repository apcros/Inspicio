$(document).ready(function(){
	$('#repositories').select2();
});

function updateAutoImport(enabled, id) {
	$.post(window.location.origin+"/ajax/auto-import/"+id,
		{
			enabled: enabled,
		}
		,function (data) {
			if(data.success) {
				location.reload();
			} else {
				displayPopup('snackbar-error', 'Error '+data.message, 4000);
			}
		},'json')
	.fail(function(data) {
		displayPopup('snackbar-error', 'Unexpected error ocurred ', 4000);
	})
}