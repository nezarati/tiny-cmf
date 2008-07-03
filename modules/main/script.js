var lastHash;
$().ready(function() {
	$('head').append('<script type="text/javascript" id="script" />');
	$('body').append('<iframe style="display: none" id="loader" />');
	setInterval(function() {
		if (lastHash != document.location.hash.substring(1))
		_$({tagName: 'A', href: lastHash = document.location.hash.substring(1)});
	}, 100)
});
$.locale = {};
function __() {
	for (var i = 1; i<arguments.length; i++)
		arguments[0] = arguments[0].replace(new RegExp("\\{"+(i-1)+"\\}", "g"), arguments[i]);
	return arguments[0];
};
String.prototype.i18n = function(args) {
	str = this;
	$.locale.strings && $.locale.strings[str] && (str = $.locale.strings[str]);
	if (args)
		for (var key in args)
			str = str.replace(key, key.charAt(0) == '@' ? String(args[key]).checkPlain() : key.charAt(0) == '%' ? '<em>'+String(args[key]).checkPlain()+'</em>' : args[key]);
	return str;
};
String.prototype.checkPlain = function() {
	str = this;
	var replace = {'&': '&amp;', '"': '&quot;', '<': '&lt;', '>': '&gt;'};
	for (var character in replace)
		str = str.replace(new RegExp(character, 'g'), replace[character]);
	return str;
}
String.prototype.trim = function() {
	return this.replace(/^\s+/, '').replace(/\s+$/, '');
}
if (!window.XMLHttpRequest)
	window.XMLHttpRequest = function() {
		var xhro = ['Msxml2.XMLHTTP.6.0', 'Msxml2.XMLHTTP.4.0', 'Msxml2.XMLHTTP.3.0', 'Msxml2.XMLHTTP', 'Microsoft.XMLHTTP'], i;
		for (i=0; i<xhro.length; i++)
			try {
				return new ActiveXObject(xhro[i]);
			} catch(e) {}
		alert('Error initializing XMLHttpRequest!');
		return null;
	};
