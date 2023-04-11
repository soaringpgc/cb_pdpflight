(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
         * 
         * The file is enqueued from inc/frontend/class-frontend.php.
	 */
	 $(function() {
    	 $(".pdp_update_time").on('click', function(e){
    		e.preventDefault();
    		var thistime = new Date();
    		var	localtime = thistime.toLocaleTimeString([], { hour12: false });
    		$(this).addClass('clicked'); 	
			$(this).closest('td').next('td').text(localtime);
// 				console.log($(this).val());
    		$.ajax({    	
    			url: passed_vars.ajax_url,
    			method: 'POST',
    			data: {
    				action: 'pdp_update_time',
    				thetime: localtime,
    				start: $(this).data("start"),
    				key: $(this).val()
    			 },
    			success : function( response ) {
    //                console.log( response );
//                    alert( response );
                      window.location.reload();
                },
                fail : function( response ) {
                    console.log( response );
                    alert( passed_vars.failure );
                }		
    		});		
    	 });   	 
		    	 //appends an "active" class to .popup and .popup-content when the "Open" button is clicked
		$(".open").on("click", function() {
		  $(".popup-overlay, .popup-content").addClass("active");
		});
		
		//removes the "active" class to .popup and .popup-content when the "Close" button is clicked 
		$(".close, .popup-overlay").on("click", function() {
		  $(".popup-overlay, .popup-content").removeClass("active");
		});
		 $(".pdp_popup_detail").on('click', function(e){
		 	e.preventDefault();
		 	alert ("popup" +  $(this).val());
//		 	window.location.replace('./html_cb_pdpflightlog_update.php');		 
		 });
	});
	
})( jQuery );

function pdpJumpTo(year){
 	oFormObj = document.forms['selectFlightYear'];
 	oFormObj.elements["pgc_year"].value= year;
 // alert (year);
  	 oFormObj.submit();
}	
function pdpDetails(pdp_type, pdp_id,year){
	const detail_types = [ "Glider", "Pilot2", "Pilot1", "Tow Pilot", "Tow Plane",  "Flight_Type", "Date", "Tow Altitude", "Flight_Type" ]; 
 	oFormObj = document.forms['selectMetricsDetails'];
 	oFormObj.elements["pdp_type"].value= detail_types[pdp_type];
 	oFormObj.elements["pdp_id"].value= pdp_id;	
 	oFormObj.elements["req_year"].value= year;	
// 	 alert (detail_types[pdp_type]);
//  	 alert (pdp_id);	
//  	 alert (year);	 
  	 oFormObj.submit();
}	
