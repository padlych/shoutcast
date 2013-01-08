$(document).ready(function() {

$('div#style_radio a').css('cursor', 'pointer');

$('div#style_radio a').click(function() {
	var thisClass = this.className.slice(0,2);
	$('div.t0').hide();
	$('div.t1').hide();
    $('div.' + thisClass).show();
	$('div#style_radio a').removeClass('tab-current');
	$(this).addClass('tab-current');
});

});