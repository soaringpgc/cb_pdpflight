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
    		$.ajax({
    			url: PDP_FLIGHT_SUBMITTER.ajax_url,
    			method: 'POST',
    			data: {
    				action: 'pdp_update_time',
    				thetime: localtime,
    				start: $(this).data("start"),
    				key: $(this).val()
    			 },
    			success : function( response ) {
    //                console.log( response );
    //                alert( PDP_FLIGHT_SUBMITTER.success );
                    window.location.reload();
                },
                fail : function( response ) {
                    console.log( response );
                    alert( PDP_FLIGHT_SUBMITTER.failure );
                }		
    		});		
    	 });
	});
})( jQuery );
