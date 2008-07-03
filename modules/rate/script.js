$.Rate = {
	count: 5,
	over: function(service, node, score) {
		for (var i = 1; i <= score; i++)
			_$('rate-'+service+'-'+node+'-'+i).className = 'over';
	},
	out: function(service, node, average) {
		for (var i = 1; i <= $.Rate.count; i++)
			_$('rate-'+service+'-'+node+'-'+i).className = $.Rate.style(i, average);
	},
	style: function(score, average) {
		return score <= average || score-average < .25 ? 'on' : score-average < .75 ? 'half' : 'off';
	},
	view: function(service, node, data) {
		for (var count = 0, average = 0, i = 1; i <= $.Rate.count; i++) {
			if (!data[i])
				data[i] = 0;
			count += parseInt(data[i], 10);
			average += i*data[i];
		}
		average /= count ? count : 1;
		for (var i = 1, output = ''; i <= $.Rate.count; i++)
			output += '<span id="rate-'+service+'-'+node+'-'+i+'"'+(!$.cookie('rate['+service+']['+node+']') ? ' onmouseover="$.Rate.over(\''+service+'\', '+node+', '+i+')" onclick="$.Rate.vote(\''+service+'\', '+node+', '+i+', 1)" onmouseout="$.Rate.out(\''+service+'\', '+node+', '+average+')" style="cursor: pointer"' : '')+' class="'+$.Rate.style(i, average)+'" title="'+__('{0} gives a rating of {1}', data[i], count)+'"></span>'
		return _$('rate-'+service+'-'+node).innerHTML = output;
	},
	vote: function(service, node, score, mode) {
		_$({
			url: '/rate/'+node+'/'+service+'/vote',
			data: {arg: {score: score}},
			process: function(data) {
				$('#rate-'+service+'-'+node).html('<img src="/img/loading.gif" />');
			},
			target: function(data) {
				$.cookie('rate['+service+']['+node+']', score);
				$('#rate-'+service+'-'+node).html(mode ? data.status.join('\r\n') : data.data.points);
				data.error.length && alert(data.error.join('\r\n'));
			}
		});
	}
};