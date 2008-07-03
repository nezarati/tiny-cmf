$.Taxonomy = {
	stack: [],
	parent: function(obj, service, id) {
		obj = $(obj);
		_$({
			url: '/taxonomy/'+id+'/'+service+'/index',
			target: function(data) {
				obj.parent().parent().parent().parent().slideUp(function() {
					$.Taxonomy.stack.push($(this).html());
					var self = this;
					$(this).html($(data.data).prepend(obj.html('←').click(function() {
						$(self).html($.Taxonomy.stack.pop());
					}).parent().parent())).slideDown();
				});
			}
		});
	}
};