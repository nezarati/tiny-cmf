$.Search = {
	quick: function(obj) {
		_$({
			url: '/search',
			data: {arg: {query: obj['arg[query]'].value}},
			target: function(result) {
				$('<div>'+result.data+'</div>').dialog({title: result.pageTitle, width: 400});
			}
		});
		return false;
	}
}