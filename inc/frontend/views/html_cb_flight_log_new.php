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
 
id="assign_trade_popup"  class="popup-content"
 */
 
?>

 <div style="text-align: center; "  > 
<?php

function instruction_Request_submit(){
		$requestType = $_SERVER['REQUEST_METHOD'];
		if($requestType == 'GET'){
			return;
		} 			
 		global $wpdb; 
// 		$table_instruction =  $wpdb->prefix . 'cloud_base_instruction';		
 		$table_type =  $wpdb->prefix . 'cloud_base_instruction_type';		
		
  		$user = get_user_by('ID', $_POST['member_id'] );
 		$user_meta = get_userdata($_POST['member_id']  );
 		$request_date = $_POST['request_date'] ;	
 		isset($request['inst_type']) ? $inst_type = $request['inst_type'] : $inst_type = 1 ;
 		$inst_type = $_POST['inst_type'] ;	
		$query_params = array( 'member_id'=> $user->ID, 'enter_date'=> date('Y-m-d'),
			'request_date'=> $request_date, 'inst_type'=> $inst_type);
		$display_name = $user->first_name .' '.  $user->last_name;
		if(isset($_POST['cfig1']) && (trim($_POST['cfig1'])!="") && ($_POST['cfig1'] > 0 )){
			   $cfig1 = get_user_by('ID', $_POST['cfig1'] );
			   $query_params = array_merge($query_params, array('cfig1'=>$cfig1->ID));	 
		} else {
			 $cfig1 = null;
		}
		if(isset($_POST['cfig2']) && (trim($_POST['cfig2'])!="") && ($_POST['cfig2'] > 0 )){		 
			 $cfig2 = get_user_by('ID', $_POST['cfig2'] );
			 $query_params = array_merge($query_params, array('cfig2'=> $cfig2->ID));			 
		} else {
			 $cfig2 = null;
		}
		if(isset($_POST['member_weight'])) {
			 $member_weight = $_POST['member_weight'] ;
			 add_user_meta($user->ID, 'weight',   $_POST['member_weight'], true);
		}		
		isset($_POST['confirmed']) ? $confirmed = 1 : $confirmed = 0 ;		
		isset($_POST['scheduling_assistance']) ? $scheduling_assistance = 1 : $scheduling_assistance = 0 ;
		$query_params = array_merge($query_params, array('confirmed'=> $confirmed));	
		$query_params = array_merge($query_params, array('scheduling_assistance'=> $scheduling_assistance));
		
		isset($_POST['comment']) ? $comment = $_POST['comment'] : $comment = "" ;
		$query_params = array_merge($query_params, array('comment'=> $comment));
 		$rest_request = new \WP_REST_REQUEST( 'POST', '/cloud_base/v1/instruction' ) ;  
		$rest_request->set_query_params($query_params );		   		  		
    	$rest_response = rest_do_request( $rest_request);     
   		
   		$sql = 'SELECT request_type FROM '. $table_type .' WHERE id=' . $inst_type ;
   		$inst_text = $wpdb->get_var($sql);
   		   		 		
   		$msg = 'Member: ' . $display_name . ', is requesting instruction on ' . substr($request_date, 0,10) . "<br>\n";  
   		$msg .=  'In the area of: ' . $inst_text  ."<br>\n";
   		$msg .=  'Student Weight is: ' . $member_weight ."<br>\n";
   		if( $cfig1 != null ){
   			   		$msg .=  'Request Instructor is: ' .  $cfig1->first_name .' '. $cfig1->last_name ."<br>\n";
   		}
   		if( $cfig2 != null ){
   			$msg .=  'Alternate Instructor is: ' . $cfig2->first_name .' '. $cfig2->last_name  ."<br>\n";	
   		}   		  		
   		$msg .=  'Preconfirmed with Insturctor: ' ;
   		if ($confirmed ){
   			$msg .= 'true';
   		} else {
   			$msg .= 'false';
   		}
   		$msg .= "<br>\n";
   		$msg .=  'Scheduling Assistance Requested: ' ; 
   		if ($scheduling_assistance ){
   			$msg .= 'true';
   		} else {
   			$msg .= 'false';
   		}
   		$msg .= "<br>\n";
   				 		
		$subject = "Instruction requested for: " . $display_name  ;
		$to = ""; 
		if($scheduling_assistance ){
			$sql = "SELECT wp_users.user_email FROM wp_users INNER JOIN wp_usermeta ON wp_users.ID = wp_usermeta.user_id WHERE wp_usermeta.meta_value like '%schedule_assist%' "; 
			$ops_emails = $wpdb->get_results($sql);			
			foreach ( $ops_emails as $m ){
				$to .= $m->user_email .', ';
			};
		}
		$to .= $user_meta->user_email.', '; 
		$to .= $cfig1 != null ? $cfig1->user_email : null ; 
		$to .= ', ' ;
		$to .= $cfig2 != null ? $cfig2->user_email : null ; 
		$headers = "MIME-Version: 1.0" . "\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\n";
		$headers .= 'From: <webmaster@pgcsoaring.com>' . "\n";
// var_dump($subject, $msg, $to )	;
// die();	
		
//   		mail($to,$subject,$msg,$headers);
		echo('<p> Your Instruction Request has been entered.</p> ');


}
function display_flight_log_new(){
	if(!is_user_logged_in()){
 		return;
	}
	global $wpdb; 
	$flight_table =  $wpdb->prefix . 'cloud_base_pdp_flight_sheet';		
 		 	
	$sql =  $wpdb->prepare( "SELECT * FROM {$flight_table} WHERE date = %s", date('Y-m-d') );				
	$todays_flights = $wpdb->get_results($sql);	

 	echo('<div class="table-container popup-content" id="edit_flight"> ');
//  	echo ('<form id="flight_update" action="#" method="POST" ><div >');
 	
 		
 	echo(' <table width="800" border="1" align="center" cellpadding="2" cellspacing="2" bordercolor="#000033" bgcolor="#666666">
  				<tr><td><div align="center"><span class="style1">PGC DATA PORTAL </span></div></td></tr>
  				<tr> <td height="481"><table width="92%" height="447" border="1" align="center" cellpadding="2" cellspacing="2" bordercolor="#005B5B" bgcolor="#4F5359">
  				<tr><td height="373"><div align="center" class="style26">PGC FLIGHT SHEET DETAIL SCREEN </div>');
 	echo('<form id="flight_update" action="#" method="POST" > '); 	
 	echo('<table align="center" cellpadding="3" cellspacing="3" bgcolor="#000066" class="style25">');
 	echo('<tr ><td ><input name="recordID" type="hidden" value=""> </div></td></tr>');
    echo('<tr ><td ><div align="left">Date:</div></td><td width="329" bgcolor="#CCCCCC"><input name="Date" type="text" class="style25" value="" size="11" readonly/></td></tr>');        
    echo('<tr> <td align="right" valign="middle" nowrap bgcolor="#CCCCCC" class="style25"><div align="left">Glider:</div></td>
        	<td bgcolor="#CCCCCC"><select name="Glider" class="style25" id="Glider">');
					$request = new WP_REST_Request('GET', '/cloud_base/v1/aircraft');
					$request->set_param('type', 'glider');
					$response = rest_do_request($request);
					$server = rest_get_server();
					$gliders = $server->response_to_data( $response, false );   
                    foreach($gliders as $glider ){
                  		echo(' <option value="'.$glider->compitition_id.'" class="nofly">'.$glider->compitition_id.'</option>');                     
                  	}        
      echo(' </select></td></tr>');  
      echo('<tr><td align="right" valign="middle" nowrap bgcolor="#CCCCCC" class="style25"><div align="left">Flight Type:</div></td>');   
      echo('<td bgcolor="#CCCCCC"><select name="Flight_Type" class="style25" select id="Flight_Type">');    
      		$table_type = $wpdb->prefix . "cloud_base_flight_type";	      
     		$sql = "SELECT * FROM ". $table_type . " WHERE `active` = true ORDER BY `title` ASC ";	
			$types = $wpdb->get_results( $sql, OBJECT);
                    foreach($types as $type ){
                  		echo(' <option value="'.$type->title.'" class="nofly">'.$type->title.'</option>');                     
                  	}        
	  echo(' </select></td></tr>');

                 
              
              <tr valign="baseline">
                <td align="right" valign="middle" nowrap bgcolor="#CCCCCC" class="style25"><div align="left">Member:</div></td>
                <td bgcolor="#CCCCCC"><span class="style17">
                  <select name="Pilot1" class="style25" id="Pilot1" >
                 
                  		<option value="" >  </option>
                        <option value="** New Member **" <?php if (!(strcmp("** New Member **", $row_Flightlog['Pilot1']))) {echo "selected=\"selected\"";} ?>>** New Member **</option>
                        <option value="** Freedoms Wings **" <?php if (!(strcmp("** Freedoms Wings **", $row_Flightlog['Pilot1']))) {echo "selected=\"selected\"";} ?>>** Freedoms Wings **</option>
  						<optgroup label="Members" class="nofly" >
                        <?php                        
                  			foreach($row_Memberpilots as $pilot ){
                  			    if ( $pilot == $row_Flightlog['Pilot1'] ) { 
                  			    	echo(' <option value="'.$pilot.'" selected>'.$pilot.'</option>');  
                  			    } else {
                  					echo(' <option value="'.$pilot.'">'.$pilot.'</option>');    
                  				}                   
                  			}                         
						?>
					</optgroup>
					  <optgroup label="Possible No Fly" class="nofly" >
                        <?php                        
                  			foreach($row_no_fly as $pilot ){
                  			    if ( $pilot == $row_Flightlog['Pilot1'] ) { 
                  			    	echo(' <option value="'.$pilot.'" selected>'.$pilot.'</option>');  
                  			    } else {
                  					echo(' <option value="'.$pilot.'" >'.$pilot.'</option>');    
                  				}                   
                  			}                         
						?>					  					  
					  </optgroup?
                  </select>
                </span></td>
              </tr>
              <tr valign="baseline">
                <td align="right" valign="middle" nowrap bgcolor="#CCCCCC" class="style25"><div align="left">Instructor:</div></td>
                <td bgcolor="#CCCCCC"><span class="style17">
                    <select name="Pilot2" class="style25" id="Pilot2">
                     <option value="" >Select </option>
                        <?php                        
                  			foreach($row_Cfigpilots as $pilot ){
                  			    if ( $pilot == $row_Flightlog['Pilot2'] ) { 
                  			    	echo(' <option value="'.$pilot.'" selected>'.$pilot.'</option>');  
                  			    } else {
                  					echo(' <option value="'.$pilot.'" >'.$pilot.'</option>');    
                  				}                   
                  			}                         
						?>
                    </select>
                </span></td>
              </tr>
              <tr valign="baseline">
                <td align="right" valign="middle" nowrap bgcolor="#CCCCCC" class="style25"><div align="left">Takeoff:</div></td>
                <td bgcolor="#CCCCCC"><input name="Takeoff" type="text" class="style25" value="<?php echo $row_Flightlog['Takeoff']; ?>" size="8" maxlength="8"></td>
              </tr>
              <tr valign="baseline">
                <td align="right" valign="middle" nowrap bgcolor="#CCCCCC" class="style25"><div align="left">Landing:</div></td>
                <td bgcolor="#CCCCCC"><input name="Landing" type="text" class="style25" value="<?php echo $row_Flightlog['Landing']; ?>" size="8" maxlength="8"></td>
              </tr>
              <tr valign="baseline">
                  <td align="right" valign="middle" nowrap bgcolor="#CCCCCC" class="style25"><div align="left">Hours:  </div></td>
                  <td bgcolor="#CCCCCC"><input name="Landing2" type="text" class="style25" value="<?php echo $row_Flightlog['Time']; ?>" size="6" maxlength="6" /></td>
              </tr>
              <tr valign="baseline">
                <td align="right" valign="middle" nowrap bgcolor="#CCCCCC" class="style25"><div align="left">Tow Altitude:</div></td>
                <td bgcolor="#CCCCCC"><select name="Tow_Altitude" class="style25">
                    <?php
                    foreach($fee_table as $key=>$value  ){
                    	if ( $key == $row_Flightlog['Tow Altitude']){
                    		echo(' <option value="'.$key.'" selected>'.$key.'</option>');                  	
                    	} else {
                    		echo(' <option value="'.$key.'" >'.$key.'</option>');    
                    	}
                    } 
					?>
                </select></td>
              </tr>
              <tr valign="baseline">
                <td align="right" valign="middle" nowrap bgcolor="#CCCCCC" class="style25"><div align="left">Tow Plane:</div></td>
                <td bgcolor="#CCCCCC"><select name="Tow_Plane" class="style25">
                   <?php
                       foreach($row_TowPlane as $tplane ){
                  		    if ( $tplane == $row_Flightlog['Tow Plane'] ) { 
                  		    	echo(' <option value="'.$tplane.'" selected>'.$tplane.'</option>');  
                  		    } else {
                  				echo(' <option value="'.$tplane.'" class="nofly">'.$tplane.'</option>');    
                  			}                   
                  		}        
                 ?>
                </select></td>
              </tr>
              <tr valign="baseline">
                <td align="right" valign="middle" nowrap bgcolor="#CCCCCC" class="style25"><div align="left">Tow Pilot:</div></td>
                <td bgcolor="#CCCCCC"><select name="Tow_Pilot" class="style25">
                    <?php
                    foreach($row_Towpilots as $pilot ){
                    	if ( $pilot == $row_Flightlog['Tow Pilot']){
                    		echo(' <option value="'.$pilot.'" selected>'.$pilot.'</option>');                  	
                    	} else {
                    		echo(' <option value="'.$pilot.'" >'.$pilot.'</option>');    
                    	}
                    } 
					?>
                </select></td>
              </tr>
              <tr valign="baseline">
                <td align="right" valign="middle" nowrap bgcolor="#CCCCCC" class="style25"><div align="left">Tow Charge:</div></td>                
                   <td bgcolor="#CCCCCC"><input name="Tow_Charge" type="text" class="style25" value="<?php echo $row_Flightlog['Tow Charge']; ?>" size="6" maxlength="6" readonly></td>             
              </tr>
              <tr valign="baseline">
                <td height="47" align="right" valign="middle" nowrap bgcolor="#CCCCCC" class="style25"><div align="left">Notes:</div></td>
                <td bgcolor="#CCCCCC"><textarea name="Notes" cols="50" rows="5" class="style25"><?php echo $row_Flightlog['Notes']; ?></textarea></td>
              </tr>
              <tr valign="baseline">
                <td colspan="2" align="right" nowrap bgcolor="#CCCCCC" class="style25"><div align="center">
                        <input name="submit" type="submit" class="style25" value="Update record" />
                </div></td>
                </tr>
            </table>
<!-- 
            <input type="hidden" name="LOG_PAGE" value=<?php echo  $_SERVER['HTTP_REFERER']  ?>>
 -->
            <input type="hidden" name="MM_update" value="form2">
            <input type="hidden" name="Key" value="<?php echo $row_Flightlog['Key']; ?>">
            <input type="hidden" name="ParentPage" value="<?php echo  $_SERVER['HTTP_REFERER']  ?>">
          </form>
          <p>
              <label></label>
              <label></label>
          </p></td>
      </tr>
      <tr>
        <td height="28"><div align="center"><strong class="style3"><a href=<?php echo  $_SERVER['HTTP_REFERER']  ?> class="style16">BACK TO FLIGHT SHEET</a></strong></div></td>
      </tr>
    </table></td>
  </tr>
</table>	
 	
 	
 	
// 	echo(' <p id="display_date" >date</p>');
// 
// 	$students = get_users(['role__in' => 'subscriber' ] );
// 	$instructors = get_users(['role__in' => 'cfi_g' ] );
// 	
// 	$roles = ( array ) $user->roles;
// 	
// // Normally drop down will auto select logged in user and be hidden/unchanagable.
// // however if user is CFIG allow to select any member to put on schedule. 	
// 
//  	if(current_user_can('cb_edit_flight')){ 
// //	if(in_array('cfi_g', $roles)){ 
// //   		echo ('<div >');
// 		echo ('<div id="member_id" class="table-row" > <label for="member_id" style=color:black class="table-col">Student: </label>
//     		<div class="table-col" > <select class="event_cal_form" name="member_id" id="member_id" form="instruction_request">
//     		<option value=NULL>Student</option>');       
//     	foreach($students as $key){ 	
//     	  if( $key->ID == $user->ID ){
//     	  		echo '<option selected value=' . $key->ID . '>'. $key->first_name . ' '. $key->last_name . '</option>';
//     	  } else {
//     	  		echo '<option value=' . $key->ID . '>'. $key->first_name . ' '. $key->last_name . '</option>';
//     	  }     			  	
//     	}; 
// 	 echo ( '</select></div></div>');
// 	 	} else {
// 	 	 echo ('<div><input type="hidden" id="member_id" name="member_id" value='. $user->ID.' >');	 
//      	 echo(' </div> ');			
// 	 	}
// 
//      echo ('<div id="prim_instructor" class="table-row"  > <label for="cfig1" style=color:black class="table-col">Instructor: </label>
//     		<div class="table-col" > <select class="event_cal_form" name="cfig1" id="cfig1" form="instruction_request">
//     		<option value=NULL>Instructor</option>');       
//     	foreach($instructors as $key){ 	
//  		  if( $key->ID == $user->ID ){
//     	   		echo '<option selected value=' . $key->ID . '>'. $key->first_name . ' '. $key->last_name . '</option>';
//     	  } else {
//     	  		echo '<option value=' . $key->ID . '>'. $key->first_name . ' '. $key->last_name . '</option>';
//     	  }  
//      	 };             
//      echo ( '</select></div></div> ');
//      echo ('<div id="cfig2ructor" class="table-row" > <label for="cfig2" style=color:black class="table-col">Alt Inst: </label>
//      	 		<div class="table-col" ><select class="event_cal_form" name="cfig2" id="cfig2" form="instruction_request">
//      	 		<option value=NULL>Alt Instructor</option>');       
//      			  foreach($instructors as $key){ 	
//      			  	echo '<option value=' . $key->ID . '>'. $key->first_name . ' '. $key->last_name . '</option>';
//      	 		 };             
//      	 		echo ( '</select></div></div> ');
//  			echo('<div class="table-row"><label for="comment" class="table-col">Comment</label>				
//  			<div class="table-col" ><textarea id="comment" name="comment" rows="2", cols="55"></textarea></div></div>');
// 
//       		echo ('<div id="inst_type"  class="table-row"> <label for="inst_type" style=color:black class="table-col">Instruction Type: </label>
//      	 		<div class="table-col" ><select class="event_cal_form" name="inst_type" id="inst_type" form="instruction_request">
//      	 		<option value=NULL>Select</option>');       
//      			  foreach($instruction_types as $key){ 	
//      			  	echo '<option value=' . $key->id . '>'. $key->request_type . '</option>';
//      	 		 };             
//      	 		echo ( '</select></div> </div>');
//      	 		echo('<div class="table-row"><label for="member_weight" style=color:black class="table-col">Member Weight: </label>
//      	 		 <div class="table-col" > <input type="number" id="member_weight" name="member_weight" value='.$user_weight .' ></input></div></div>');
//  
//      	 		echo('<div class="table-row"><label for="array_mergeirmed" style=color:black class="table-col">Confirmed with CFIG?: </label>
//      	 		 <div class="table-col" > <input type="checkbox" id="confirmed" name="confirmed" ></input></div></div>');
// 
//      	 		echo('<div class="table-row"><label for="scheduling_assistance" style=color:black class="table-col">Scheduling Assistance Requested? </label>
//      	 		 <div class="table-col" > <input type="checkbox" id="scheduling_assistance" name="scheduling_assistance" ></input></div></div>');
// 	echo(' <input type="hidden" id="request_date" name="request_date" ></input>');
// 	echo('<div><input type="submit" value="Submit" >'); //
// 	echo('<input type="button" value="Cancel"  onclick="hideinstructionrequest()" >'); //
// 	echo('</div></form> </div></div>');
// 	echo('<div id="cfig_accept" title="Instructor Acceptance" class="instructor_hidden">Click Accept to accept Instruction Request.</div>');
// 
// 	if(current_user_can('schedule_assist')){ 
// // 		echo('<div id=editdate>  </div>');
// 		echo ('<div id="assigned_instructor" >');	 // 
// 		echo ('<form id="schedule_assist" action="#" >');
// 	
// 		echo(' <label for="assigned_instructor" style=color:black class="table-col">Instructor: </label>
//     		 <select class="instructor_select" name="assigned_cfig" id="assigned_cfig" form="schedule_assist">
//     		<option value=NULL>Instructor:</option>');       
//     	foreach($instructors as $key){ 	
//     	  if( $key->ID == $user->ID ){
//     	  		echo '<option selected value=' . $key->ID . '>'. $key->first_name . ' '. $key->last_name . '</option>';
//     	  } else {
//     	  		echo '<option value=' . $key->ID . '>'. $key->first_name . ' '. $key->last_name . '</option>';
//     	  }     			  	
//     	}; 
// 	 	echo ( '</select>');	 	
// 	 	echo('<input type="button" value="Cancel"  onclick="hideassigninstuctor()" >'); // 		
 		echo('</form></div> ');
// 	 		 	
// 	}


// 	echo ('<div id="calendar" "></div>');
	
	echo ('<table width="100%" height="447" align="center" cellpadding="2" cellspacing="2" bordercolor="#005B5B" bgcolor="#4F5359">  
            <tr width="100%" > <td height="373" colspan="5" valign="top"> ');

	echo(' <table class="flight_table">');
    echo(' <tr class="flight_row"  >');    
    echo(' <td class="flight_cell1" ><div ">Flight</div></td>');    
    echo(' <td class="flight_cell1" ><div ">GLDR</div></td>');    
    echo(' <td class="flight_cell1" ><div ">Type</div></td>');    
    echo(' <td class="flight_cell1" ><div ">Member</div></td>');    
    echo(' <td class="flight_cell1" ><div ">Instructor</div></td>');    
    echo(' <td class="flight_cell1" ><div ">Takeoff</div></td>');    
    echo(' <td class="flight_cell1" ><div ">Landing</div></td>');    
    echo(' <td class="flight_cell1" ><div ">Tow</div></td>');    
    echo(' <td class="flight_cell1" ><div ">Tow Pilot</div></td>');    
    echo(' <td class="flight_cell1" ><div ">Charge</div></td>');    
    echo(' <td class="flight_cell1" ><div ">Notes</div></td>');    
 	echo('</tr>');   
//   	echo('<td class="flight_cell2"> ');  

	foreach( $todays_flights as $flight ){
		echo('<tr class="data_row">');
		echo('<td bgcolor="#999999" align="center"><div align="center">  '  . $flight->yearkey .   ' </div></td>');
	    echo('<td bgcolor="#FFFFFF" class="fl_style25" align="center">' . $flight->Glider .'</td>') ;                  
	    echo('<td bgcolor="#FFFFFF" class="fl_style25" align="center">' . $flight->Flight_Type .'</td>');                   
	    echo('<td bgcolor="#FFFFFF" class="fl_style25" align="center">' . $flight->Pilot1 .'</td>')  ;                 
	    echo('<td bgcolor="#FFFFFF" class="fl_style25" align="center">' . $flight->Pilot2 .'</td>')  ;                 
	    echo('<td bgcolor="#FFFFFF" class="fl_style25" align="center">' . $flight->Takeoff .'</td>') ;                  
	    echo('<td bgcolor="#FFFFFF" class="fl_style25" align="center">' . $flight->Landing .'</td>') ;                  
 	    echo('<td bgcolor="#FFFFFF" class="fl_style25" align="center">' . $flight->Time .'</td>')     ;              
	    echo('<td bgcolor="#FFFFFF" class="fl_style25" align="center">' . $flight->Tow_Altitude .'</td>') ;                  
	    echo('<td bgcolor="#FFFFFF" class="fl_style25" align="center">' . $flight->Tow_Plane .'</td>') ;                  
	    echo('<td bgcolor="#FFFFFF" class="fl_style25" align="center">' . $flight->Tow_Pilot .'</td>')  ;                 
	    echo('<td bgcolor="#FFFFFF" class="fl_style25" align="center">' . $flight->Tow_Charge .'</td>')   ;                
	    echo('<td bgcolor="#FFFFFF" class="fl_style25" align="center">' . $flight->Notes .'</td></tr>')  ;                 

	}
    echo('</tr>');        
    echo('</table></td></tr></table>');
 }	
	
?> 

 


