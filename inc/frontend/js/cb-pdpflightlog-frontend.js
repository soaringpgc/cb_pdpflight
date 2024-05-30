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
	 var working_count =0;
	 $(function() {
	 	 $('.pdp_update_time').on('click', function(e){	 
  	 		working_count++;
	    	$('#action_status').text("working: " +  working_count);	    	

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
//                     console.log( response );
                    alert('fail');
                    $('#action_status').text("error-T");	
                    },
                success: function( response ) {
    				target.removeClass('clicked'); 
    				working_count--;
	    			$('#action_status').text("idle: " +  working_count);	    	
//     				   console.log(target);
                	}	                        			
			}
			if( start ){					
				params.data.Takeoff = takeoff;
			} else {
				params.data.Landing = landing;	
				params.data.Time = flightTime;
   		    }
//    		    console.log(params);
   			$.ajax(params);	
   			target.removeClass('clicked'); 
    	
    							
    	 });   	 
		//appends an "active" class to .popup and .popup-content when the "Open" button is clicked
		$(".open").on("click", function() {
			var id = $(this).attr('id');
			$("#"+id).addClass("active");
			$("#flightPage").addClass("popup-overlay");
// 		  $(".popup-overlay, .popup-content").addClass("active");
		});

// 		$('.flightdata').click( function(){
		$(document).on('click', '.flightdata' , function(){
			var thisRow = $(this).parent();		
			flightDetail(thisRow);
		});
// populate the detail form with existing flight info. 		
	function flightDetail(thisRow){			
			$('#id').val(thisRow.children('#flight_id').text());
			$('#yearkey').text("PGC FLIGHT SHEET DETAIL SCREEN for flight: " + thisRow.children( '#yearkey').text());	
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
			$('#Notes').val(thisRow.children('#notes').text());			
			$("#detailPage").addClass("active");
		}	
				
		//removes the "active" class to .popup and .popup-content when the "Close" button is clicked 
// 		$(".close, .popup-overlay").on("click", function() {
		$(".close").on("click", function() {			
		  $(".popup-overlay, .popup-content").removeClass("active");
		  $("#flightPage").removeClass("popup-overlay");

		});
		 $(".pdp_popup_detail").on('click', function(e){
		 	e.preventDefault();
		 	alert ("popup" +  $(this).val());
//		 	window.location.replace('./html_cb_pdpflightlog_update.php');		 
		 });
	});
	// add new flight to list. 
	$("#add_new_record").on('click', function(e){
	    	e.preventDefault();	  	
    	 	working_count++;
	    	$('#action_status').text("working: " +  working_count);	    	
				$.ajax({
					type: "POST",
					url: passed_vars.root + 'cloud_base/v1/pdp_flightlog',
					async: true,
				   cache: false,
				   timeout: 30000,
					beforeSend: function (xhr){
						xhr.setRequestHeader('X-WP-NONCE',  passed_vars.nonce );
					},
					success : function (response){
//   						console.log(response);
//  						$('#id').val(thisRow.children('#flight_id').text(response));
  						working_count--;
  						$('#action_status').text('Idle');	    		
					},
					error: function(XMLHttpRequest, textStatus, errorThrown) { 
        					alert("Status: " + textStatus); 
        					alert("Error: " + errorThrown); 
        					$('#action_status').text('Error');	  
   					} 
				});	 		  
	}) ;
	flightForm.onsubmit = async (e) => {
  		e.preventDefault();
		working_count++;
  	    $('#action_status').text("idle: " +  working_count);	
  		var formData = new FormData(flightForm);
  		var obj ={};
    	for(var pair of formData.entries()){  // NTFS: should be able to somehow send FormData directly, but I could not get it to work. So...
//  	       console.log(pair[0], pair[1]);
    	    obj[pair[0]] =  pair[1];
    	} 
    	// make sure we have a member to charge. 
    	if(formData.get('Pilot1') == "" || formData.get('Pilot1') == null){
    		alert( "Member field cannot be blank.");
    		return;
    	}
// calcuate flight time.     
     	let splitStartTime =formData.get('Takeoff').split(':'); // split in to array
    	let startDate = new Date(2020,1,1,splitStartTime[0],splitStartTime[1],splitStartTime[2]);	 // create starttime object, (date does not matter. )			
    	let splitEndTime = formData.get('Landing').split(':'); // split in to array    
        let endDate = new Date(2020,1,1,splitEndTime[0],splitEndTime[1],splitEndTime[2]);		// create end time object, (date does not matter. )		    
  		let difference = endDate - startDate;			// get the difference in milliseconds. 
   		difference = difference / 1000; 
   	  	let hourDifference = Math.round(difference / 36);   // get hours	
 		let flightTime = hourDifference/100 ;	    
 		obj["Time"] =  flightTime;  
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
            fail: function( response ) {
//                  console.log( response );
                 alert("failed");
                     $('#action_status').text("idle: " +  working_count);	
                 },
            success: function( response ) {
//              alert("success");
//      			 console.log( response );
				working_count--;
    			$('#action_status').text("idle: " +  working_count);	
    			 currentRow.find('#towcharge').text(response[0].Tow_Charge);
    			 currentRow.find('#flighttime').text(response[0].Time);			
             	}	                        			
		}
    	  $.ajax(params);
		  $(".popup-overlay, .popup-content").removeClass("active");
		  $("#flightPage").removeClass("popup-overlay");
		  
		  var currentRow = $("td").filter(function() {
    		return $(this).text() == formData.get('id');
		  }).closest("tr");

     	  currentRow.find('#flight_type').text(formData.get('Flight_Type'));	
     	  currentRow.find('#glider').text(formData.get('Glider'));	
     	  currentRow.find('#pilot1').text(formData.get('Pilot1'));	
     	  currentRow.find('#pilot2').text(formData.get('Pilot2'));	
     	  currentRow.find('#takeoff').text(formData.get('Takeoff'));
     	  currentRow.find('#landing').text(formData.get('Landing'));		
     	  currentRow.find('#towaltitude').text(formData.get('Tow_Altitude'));	
     	  currentRow.find('#towplane').text(formData.get('Tow_Plane'));	
     	  currentRow.find('#towpilot').text(formData.get('Tow_Pilot'));		
     	  currentRow.find('#notes').text(formData.get('Notes'));	
