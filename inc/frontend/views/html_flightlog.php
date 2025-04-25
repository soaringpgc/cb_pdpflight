<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Cloud_Base - 
 * @subpackage Flight Log
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<?php     
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$strict_no_fly = get_option("glider_club_strict_no_fly") === 'y' ? true : false ;

$active_date = date("Y-m-d");

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

$ch_1 = curl_init('https://pgctest.local:8890/wp-json/cloud_base/v1/flight_types');
curl_setopt($ch_1, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_1, CURLOPT_SSL_VERIFYPEER, false);

$ch_2 = curl_init('https://pgctest.local:8890/wp-json/cloud_base/v1/aircraft?aircraft_type=1');
curl_setopt($ch_2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_2, CURLOPT_SSL_VERIFYPEER, false);

// $ch_3 = curl_init('https://pgctest.local:8890/wp-json/cloud_base/v1/pilots');
// curl_setopt($ch_3, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($ch_3, CURLOPT_SSL_VERIFYPEER, false);

$ch_4 = curl_init('https://pgctest.local:8890/wp-json/cloud_base/v1/pilots?role=cfi_g');
curl_setopt($ch_4, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_4, CURLOPT_SSL_VERIFYPEER, false);

$ch_5 = curl_init('https://pgctest.local:8890/wp-json/cloud_base/v1/pilots?role=tow_pilot');
curl_setopt($ch_5, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_5, CURLOPT_SSL_VERIFYPEER, false);

$ch_6 = curl_init('https://pgctest.local:8890/wp-json/cloud_base/v1/fees');
curl_setopt($ch_6, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_6, CURLOPT_SSL_VERIFYPEER, false);


$mh = curl_multi_init();
curl_multi_add_handle($mh, $ch_1);
curl_multi_add_handle($mh, $ch_2);
// curl_multi_add_handle($mh, $ch_3);
curl_multi_add_handle($mh, $ch_4);
curl_multi_add_handle($mh, $ch_5);
curl_multi_add_handle($mh, $ch_6);

  $running = null;
  do {
    curl_multi_exec($mh, $running);
  } while ($running);

//close the handles
curl_multi_remove_handle($mh, $ch_1);
curl_multi_remove_handle($mh, $ch_2);
// curl_multi_remove_handle($mh, $ch_3);
curl_multi_remove_handle($mh, $ch_4);
curl_multi_remove_handle($mh, $ch_5);
curl_multi_remove_handle($mh, $ch_6);

curl_multi_close($mh);
  
// all of our requests are done, we can now access the results
$flight_types = json_decode(curl_multi_getcontent($ch_1));
$aircraft = json_decode(curl_multi_getcontent($ch_2));
// $members = json_decode(curl_multi_getcontent($ch_3));
$instructors = json_decode(curl_multi_getcontent($ch_4));
$towpiltos = json_decode(curl_multi_getcontent($ch_5));
$fees = json_decode(curl_multi_getcontent($ch_6));

