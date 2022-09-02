<?php
// get gliders
$request = new WP_REST_Request('GET', '/cloud_base/v1/aircraft');
$request->set_param('type', 'glider');
$response = rest_do_request($request);
$server = rest_get_server();
$glider = $server->response_to_data( $response, false );

// get  members not on no fly list
$Memberpilots = array();
$request = new WP_REST_Request('GET', '/cloud_base/v1/pilots');
//$request->set_param('no_fly', 'false');
$response = rest_do_request($request);
$server = rest_get_server();
$member_pilots = $server->response_to_data( $response, false );

//get membets on no fly list
$request = new WP_REST_Request('GET', '/cloud_base/v1/pilots');
$request->set_param('no_fly', 'true');
$response = rest_do_request($request);
$server = rest_get_server();
$no_fly_pilots = $server->response_to_data( $response, false );

// get tow planes
$request = new WP_REST_Request('GET', '/cloud_base/v1/aircraft');
$request->set_param('type', 'tow');
$response = rest_do_request($request);
$server = rest_get_server();
$tow_plane = $server->response_to_data( $response, false );

// get tow pilots
$Towpilots = array();
$args = array('role'=> 'tow_pilot', 'role__not_in'=>'inactive', 'orderby'=>'user_nice_name', 'order'=> 'ASC');
$tow_pilots = get_users( $args );

// select instructors from Wordpress user database where role = 'cfi_g'
$Cfigpilots = array();
$args = array('role'=> 'cfi_g', 'role__not_in'=>'inactive', 'orderby'=>'user_nice_name', 'order'=> 'ASC');
$cfi_pilots = get_users($args );

$row_rsAltitudes = array();
$request = new WP_REST_Request('GET', '/cloud_base/v1/fees');
//$request->set_param('type', 'glider');
$response = rest_do_request($request);
$server = rest_get_server();
$fees_list = $server->response_to_data( $response, false );

$fee_table = array();

// foreach ($data as $key=>$value){
// 	$fee_table[$value->altitude]=$value->charge;	
// }

