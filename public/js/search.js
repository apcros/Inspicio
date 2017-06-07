function search() {

	$.post(window.location.origin+"/api/reviews/search",
		{
			filters: {
				query: 'test',
				languages: []
			},
			page: '1'
		}
		,function(data) {
		console.log(data);
	},'json')
	.fail(function(data) {

		$('body').html(data.responseText);

	})
}