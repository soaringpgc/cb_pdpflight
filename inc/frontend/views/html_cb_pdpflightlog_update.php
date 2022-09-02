<?php
//require dirname(__DIR__, 7) . '/Connections/PGC.php';
global $PGCwp; // database handle for accessing wordpress db
global $PGCi;  // database handle for PDP external db
global $wpdb;
?>
<?php
error_reporting(E_ALL);
//ini_set('display_errors', 'On');
$view_only = true; 
if (isset( $flight_atts['view_only'] )) {
	$view_only = $flight_atts['view_only']==='true' ? true : false ;
	if (!$view_only && !current_user_can('flight_edit')){
		wp_redirect( wp_login_url() );
	}
}
?>
<?php 

$entry_ip = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
    {
       $entry_ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
    {
       $entry_ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else
    {
       $entry_ip=$_SERVER['REMOTE_ADDR'];
    }
	$_POST['entry_ip'] = $entry_ip;

$editFormAction =  $_SERVER['PHP_SELF'];

if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form2")) {

$PGCwp->update( 'pgc_flightlog_lastpilot', array('LastPilot'=>$_POST['Tow_Pilot'], 'TowPlane'=>$_POST['Tow_Plane']), array('seq'=>'1'));
$sql = $PGCwp->prepare("SELECT Date FROM pgc_flightsheet WHERE `Key` =  %s" ,$_GET['key']);
$flightdate=$PGCwp->get_var($sql);
 
$charge= $wpdb->get_var($wpdb->prepare("SELECT charge from wp_cloud_base_tow_fees WHERE altitude = %s", $_POST['Tow_Altitude'] )) ;
//exit(var_dump($charge));

// take care of O'  in names! an "'" is escaped with "\" to be sent over html need to 
// remove it before saving in the database 
$pilot1 = str_replace(array("\'"), array("'"), $_POST['Pilot1']);
$pilot2 = str_replace(array("\'"), array("'"), $_POST['Pilot2']);
$tow_pilot = str_replace(array("\'"), array("'"), $_POST['Tow_Pilot']);

$Result1 = $PGCwp->update('pgc_flightsheet', array('Glider'=>$_POST['Glider'], 'Flight_Type'=>$_POST['Flight_Type'], 'Pilot1'=>$pilot1, 
				'Pilot2'=>$pilot2, 'Takeoff'=>$_POST['Takeoff'], 'Landing'=>$_POST['Landing'], 'Tow Altitude'=>$_POST['Tow_Altitude'], 
				'Tow Plane'=>$_POST['Tow_Plane'], 'Tow Pilot'=>$tow_pilot, 'Tow Charge'=>$charge, 'Notes'=>$_POST['Notes'], 
				'ip'=>$_POST['entry_ip']), array('Key'=>$_POST['recordID']), array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s'), array('%s'));  

$Result2 = $PGCwp->insert('pgc_flightsheet_audit', array('Date'=>$flightdate , 'Glider'=>$_POST['Glider'], 'Flight_Type'=>$_POST['Flight_Type'], 'Pilot1'=>$pilot1, 
				'Pilot2'=>$pilot2, 'Takeoff'=>$_POST['Takeoff'], 'Landing'=>$_POST['Landing'], 'Tow Altitude'=>$_POST['Tow_Altitude'], 
				'Tow Plane'=>$_POST['Tow_Plane'], 'Tow Pilot'=>$tow_pilot, 'Tow Charge'=>$charge, 'Notes'=>$_POST['Notes'], 
				'ip'=>$_POST['entry_ip'], 'Key'=>$_POST['recordID']), array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s'));  
//exit ($Result2); 
 /*==END INSERT AUDIT====*/ 
 
 /*  Calculate Time   */
  
 // $Result2 = mysqli_query($PGCi, $updateTime ) or die(mysqli_error($PGCi));

// $updateTime = sprintf("UPDATE pgc_flightsheet SET Time = TIME_TO_SEC(TIMEDIFF(Landing,Takeoff))/3600 WHERE Landing > Takeoff AND `Key`=%s",
//                         GetSQLValueString($_POST['recordID'], "int"));

  //mysql_select_db($database_PGC, $PGC);
//  $Result2 = mysqli_query($PGCi, $updateTime )  or die(mysqli_error($PGCi));

	$sql = $PGCwp->prepare("UPDATE pgc_flightsheet SET Time = TIME_TO_SEC(TIMEDIFF(Landing,Takeoff))/3600 WHERE Landing > Takeoff AND `Key`=%s", $_POST['recordID']);
	$PGCwp->query($sql );
   
//  $updateTime = sprintf("UPDATE pgc_flightsheet SET Time = (TIME_TO_SEC(TIMEDIFF(Landing,Takeoff)) + 60*60*12)/3600 WHERE Landing < Takeoff AND `Key`=%s",
//                          GetSQLValueString($_POST['recordID'], "int"));
// 
//   //mysql_select_db($database_PGC, $PGC);
//   $Result2 = mysqli_query($PGCi, $updateTime )  or die(mysqli_error($PGCi));
//   
  
//   $updateTime = sprintf("UPDATE pgc_flightsheet A, pgc_flightlog_charges B SET  A.`Tow Charge` = B.`charge` WHERE A.`Tow Altitude` = B.`altitude` AND `Key`=%s",
//                          GetSQLValueString($_POST['recordID'], "int"));
// 
//   $Result2 = mysqli_query($PGCi, $updateTime )  or die(mysqli_error($PGCi));
  
//     $updateTime = sprintf("UPDATE pgc_flightsheet A, pgc_flightlog_charges B SET  A.`Tow Charge` = B.`charge` * A.Time WHERE A.`Tow Altitude` = B.`altitude` AND  A.`Tow Altitude` =  'AERO' AND`Key`=%s",
//                          GetSQLValueString($_POST['recordID'], "int"));

  //mysql_select_db($database_PGC, $PGC);
//  $Result2 = mysqli_query($PGCi, $updateTime )  or die(mysqli_error($PGCi));
 
 /* End Calculate Time  */
 
//  $updateID = sprintf("UPDATE pgc_flightsheet A, pgc_members B SET  A.`email` = B.`USER_ID` WHERE A.`Pilot1` = B.`NAME` AND `Key`=%s",
//                         GetSQLValueString($_POST['recordID'], "int"));
						 
   //mysql_select_db($database_PGC, $PGC);
//   $ResultID = mysqli_query($PGCi, $updateID )  or die(mysqli_error($PGCi));

   	$sql = $PGCwp->prepare("UPDATE pgc_flightsheet A, pgc_members B SET  A.`email` = B.`USER_ID` WHERE A.`Pilot1` = B.`NAME` AND `Key`=%s", $_POST['recordID']);
	$PGCwp->query($sql );   
 
 /*== Send Email if we have a takeoff and a landing time ====*/
 If ($_POST['Landing'] <> '' AND $_POST['Takeoff'] <> '') {

	$row_Flightlog= $PGCwp->get_row($PGCwp->prepare("SELECT * from pgc_flightsheet WHERE `Key` = %d", $_POST['recordID']), $output='ARRAY_A') ;
	
	$intro = "\n" . "This message was generated by the PDP when tow altitude or other data in your flight log record was updated by the flight desk after you landed." . "\n\n". "You may receive additional updates if other changes are made to this log record.". "\n\n" . "Please email the Flight Log Administrator at flightlog.pgc@gmail.com or contact a BOD member if this data is not accurate."."\n\n";
	
	$emaillog =  $intro . "Source IP: " . $row_Flightlog['ip'] . "\n" . "Key: " . $row_Flightlog['Key'] . "\n" . "Date: " . $row_Flightlog['Date'] ."\n" . "Glider: " . $row_Flightlog['Glider'] ."\n" . "Pilot1: " . $row_Flightlog['Pilot1'] ."\n" . "Pilot2: " . $row_Flightlog['Pilot2'] ."\n" . "Takeoff: " . $row_Flightlog['Takeoff'] . "\n" . "Landing: " .  $row_Flightlog['Landing'] ."\n" . "Duration: " . $row_Flightlog['Time'] ."\n".  "Tow Altitude: " . $row_Flightlog['Tow Altitude'] . "\n" . "Tow Plane: " . $row_Flightlog['Tow Plane'] . "\n" . "Tow Pilot: " . $row_Flightlog['Tow Pilot'] . "\n" . "Notes: " . $row_Flightlog['Notes'] . "\n" ;  
					 
	$webmaster = "support@pgcsoaring.org";
	$treasurer = "treasure.pgc@gmail.com";
	$member = $row_Flightlog['email']; 

	$to = $webmaster. "," . $member;
	$subject = "PGC Flightlog - Record Updated for Flight: " . $row_Flightlog['Key'] . " Updated By: " . $row_Flightlog['ip'] ;
	$email = $member ;
	$headers = "From: PGC Pilot Data Portal";
	$headers = "From: support@pgcsoaring.org";
	$headers = "From: PGC-DataPortal@noreply.com";

	If ($row_Flightlog['Tow Altitude'] <> ' 5000') {
		$sent = mail($to, $subject, $emaillog, $headers);
	}
	}
/*=======*/
   wp_redirect($_POST['ParentPage'] );
   exit();
 }
$colname_Flightlog = "-1";
if (isset($_GET['key'])) {
  $colname_Flightlog = $_GET['key'];
}

$row_Flightlog= $PGCwp->get_row($PGCwp->prepare("SELECT * from pgc_flightsheet WHERE `Key` = %d", $colname_Flightlog), $output='ARRAY_A') ;

$row_Memberpilots = array();
$request = new WP_REST_Request('GET', '/cloud_base/v1/pilots');
$request->set_param('no_fly', 'false');
$response = rest_do_request($request);
$server = rest_get_server();
$member_pilots = $server->response_to_data( $response, false );

foreach($member_pilots as $pilot ){
	array_push ($row_Memberpilots, $pilot->name );	
}
sort($row_Memberpilots ); 

$row_no_fly = array();
$request = new WP_REST_Request('GET', '/cloud_base/v1/pilots');
$request->set_param('no_fly', 'true');
$response = rest_do_request($request);
$server = rest_get_server();
$member_pilots = $server->response_to_data( $response, false );

foreach($member_pilots as $pilot ){
	array_push ($row_no_fly, $pilot->name );	
}
sort($row_no_fly ); 

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
//exit(var_dump($row_rsGliders));

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
// exit(var_dump($row_TowPlane));

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

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>PGC Data Portal - Flightlog</title>
<style type="text/css">
<!--
.style1 {	font-size: 18px;
	font-weight: bold;
}
body {
	background-color: #333333;
}
body,td,th {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
	color: #CCCCCC;
}
.style3 {font-size: 16px; font-weight: bold; }
.style16 {color: #CCCCCC; }
.style17 {color: #330033}
.style25 {font-size: 18px; font-weight: bold; color: #000000; }
.style26 {
	color: #CCCCCC;
	font-size: 16px;
	font-weight: bold;
}
-->
</style>
</head>
<!-- 
<script language="javascript" src="../calendar/calendar.js"></script>
 -->

<body>
<p>&nbsp;</p>
<table width="800" border="1" align="center" cellpadding="2" cellspacing="2" bordercolor="#000033" bgcolor="#666666">
  <tr>
    <td><div align="center"><span class="style1">PGC DATA PORTAL </span></div></td>
  </tr>
  <tr>
    <td height="481"><table width="92%" height="447" border="1" align="center" cellpadding="2" cellspacing="2" bordercolor="#005B5B" bgcolor="#4F5359">
      
      <tr>
        <td height="373"><p align="center" class="style26">PGC FLIGHT SHEET DETAIL SCREEN </p>
            <p>
           </p>
          <form method="post" name="form2" action="<?php echo $editFormAction; ?>">
            <table align="center" cellpadding="3" cellspacing="3" bgcolor="#000066" class="style25">
              <tr valign="baseline">
                <td width="165" align="right" nowrap bgcolor="#CCCCCC" class="style25"><div align="left">Date:</div></td>
                <input name="recordID" type="hidden" value=<?php echo $_GET['key'] ?> > 
                <td width="329" bgcolor="#CCCCCC"><input name="Date" type="text" class="style25" value="<?php echo $row_Flightlog['Date']; ?>" size="11" readonly/></td>
              </tr>
              <tr valign="baseline">
                <td align="right" valign="middle" nowrap bgcolor="#CCCCCC" class="style25"><div align="left">Glider:</div></td>
                <td bgcolor="#CCCCCC"><select name="Glider" class="style25" id="Glider">
                  <?php
                       foreach($row_rsGliders as $glider ){
                  		    if ( $glider == $row_Flightlog['Glider'] ) { 
                  		    	echo(' <option value="'.$glider.'" selected>'.$glider.'</option>');  
                  		    } else {
                  				echo(' <option value="'.$glider.'" class="nofly">'.$glider.'</option>');    
                  			}                   
                  		}        
                 ?>
                </select></td>
              </tr>
              <tr valign="baseline">
                  <td align="right" valign="middle" nowrap bgcolor="#CCCCCC" class="style25"><div align="left">Flight Type:</div></td>
                  <td bgcolor="#CCCCCC">
				  <select name="Flight_Type" class="style25" select id="Flight_Type">
				        <option value="REG" <?php if (!(strcmp("REG", $row_Flightlog['Flight_Type']))) {echo "selected=\"selected\"";} ?>>REG</option>
				        <option value="PVT" <?php if (!(strcmp("PVT", $row_Flightlog['Flight_Type']))) {echo "selected=\"selected\"";} ?>>PVT</option>
					<option value="AOF" <?php if (!(strcmp("AOF", $row_Flightlog['Flight_Type']))) {echo "selected=\"selected\"";} ?>>AOF</option>
                  </select></td>
              </tr>
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
                     <option value="" >  </option>
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
<p>&nbsp;</p>
</body>
</html>