?> 

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8"/>
		<title>PGC Flight Log</title>
	</head>
	<body>
	<header id="header">
      <h3 class="pdp-center">Philadelphia Glider Council Flight Log</h3>
    </header>
    <div id="FlightLog" >
        <form id="flightedit" action="#">
        	<div  class="pdp-block" > Flight # </div>
        	<div  class="pdp-block" > Glider </div>
        	<div  class="pdp-block" > Pilot </div>
        	<div  class="pdp-block" > Instuctor </div>
        	<div  class="pdp-block" > Altitude </div>
        	<div  class="pdp-block pdp-wide" >  Notes </div>  	    
        	<div  class="pdp-block pdp-right" id="id"> </div>
            <div  class="pdp-block"> 
             	<select id="glider_id" >
             	<option  value=" ">Glider</option>
                <?php
            		foreach($glider as $item ){                 		    	
                		echo(' <option value="'.$item->id.'">'.$item->compitition_id.'</option>');    
                	}   
            	?> 	
           	</select>
	    	 </div>
	    	 <div  class="pdp-block"> 
             	<select id="pilot_id" >
             	<option  value=" ">Member</option>
             	<?php
             	foreach($member_pilots as $pilot ){
	
					echo(' <option value="'.$pilot->ID.'">'.$pilot->name .'</option>');   
					}             	
             	?>
            	</select>
	    	 </div>
  	    	 <div  class="pdp-block"> 
             	<select id="cfig_id" >
             	<option  value=" ">Instructor</option>
             	<?php
             	foreach($cfi_pilots as $pilot ){
					$pilot_user = get_userdata( $pilot->id );
					echo(' <option value="'.$pilot->id.'">'.$pilot_user->last_name .', '. $pilot_user->first_name .'</option>');   
					}             	
             	?>
            	</select>
	    	 </div>
             <div  class="pdp-block"> 
             	<select id="altitude_id" >
             	 <option  value=" ">Alitude</option>
            	<?php
             	foreach($fees_list as $item ){
					echo(' <option value="'.$item->id.'">'.$item->altitude .'</option>');  
// 					echo(' <option value="'.$item->id.'">'.$item->altitude .', '. $item->charge .'</option>');  

					}             	
             	?>  
            	</select>
	    	 </div>
        	 <div class="pdp-block pdp-tall" >
	    	 	<textarea rows="5" cols="22" wrap="off" name="notes"> </textarea>
        	 </div>	
        	 <div  class="pdp-block" > Start </div>
	    	 <div  class="pdp-block" > Stop </div>
        	 <div  class="pdp-block" > Tow Pilot </div>
        	 <div  class="pdp-block" > Tow Plane </div>
             <div  class="pdp-block">  Charge </div>
        	 <div  class="pdp-block" > <input type="time" id="start" /> </input> </div>
        	 <div  class="pdp-block" > <input type="time" id="stop" />  </input>  </div>
    
	    	 <div  class="pdp-block"> 
             	<select id="towpilot_id" >
             	<option  value=" ">Tow Pilot</option>
            	<?php
             	foreach($tow_pilots as $pilot ){
					$pilot_user = get_userdata( $pilot->id );
					echo(' <option value="'.$pilot->id.'">'.$pilot_user->last_name .', '. $pilot_user->first_name .'</option>');   
					}             	
             	?>             	
            	</select>
	    	 </div>
             <div  class="pdp-block"> 
              	<select id="towplane_id" >
             	<option  value=" ">Tug</option>
                <?php
            		foreach($tow_plane as $item ){                 		    	
                		echo(' <option value="'.$item->id.'">'.$item->compitition_id.'</option>');    
                	}   
            	?> 	
            	</select>
	    	 </div>
	    	 <div class="pdp-block">      </div>
            <div>
               <input type="button" id="add" value="add" class="pdp-tableCell "/>
            </div>
            <div>
               <input type="button" id="addNew" value="addNew" class="pdp-tableCell "/>
            </div>
 <!-- 
           <div>
               <button id="add" value="add" class="pdp-tableCell "/>Add</button>
            </div>
 -->
         </form>  
     </div>
         <div id="Flightlist" ></div>

		<!-- Templates -->   
		
					
		<script type="text/template" id="flightTemplate" >
		    <div id="key" class="pdp-tableCell"><%=Key%></div>
		    <div id="glider" class="pdp-tableCell "><%=Glider%></div> 
		    <div id="pilot" class="pdp-tableCell "><%=Pilot1%></div>
		    <div id="cfig" class="pdp-tableCell "><%=Pilot2%></div>
		    <div id="altitude" class="pdp-tableCell"><%=tow_alitude%></div> 
		    <div id="towpilot" class="pdp-tableCell "><%=tow_pilot%></div>		   
		    <div id="towplane" class="pdp-tableCell "><%=tow_plane%></div>
		    <div id="start" class="pdp-tableCell pdp-time"><%=start%></div>
		    <div id="stop" class="pdp-tableCell pdp-time"><%=stop%></div>

		    <div id="notes" class="pdp-note"><%=Notes%></div>    
		    <div id="glider_id" class="hidden"><%=glider_id%></div>
		    
		</script>   
 
		<script type="text/template" id="archiveTemplate" >
		    <div id="id" class="tableCell pdp-narrow"><%=id%></div>
		    <div id="glider_id" class="hidden"><%=glider_id%></div>
		    <div id="glider" class="pdp-tableCell pdp-narrow"><%=glider%></div>
		    <div id="pilot_id" class="hidden"><%=pilot_id%></div>
		    <div id="pilot" class="pdp-tableCell pdp-name"><%=pilot%></div>
		    <div id="cfig_id" class="hidden"><%=cfig_id%></div>
		    <div id="cfig" class="pdp-tableCell pdp-name"><%=cfig%></div>
		    <div id="altitude_id" class="hidden"><%=altitude_id%></div>
		    <div id="altitude" class="pdp-tableCell"><%=altitude%></div>
		    <div id="towpilot_id" class="hidden"><%=towpilot%></div>
		    <div id="towpilot" class="pdp-tableCell pdp-name"><%=towpilot%></div>
		    <div id="towplane_id" class="hidden"><%=towplane%></div>
		    <div id="towplane" class="pdp-tableCell pdp-narrow"><%=towplane%></div>
		    <div id="duration" class="pdp-tableCell pdp-time"><%=start%></div>
		    <div id="notes" class="hidden"><%=towplane%></div>    
		</script>     		
				
	</body>
</html>