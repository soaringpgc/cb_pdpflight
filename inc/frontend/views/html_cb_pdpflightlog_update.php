<?php
//require dirname(__DIR__, 7) . '/Connections/PGC.php';
global $PGCwp; // database handle for accessing wordpress db
global $PGCi;  // database handle for PDP external db
?>
<?php
error_reporting(E_ALL);
//ini_set('display_errors', 'On');
if (!$view_only && !current_user_can('flight_edit')){
	wp_redirect( wp_login_url() );
}
?>
<?php function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{


  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
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

// yes i'm mixing data base access between wpdb and mysqli. 
// wpdb is new stuff, mysqli is old -- life with it or change it yourself. -- dsj
//

$PGCwp->update( 'pgc_flightlog_lastpilot', array('LastPilot'=>$_POST['Tow_Pilot'], 'TowPlane'=>$_POST['Tow_Plane']), array('seq'=>'1'));
$sql = $PGCwp->prepare("SELECT Date FROM pgc_flightsheet WHERE `Key` =  %s" ,$_GET['key']);
$flightdate=$PGCwp->get_var($sql);
 
 $updateSQL = sprintf("UPDATE pgc_flightsheet SET Glider=%s, Flight_Type=%s, Pilot1=%s, Pilot2=%s, Takeoff=%s, Landing=%s, `Tow Altitude`=%s, `Tow Plane`=%s, `Tow Pilot`=%s, `Tow Charge`=%s, Notes=%s ,`ip`=%s WHERE `Key`=%s",
                       GetSQLValueString($_POST['Glider'], "text"),
					   GetSQLValueString($_POST['Flight_Type'], "text"),					   
                       GetSQLValueString($_POST['Pilot1'], "text"),
                       GetSQLValueString($_POST['Pilot2'], "text"),
                       GetSQLValueString($_POST['Takeoff'], "date"),
                       GetSQLValueString($_POST['Landing'], "date"),
                       GetSQLValueString($_POST['Tow_Altitude'], "text"),
                       GetSQLValueString($_POST['Tow_Plane'], "text"),
                       GetSQLValueString($_POST['Tow_Pilot'], "text"),
                       GetSQLValueString($_POST['Tow_Charge'], "double"),
                       GetSQLValueString($_POST['Notes'], "text"),
					   GetSQLValueString($_POST['entry_ip'], "text"),
                       GetSQLValueString($_POST['recordID'], "int"));
  $Result1 = mysqli_query($PGCi, $updateSQL )  or die(mysqli_error($PGCi));

/*== INSERT AUDIT RECORD ==*/ 
$insertSQL = sprintf("INSERT INTO pgc_flightsheet_audit (`Date`, Glider, Flight_Type, Pilot1, Pilot2, Takeoff, Landing, `Tow Altitude`, `Tow Plane`, `Tow Pilot`, `Tow Charge`, Notes,`ip`,`Key`) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s )",
                       GetSQLValueString($flightdate, "text"),
                       GetSQLValueString($_POST['Glider'], "text"),
					   GetSQLValueString($_POST['Flight_Type'], "text"),					   
                       GetSQLValueString($_POST['Pilot1'], "text"),
                       GetSQLValueString($_POST['Pilot2'], "text"),
                       GetSQLValueString($_POST['Takeoff'], "date"),
                       GetSQLValueString($_POST['Landing'], "date"),
                       GetSQLValueString($_POST['Tow_Altitude'], "text"),
                       GetSQLValueString($_POST['Tow_Plane'], "text"),
                       GetSQLValueString($_POST['Tow_Pilot'], "text"),
                       GetSQLValueString($_POST['Tow_Charge'], "double"),
                       GetSQLValueString($_POST['Notes'], "text"),
					   GetSQLValueString($_POST['entry_ip'], "text"),
                       GetSQLValueString($_POST['recordID'], "int"));

 $Result1 = mysqli_query($PGCi, $insertSQL )  or die(mysqli_error($PGCi)); 
 /*==END INSERT AUDIT====*/ 
 
 /*  Calculate Time   */
  
  $updateTime = sprintf("UPDATE pgc_flightsheet SET Time = null WHERE `Key`=%s",
                         GetSQLValueString($_POST['recordID'], "int"));

  $Result2 = mysqli_query($PGCi, $updateTime ) or die(mysqli_error($PGCi));
  
 $updateTime = sprintf("UPDATE pgc_flightsheet SET Time = TIME_TO_SEC(TIMEDIFF(Landing,Takeoff))/3600 WHERE Landing > Takeoff AND `Key`=%s",
                         GetSQLValueString($_POST['recordID'], "int"));

  //mysql_select_db($database_PGC, $PGC);
  $Result2 = mysqli_query($PGCi, $updateTime )  or die(mysqli_error($PGCi));
  
 $updateTime = sprintf("UPDATE pgc_flightsheet SET Time = (TIME_TO_SEC(TIMEDIFF(Landing,Takeoff)) + 60*60*12)/3600 WHERE Landing < Takeoff AND `Key`=%s",
                         GetSQLValueString($_POST['recordID'], "int"));

  //mysql_select_db($database_PGC, $PGC);
  $Result2 = mysqli_query($PGCi, $updateTime )  or die(mysqli_error($PGCi));
 
 $updateTime = sprintf("UPDATE pgc_flightsheet SET Time = (TIME_TO_SEC(TIMEDIFF(Landing,Takeoff)) + 60*60*12)/3600 WHERE Landing < Takeoff AND `Key`=%s",
                         GetSQLValueString($_POST['recordID'], "int"));

  //mysql_select_db($database_PGC, $PGC);
  $Result2 = mysqli_query($PGCi, $updateTime )  or die(mysqli_error($PGCi));
  
  
  $updateTime = sprintf("UPDATE pgc_flightsheet A, pgc_flightlog_charges B SET  A.`Tow Charge` = B.`charge` WHERE A.`Tow Altitude` = B.`altitude` AND `Key`=%s",
                         GetSQLValueString($_POST['recordID'], "int"));

  //mysql_select_db($database_PGC, $PGC);
  $Result2 = mysqli_query($PGCi, $updateTime )  or die(mysqli_error($PGCi));
  
    $updateTime = sprintf("UPDATE pgc_flightsheet A, pgc_flightlog_charges B SET  A.`Tow Charge` = B.`charge` * A.Time WHERE A.`Tow Altitude` = B.`altitude` AND  A.`Tow Altitude` =  'AERO' AND`Key`=%s",
                         GetSQLValueString($_POST['recordID'], "int"));

  //mysql_select_db($database_PGC, $PGC);
  $Result2 = mysqli_query($PGCi, $updateTime )  or die(mysqli_error($PGCi));
 
 
 /* End Calculate Time  */
 
  
  $updateID = sprintf("UPDATE pgc_flightsheet A, pgc_members B SET  A.`email` = B.`USER_ID` WHERE A.`Pilot1` = B.`NAME` AND `Key`=%s",
                         GetSQLValueString($_POST['recordID'], "int"));
						 
   //mysql_select_db($database_PGC, $PGC);
   $ResultID = mysqli_query($PGCi, $updateID )  or die(mysqli_error($PGCi));
 
 /*== Send Email if we have a takeoff and a landing time ====*/
 If ($_POST['Landing'] <> '' AND $_POST['Takeoff'] <> '') {

 						 
  	//mysql_select_db($database_PGC, $PGC);
	$query_Flightlog = sprintf("SELECT * FROM pgc_flightsheet WHERE `Key` = %s",
	 GetSQLValueString($_POST['recordID'], "int"));
	$Flightlog = mysqli_query($PGCi, $query_Flightlog )  or die(mysqli_error($PGCi));
	$row_Flightlog =mysqli_fetch_assoc($Flightlog);
	/*$totalRows_Flightlog = mysqli_num_rows($Flightlog);*/

	$intro = "\n" . "This message was generated by the PDP when tow altitude or other data in your flight log record was updated by the flight desk after you landed." . "\n\n". "You may receive additional updates if other changes are made to this log record.". "\n\n" . "Please email the Flight Log Administrator at flightlog.pgc@gmail.com or contact a BOD member if this data is not accurate."."\n\n";
	
	$emaillog =  $intro . "Source IP: " . $row_Flightlog['ip'] . "\n" . "Key: " . $row_Flightlog['Key'] . "\n" . "Date: " . $row_Flightlog['Date'] ."\n" . "Glider: " . $row_Flightlog['Glider'] ."\n" . "Pilot1: " . $row_Flightlog['Pilot1'] ."\n" . "Pilot2: " . $row_Flightlog['Pilot2'] ."\n" . "Takeoff: " . $row_Flightlog['Takeoff'] . "\n" . "Landing: " .  $row_Flightlog['Landing'] ."\n" . "Duration: " . $row_Flightlog['Time'] ."\n".  "Tow Altitude: " . $row_Flightlog['Tow Altitude'] . "\n" . "Tow Plane: " . $row_Flightlog['Tow Plane'] . "\n" . "Tow Pilot: " . $row_Flightlog['Tow Pilot'] . "\n" . "Notes: " . $row_Flightlog['Notes'] . "\n" ;  
					 
	$webmaster = "support@pgcsoaring.org";
	$treasurer = "treasure.pgc@gmail.com";
	$member = $row_Flightlog['email']; 
	 
	$to = $webmaster. "," . $member;
	$subject = "PGC Flightlog - Record Updated for Flight: " . $row_Flightlog['Key'] . " Updated By: " . $row_Flightlog['ip'] ;
	$email = $_REQUEST['email'];
	$headers = "From: PGC Pilot Data Portal";
	$headers = "From: support@pgcsoaring.org";
	$headers = "From: PGC-DataPortal@noreply.com";
	If ($row_Flightlog['Tow Altitude'] <> ' 5000') {
		$sent = mail($to, $subject, $emaillog, $headers);
		}
	$sent = mail($to, $subject, $emaillog, $headers);
	}

/*=======*/
       
  $insertGoTo = $_POST['LOG_PAGE'] ;
//  $insertGoTo = $_SESSION[last_query];
//  $insertGoTo = "pgc_flightlog_list_edit.php";
//   if (isset($_SERVER['QUERY_STRING'])) {
//     $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
//     $insertGoTo .= $_SERVER['QUERY_STRING'];
//   }
  header(sprintf("Location: %s", $insertGoTo));
 }
