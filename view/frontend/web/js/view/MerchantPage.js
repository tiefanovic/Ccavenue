function showMerchantPage() {
    if(jQuery("#ccavenue_page").size()) {
        jQuery( "#ccavenue_page" ).remove();
    }
    jQuery("#review-buttons-container .btn-checkout").hide();
    jQuery("#review-please-wait").show();
    var merchantPageUrl = jQuery('#ccavenue_payment_form').attr('action');
    jQuery('<iframe  name="ccavenue_page" id="ccavenue_page" height="auto" style="width: 100%;" frameborder="0" scrolling="no" onload="pfIframeLoaded(this)" style="display:none"></iframe>').appendTo('#pf_iframe_content');
    jQuery('.pf-iframe-spin').show();
    jQuery('.pf-iframe-close').hide();
    jQuery( "#ccavenue_page" ).attr("src", merchantPageUrl);
    jQuery( "#ccavenue_payment_form" ).attr("target","ccavenue_page");
    jQuery( "#ccavenue_payment_form" ).submit();
    //fix for touch devices
    if (fnIsTouchDevice()) {
        setTimeout(function() {
            jQuery("html, body").animate({ scrollTop: 0 }, "slow");
        }, 1);
    }
}
jQuery(document).ready(function(){
	 window.addEventListener('message', function(e) {
    	 jQuery("#ccavenue_page").css("height",e.data['newHeight']+'px'); 	 
 	 }, false);
  	
});
//fix for touch devices
function fnIsTouchDevice() {
    return 'ontouchstart' in window        // works on most browsers 
        || navigator.maxTouchPoints;       // works on IE10/11 and Surface
}

function pfClosePopup() {
    jQuery( "#div-pf-iframe" ).hide();
    jQuery( "#ccavenue_page" ).remove();
    window.location = jQuery( "#ccavenue_cancel_url" ).val();
}
function pfIframeLoaded(ele) {
    jQuery('.pf-iframe-spin').hide();
    jQuery('.pf-iframe-close').show();
    jQuery('#ccavenue_page').show();
}