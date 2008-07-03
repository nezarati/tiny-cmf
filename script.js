function navigation() {
	_$({
		url: '/menu/navigation',
		target: function(result) {
			var data = result.data;
			$('#ja-splitmenu').html(_navigation(data));
			function _navigation(data, ul) {
				var nav = '';
				if (!ul) {
					var status = '';
					for (var i = 0; i<data.status.length; i++)
						status += data.status[i];
					status = '<div id="status">'+status+'</div>';
					data = data.navigation;
				}
				for (var i in data)
					nav += '<li><a href="/'+i+'" title="'+(data[i].description ? data[i].description : '')+'" style="'+(data[i].type == 0 ? 'cursor: default' : '')+'" onclick="'+(data[i].type == 0 ? 'return false' : (data[i].onclick ? data[i].onclick : 'return _$(this)'))+'"><span>'+data[i].title+'</span></a>'+(data[i].children ? _navigation(data[i].children, 1) : '')+'</li>';
				return ul == 1 ? '<ul>'+nav+'</ul>' : nav+status;
			}
			$('#ja-splitmenu ul').each(function() {
				var self = this;
				$(this.parentNode).addClass('expanded');
				$(this.parentNode).mouseenter(function() {
					$(self).css('zIndex', 10).stop(true, true).fadeOut().slideDown();
					$($(this).find('a')[0]).addClass('hover');
				}).mouseleave(function() {
					$(self).fadeIn().slideUp();
					$($(this).find('a')[0]).stop(true, true).removeClass('hover');
				});
			});
		}
	});
};
$().ready(function() {
/*$('#sidebar div h3').each(function() {
	var self = $(this);
	$.cookie('preferences[widget]['+self.parent().attr('id')+']', {path: '/'}) && self.addClass('hide').removeClass('show') && self.parent().find('div').slideToggle();
	self.click(function() {
		self.parent().find('div').slideToggle();
			self.hasClass('hide') ? (self.addClass('show').removeClass('hide'), $.cookie('preferences[widget]['+self.parent().attr('id')+']', null, {path: '/'})) : (self.addClass('hide').removeClass('show'), $.cookie('preferences[widget]['+self.parent().attr('id')+']', true, {path: '/'}));
	});
});*/
navigation();
$('#ja-usertools ul[action=screen] li[data=narrow]').click(function() {
	$('body').removeClass('wide');
	$(this).css('backgroundPosition', '0 0');
	$(this).next().css('backgroundPosition', '-12px -15px');
	$.cookie('preferences[screen]', 'narrow', {path: '/'});
}).parent().find('li[data=wide]').click(function() {
	$('body').addClass('wide');
	$(this).css('backgroundPosition', '-12px 0');
	$(this).prev().css('backgroundPosition', '0 -15px');
	$.cookie('preferences[screen]', 'wide', {path: '/'});
}).parent().find('li[data='+($.cookie('preferences[screen]', {path: '/'}) || 'wide')+']').click();

var currentFontSize = $.cookie('preferences[fontSize]', {path: '/'}) || 3;
var fn;
$('#ja-usertools ul[action=fontSize] li[size]').click(fn = function(size) {
	if (size != 1) {
		size = parseInt($(this).attr('size'), 10);
		size<0 ? currentFontSize-- : size>0 ? currentFontSize++ : currentFontSize = 3;
	}
	$('body').css('fontSize', (57+currentFontSize*6)+'%');
	$.cookie('preferences[fontSize]', currentFontSize, {path: '/'});
});
fn(1);
fn = null;

$('#ja-usertools ul[action=style] li[color]').click(function() {
	$(this).siblings().css('backgroundPosition', function(index, value) {
		return value.substring(0, 5)+' -15px';
	});
	var color = $(this).attr('color');
	$('#logo').css('backgroundImage', 'url(/img/theme/zibal/logo-'+color+'.jpg)');
	$('#switchStyle').attr('href', 'http://templates.joomlart.com/ja_zibal/templates/ja_zibal/css/colors/'+color+'.css');
	$(this).css('backgroundPosition', $(this).css('backgroundPosition').substring(0, 5)+' 0');
	// $('#sh .container').css('backgroundImage', 'url(/img/'+color+'/sh0'+(Math.floor(Math.random()*10%3)+1)+'.jpg)');
	$.cookie('preferences[color]', color, {path: '/'});
}).parent().find('li[color='+($.cookie('preferences[color]', {path: '/'}) || 'default')+']').click();
$('#ja-search input[name=arg[query]]').focus(function() {
	this.value == 'search...' && (this.value = '');
}).blur(function() {
	this.value == '' && (this.value = 'search...');
});
$('#mainlevel-nav').append('<li><a href="/" class="mainlevel-nav">Home</a></li><li><a href="/user/login" onclick="return $(this).dialog()" class="mainlevel-nav">Log In</a></li><li><a href="/user/register" onclick="return $(this).dialog()" class="mainlevel-nav">Register</a></li>');
			});