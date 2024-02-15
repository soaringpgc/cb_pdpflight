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
    		var target = $(this);  	
    		$(this).addClass('clicked'); 	
			$(this).closest('td').next('td').text(localtime);
			var takeoff = null;
			var landing = null;
			var flightTime = null;			
			var currentRow=$(this).closest("tr"); // get this flight row 
			
// 			var id = currentRow.find('input[name="flight_id"]').val();  // get the hidden record id. 	
			var id = currentRow.children('#flight_id').text();			
			var start = $(this).data("start");   // get the start/stop flag 
			if(!start ){
				landing = localtime;				
				let splitStartTime =currentRow.children('#takeoff').text().split(':'); // split in to array
// 				let splitStartTime =currentRow.find("td:eq(6)").text().split(':'); // split in to array								
    			let startDate = new Date(2020,1,1,splitStartTime[0],splitStartTime[1],splitStartTime[2]);	 // create starttime object, (date does not matter. )			
    			let splitEndTime = localtime.split(':');                                         // split in to array     
   				let endDate = new Date(2020,1,1,splitEndTime[0],splitEndTime[1],splitEndTime[2]);  // create endtime object, (date does not matter. )	
  				let difference = endDate - startDate;			// get the difference in milliseconds. 
   				difference = difference / 1000; 
   	  		    let hourDifference = Math.round(difference / 36);   // get hours	
 				flightTime = hourDifference/100 ;	
 				currentRow.children('#flighttime').text(flightTime);	
			} else {
				takeoff = localtime;
			}

//  		$fields = array('flightyear', 'yearkey', 'Date', 'Glider', 'Flight_Type', 'Pilot1', 
//  			'Pilot2', 'Takeoff', 'Landing', 'Time', 'Tow_Altitude', 'Tow_Pilot', 'Tow_Plane', 
//  			 'Notes' ); 

			var params = {
				type: "PUT",
				url: passed_vars.root + 'cloud_base/v1/pdp_flightlog',
				async: true,
				cache: false,
				timeout: 30000,
				beforeSend: function(xhr){
					xhr.setRequestHeader('X-WP-NONCE',  passed_vars.nonce );
					},		
 				data:{  id:  id  },
                fail: function( response ) {
                    console.log( response );
                    alert( passed_vars.failure );
                    },
                success: function( response ) {
    				target.removeClass('clicked'); 
                	}	                        			
			}
			if( start ){					
				params.data.Takeoff = takeoff;
			} else {
				params.data.Landing = landing;	
				params.data.Time = flightTime;
   		    }
//   console.log(params);
   			$.ajax(params);							
    	 });   	 
		    	 //appends an "active" class to .popup and .popup-content when the "Open" button is clicked
		$(".open").on("click", function() {
			var id = $(this).attr('id');
			$("#"+id).addClass("active");
			$("#flightPage").addClass("popup-overlay");
// 		  $(".popup-overlay, .popup-content").addClass("active");
		});

		$('.flightdata').click( function(){
			var thisRow = $(this).parent();		
			$('#id').val(thisRow.children('#flight_id').text());
// 			$('#flightyear').val(thisRow.children('#flightyear').text()).change();	
// 			$('#yearkey').val(thisRow.children('#yearkey').text()).change();	
// 			$('#Date').val(thisRow.children('#Date').text()).change();				
			$('#glider').val(thisRow.children('#glider').text()).change();	
			$('#Flight_Type').val(thisRow.children('#flight_type').text()).change();	
			$('#Pilot1').val(thisRow.children('#pilot1').text()).change();		
			$('#Pilot2').val(thisRow.children('#pilot2').text()).change();					
			$('#Takeoff').val(thisRow.children('#takeoff').text());	
			$('#Landing').val(thisRow.children('#landing').text());	
// 			$('#Time').val(thisRow.children('#flighttime').text());	
			$('#Tow_Pilot').val(thisRow.children('#towpilot').text()).change();
			$('#Tow_Plane').val(thisRow.children('#towplane').text().trim()).change(); // why I need trim on this one I do not know!
			$('#Tow_Altitude').val(thisRow.children('#towaltitude').text()).change();
// 			$('#form_towcharge').val(thisRow.children('#towcharge').text());
			$('#Notes').val(thisRow.children('#note').text());			
			$("#detailPage").addClass("active");
		})

