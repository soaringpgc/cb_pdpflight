<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Cloud_Base
 * @subpackage Cloud_Base/public/partials
 */
?>
<script language="JavaScript">
	var cb_admin_tab = "flights";
</script>


<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div>
   <div id="eflights"  >
    <?php 	
   		global $wpdb; 
		$flight_table =  $wpdb->prefix . 'cloud_base_pdp_flight_sheet';		
		$flight_type =  $wpdb->prefix . 'cloud_base_flight_type';
		$cfig_table =  $wpdb->prefix . 'cloud_base_cfig';		
		$tow_pilot_table =  $wpdb->prefix . 'cloud_base_towpilot';		
		$pilots_table =  $wpdb->prefix . 'cloud_base_pilots';		
		$aircraft_table =  $wpdb->prefix . 'cloud_base_aircraft';		
		$tow_planes_table =  $wpdb->prefix . 'cloud_base_towplane';	
		$fees_table =  $wpdb->prefix . 'cloud_base_relay_fees';	
		$type_table = $wpdb->prefix . "cloud_base_aircraft_type";		
		$active_date = date("Y-m-d");

		$flight_types = $wpdb->get_results('SELECT * FROM ' . $flight_type .' ORDER BY description ASC ');	
		$aircraft = $wpdb->get_results('SELECT *, t.title as type FROM ' . $aircraft_table .' s inner join '. $type_table  .' t on s.aircraft_type=t.id WHERE s.valid_until is NULL  ORDER BY s.aircraft_type DESC, s.registration ASC');		
		
		$request = new WP_REST_Request('GET', '/cloud_base/v1/pilots');
		$request->set_param('no_fly', 'false');
		$response = rest_do_request($request);
		$server = rest_get_server();
		$member_pilots = $server->response_to_data( $response, false );
		// sort($member_pilots ); 
		foreach($member_pilots as $pilot) {
			$pilot->nofly = false; 
		}
		 
		$request = new WP_REST_Request('GET', '/cloud_base/v1/pilots');
		$request->set_param('no_fly', 'true');
		$response = rest_do_request($request);
		$server = rest_get_server();
		$no_fly_pilots = $server->response_to_data( $response, false );
		// sort($no_fly_pilots);
		foreach($no_fly_pilots as $pilot) {
			$pilot->nofly = true; 
		}
		$members = array_merge($member_pilots, $no_fly_pilots );
		function sort_members($a, $b) { 
    		if($a->last_name == $b->last_name) {
        		return 0;
    		} 
    		return ($a->last_name < $b->last_name) ? -1 : 1;
		} 
		usort($members, 'sort_members'); 

		$request = new WP_REST_Request('GET', '/cloud_base/v1/pilots');
		$request->set_param( 'role', 'cfi_g' );
        $response = rest_do_request($request);
		$instructors = $response->get_data();
	
		$request = new WP_REST_Request('GET', '/cloud_base/v1/pilots');
		$request->set_param( 'role', 'tow_pilot' );
        $response = rest_do_request($request);
		$towpiltos = $response->get_data();
		
		$request = new WP_REST_Request('GET', '/cloud_base/v1/fees');
        $response = rest_do_request($request);
		$fees = $response->get_data();		

		$sql = $wpdb->prepare("SELECT Tow_Pilot, Tow_Plane FROM {$flight_table} WHERE `flightyear`=%s ORDER BY yearkey DESC LIMIT 1",  date("Y"));	
		$current_tow = $wpdb->get_results($sql);
        		
      if( current_user_can( 'read' ) ) {	
      
      echo ('<form id="addFlight" action="#" class="hidden">

      	<div class="container">
  	    <input type = "hidden"
          id = "id"
          size = "8"
          name = "id"/>   
        <input type = "hidden"
          id = "yearkey"
          size = "4"
          name = "yearkey"/>    
        <div class="form-row"> 
        <label for="Flight_Type">Type: </label>
        <select name="Flight_Type" id="Flight_Type" form="addFlight" >');
     	foreach($flight_types as $key){ 
     		if($key->title == 'REG'){  // a bit of a cludge for now. 
     			echo '<option value=' . $key->title . ' selected>'. $key->title . '</option>';  
     		} else {     		
     			echo '<option value=' . $key->title . '>'. $key->title . '</option>';       			
      		}     			
         };     
         echo ('</select>  </div> <div class="form-row">   
        <label for="glider">Glider: </label>
        <select name="Glider" id="Glider" form="addFlight" >
        <option value=" " selected>Select Aircraft</option>');
     	foreach($aircraft as $key){ 	
     		if ($key->type == 'Glider'){
     			echo '<option value=' . $key->compitition_id  . '>'. $key->compitition_id . '</option>';
     		}
         };     
        echo ( '</select>  </div> <div class="form-row">   
        <label for="pilot1">Pilot:  </label>
        <select name="Pilot1" id="Pilot1" form="addFlight">
        <option value=" " selected>Select Member</option>');
         foreach($members as $pilot ){
         	echo(' <option value="'.$pilot->name.'" >'.$pilot->name. ($pilot->nofly? ' --NF' : ' ') .'</option>');                      
         }                       			
         echo ( '</select>  </div><div class="form-row">    
        <label for="Pilot2">Instructor: </label>
        <select name="Pilot2" id="Pilot2" form="addFlight">
        <option value=" " selected>None</option>');       
     	    foreach($instructors as $key){ 	
     	    	echo '<option value="' . $key->name  . '" >'. $key->name . '</option>';
            };               
         echo ( '</select> </div> <div class="form-row"> 
        <label for="altitude">Altitude: </label>
        <select name="Tow_Altitude" id="Tow_Altitude" form="addFlight">
        	<option value=" "  selected>$$$</option>');        
     		foreach($fees as $key){ 	
     			echo '<option value=' .  $key->altitude . '>'. $key->altitude . '</option>';
        	 };                  
        echo ('</select></div>
        <div class="form-row">  
       	 	<label for="launch">Launch:</label>
			<input type="time" id="Takeoff" name="Takeoff" >
			<button id="reset_time_launch" type="button">Reset</button>
		</div> 

		<div class="form-row"> 
        	<label for="landing">Landing:</label>
			<input type="time" id="Landing" name="Landing">
 			<button id="reset_time_landing" type="button">Reset</button>
		</div> 
		
        <div class="form-row"> 
        <label for="Tow_Pilot">Tow Pilot: </label>
        <select name="Tow_Pilot" id="Tow_Pilot" form="addFlight">
        <option value="" selected>Tow Pilot</option>');

     	foreach($towpiltos as $key){ 	
     		if( $key->name == $current_tow[0]->Tow_Pilot){
     			echo '<option value="' .  $key->name . '" selected >'. $key->name . '</option>';
     		} else {    		
     			echo '<option value="' .  $key->name . '" >'. $key->name . '</option>';
     		}
         };     
        echo ( '</select>  </div> 
        <div class="form-row">   
        <label for="towplane">Tug: </label>
        <select name="Tow_Plane" id="Tow_Plane" form="addFlight">');
     	foreach($aircraft as $key){ 	
     		if ($key->type == 'Tow'){
     		
     			if($key->compitition_id == $current_tow[0]->Tow_Plane){
     			   echo '<option value=' . $key->compitition_id  . ' selected>'.$key->compitition_id  . '</option>';
     			} else {
     				echo '<option value=' . $key->compitition_id  . '>'.$key->compitition_id  . '</option>';
     			}
     		}
         };   
        echo '<option value="self">Self Launch</option>';        
         echo ( '</select></div>         
         <div class="form-row"><label for="Notes">Notes: </label>
          <textarea  form="addFlight" id="Notes" name="Notes"  rows=3 cols=35">
         </textarea></div>
      	<div id="addorupdate" >
      		<div class="div-left" > 
       	 		<button  id="add"  class="view" style="background-color:red; color:black; font-size:14px">ADD</button>
       	 		<button  id="update"  class="edit" style="background-color:orange; color:black;  font-size:14px";">Update</button>
       	 	</div>
       	 	<div class="div-right">
       	 		<button  id="cancel" style="background-color:yellow; color:black;  font-size:14px"" >Cancel</button>
       	 	</div>
		</div>         
        </div>
	</form>
            
    <div id="flight_table" class="Title ">
        <div class="Heading " > <button  id="flight_button"  class="view">ADD FLight</button><div id="flightCount">Count</div>');
// not quite ready for prime time.                    
             if( current_user_can( 'cb_edit_flight' ) ) {	        
             		echo ( 'Select Date to Edit:<input type="text" id="datepicker" > 
                   <form class="hidden" id="changedate" action="#" ><button  id="select_date"  class="view">Select Date</button></form>');            
             }                   
            echo ( '<div id="editDate"> Today\'s Flights ' . $active_date . '</div></div>
             </div>
             <div class="Heading">
                 <div class="fl_Cell0">
                     <p>Flight</p>
                 </div>
                 <div class="fl_Cell0a">
                     <p>Type</p>
                 </div>
                <div class="fl_Cell0">
                     <p>Glider</p>
                 </div>
                 <div class="fl_Cell">
                     <p>Pilot</p>
                 </div>
                 <div class="fl_Cella">
                     <p>Instructor</p>
                 </div>
                 <div class="fl_Cell0a">
                     <p>Action</p>
                 </div>
                 <div class="fl_Cell0">
                     <p>Time</p>
                 </div>
                  <div class="fl_Cell0a">
                     <p>Altitude</p>
                 </div>
                 <div class="fl_Cell0">
                     <p>Tug</p>
                 </div>  
                 <div class="fl_Cell0a">
                     <p>Charge</p>
                 </div>  
              </div> 
         </div>     
          ' );
     } else {
     	Echo 'Please log in. ';
     }
?>
<div class="modal"></div>
