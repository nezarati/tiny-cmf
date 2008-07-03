$.fn.collapse = function(options) {
	var defaults = {
		closed : false
	}
	settings = $.extend({}, defaults, options);

	return this.each(function() {
		var obj = $(this);
		obj.find("legend").css('cursor', 'pointer').addClass('collapsible').click(function() {
			if (obj.hasClass('collapsed'))
				obj.removeClass('collapsed').addClass('collapsible');
	
			$(this).removeClass('collapsed');
	
			obj.children().not('legend').toggle("slow", function() {
			 
				 if ($(this).is(":visible"))
					obj.find("legend").addClass('collapsible');
				 else
					obj.addClass('collapsed').find("legend").addClass('collapsed');
			 });
		});
		if (settings.closed) {
			obj.addClass('collapsed').find("legend").addClass('collapsed');
			obj.children().not('legend').css('display', 'none');
		}
	});
};