jQuery.parseUrl = function(url) {
	url.match(/^((http|ftp):\/)?\/?([^:\/\s]+)((\/\w+)*\/)([\w\-\.]+\.[^#?\s]+)(#[\w\-]+)?$/);
	return {url: RegExp['$&'], protocol: RegExp.$2, host:RegExp.$3, path:RegExp.$4, file:RegExp.$6, hash:RegExp.$7}
}

function formFilterSubmit(form, url) {
	$(form).parent().parent().parent().find('.header a').click();
	$('<a href="'+url+'" onclick="return _$(this)" />').click();
}
_$ = function(obj) {
	if (obj.tagName == 'FORM') {
		var self = $(obj);
		_$({url: self.attr('action'), method: self.attr('method'), data: self.serialize(), target: function(result) {
			self.find('ul').remove();
			if (result.error.length)
				self.prepend(_$.message(result.error, 'error'));
			else if (result.status.length && isNaN(result.data))
				self.html(_$.message(result.status, 'status'));
			else {
				self.html(result.data);
				result.status.length && self.prepend(_$.message(result.status, 'status'));
			}
			if (result.warning.length)
				self.prepend(_$.message(result.warning, 'warning'));
			if (result.script)
				eval(result.script);
		}});
		return false;
	} else if (obj.tagName == 'A') {
		obj = $(obj);
		_$({
			url: obj.attr('href'),
			method: 'get',
			target: function(result) {
				var tmp, regex = new RegExp('(?:<script(?: src="([^"]+)")?.*?>)((\n|\r|.)*?)(?:<\/script>)', 'img'), script = _$('script');
				while (tmp = regex.exec(this.responseText)) {
					tmp[1] && _$.load(tmp[1]);
					script.text = tmp[2];
					script.text = '// :-D';
				}
				$('#'+(obj.attr('target') ? obj.attr('target') : 'content')).html(result.data);
				document.title = result.pageTitle;
				$('#pageTitle').html(result.pageTitle);
				var path = $.parseUrl(obj.attr('href')).path;
				path = path ? path : obj.attr('href');
				if (!obj.attr('target'))
					document.location.hash = lastHash = path;
				try {
					pageTracker._trackPageview(path);
				} catch(e) {}
			}
		});
		return false;
	} else if (typeof obj == 'string')
		return document.getElementById(obj);
	else if (typeof obj == 'object') {
		obj.method || (obj.method = 'get');
		var xhr = new XMLHttpRequest(), queryString, rnd = (obj.url.indexOf('?') == -1 ? '?' : '&')+(obj.cache ? '' : 'rnd='+new Date().getTime()+'&');
		if (obj.method.toLowerCase() == 'get')
			xhr.open('GET', obj.url+((queryString = (typeof obj.data == 'string' ? obj.data : obj.data ? $.param(obj.data) : '')) ? (obj.url.indexOf('?') == -1 ? '?' : '&')+queryString : ''), true);
		else {
			xhr.open('POST', obj.url, true);
			xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
		}
		xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		if (!obj.cache)
			xhr.setRequestHeader('Cache-Control', 'no-cache');
		xhr.onreadystatechange = function() {
			var msg = '';
			$('#loading').fadeIn();
			if (this.readyState != 4 && obj.process)
				obj.process(this.readyState);
			switch (this.readyState) {
				case 0:
				case 1:
					msg = 'Please wait...';
					break;
				case 2:
					msg = 'Still working...';
					break;
				case 3:
					msg = 'Transfering data...';
					break;
				case 4:
					msg = 'Loading...';
					if (this.status == 200) {
						$('#loading').fadeOut();
						var result = this.getResponseHeader('Content-Type').indexOf('application/json') != -1 ? eval('('+this.responseText+')') : this.responseText;
						if (typeof obj.target == 'function')
							obj.target(result, this.status);
						delete xhr;
					} else
						msg = 'The server encountered an error. Please try again later.';
			}
			$('#loading').text(msg);
		}
		xhr.send(obj.method.toLowerCase() == 'get' ? null : typeof obj.data == 'string' ? obj.data : $.param(obj.data));
	} else
		return window.document;
}
_$.load = function(url) {
	_$({
		url: url,
		cache: true,
		target: function(data) {
			var script = _$('script');
			script.text = data;
			script.text = '// :-D';
		}
	});
}
_$.message = function(data, className) {
	return '<ul class="message '+className+'">'+$.map(data, function(entry) {return '<li>'+entry+'</li>'}).join('')+'</ul>';
}
$.fn.confirmDelete = function(target) {
	if (confirm('Are you sure you want to permanently delete this item?'))
		_$({
			url: this.attr('href'),
			target: function(result) {
				var html = target.tagName == 'TD' ? $('<div />') : $('<td colspan="'+$('td', target).length+'" />');
				result.error.length && html.append(_$.message(result.error, 'error'));
				result.warning.length && html.append(_$.message(result.warning, 'warning'));
				result.status.length && html.append(_$.message(result.status, 'status'));
				$(target).html(html);
			}
		});
	return false;
};

$.fn.dialog = function(options) {
	options = options || {};
	options.width = options.width || 300;
	options.display = 'none';
	var self = this;
	var process = function(result) {
		var dialog = $('<div class="dialog ui"><div class="header"><h4></h4><a><span class="close" title="Close"></span></a></div><div class="content"></div></div>').css(options);
		$.fn.dialog.last && $.fn.dialog.last.css('zIndex', 50000);
		dialog.css('zIndex', 60000);
		dialog.data('disabled', true).
			find('.header').
				find('h4').
					html(options.title || result.pageTitle || self.html()).
				end().
				find('a').
					click(function() {
						$(this).parent().parent().slideUp(function() {
							$(this).remove()
						});
					}).
				end().
			end().
			find('.content').
				html(result.data);
		dialog.find('.header').bind(
			{
				mousemove: function(event) {
					dialog.data('disabled') || dialog.css({left: dialog.data('left')+event.pageX+'px', top: dialog.data('top')+event.pageY+'px'});
				},
				mousedown: function(event) {
					$.fn.dialog.last && $.fn.dialog.last.css('zIndex', 50000);
					$.fn.dialog.last = dialog.data('left', parseInt(dialog.css('left'), 10)-event.pageX).data('top', parseInt(dialog.css('top'), 10)-event.pageY).css('zIndex', 6);
					dialog.data('disabled', false);
				},
				mouseup: function() {
					dialog.data('disabled', true);
				}
			}
		);
		$('body').append(dialog);
		var tmp;
		dialog.css({top: (tmp = $(window).scrollTop() + ($(window).height()/2-$(dialog).height()/2))>10 ? tmp : $(window).height()/2, left: $(window).scrollLeft() + ($(window).width()/2-$(dialog).width()/2)});
		dialog.fadeIn();
	}
	if (this.is('a'))
		_$({url: this.attr('href'), target: process});
	else
		process({data: this});
	return false;
}

/**
 * Cookie plugin
 *
 * Copyright (c) 2006 Klaus Hartl (stilbuero.de)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

/**
 * Create a cookie with the given name and value and other optional parameters.
 *
 * @example $.cookie('the_cookie', 'the_value');
 * @desc Set the value of a cookie.
 * @example $.cookie('the_cookie', 'the_value', { expires: 7, path: '/', domain: 'jquery.com', secure: true });
 * @desc Create a cookie with all available options.
 * @example $.cookie('the_cookie', 'the_value');
 * @desc Create a session cookie.
 * @example $.cookie('the_cookie', null);
 * @desc Delete a cookie by passing null as value. Keep in mind that you have to use the same path and domain
 *       used when the cookie was set.
 *
 * @param String name The name of the cookie.
 * @param String value The value of the cookie.
 * @param Object options An object literal containing key/value pairs to provide optional cookie attributes.
 * @option Number|Date expires Either an integer specifying the expiration date from now on in days or a Date object.
 *                             If a negative value is specified (e.g. a date in the past), the cookie will be deleted.
 *                             If set to null or omitted, the cookie will be a session cookie and will not be retained
 *                             when the the browser exits.
 * @option String path The value of the path atribute of the cookie (default: path of page that created the cookie).
 * @option String domain The value of the domain attribute of the cookie (default: domain of page that created the cookie).
 * @option Boolean secure If true, the secure attribute of the cookie will be set and the cookie transmission will
 *                        require a secure protocol (like HTTPS).
 * @type undefined
 *
 * @name $.cookie
 * @cat Plugins/Cookie
 * @author Klaus Hartl/klaus.hartl@stilbuero.de
 */

/**
 * Get the value of a cookie with the given name.
 *
 * @example $.cookie('the_cookie');
 * @desc Get the value of a cookie.
 *
 * @param String name The name of the cookie.
 * @return The value of the cookie.
 * @type String
 *
 * @name $.cookie
 * @cat Plugins/Cookie
 * @author Klaus Hartl/klaus.hartl@stilbuero.de
 */
jQuery.cookie = function(name, value, options) {
    if (typeof value != 'undefined') { // name and value given, set cookie
        options = options || {};
        if (value === null) {
            value = '';
            options.expires = -1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
        }
        // CAUTION: Needed to parenthesize options.path and options.domain
        // in the following expressions, otherwise they evaluate to undefined
        // in the packed version for some reason...
        var path = options.path ? '; path=' + (options.path) : '';
        var domain = options.domain ? '; domain=' + (options.domain) : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    } else { // only name given, get cookie
        var cookieValue = null;
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = jQuery.trim(cookies[i]);
                // Does this cookie string begin with the name we want?
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }
};