$colname_Flightlog = "-1";
if (isset($_GET['key'])) {
  $colname_Flightlog = $_GET['key'];
}
//mysql_select_db($database_PGC, $PGC);
$query_Flightlog = sprintf("SELECT * FROM pgc_flightsheet WHERE `Key` = %s", GetSQLValueString($colname_Flightlog, "int"));
$Flightlog = mysqli_query($PGCi, $query_Flightlog )  or die(mysqli_error($PGCi));
$row_Flightlog =mysqli_fetch_assoc($Flightlog);
$totalRows_Flightlog = mysqli_num_rows($Flightlog);
$FlightStart = $row_Flightlog['Takeoff'];
$FlightEnd = $row_Flightlog['Landing'];
$FlightDuration = $row_Flightlog['Time'];
   
//mysql_select_db($database_PGC, $PGC);
$query_rsMembers = "SELECT USER_ID, NAME, PGC_STATUS, active FROM pgc_members WHERE active = 'YES' ORDER BY NAME ASC";
$rsMembers = mysqli_query($PGCi, $query_rsMembers )  or die(mysqli_error($PGCi));
$row_rsMembers =mysqli_fetch_assoc($rsMembers);
$totalRows_rsMembers = mysqli_num_rows($rsMembers);

//mysql_select_db($database_PGC, $PGC);
$query_rsGliders = "SELECT glider, nnumber FROM pgc_gliders ORDER BY glider ASC";
$rsGliders = mysqli_query($PGCi, $query_rsGliders )  or die(mysqli_error($PGCi));
$row_rsGliders =mysqli_fetch_assoc($rsGliders);
$totalRows_rsGliders = mysqli_num_rows($rsGliders);

