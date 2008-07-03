$().ready(function() {
	/*
	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', 'UA-8218783-1']);
	_gaq.push(['_trackPageview']);

	(function() {
		var ga = document.createElement('script');
		ga.type = 'text/javascript';
		ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();
	*/
	$('head').append('<script src="http://www.google-analytics.com/ga.js" />');
	$().ready(function() {
		try {
			var pageTracker = _gat._getTracker('UA-8218783-1');
			pageTracker._trackPageview();
		} catch(e) {}
	});
});