//  		$fields = array('flightyear', 'yearkey', 'Date', 'Glider', 'Flight_Type', 'Pilot1', 
//  			'Pilot2', 'Takeoff', 'Landing', 'Time', 'Tow_Altitude', 'Tow_Pilot', 'Tow_Plane', 
//  			 'Notes' );

		
		//removes the "active" class to .popup and .popup-content when the "Close" button is clicked 
		$(".close, .popup-overlay").on("click", function() {
		  $(".popup-overlay, .popup-content").removeClass("active");
		  $("#flightPage").removeClass("popup-overlay");

		});
		 $(".pdp_popup_detail").on('click', function(e){
		 	e.preventDefault();
		 	alert ("popup" +  $(this).val());
//		 	window.location.replace('./html_cb_pdpflightlog_update.php');		 
		 });
	});
	$("#add_new_record").on('click', function(e){
	    	e.preventDefault();	  
				$.ajax({
					type: "POST",
					url: passed_vars.root + 'cloud_base/v1/pdp_flightlog',
					async: true,
				   cache: false,
				   timeout: 30000,
					beforeSend: function (xhr){
						xhr.setRequestHeader('X-WP-NONCE',  passed_vars.nonce );
					},
// 					data:{
// 						date: startdate,
// 						trade_id : "1",
// 						member_id: passed_vars.current_user_id
// 					},
					success : function (response){
// 						console.log(response);
						$('#flightTable').prepend(pdpFlightString(response));
// 						console.log (passed_vars.post_url + ' ?action="pdp-flight-log-details"&id='+response[0].id);
						// once we have inserted the new record open up he edit page. 
						window.location.assign(passed_vars.post_url+'?action=pdp-flight-log-details&id='+response[0].id);	
								
					},
					error: function(XMLHttpRequest, textStatus, errorThrown) { 
        					alert("Status: " + textStatus); 
        					alert("Error: " + errorThrown); 
   					} 
				});	 		  
	}) ;
	flightForm.onsubmit = async (e) => {
  		e.preventDefault();
  		var formData = new FormData(flightForm);
  		var obj ={};
    for(var pair of formData.entries()){
//         console.log(pair[0], pair[1]);
        obj[pair[0]] =  pair[1];
    }
		var params = {
			type: "PUT",
			url: passed_vars.root + 'cloud_base/v1/pdp_flightlog',
			async: true,
			cache: false,
			timeout: 30000,
			beforeSend: function(xhr){
				xhr.setRequestHeader('X-WP-NONCE',  passed_vars.nonce );
				},		
			data: obj,
//  			data: formData,
//  			processData: false,
//   			contentType: false,
//   			dataType: "json",
            fail: function( response ) {
                 console.log( response );
                 alert( passed_vars.failure );
                 },
            success: function( response ) {
    			 console.log( response );
             	}	                        			
		}

  
    	  $.ajax(params);
		  $(".popup-overlay, .popup-content").removeClass("active");
		  $("#flightPage").removeClass("popup-overlay");


		  // get the row we started with.  Not working !!!!
		  var currentRow = $('#flightTable tr input:first').filter(function () {
        		return this.value === '2014' || $(this).attr('id') === formData.get('id');
    	  }).closest('tr').parent().parent();
     	  alert(currentRow.find('#pilot1').text());
    	  currentRow.find('#pilot1').text(formData.get('Pilot1'));	
    	  
 console.log(currentRow);
	};
	
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
}  // 
function pdpFlightString(result){
    var row_string = `<tr> <td bgcolor="#999999" class="fl_style25"><div align="center"> <form action=`;
        row_string +=  passed_vars.post_url;      //'  "http://localhost:8888/wordpress/wp-admin/admin-post.php"';          	    	        	    	
        row_string += ` method="get">
          	<input type="hidden" name="action" value="pdp-flight-log-details">
          	<input type="hidden" name="id" value="`;
      row_string += result[0].id;          	    	        	    	
      row_string +=   `">	
            <input type="hidden" name="source_page" value="http://localhost:8888/wordpress/69-2/">	 	 
            <input type="submit" value="`
      row_string += result[0].yearkey;           	    	
      row_string +=`"></form>            		   
             </div></td>
             <td  class="fl_flight_row" align="center"></td>
             <td  class="fl_flight_row"><div align="center"></div></td>
             <td  class="fl_flight_row"></td>
             <td  class="fl_flight_row"></td>
             <td bgcolor="#FFFFFF"><button type="button" 5="" align="center" class="pdp_update_time button-flightlog button-start" value="29154" data-start="1"></button></td> 
             <td class="fl_flight_row"><div align="center">00:00:00</div></td>

             <td bgcolor="#FFFFFF"><button type="button" 5="" align="center" class="pdp_update_time button-flightlog button-stop" value="29154" data-start="0"></button></td> 
             
             <td  class="fl_flight_row"><div align="center">00:00:00</div></td>
             <td  class="fl_flight_row"><div align="center">0.00</div></td>
             <td  class="fl_flight_row"><div align="center"></div></td>
             <td  class="fl_flight_row"><div align="center">`
        row_string += result[0].Tow_Plane;          	    	        	    	
        row_string += `</div>                                    </td>
             <td class="fl_flight_row">`                             
        row_string += result[0].Tow_Pilot;          	    	        	    	
        row_string += `</td> <td class="fl_flight_row">999.00</td><td width="20" nowrap="nowrap" bgcolor="#FFFFFF" class="fl_style25"></td></tr>`;
        
        return(row_string);

}	