//  		  currentRow.find('#flighttime').text(obj["Time"]);		 			
	};
// Backbone upgrades start here. 
	var AddForm = Backbone.View.extend({
	    // add configuration, methods and behavior here
	    render: function(){
   		     var rawTemplate = $("#add-form-template").html();
   		     var compiledTemplate = _.template(rawTemplate);
   		     var renderedTemplate = compiledTemplate();
   		     this.$el.html(renderedTemplate);
   		     return this;
   		 }
	    
	});
	
	// this is working,but will need to modify the "view Only" mode for that to work. 
// 	 	 $(document).ready(function(){
// 	 	 
// 	 	     	let startDate = new Date().toISOString().substring(0,10);	 		
// 				$.ajax({
// 					type: "GET",
// 					url: passed_vars.root + 'cloud_base/v1/pdp_flightlog',
// 					async: true,
// 				   cache: false,
// 				   timeout: 30000,
// 					beforeSend: function (xhr){
// 						xhr.setRequestHeader('X-WP-NONCE',  passed_vars.nonce );
// 					},
// 					data:{
// 						start: startDate,
// 					},
// 					success : function (response){
//  					  console.log(response);	
// 					  response.forEach(pdpFlightString);
// 					},
// 					error: function(XMLHttpRequest, textStatus, errorThrown) { 
//         					alert("Status: " + textStatus); 
//         					alert("Error: " + errorThrown); 
//    					} 
// 				});	 		  	 	 
// 	 	 });	

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
function pdpFlightString(result){  // build the new row to be inserted into the flight table. 
	var row_string = `<tr class="flightrow"><td class="hidden"  id="flight_id"><div align="center">'` + result.id + `'</td>
        <td bgcolor="#999999" class="fl_style25 flightdata"  > 
        <div align="center" class"flightdata">` + result.yearkey + `</div>`;
        row_string +=`<td  class="fl_flight_row" align="center" id="glider">` + (result.Glider != null ? result.Glider : "" )+ `</td>
             <td  class="fl_flight_row" id="flight_type"><div align="center">`;
        row_string += result.Flight_Type;          	    	        	    	
        row_string += `</div></td>
             <td  class="fl_flight_row" id="pilot1">` +(result.Pilot1 != null ? result.Pilot1 : "" )+ `</td>
             <td  class="fl_flight_row" id="pilot2">` +(result.Pilot2 != null ? result.Pilot2 : "" )+ `</td>

             <td bgcolor="#FFFFFF"><button type="button" align="center" class="pdp_update_time button-flightlog button-start" data-start="1"></button></td> 
             <td class="fl_flight_row"><div align="center">` + (result.Takeoff != null ? result.Takeoff : "00:00:00" )+ `</div></td>

             <td bgcolor="#FFFFFF"><button type="button" align="center" class="pdp_update_time button-flightlog button-stop" data-start="0"></button></td>              
             <td class="fl_flight_row"><div align="center">` + (result.Landing != null ? result.Landing : "00:00:00" )+ `</div></td>

             <td  class="fl_flight_row" id="flighttime"><div align="center">` + (result.Hours != null ? result.Hours : "0.0" )+ `</div></td>
             <td  class="fl_flight_row" id="towaltitude"><div align="center">` +(result.Tow_Altitude != null ? result.Tow_Altitude : "" )+ `</div></td>
             <td  class="fl_flight_row" id="towplane"><div align="center">` +(result.Tow_Plane != null ? result.Tow_Plane : "" )+ `</div></td>
             <td  class="fl_flight_row" id="towpilot"><div align="center">` +(result.Tow_Pilot != null ? result.Tow_Pilot : "" )+ `</div></td>`;
         	    	        	    	
        row_string += `</td> <td class="fl_flight_row">999.00</td></tr>`;        
        jQuery('#flightTable').prepend(row_string);
                        	    	        	    	
//         row_string += `</td> <td class="fl_flight_row">999.00</td><td width="20" nowrap="nowrap" bgcolor="#FFFFFF" class="fl_style25"></td></tr>`;        
        return;
}	


