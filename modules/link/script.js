$.Link = {
	hit: function(service, id) {
		_$({url: '/link/'+id+'/'+service+'/hit'});
	}
};