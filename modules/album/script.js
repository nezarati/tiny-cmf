$.PhotoAlbum = {
	edit: function(id) {
		_$({
			url: '/album/'+id+'/edit',
			target: function(response) {
				$(response.data).dialog({title: response.pageTitle, width: 400});
			}
		});
	},
	chooser: function(element, id) {
		// TODO: Photo Zome (size: 160, default: 40, min: 25, max: 355)
		_$({
			url: '/album/'+id+'/chooser',
			target: function(response) {
				var value = $('#'+element).val();
				photos = $('<div class="PhotoAlbum-chooser" />');
				if (response.warning[0])
					photos.html(response.warning[0]);
				else
					$.each(response.data, function(id, src) {
						$('<img />').attr({src: src}).click(function() {
							$('#'+element).val(id);
							$(this).parent().parent().parent().find('.header a').click();
						}).css(value == id ? {opacity: .5} : {}).appendTo(photos);
					});
				photos.dialog({title: response.pageTitle, width: 400, height: 250});
			}
		});
	},
	information: function(id) {
		_$({
			url: '/album/'+id+'/information',
			target: function(response) {
				$(response.data).dialog({title: response.pageTitle});
			}
		});
		return false;
	},
	save: function(obj) {
		$('#loader').attr({src: obj.href});
		return false;
	},
};