if( current_user_can( 'read' ) ) {	
// echo ('    <div id="content" class="outercontainer">');
// echo('<div class="container">'); //id="addFlight"  name="addFlight"
// build the hidden form. 
	echo ('<form id="addFlight" action="#" class="editForm"> 
	<div class="fl_container">
      		<div class="div-left" id="addorupdate"> 
       	 		<button  id="add"  class="view fl-colum-tp" style="background-color:red; color:black; font-size:22px">ADD</button>
       	 		<button  id="update"  class="edit fl-colum-tp" style="background-color:orange; color:black;  font-size:22px";">Update</button>
       	 		 <div class="fl-colum-ht"> </div>
       	 	</div>
	<div class="div-center">
  	    <input type = "hidden"
          id = "id"
          size = "8"
          name = "id"/>   
        <input type = "hidden"
          id = "yearkey"
          size = "4"
          name = "yearkey"/>   
        <div class="form-row-fl"> 
        <label for="Flight_Type">Type: </label>
        <select name="Flight_Type" id="Flight_Type" form="addFlight" >');
     	foreach($flight_types as $key){ 
     		if($key->title == 'REG'){  // a bit of a cludge for now. 
     			echo '<option value=' . $key->title . ' selected>'. $key->title . '</option>';  
     		} else {     		
     			echo '<option value=' . $key->title . '>'. $key->title . '</option>';       			
      		}     			
         };     
         echo ('</select>  </div> <div class="form-row-fl">   
        <label for="glider">Glider: </label>
        <select name="Glider" id="Glider" form="addFlight" >
        <option value=" " selected>Select Aircraft</option>');
     	foreach($aircraft as $key){ 	
     		if ($key->type == 'Glider'){
     			echo '<option value=' . $key->compitition_id  . '>'. $key->compitition_id . '</option>';
     		}
         };     
        echo ( '</select>  </div> <div class="form-row-fl">   
        <label for="pilot1">Pilot:  </label>
        <select name="Pilot1" id="Pilot1" form="addFlight">
        <option required value=" " selected>Select Member</option>');        
         foreach($members as $pilot ){
         	if($strict_no_fly ){
         		echo(' <option value="'.$pilot->name.'" '. ($pilot->nofly? 'disabled' : ' ') . ">".$pilot->name.'</option>');  
         	} else {
         		echo(' <option value="'.$pilot->name.'" >'.$pilot->name. ($pilot->nofly? ' --NF' : ' ') .'</option>');  
         	}                    
         }                               			
         echo ( '</select>  </div><div class="form-row-fl">    
        <label for="Pilot2">Instructor: </label>
        <select name="Pilot2" id="Pilot2" form="addFlight">
        <option value=" " selected>None</option>');       
     	    foreach($instructors as $key){ 	
     	    	echo '<option value="' . $key->name  . '" >'. $key->name . '</option>';
            };               
         echo ( '</select> </div> <div class="form-row-fl"> 
        <label for="altitude">Altitude: </label>
        <select name="Tow_Altitude" id="Tow_Altitude" form="addFlight">
        	<option value=" "  selected>$$$</option>');        
     		foreach($fees as $key){ 	
     			echo '<option value=' .  $key->altitude . '>'. $key->altitude . '</option>';
        	 };                  
        echo ('</select></div>
        <div class="form-row-fl">  
       	 	<label for="Takeoff">Launch:</label>
			<input type="time" id="Takeoff" name="Takeoff" class="timefield" form="addFlight">
			<button id="reset_time_launch" type="button" class="timefield">Reset</button>
		</div> 

		<div class="form-row-fl"> 
        	<label for="Landing">Landing:</label>
			<input type="time" id="Landing" name="Landing" class="timefield"  form="addFlight">
 			<button id="reset_time_landing" type="button" class="timefield">Reset</button>
		</div> 
		
        <div class="form-row-fl"> 
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
        <div class="form-row-fl">   
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
         <div class="form-row-fl"><label for="Notes">Notes: </label>
          <textarea  form="addFlight" id="Notes" name="Notes"  rows=3 cols=35">
         </textarea></div>
    </div>
		    <div class="div-right">
       	 		<button  id="cancel" class="fl-colum-tp" style="background-color:yellow; color:black;  font-size:22px">Cancel</button>
       	 	    <div class="fl-colum-ht"> </div>
       	 	</div>	
       	 			</div>  
			</div>       
		</form>
');
//         	<button style="background-color:yellow; color:black;  font-size:14px"><a href="#/cancel">rCancel</a></button> 		
 	
// end of hidden form  
//   <div class="Heading fhcontainer" ><div><button id="flight_button" class="view product"><a href="#/edit">ADD FLight</a></button></div>               
echo ( ' <div id="flight_log_table" class="flightLog">

			<div class="fl_Heading fhcontainer" >
				<div> <button  id="flight_button"  class="view button">ADD FLight</button></div> ');
			echo ( '<div id="editDate" class="view product"> Today\'s Flights ' . $active_date . '</div>');	

             if( current_user_can( 'cb_edit_flight' ) ) {       
             		echo ( 'Select:<input type="text" id="datepicker" class="fl_Cell0" > ');            
             }      

				

//              if( current_user_can( 'cb_edit_flight' ) ) {       
//              		echo ( 'Date to Edit:<input type="text" id="datepicker" > 
//                    <form class="hidden" id="changedate" action="#" ><button  id="select_date"  class="view">Select Date</button></form>');            
//              }      
			
				    
//            		echo ( '<div id="editDate" class="view product"> Today\'s Flights ' . $active_date . '</div>');
           		echo ( '<div id="activeCount" class="active"></div><div id="flightCount" class="active">Count</div>
           		<div id="connectstatus" class="online">OnLine</div>
           		<button  id="reload"  class="view button">Reload Page</button>
            </div>');
         	echo ( ' <table>

             <tr class="Row">
                 <th class="fl_Cell0"> Flight</th>
                 <th class="fl_Cell0a"> Type</th>
                 <th class="fl_Cell0"> Glider</th>
                 <th class="fl_Cell">Pilot</th>
                 <th class="fl_Cella">Instructor </th>
                 <th class="fl_Cell0a">Action </th>
                 <th class="fl_Cell0">Time  </th>
               	 <th class="fl_Cell0a">Altitude </th>
               	 <th class="fl_Cell0">Tow Pilot </th>  
                 <th class="fl_Cell0">Tug </th>  
                 <th class="fl_Cell0a">Charge</th>  
              </tr> 

			  <tbody  id="eflights"> 
			  </tbody>
			</table>  
		</div>
     ' );
} else {
	Echo 'Please log in. ';
}
?>
</body>
</html>