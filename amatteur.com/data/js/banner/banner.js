jQuery(document).ready( function() {
	var images = $("#carousel img");

	$("#carousel img:not(:first)").hide();
	setInterval("changeImg()", 10000);
});

function changeImg() {
	var v = $("#carousel img:visible");
	var all = $("#carousel img");
	var i = all.index( v );
	
	i++;
	
	if (i>=all.length) i = 0;
	
	v.fadeOut();
	$(all[i]).fadeIn();
}