//mysql_select_db($database_PGC, $PGC);
$query_rsTowpilots = "SELECT pilot_name FROM pgc_pilot_ratings WHERE pgc_rating = 'Tow Pilot'";
$rsTowpilots = mysqli_query($PGCi, $query_rsTowpilots )  or die(mysqli_error($PGCi));
$row_rsTowpilots =mysqli_fetch_assoc($rsTowpilots);
$totalRows_rsTowpilots = mysqli_num_rows($rsTowpilots);

//mysql_select_db($database_PGC, $PGC);
$query_rs_instructors = "SELECT * FROM pgc_instructors WHERE rec_active = 'Y' AND cfig = 'Y' ORDER BY Name ASC";
$rs_instructors = mysqli_query($PGCi, $query_rs_instructors )  or die(mysqli_error($PGCi));
$row_rs_instructors =mysqli_fetch_assoc($rs_instructors);
$totalRows_rs_instructors = mysqli_num_rows($rs_instructors);

//mysql_select_db($database_PGC, $PGC);
$query_rs_altitudes = "SELECT altitude FROM pgc_flightlog_charges ORDER BY seq ASC";
$rs_altitudes = mysqli_query($PGCi, $query_rs_altitudes )  or die(mysqli_error($PGCi));
$row_rs_altitudes =mysqli_fetch_assoc($rs_altitudes);
$totalRows_rs_altitudes = mysqli_num_rows($rs_altitudes);
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
<script language="javascript" src="../calendar/calendar.js"></script>

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
            <!--<form action="somewhere.php" method="post">
