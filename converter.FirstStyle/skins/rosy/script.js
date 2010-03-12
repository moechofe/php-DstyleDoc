$(function(){
	var content = $("#page-content");
	var browser = $("#page-browser");
	if( content && browser )
	{
		while( (browser.outerHeight() > content.outerHeight()) && (parseInt(browser.css('width')) < ($(document).width()/3)) )
		{
			var width = parseInt(browser.css('width')) + 20;
			browser.css('width',''+width+'px');
			content.css('left',''+width+'px');
		}
	}
});
