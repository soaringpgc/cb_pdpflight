<?php
// global $PGCwp; // database handle for accessing wordpress db

if(!is_user_logged_in()){
	return;
}


global $wpdb;
$flight_table =  $wpdb->prefix . 'cloud_base_pdp_flight_sheet';		

$view_only = true; 
if (isset( $flight_atts['view_only'] )) {
	$view_only = $flight_atts['view_only']==='true' ? true : false ;
	if (!$view_only && !current_user_can('flight_edit')){
		wp_redirect( wp_login_url() );
	}
}

?>
<?php
error_reporting(E_ALL);
//ini_set('display_errors', 'On');

date_default_timezone_set('America/New_York');
$pgc_flight_date = date("Y-m-d");
// var_dump($pgc_flight_date );
// die();
/////================================================================================================

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

$row_rsGliders = array();
$request = new WP_REST_Request('GET', '/cloud_base/v1/aircraft');
$request->set_param('type', 'glider');
$response = rest_do_request($request);
$server = rest_get_server();
$aircraft = $server->response_to_data( $response, false );

foreach($aircraft as $glider ){
	array_push ($row_rsGliders, $glider->compitition_id );	
}
sort($row_rsGliders); 

$row_TowPlane = array();
$request = new WP_REST_Request('GET', '/cloud_base/v1/aircraft');
$request->set_param('type', 'tow');
$response = rest_do_request($request);
$server = rest_get_server();
$aircraft = $server->response_to_data( $response, false );

foreach($aircraft as $towplane ){
	array_push ($row_TowPlane, $towplane->compitition_id );	
}
 sort($row_TowPlane); 

// select tow pilots from Wordpress user database where role = 'tow_pilot'
$row_Towpilots = array();
$args = array('role'=> 'tow_pilot', 'role__not_in'=>'inactive', 'orderby'=>'user_nice_name', 'order'=> 'ASC');
$tow_pilots = get_users( $args );

foreach($tow_pilots as $pilot ){
	$pilot_user = get_userdata( $pilot->id );
	array_push ($row_Towpilots, ($pilot_user->last_name .', '. $pilot_user->first_name ));	
}
sort($row_Towpilots ); 

// select instructors from Wordpress user database where role = 'cfi_g'
$row_Cfigpilots = array();
$args = array('role'=> 'cfi_g', 'role__not_in'=>'inactive', 'orderby'=>'user_nice_name', 'order'=> 'ASC');
$cfi_pilots = get_users($args );

foreach($cfi_pilots as $pilot ){
	$pilot_user = get_userdata( $pilot->id );
	array_push ($row_Cfigpilots, ($pilot_user->last_name .', '. $pilot_user->first_name ));	
}
sort($row_Cfigpilots ); 

$row_rsAltitudes = array();
$request = new WP_REST_Request('GET', '/cloud_base/v1/fees');
//$request->set_param('type', 'glider');
$response = rest_do_request($request);
$server = rest_get_server();
$data = $server->response_to_data( $response, false );

$fee_table = array();

foreach ($data as $key=>$value){
	$fee_table[$value->altitude]=$value->charge;	
}

$row_rsAltitudes = array();
$request = new WP_REST_Request('GET', '/cloud_base/v1/flight_types');
//$request->set_param('type', 'glider');
$response = rest_do_request($request);
$server = rest_get_server();
$ftype = $server->response_to_data( $response, false );

// $flight_type_table = array();
// 
// foreach ($ftype as $type ){
// 	array_push ($flight_type_table, $type);	
// 	
// }


/////========================================

$maxRows_Flightlog = 100;
$pageNum_Flightlog = 0;
if (isset($_GET['pageNum_Flightlog'])) {
  $pageNum_Flightlog = $_GET['pageNum_Flightlog'];
}
$startRow_Flightlog = $pageNum_Flightlog * $maxRows_Flightlog;

