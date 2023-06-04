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
<div >
   <div id="eflights" >
    <h3 class="datetime hidden"> Flight Editor</h3>
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
		 
		$request = new WP_REST_Request('GET', '/cloud_base/v1/pilots');
		$request->set_param('no_fly', 'true');
		$response = rest_do_request($request);
		$server = rest_get_server();
		$no_fly_pilots = $server->response_to_data( $response, false );
		// sort($no_fly_pilots); 

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
          size = "2"
          name = "id"/>   
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
        echo ( ' <option value="0" >PVT</option>
        </select>  </div> <div class="form-row">   
        <label for="pilots">Pilot: (Member to be charged) </label>
        <select name="Pilot1" id="Pilot1" form="addFlight">
        <option value=" " selected>Select Member</option>
  			<optgroup label="Members" class="nofly" >');               
         		foreach($member_pilots as $pilot ){
         			echo(' <option value="'. $pilot->name .'">'.$pilot->name.'</option>');                     
         		}                       			
			echo('	</optgroup>
				<optgroup label="Possible No Fly" class="nofly" >');                   
              		foreach($no_fly_pilots as $pilot ){
              			echo(' <option value="'. $pilot->name .'" >'. $pilot->name . '</option>');                       
              		}                         									  
		  echo('</optgroup> </select></div>');
          echo ( '<div class="form-row">    
        <label for="Pilot2">Instructor: </label>
        <select name="Pilot2" id="Pilot2" form="addFlight">
        <option value="" selected>None</option>');       
     	    foreach($instructors as $key){ 	
     	    	echo '<option value="' . $key->name  . '" >'. $key->name . '</option>';
            };               
         echo ( '</select> </div> <div class="form-row"> 
        <label for="altitude">Altitude: </label>
        <select name="Tow_Altitude" id="Tow_Altitude" form="addFlight">');
     		foreach($fees as $key){ 	
     			echo '<option value=' .  $key->altitude . '>'. $key->altitude . '</option>';
        	 };      
        	 echo '<option value= self_launch > Self Launch </option>';             
        echo ('</select></div>
        <div class="form-row">  
       	 	<label for="launch">Launch:</label>
			<input type="time" id="Takeoff" name="Takeoff" value=" " >
		</div> 

		<div class="form-row"> 
        	<label for="landing">Landing:</label>
			<input type="time" id="Landing" name="Landing">
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
         echo ( '</select></div>         
         <div class="form-row"><label for="Notes">Notes: </label>
          <textarea  form="addFlight" id="Notes" name="Notes"  rows=4 cols=35">
         </textarea></div></div>
        <div id="addorupdate">
        <button  id="add"  class="view">ADD</button>
        <button  id="update"  class="edit">Update</button>
        <button  id="cancel" >Cancel</button>
		</div>
	</form>
              <div class="Title">
                   <div class="Heading" id="addbutton"> <button  id="flight_button"  class="view">ADD FLight</button>
              <div id="editDate"> Today\'s Flights ' . $active_date . '</div></div>
             </div>
             <div class="Heading">
                 <div class="Cell0">
                     <p>Flight</p>
                 </div>
                <div class="Cell">
                     <p>Glider</p>
                 </div>
                 <div class="Cell2">
                     <p>Pilot</p>
                 </div>
                 <div class="Cell">
                     <p>Action</p>
                 </div>
                 <div class="Cell">
                     <p>Time</p>
                 </div>
                  <div class="Cell">
                     <p>Altitude</p>
                 </div>
                 <div class="Cell">
                     <p>Tug</p>
                 </div>  
              </div>      
          ' );

     } else {
     	Echo 'Please log in. ';
     }
?>
<!-- 
     </div>
         <div  class="TowFee Table aside" id="flights">
             <div class="Title ">
                 <p>Today's Flights <?php echo $active_date ?></p>
             </div>
             <div class="Heading">
                 <div class="Cell0">
                     <p>Flight</p>
                 </div>
                <div class="Cell">
                     <p>Glider</p>
                 </div>
                 <div class="Cell2">
                     <p>Pilot</p>
                 </div>
                 <div class="Cell">
                     <p>Action</p>
                 </div>
                 <div class="Cell">
                     <p>Time</p>
                 </div>
                  <div class="Cell">
                     <p>Altitude</p>
                 </div>
                 <div class="Cell">
                     <p>Tug</p>
                 </div>
             </div>
         </div>
   </div>
 -->
<!-- 
	<div class="footer">Today's Flights </div>
  </div>

</div>
 -->

