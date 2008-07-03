$.Comment = {
	add: function(service, node, parent) {
		parent = parent || 0;
		_$({
			url: '/comment/'+node+'/'+service+'/add',
			data: {arg: {parent: parent}},
			target: function(data) {
				$('<div>'+data.data+'</div>').dialog({title: data.pageTitle, width: 400});
			}
		});
	},
	status: function(target, service, id, flag) {
		parent = parent || 0;
		_$({
			url: '/comment/'+id+'/'+service+'/status',
			data: {arg: {flag: flag}},
			target: function(result) {
				var html = $('<td colspan="0" />');
				result.error.length && html.append(_$.message(result.error, 'error'));
				result.warning.length && html.append(_$.message(result.warning, 'warning'));
				result.status.length && html.append(_$.message(result.status, 'status'));
				$(target).html(html);
			}
		});
	}
};