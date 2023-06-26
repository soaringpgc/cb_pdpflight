<div>

<?php
// get gliders

global $wpdb; 
$flight_table =  $wpdb->prefix . 'cloud_base_pdp_flight_sheet';	
$user= wp_get_current_user();		
$display_name = $user->last_name .', '.  $user->first_name;

$sql = $wpdb->prepare("SELECT * FROM {$flight_table} WHERE (`Pilot1`=%s OR `Pilot2`=%s  OR `Tow_Pilot`=%s )AND `flightyear`=%d ORDER BY yearkey DESC",
		 $display_name,  $display_name, $display_name, date("Y") );				
$my_flights = $wpdb->get_results($sql); 
// get tow planes

echo('<div>
    <table class="logbook" >
     <tr class ="logbook"  span="13">Log Book for: ' . $display_name . '</tr>
    <tr class ="logbook" >
        <th  class="logbook"> Flight </th>
        <th  class="logbook"> Type </th>
        <th  class="logbook"> Date </th>
        <th  class="logbook" > Glider </th>
        <th   class="logbook"> Pilot </th>
        <th   class="logbook"> Instuctor </th>
        <th   class="logbook"> Altitude </th>
        <th   class="logbook"> Start </th>
	    <th  class="logbook"> Stop </th>
	    <th  class="logbook"> Time </th>
        <th   class="logbook"> Tow Pilot </th>
        <th   class="logbook"> Tow Plane </th>
        <th  class="logbook">  Charge </th>
        <th  class="logbook" >  Notes </th> </tr> 	    
     '); 
 
    foreach($my_flights as $item ){                 		    	
    	echo(' <tr  ><td class="logbook">'.  $item->yearkey     .'</td>');    
    	echo(' <td class="logbook">'.  $item->Flight_Type .'</td>');   
    	echo(' <td  class="logbook" >'.  $item->Date .'</td>');   
    	echo(' <td  class="logbook" >'.  $item->Glider .'</td>');   
    	echo(' <td  class="logbook" >'.  $item->Pilot1       .    '</td>');   
    	echo(' <td  class="logbook" >'.  $item->Pilot2       .'</td>');   
    	echo(' <td  class="logbook"class="logbook" >'.  $item->Tow_Altitude    .'</td>');   
    	echo(' <td  class="logbook" >'.  $item->Takeoff    .'</td>');   
    	echo(' <td  class="logbook">'.  $item->Landing    .'</td>');   
    	echo(' <td  class="logbook" >'.  $item->Time    .'</td>');   
    	echo(' <td  class="logbook" >'.  $item->Tow_Pilot    .'</td>');   
    	echo(' <td  class="logbook" >'.  $item->Tow_Plane    .'</td>');   
    	echo(' <td  class="logbook" >'.  $item->Tow_Charge    .'</td>');   
    	echo(' <td  class="logbook" >'.  $item->Notes    .'</td></tr>');   
    }   
 echo("</table></div>");
 
 ?> 
 </div>