*/</form>

<p>&nbsp;</p>
--></p>
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
do {  
?>
                  <option value="<?php echo $row_rsGliders['glider']?>"<?php if (!(strcmp($row_rsGliders['glider'], $row_Flightlog['Glider']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsGliders['glider']?></option>
                  <?php
} while ($row_rsGliders =mysqli_fetch_assoc($rsGliders));
  $rows = mysqli_num_rows($rsGliders);
  if($rows > 0) {
      mysqli_data_seek($rsGliders, 0);
	  $row_rsGliders =mysqli_fetch_assoc($rsGliders);
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
                  <select name="Pilot1" class="style25" id="Pilot1">
                        <option value="** New Member **" <?php if (!(strcmp("** New Member **", $row_Flightlog['Pilot1']))) {echo "selected=\"selected\"";} ?>>** New Member **</option>
                        <option value="** Freedoms Wings **" <?php if (!(strcmp("** Freedoms Wings **", $row_Flightlog['Pilot1']))) {echo "selected=\"selected\"";} ?>>** Freedoms Wings **</option>
                        <?php
do {  
?>
<option value="<?php echo $row_rsMembers['NAME']?>"<?php if (!(strcmp($row_rsMembers['NAME'], $row_Flightlog['Pilot1']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsMembers['NAME']?></option>
                        <?php
} while ($row_rsMembers =mysqli_fetch_assoc($rsMembers));
  $rows = mysqli_num_rows($rsMembers);
  if($rows > 0) {
      mysqli_data_seek($rsMembers, 0);
	  $row_rsMembers =mysqli_fetch_assoc($rsMembers);
  }
?>
                  </select>
                </span></td>
              </tr>
              <tr valign="baseline">
                <td align="right" valign="middle" nowrap bgcolor="#CCCCCC" class="style25"><div align="left">Instructor:</div></td>
                <td bgcolor="#CCCCCC"><span class="style17">
                    <select name="Pilot2" class="style25" id="Pilot2">
                        <?php
do {  
?>
                        <option value="<?php echo $row_rs_instructors['Name']?>"<?php if (!(strcmp($row_rs_instructors['Name'], $row_Flightlog['Pilot2']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rs_instructors['Name']?></option>
                        <?php
} while ($row_rs_instructors =mysqli_fetch_assoc($rs_instructors));
  $rows = mysqli_num_rows($rs_instructors);
  if($rows > 0) {
      mysqli_data_seek($rs_instructors, 0);
	  $row_rs_instructors =mysqli_fetch_assoc($rs_instructors);
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
do {  
?>
                  <option value="<?php echo $row_rs_altitudes['altitude']?>"<?php if (!(strcmp($row_rs_altitudes['altitude'], $row_Flightlog['Tow Altitude']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rs_altitudes['altitude']?></option>
                  <?php
} while ($row_rs_altitudes =mysqli_fetch_assoc($rs_altitudes));
  $rows = mysqli_num_rows($rs_altitudes);
  if($rows > 0) {
      mysqli_data_seek($rs_altitudes, 0);
	  $row_rs_altitudes =mysqli_fetch_assoc($rs_altitudes);
  }
?>
                                </select></td>
              </tr>
              <tr valign="baseline">
                <td align="right" valign="middle" nowrap bgcolor="#CCCCCC" class="style25"><div align="left">Tow Plane:</div></td>
                <td bgcolor="#CCCCCC"><select name="Tow_Plane" class="style25">
                    <option value="" <?php if (!(strcmp("", $row_Flightlog['Tow Plane']))) {echo "selected=\"selected\"";} ?>></option>
                    <option value="305A" <?php if (!(strcmp("305A", $row_Flightlog['Tow Plane']))) {echo "selected=\"selected\"";} ?>>305A</option>
                    <option value="76P" <?php if (!(strcmp("76P", $row_Flightlog['Tow Plane']))) {echo "selected=\"selected\"";} ?>>76P</option>
<!--  This is the only place I could find where tow planes are list. not in any database i cold fine DSJ 18 March 2017 -->
                    <option value="80Y" <?php if (!(strcmp("80Y", $row_Flightlog['Tow Plane']))) {echo "selected=\"selected\"";} ?>>80Y</option>
                </select></td>
              </tr>
              <tr valign="baseline">
                <td align="right" valign="middle" nowrap bgcolor="#CCCCCC" class="style25"><div align="left">Tow Pilot:</div></td>
                <td bgcolor="#CCCCCC"><select name="Tow_Pilot" class="style25">
                    <?php
do {  
?>
                    <option value="<?php echo $row_rsTowpilots['pilot_name']?>"<?php if (!(strcmp($row_rsTowpilots['pilot_name'], $row_Flightlog['Tow Pilot']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsTowpilots['pilot_name']?></option>
                    <?php
} while ($row_rsTowpilots =mysqli_fetch_assoc($rsTowpilots));
  $rows = mysqli_num_rows($rsTowpilots);
  if($rows > 0) {
      mysqli_data_seek($rsTowpilots, 0);
	  $row_rsTowpilots =mysqli_fetch_assoc($rsTowpilots);
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
            <input type="hidden" name="LOG_PAGE" value=<?php echo  $_SERVER['HTTP_REFERER']  ?>>
            <input type="hidden" name="MM_update" value="form2">
            <input type="hidden" name="Key" value="<?php echo $row_Flightlog['Key']; ?>">
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
<?php
mysqli_free_result($Flightlog);
mysqli_free_result($rsMembers);
mysqli_free_result($rsGliders);
mysqli_free_result($rsTowpilots);
mysqli_free_result($rs_instructors);
mysqli_free_result($rs_altitudes);
?>



