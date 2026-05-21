function cookieadmin_dotweet(ele){
	window.open(jQuery("#"+ele.id).attr("action")+"?"+jQuery("#"+ele.id).serialize(), "_blank", "scrollbars=no, menubar=no, height=400, width=500, resizable=yes, toolbar=no, status=no");
	return false;
}

document.addEventListener("DOMContentLoaded", function() {
	if(!cookieadmin_data['is_pro']){
		jQuery("[cookieadmin-pro-only]").each(function(index) {
			jQuery(this).filter("input, textarea, select").attr("disabled", true).css('cursor', 'not-allowed');
			jQuery(this).find( "input, textarea, select, span" ).attr("disabled", true).css('cursor', 'not-allowed');
			jQuery(this).find( "input.cookieadmin-color-input" ).css("margin-left", "0px");
			jQuery(this).find( "label span" ).not('.cookieadmin-tooltip-box').css("background-color", "#e8e8e8").css("color", "#9f9f9f");
		});
	}
});