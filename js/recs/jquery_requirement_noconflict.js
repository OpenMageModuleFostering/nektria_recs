//Load jQuery as js library requirement
if (typeof(jQuery)=='undefined'){
	var recs_script_obj = document.createElement('script');
	recs_script_obj.src = "https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js";
	recs_script_obj.onload = function(){
		window.recs_jq = jQuery.noConflict();
	}
	document.getElementsByTagName('head')[0].appendChild(recs_script_obj);
}else{
	//Use jQuery if it is loaded before
	window.recs_jq = jQuery;
}