// $query = $wpdb->prepare("SELECT * FROM {$flight_table} WHERE CONVERT_TZ(`Date`, 'UTC', 'US/Eastern' )= '%s' ORDER BY `yearkey` DESC  LIMIT %d, %d  ", $pgc_flight_date, $startRow_Flightlog, $maxRows_Flightlog);
// $query = $wpdb->prepare("SELECT * FROM {$flight_table} WHERE `Date` = CURRENT_DATE() ORDER BY `yearkey` DESC  LIMIT %d, %d  ", $startRow_Flightlog, $maxRows_Flightlog);

$query = $wpdb->prepare("SELECT * FROM {$flight_table} WHERE `Date` = '%s' ORDER BY `yearkey` DESC  LIMIT %d, %d  ", $pgc_flight_date, $startRow_Flightlog, $maxRows_Flightlog);

$flight_log =  $wpdb->get_results($query ); 
$todaycount= $wpdb->num_rows;

$todaycount = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM {$flight_table} WHERE `Date` = '%s'", $pgc_flight_date));


$totalPages_Flightlog = ceil($todaycount/$maxRows_Flightlog)-1;

$queryString_Flightlog = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {

    if (stristr($param, "pageNum_Flightlog") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_Flightlog = "&" . htmlentities(implode("&", $newParams));
  }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>PGC Data Portal - Flightlog</title>
<style>

</style>
</head>

<body id="cb_pdp_body">
                                 		<!--Creates the popup body-->
<div  class="popup-overlay"  id="helpPage">
<!--Creates the popup content-->
    <h4>PGC PDP Help<button class="close">Close</button>  </h4> 
 <div >
    <li>The<u>ADD Flight</u> button is hit on the main screen to create a new  blank row on the main flightlog screen.</li>
    <li>The new record is edited in the Detail Screen:  Glider, Member Charged and Instructor are added  to the record using the appropriate  dropdowns. The record is typically saved (updated) at this time.</li>
    <li >A PGC Flight Sheet record is considered 'official' by  the system when a Charged Member and/or Instructor is entered - and Takeoff and  Landing times are recorded.&nbsp; A record on  the main page that does not have a Member Charged or Instructor entry is not  considered a valid record for any billing or reporting process.</li>
 <ul >
     <li >If the PGC AOF Pilot is not a CFIG, put their name in the Member column. (Example - Bob Lacovara) </li>
     <li >If the PGC AOF Pilot is a CFIG, put their name in the Instructor Column - and put AOF in the member column. (Example - Jack Goritski)</li>
     <li >A note is not required as the type indicates it is an AOF. </li>
 </ul>
<li>Put blanks in the Member  Charged and Instructor fields to logically delete a record. </li>
     <li >
      A NEW MEMBER  - Some very new members may not be in the PDP. Select 'A New Member' as the member name. We will update to the proper member name later in the week.
   </li>
   <li >
       A DEMO FLIGHT - Use this as the member name for any other flight that should not be charged to a member. We will research and update later in the week. Enter a note with details. 
   </li>
   <li >
      Tow Pilot and Tow Plane can be added at any time  using the appropriate drop-downs. The last Tow Pilot and Plane selected are  also saved by the system and are used as the default for future entries.
   </li>
   <li >
       The <span class="style31">GREEN</span> Takeoff button on the main page is selected to auto-enter the takeoff time for  that flight as it occurs.
   </li>
   <li >
      The <span class="style32">RED</span> Landing button on the main page is selected to auto-enter the  landing time as it occurs. This will auto-generate a flight receipt  email to the member. 
   </li>
   <li >
       The  tow altitude is typically updated at some  later time when the pilot reports to the flight desk. (Although they may not - which is why the flight receipt  email is sent when the landing time is entered by the flight desk.)
   </li>
   <li ">
       T<em>ow altitude entry, and any other updates  made in the detail screen after the landing time is entered,  <span class="style36">will auto-generate</span> a follow up email of  flight details for the Member, Webmaster, and Treasurer.</em>
   </li>
  
</div>
</div>

<!-- ======================================================================================-->
<div  class="popup-overlay"  id="detailPage" width="100%">
<!--Creates the popup content-->
    <table width="100%" height="100%" border="1" align="center" cellpadding="2" cellspacing="2" bordercolor="#005B5B" bgcolor="#4F5359">     
      <tr>
        <td height="373"><p align="center" class="fl_style37" id="yearkey" > </p>
          <form method="post" name="flightForm">
            <input type="hidden" id='id' name='id' value="">    </input>      	
            <table align="center" cellpadding="3" cellspacing="3" bgcolor="#000066" class="style25">
              <tr valign="baseline">
                <td class="label"><label for="Glider" align="left">Glider:</label></td>
                <td class="detail"><select name="Glider"  id="Glider" class="style25" >
                  <?php
                       foreach($row_rsGliders as $glider ){
                  				echo(' <option value="'.$glider.'" class="nofly">'.$glider.'</option>');                       
                  		}        
                 ?>
                </select></td>
<!-- 
              </tr>
              <tr valign="baseline">
 -->
                  <td class="label"><label for="Flight_Type" align="left">Flight Type:</label></td>
                  <td class="detail">
				  <select id="Flight_Type" name="Flight_Type" class="style25" select >
				   <option value="REG"  selected>REG</option>
				<?php
                    foreach($ftype as $value  ){
                    		echo(' <option value="'.$value->title.'" >'.$value->title.'</option>');    
                    } 
					?>
                  </select></td>
 <!-- 
             </tr>
             <tr valign="baseline">
 
 -->               <td class="label"><label for="Tow_Altitude" align="left">Tow Altitude:</label></td>
                <td class="detail"><select id="Tow_Altitude" name="Tow_Altitude" class="style25">
                    <?php
                    foreach($fee_table as $key=>$value  ){
                    		echo(' <option value="'.$key.'" >'.$key.'</option>');    
                    } 
					?>
                </select>
                </td>
              </tr>              
              <tr valign="baseline">
                <td class="label" ><label for="Pilot1"  align="left">Member:</label></td>
                <td class="detail" colspan="2"><span >
                  <select id="Pilot1"  name="Pilot1" class="style25" >                 
                  		<option value="" >  </option>
                        <?php                        
                  			foreach($members as $pilot ){
//                   					echo(' <option value="'.$pilot->pilot_id.'" >'.$pilot->name. ($pilot->nofly? ' --NF' : ' ') .'</option>'); 
                  					echo(' <option value="'.$pilot->name.'" >'.$pilot->name.'</option>');                      
                     
                  			}                         
						?>					  					  
                  </select>
                </span></td>
<!-- 
              </tr>
              <tr valign="baseline">
 -->
                <td class="label"><label for="Pilot2" align="left">Instructor:</label></td>
                <td class="detail" colspan="2"><span class="style17">
                    <select id="Pilot2" name="Pilot2" class="style25">
                     <option value="" > </option>
                        <?php                        
                  			foreach($row_Cfigpilots as $pilot ){
                  					echo(' <option value="'.$pilot.'" >'.$pilot.'</option>');                       
                  			}                         
						?>
                    </select>
                </span></td>
              </tr>
              <tr valign="baseline">
                <td class="label" ><label for="Takeoff" align="left">Takeoff:</label></td>
                <td class="detail" colspan="2"><input name="Takeoff" id="Takeoff" type="text" class="style25" value="" maxlength="8"></input></td>
<!-- 
              </tr>
              <tr valign="baseline">
 -->
                <td class="label"><label for="Landing" align="left">Landing:</label></td>
                <td class="detail" colspan="2"><input id="Landing" name="Landing" type="text" class="style25" value="" maxlength="8"></input></td>
              </tr>

              <tr valign="baseline">
                <td class="label"><label for="Tow_Plane" align="left">Tow Plane:</label></td>
                <td class="detail" colspan="2"><select id="Tow_Plane" name="Tow_Plane"  class="style25"><option value="" >  </option>
                   <?php
                       foreach($row_TowPlane as $tplane ){
                  				echo(' <option value="'.$tplane.'" >'.$tplane.'</option>');                      
                  		}        
                 ?>
                </select></td>
<!-- 
              </tr>
              <tr valign="baseline">
 -->
                <td class="label" ><label for="Tow_Pilot" align="left">Tow Pilot:</label></td>
                <td class="detail" colspan="2"><select id="Tow_Pilot" name="Tow_Pilot" class="style25">	<option value="" >  </option>
                    <?php
                    foreach($row_Towpilots as $pilot ){
                     		echo(' <option value="'.$pilot.'" >'.$pilot.'</option>');    
                    } 
					?>
                </select></td>
              </tr>
              <tr valign="baseline">
                <td height="47" class="label" ><label for="Notes" align="left">Notes:</label></td>
                <td class="detail" colspan="5"><textarea id="Notes" name="Notes"  cols="50" rows="4" class="style25"></textarea></td>
              </tr>
              <tr valign="baseline">
                <td class="detail"><div align="center">
                        <input name="submit" type="submit" class="style25" value="Update" />
                </div></td>
                <td class="detail" colspan="5"> <button class="style25 close">Return to Flight Sheet</button></td>
                </tr>
            </table>
          </form></td>
      </tr>
<!-- 
      <tr><td align="center"> <button class="close">Return to Flight Sheet</button></td>
       </tr>
 -->
    </table>  

</div>
<!-- ======================================================================================-->
<div id="flightPage">
	<table width:"100%" height:"100%" align="center" cellpadding="2" cellspacing="2" bordercolor="#000033" bgcolor="#666666" class="flightlog" >
  	<tr width="100%">
        <th><div align="center">
<!--             <table width="100%"> -->
                <tr>   
                	<td width="5%"> </td>                
 					<td width="8%" <?php if ( !$view_only ){ echo ' bgcolor="#FF9900"'; } ?> >
 						<div align="center">
 							<span class="fl_style25">
							<?php if ( !$view_only ){ 					
                 			    echo ('<button type="button"') ;
                                echo ('id = "add_new_record" class="add_new" bgcolor="#1e90ff">Add Flight</button>'); 
                 			} ?> 
							</span>
						</div>
					</td>
                    <td width="73%"><div align="center"><span class="fl_style1"> PGC DATA PORTAL - FLIGHT SHEET for <?php echo$pgc_flight_date ?></span></div></td>
             		<?php if ( $view_only )  {     
             			echo ('<td></td>');
             		} else {
             		//	echo ('<td width="8%" bgcolor="#006633"><div align="center"><a href="pgc_flightsheet_help.php" class="fl_style25">HELP</a></div></td> ' );
             			echo ('<td width="8%" bgcolor="#006633"><div align="center"><button id="helpPage" class="open">Help</button></div></td> ' );
             		}
             		?>
             	
                    <td width="5%"class="fl_style1"><?php echo 'TDA: ' . $todaycount ?> </td>                                    
				</tr>

		</div></th>
 	</tr>
</table>
	</div>
	    <tr>
	        <td height="481">
	          <table width="90%" height="447" cellpadding="2" cellspacing="2" bordercolor="#005B5B" bgcolor="#4F5359">  
	            <tr >
	                <td height="373" colspan="5" valign="top"> <!--  flight log table  -->
	                   <table width="99%"  id="flightTable" class="flightTable" id="flightList">
	                   <tbody width="100%" >
	                      <thead>   				
                        <tr  width="100%" white-space: nowrap alilgn="center">                      
                            <td bgcolor="#66CCFF" class="fl_style1 fl_style24"><div align="center">Flight</div></td>
                            <td  class="fl_header"><div align="center">GLDR</div></td>
                            <td  class="fl_header"><div align="center">Type</div></td>
                            <td  class="fl_header"  ><div align="center">Member</div></td>
                            <td  class="fl_header" "><div align="center">Instructor</div></td>
                            <?php if ( !$view_only )  {  
                            	echo ('<td width="1" bgcolor="#66CCFF" class="fl_style25"><div align="center"></div></td>');
                            } ?>
                            <td bgcolor="#66CCFF" class="fl_style25"><div align="center">Takeoff</div></td>
                            <?php if ( !$view_only )  {  
                            	echo ('<td width="1" bgcolor="#66CCFF" class="fl_style25"></td>');
                            } ?> 
                            <td  class="fl_header"><div align="center">Landing</div></td>
                            <td  class="fl_header"><div align="center">Hours</div></td>
                            <td  class="fl_header"><div align="center">Altitude </div></td>
                            <td  class="fl_header"><div align="center">Tug</div></td>
                            <td  class="fl_header"><div align="center">Tow Pilot</div></td>
                            <td  class="fl_header"><div align="center">Charge</div></td>
<!-- 
                            <td  class="fl_header"><div align="center">Notes</div></td>
 -->
                        </tr>
                    </thead>
							<?php
							 foreach( $flight_log as $flight ){	 // list all of today's flights. 
							?>
	                        <tr class="flighRow">                             
                             <?php 
                             if ( !$view_only )  {  
                             	echo ('<td class="hidden"  id="flight_id"><div>' .$flight->id . '</td>');
                             	echo ('<td bgcolor="#999999" class="fl_style25 flightdata"  id="yearkey"> ');
                             	echo ('<div align="center" class"flightdata" >' . $flight->yearkey . '</div>' );
							 } else{ 
							 	echo ('<td bgcolor="#999999" class="fl_style25"  ><div align="center">  ');
                      		 	echo $flight->yearkey;
                      		 }
                      		 ?>  
                             </div></td>
                             <td  class="fl_flight_row" id="glider" align="center"><?php echo $flight->Glider; ?></td>
                             <td  class="fl_flight_row" id="flight_type"><div align="center"><?php echo $flight->Flight_Type; ?></div></td>
                             <td  class="fl_flight_row" id="pilot1"><?php echo $flight->Pilot1; ?></td>
                             <td  class="fl_flight_row" id="pilot2"><?php echo $flight->Pilot2; ?></td>
                              <?php if ( !$view_only )  {  
                                 echo ('<td bgcolor="#FFFFFF" ><button type="button"') ;
                                 echo (' align="center" class="pdp_update_time button-flightlog button-start" data-start=1 ></button></td>');
                               }; ?> 
                             <td bgcolor="#FFFFFF" class="fl_flight_row" id="takeoff"><div align="center"><?php echo $flight->Takeoff; ?></div></td>

                             <?php if ( !$view_only )  {  
                                 echo ('<td bgcolor="#FFFFFF" ><button type="button"') ;
                                 echo (' align="center" class="pdp_update_time button-flightlog button-stop" data-start="0"></button></td>');
								}; ?> 
                          
                             <td  class="fl_flight_row" id="landing"><div align="center"><?php echo $flight->Landing; ?></div></td>
                             <td  class="fl_flight_row" id="flighttime"><div align="center"><?php echo $flight->Time; ?></div></td>
                             <td  class="fl_flight_row" id="towaltitude"><div align="center"><?php echo $flight->{'Tow_Altitude'}; ?></div></td>
                             <td  class="fl_flight_row" id="towplane"><div align="center"><?php echo $flight->{'Tow_Plane'}; ?></div>                                    </td>
                             <td  class="fl_flight_row" id="towpilot"><?php echo $flight->{'Tow_Pilot'}; ?></td>
                             <td  class="fl_flight_row" id="towcharge"><?php echo $flight->{'Tow_Charge'}; ?></td>

                             <td width="20" nowrap="nowrap" bgcolor="#FFFFFF" class="hidden" id="notes"><?php echo $flight->Notes; ?></td>
                         </tr>	
						<?php	
							}
							?>
	
						</tbody>
	                	</table>
					</td>
	            </tr>
	            <tr>
	                <td height="28"><div align="center"><strong class="fl_style3"><a class="fl_style16"></a></strong></div></td>
	            </tr>
	        </table></td>
	    </tr> 
	</table>
	<div class="float-container">
		<div class="float-child"> Status:</div>
		<div class="float-child" id="action_status">Idle</div>
	</div>
</div>

</body>
<script id="add-form-template" type="text/html-template">




</script>
</html>
