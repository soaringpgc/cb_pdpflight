<?php
//require dirname(__DIR__, 7) . '/Connections/PGC.php';
global $PGCwp; // database handle for accessing wordpress db
global $PGCi;  // database handle for PDP external db

//	    echo  $metrcis_atts['title'];
?>
<?php

error_reporting(E_ALL);
//ini_set('display_errors', 'On');
// require_once('pgc_check_login.php'); 
// /* ==========================================================*/
// require_once('pgc_access_save_appname.php'); 
// /* START - PAGE ACCESS CHECKING LOGIC - ADD TO ALL APPS - START */
// require_once('pgc_access_check.php');
// /* END - PAGE ACCESS CHECKING LOGIC - END */
// /* ==========================================================*/
?>
<?php
// Add year and flightlog history table name to pgc_flight_tables table 
//  $pgc_year_session = 'pgc_flightsheet';
if (isset($_GET['pgc_year'])) {
$pgc_year_session = $_GET['pgc_year'];
}
//mysql_select_db($database_PGC, $PGC);
$query_Recordset1 = "SELECT * FROM pgc_flight_tables ORDER by ops_year DESC";
$Recordset1 = mysqli_query($PGCi, $query_Recordset1 )  or die(mysqli_error($PGCi));
$row_Recordset1 =mysqli_fetch_assoc($Recordset1);
$totalRows_Recordset1 = mysqli_num_rows($Recordset1);
//var_dump($totalRows_Recordset1);
if (isset($pgc_year_session)) 
$pgc_table = $pgc_year_session;
ELSE
$pgc_table = 'pgc_flightsheet';
$pgc_table_col = $pgc_table .'.glider';
$pgc_table_pilot = $pgc_table .'.Pilot1';
if ($pgc_tablex = 'pgc_flightsheet') {
 /*  Calculate Time  AND  YEAR( `Date`) = YEAR(CURDATE()) */
$updateHours = "UPDATE pgc_gliders SET pgc_hours = (SELECT Sum(`Time`) FROM " . $pgc_table . " WHERE pgc_gliders.glider = " . $pgc_table_col . " AND " . $pgc_table_pilot . " <> '') ";
//mysql_select_db($database_PGC, $PGC);
$HoursResult = mysqli_query($PGCi, $updateHours )  or die(mysqli_error($PGCi));

$updateHours = "UPDATE pgc_gliders SET total_hours = start_hours + pgc_hours";
//mysql_select_db($database_PGC, $PGC);
$HoursResult = mysqli_query($PGCi, $updateHours )  or die(mysqli_error($PGCi));

$updateHours = "UPDATE pgc_gliders SET total_hours = start_hours WHERE pgc_hours IS NULL";
//mysql_select_db($database_PGC, $PGC);
$HoursResult = mysqli_query($PGCi, $updateHours )  or die(mysqli_error($PGCi));


$updateHours = "UPDATE pgc_gliders SET delta_hours = total_hours - inspection_hours";
//mysql_select_db($database_PGC, $PGC);
$HoursResult = mysqli_query($PGCi, $updateHours )  or die(mysqli_error($PGCi));
}
 
?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{

  $theValue = mysqli_real_escape_string($PGCi, $theValue) ;

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
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
}



// METRICS 
$query_rsGliders = "SELECT Glider, Count(Glider), Sum(`Time`) FROM " . $pgc_table . " WHERE (Pilot1 <> '' OR Pilot2 <> '') AND Glider <> '' AND `Time` <> 0 GROUP BY Glider";
$rsGliders = mysqli_query($PGCi, $query_rsGliders )  or die(mysqli_error($PGCi));
$row_rsGliders =mysqli_fetch_assoc($rsGliders);

$query_rs_instructors = "SELECT Pilot2, Count(Pilot2), Sum( `Time`) FROM " . $pgc_table . " WHERE Pilot2 <> '' AND Glider <> '' AND `Time` <> 0 GROUP BY Pilot2";
$rs_instructors = mysqli_query($PGCi, $query_rs_instructors )  or die(mysqli_error($PGCi));
$row_rs_instructors =mysqli_fetch_assoc($rs_instructors);

$query_rsTowpilot = "SELECT `Tow Pilot`, Count(`Tow Pilot`),Sum(`Tow Altitude`)  FROM " . $pgc_table . " WHERE (Pilot1 <> '' OR Pilot2 <> '') AND Glider <> '' AND `Time` <> 0 GROUP BY `Tow Pilot`  ";
$rsTowpilot = mysqli_query($PGCi, $query_rsTowpilot )  or die(mysqli_error($PGCi));
$row_rsTowpilot =mysqli_fetch_assoc($rsTowpilot);

$query_rsCFIGgrandTot = "SELECT Count(Pilot2), Sum( `Time`) FROM " . $pgc_table . " WHERE Pilot2 <> '' AND Glider <> '' AND `Time` <> 0 ";
$rsCFIGgrandTot = mysqli_query($PGCi, $query_rsCFIGgrandTot )  or die(mysqli_error($PGCi));
$row_rsCFIGgrandTot =mysqli_fetch_assoc($rsCFIGgrandTot);

$query_rsGLIDERGrandTot = "SELECT Count(Glider), Sum(`Time`) FROM " . $pgc_table . " WHERE (Pilot1 <> '' OR Pilot2 <> '') AND Glider <> '' AND `Time` <> 0 ";
$rsGLIDERGrandTot = mysqli_query($PGCi, $query_rsGLIDERGrandTot )  or die(mysqli_error($PGCi));
$row_rsGLIDERGrandTot =mysqli_fetch_assoc($rsGLIDERGrandTot);

$query_rsAOFGrandTot = "SELECT Count(Glider), Sum(`Time`) FROM " . $pgc_table . " WHERE (Pilot1 <> '' OR Pilot2 <> '') AND Glider <> '' AND `Time` <> 0 AND flight_type = 'AOF' ";
$rsAOFGrandTot = mysqli_query($PGCi, $query_rsAOFGrandTot )  or die(mysqli_error($PGCi));
$row_rsAOFGrandTot =mysqli_fetch_assoc($rsAOFGrandTot);

$query_rsTowPilotTotal = "SELECT  Count(`Tow Pilot`),Sum(`Tow Altitude`) FROM " . $pgc_table . " WHERE (Pilot1 <> '' OR Pilot2 <> '') AND Glider <> '' AND `Time` <> 0 ";
$rsTowPilotTotal = mysqli_query($PGCi, $query_rsTowPilotTotal )  or die(mysqli_error($PGCi));
$row_rsTowPilotTotal =mysqli_fetch_assoc($rsTowPilotTotal);

$query_rsMembers = "SELECT Pilot1, Count(Glider), Sum(`Time`) FROM " . $pgc_table . " WHERE (Pilot1 <> '' OR Pilot2 <> '') AND Glider <> '' AND `Time` > 0 GROUP BY Pilot1";
$rsMembers = mysqli_query($PGCi, $query_rsMembers )  or die(mysqli_error($PGCi));
$row_rsMembers =mysqli_fetch_assoc($rsMembers);

$query_rsMemberGtotals = "SELECT Count(Glider), Sum(`Time`) FROM " . $pgc_table . " WHERE (Pilot1 <> '' OR Pilot2 <> '') AND Glider <> '' AND `Time` <> 0 ";
$rsMemberGtotals = mysqli_query($PGCi, $query_rsMemberGtotals )  or die(mysqli_error($PGCi));
$row_rsMemberGtotals =mysqli_fetch_assoc($rsMemberGtotals);

$query_Glider_log = "SELECT glider, start_hours, pgc_hours, inspection_hours, inspection_date, delta_hours, total_hours, hour_display FROM pgc_gliders WHERE hour_display = 'Y' ORDER BY glider ASC";
$Glider_log = mysqli_query($PGCi, $query_Glider_log )  or die(mysqli_error($PGCi));
$row_Glider_log =mysqli_fetch_assoc($Glider_log);
;
$query_Table_Date = "SELECT date_format(`Date`,'%Y') as flight_year FROM " . $pgc_table . " Where `Date` <> '' LIMIT 1";
$Table_Date = mysqli_query($PGCi, $query_Table_Date )  or die(mysqli_error($PGCi));
$row_Table_Date =mysqli_fetch_assoc($Table_Date);
$flight_year = $row_Table_Date['flight_year'];

$query_Recordset2 = "SELECT date_format(`Date`,'%Y') as Mstart FROM pgc_flightsheet ";
$Recordset2 = mysqli_query($PGCi, $query_Recordset2 )  or die(mysqli_error($PGCi));
$row_Recordset2 =mysqli_fetch_assoc($Recordset2);
//$totalRows_Recordset2 = mysqli_num_rows($Recordset2);

$_SESSION['$Logdate'] = date("Y-m-d"); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>PGC Data Portal - Flightlog</title>
<?php 
//$style_filename =  dirname(__DIR__, 1) . '/css/pdp_style.css' ;
//echo '<link rel="stylesheet" href="' . $style_filename .'">';
?>
<style>
/* 
tr:nth-child(even) {background-color: #f2f2f2;}
tr:nth-child(odd) {background-color: #ffffff;}
*/
</style>

</head>
<body id="cb_pdp_body">
<div class="cb-pdp-main-box">
<div class="pdp-inner-box">
<table width="900" align="center" cellpadding="2" cellspacing="2" bordercolor="#000033"  >
    <tr>
      <td>
        <div align="center">
          <table width="97%" cellspacing="0" cellpadding="0" >
              <tr>
                <td width="23%" align="center">
                  <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
                    <input type="hidden" name="action" value="pdp_flight_export">
                    <input type="submit" value="YTD Activity.xls">
                 </form>
                </td>
                <td width="57%"><div align="center"><span class="fl_style1">PILOT DATA PORTAL</span></div></td>
              </tr>
              <tr>
                <td class="fl_style34"><div align="center"><strong ><a href=<?PHP echo home_url(); ?> >Members Page</a></strong></div></td>
                <td><div align="center"><span class="fl_style1" ><?php echo $flight_year. " " ?>FLIGHT SHEET METRICS (v2) </span></div></td>                        
					<?php echo "<td>
					  <form id='selectFlightYear' action='".get_admin_url()."admin-post.php' method='get'>" ;
					      echo "<input type='hidden' name='action' value='pdp-flight-metrics' />";    ?> 
								<input type='hidden' name='source_page' value='<?php the_permalink() ?>' >	   
                    			<select name="pgc_year" id = "pgc_year">
                    <?php
						do {  
							echo '<option value="';
							echo $row_Recordset1['history_table'] . '"';
                            if (!(strcmp($row_Recordset1['history_table'], $row_Recordset1['ops_year']))) {
                        	  echo "selected=\"selected\">";
                        	  } 
                        	  echo ">" . $row_Recordset1['ops_year'];
                              echo "</option>";
							} while ($row_Recordset1 =	mysqli_fetch_assoc($Recordset1));
  						$rows = mysqli_num_rows($Recordset1);
  						if($rows > 0) {
      						mysqli_data_seek($Recordset1, 0);
	  						$row_Recordset1 =mysqli_fetch_assoc($Recordset1);
						}
					  ?>
                    </select>
                    <input type="submit" name="Submit" value="Submit" />
                </form></td>
              </tr>
          </table>
        </div></td>
    </tr>
    <tr>
        <td height="458" valign="top">
          <table width="96%" border="0" align="center" cellpadding="0" cellspacing="0" class="cb-pdp-main-box" >
            <tr>
              <td height="304" valign="top"><table width="616" border="0" align="center" cellpadding="2" cellspacing="2">
            <tr>
              <td height="47" colspan="7" align="center" >
              <table width="92%" align="center" cellpadding="0" cellspacing="0">
            <tr class="fl_style34hd">
              <td height="22" >FLIGHT OPS TO DATE - REALTIME SEASONAL COMPARISON (Current year PGC activity - compared to the same date in prior seasons)
              scroll down for details.</td>
            </tr>
           </table>
        </td>
  </tr>                                
  <tr class="fl_style34hd">
      <td width="73"  >SEASON</td>
      <td width="89"  >TOTAL FLTS TO DATE</td>
      <td width="73"  >TRN FLTS</td>
      <td width="62"  >WINCH</td>
      <td width="95"  >TOTAL TIME</td>
      <td width="85"  >OPERATING DAYS</td>
      <td width="95"  >AVG FLTS PER OPS DAY</td>
   </tr>
     <?php 
     do {
           $test_year = $row_Recordset1['ops_year'];
           $flight_table = $row_Recordset1['history_table'];  
           
     //   $query = "SELECT '" .$test_year. "', Count(Glider), Sum(`Time`), COUNT(distinct `Date`)FROM " .$flight_table.  " WHERE (Pilot1 <> '' OR Pilot2 <> '') AND Glider <> '' AND `Date` <=  DATE_SUB(CURDATE(),INTERVAL 1 YEAR)";
          $query = "SELECT '" .$test_year. "', Count(Glider), Sum(`Time`), COUNT(distinct `Date`)FROM " .$flight_table.  " WHERE (Pilot1 <> '' OR Pilot2 <> '') AND Glider <> ''";
          $Flight = mysqli_query($PGCi, $query )  or die(mysqli_error($PGCi));
          $row_Flight=mysqli_fetch_assoc($Flight);
          $FlightAvg = round($row_Flight['Count(Glider)'] / $row_Flight['COUNT(distinct `Date`)'],1);
          
          $query = "SELECT '" .$test_year. "W', Count(Glider), Sum(`Time`)FROM " .$flight_table. " WHERE (Pilot1 <> '' OR Pilot2 <> '') AND Glider <> '' AND `Date` <=  DATE_SUB(CURDATE(),INTERVAL 1 YEAR) AND `Tow Pilot` = 'PGC Winch'";
          $Flight= mysqli_query($PGCi, $query )  or die(mysqli_error($PGCi));
          $row_FlightW =mysqli_fetch_assoc($Flight);
          
          // Modified in 2015 to count training flights as flights with an instructor
          $query = "SELECT '" .$test_year. "T' ,Count(Glider)FROM " .$flight_table. " WHERE Pilot1 <> '' AND Pilot2 <> '' AND Glider <> '' AND `Time` <> 0";
          $Flight = mysqli_query($PGCi, $query )  or die(mysqli_error($PGCi));
          $row_FlightT =mysqli_fetch_assoc($Flight);
          echo  '<tr class="fl_style34"><td align="center" > <a href="#" onClick="pdpJumpTo(\''.$flight_table.'\')" class="style3"><span >'.$test_year.' </span></a></td>';
     //     echo  '<tr><td align="center" > <a href="pgc_flightlog_metrics-v2.php?pgc_year='. $flight_table.' "class="style3"><span >'.$test_year.' </span></a></td>';
//          echo  '<tr><td align="center" >'. $test_year .'</td>';
          echo  '<td align="center" >'. $row_Flight['Count(Glider)'] . '</td>';
          echo  '<td align="center" >'. $row_FlightT['Count(Glider)'] . '</td>';
          echo  '<td align="center" >'. $row_FlightW['Count(Glider)'] . '</td>';
          echo  '<td align="center" >'. $row_Flight['Sum(`Time`)'] . '</td>';
          echo  '<td align="center" >'. $row_Flight['COUNT(distinct `Date`)'] . '</td>';
          echo  '<td align="center" >'. $FlightAvg  . '</td></tr> ';
          
      } while ($row_Recordset1 =	mysqli_fetch_assoc($Recordset1));
      ?> 
       <tr>
        <td height="46" colspan="7" align="center" ><table width="71%" align="center" cellpadding="0" cellspacing="0">
          <tr class="fl_style34hd">
                <td height="15" align="center"   ><span > Total Flights  include displayed TRN and WINCH counts</span></td>
          </tr>
          <tr class="fl_style34hd">
                <td height="15" align="center" ><span > A day is counted as 'operational' if one or more flights are logged</span></td>
          </tr>
          <tr class="fl_style34hd">
                <td height="15" align="center"  ><span >A flight is counted as TRN if a CFIG is on board (v2015)</span></td>
          </tr>
        </table>
       </td>
     </tr>                             
    </table>
   </td>
  </tr>
    <tr>
      <td height="114" valign="top">
        <table width="600" border="0" align="center" cellpadding="3" cellspacing="3" >
            <tr class="fl_style1">
                <td colspan="3" ><div align="center">
                <?php echo $flight_year. " " ?>FLIGHT ACTIVITY </div></td>
            </tr>
            
      <?php do { ?>
        <tr class="fl_style34" >
               <td ><div align="center" ><a href="#" onClick="pdpDetails('8', 'AOF','<?php echo $flight_year; ?>')"> AOF FLIGHTS </a></div></td>       
<!-- 
            <td width="250" ><div align="center"><strong><span ><a href="pgc_flightlog_lookup_AOF.php">AOF FLIGHTS</a></span></strong></div></td>
 -->
            <td width="150" ><div align="center"><strong><?php echo $row_rsAOFGrandTot['Count(Glider)']; ?></strong></div>
                  <div align="center"></div></td>
            <td ><div align="center" ><strong><?php echo $row_rsAOFGrandTot['Sum(`Time`)']; ?></strong></div></td>
        </tr>
       <?php } while ($row_rsAOFGrandTot =mysqli_fetch_assoc($rsAOFGrandTot)); ?>                        
            <tr class="fl_style34hd">
               <td width="250" ><div align="center" >PGC GLIDER </div></td>
               <td width="150" ><div align="center" >FLIGHTS</div></td>
               <td ><div align="center" >TOTAL HOURS </div></td>
            </tr>
            <?php 
            do { ?>
            <tr class="fl_style34">                 
               <td ><div align="center" ><a href="#" onClick="pdpDetails('0', '<?php echo $row_rsGliders['Glider']; ?>','<?php echo $flight_year; ?>')"> <?php echo $row_rsGliders['Glider']; ?></a></div></td>       
<!--            
               <td ><div align="center" ><a href="pgc_flightlog_lookup.php?recordID=<?php echo $row_rsGliders['Glider']; ?>"><?php echo $row_rsGliders['Glider']; ?></a></div></td>
 -->
               <td ><div align="center" ><?php echo $row_rsGliders['Count(Glider)']; ?></div></td>
               <td ><div align="center" ><?php echo $row_rsGliders['Sum(`Time`)']; ?></div></td>
            </tr>
            <?php } while ($row_rsGliders =mysqli_fetch_assoc($rsGliders)); ?>
        </table>
        <table width="600" border="0" align="center" cellpadding="3" cellspacing="3" >
          <?php do { ?>
            <tr >
                  <td class="fl_style34 width="250" ><div align="center"><strong><span >TOTALS </span></strong></div></td>
                  <td class="fl_style34 width="150" ><div align="center"><strong><?php echo $row_rsGLIDERGrandTot['Count(Glider)']; ?></strong></div>
                        <div align="center"></div></td>
                  <td class="fl_style34 ><div align="center"><strong><?php echo $row_rsGLIDERGrandTot['Sum(`Time`)']; ?></strong></div></td>
            </tr>
          <?php } while ($row_rsGLIDERGrandTot =mysqli_fetch_assoc($rsGLIDERGrandTot)); ?>
        </table></td>
      </tr>
      <tr>
            <td height="100" valign="top">&nbsp;
                  <table width="600" border="0" align="center" cellpadding="3" cellspacing="3" >
                        <tr class="fl_style34hd">
                              <td width="250" ><div align="center" >PGC INSTRUCTOR </div></td>
                              <td width="150" ><div align="center" >FLIGHTS</div></td>
                              <td ><div align="center" >TOTAL HOURS</div></td>
                        </tr>
                        <?php do { ?>
                        <tr class="fl_style34" >
                <td ><div align="center" ><a href="#" onClick="pdpDetails('1', '<?php echo $row_rs_instructors['Pilot2']; ?>','<?php echo $flight_year; ?>')"> <?php echo $row_rs_instructors['Pilot2']; ?></a></div></td>       
<!-- 
                              <td ><div align="center" ><strong><a href="pgc_flightlog_lookup_cfig.php?recordID=<?php echo $row_rs_instructors['Pilot2']; ?>"><?php echo $row_rs_instructors['Pilot2']; ?></a></strong></div></td>
 -->
                              <td ><div align="center" ><strong><?php echo $row_rs_instructors['Count(Pilot2)']; ?></div></td>
                              <td ><div align="center" ><strong><?php echo $row_rs_instructors['Sum( `Time`)']; ?></div></td>
                        </tr>
                        <?php } while ($row_rs_instructors =mysqli_fetch_assoc($rs_instructors)); ?>
                  </table>
                  <table width="600" border="0" align="center" cellpadding="3" cellspacing="3" >
                        <?php do { ?>
                        <tr class="fl_style34" >
                              <td width="250" ><div align="center"><strong><span >TOTALS </span></strong></div></td>
                              <td width="150" ><div align="center"><strong><?php echo $row_rsCFIGgrandTot['Count(Pilot2)']; ?></strong></div></td>
                              <td ><div align="center"><strong><?php echo $row_rsCFIGgrandTot['Sum( `Time`)']; ?></strong></div></td>
                        </tr>
                        <?php } while ($row_rsCFIGgrandTot =mysqli_fetch_assoc($rsCFIGgrandTot)); ?>
                  </table></td>
      </tr>
      <tr>
            <td height="99" valign="top">&nbsp;
                  <table width="600" border="0" align="center" cellpadding="3" cellspacing="3" >
                        <tr class="fl_style34hd">
                              <td width="250" ><div align="center" >TOW PILOT</div></td>
                              <td width="150" ><div align="center" >TOWS</div></td>
                              <td ><div align="center" >TOTAL ALTITUDE</div></td>
                        </tr>
                        <?php do { ?>
                        <tr class="fl_style34" >
                            <td ><div align="center" ><a href="#" onClick="pdpDetails('3', '<?php echo $row_rsTowpilot['Tow Pilot']; ?>','<?php echo $flight_year; ?>')"> <?php echo $row_rsTowpilot['Tow Pilot']; ?></a></div></td>       
<!-- 
                              <td ><div align="center" ><strong><a href="pgc_flightlog_lookup_towpilot.php?recordID=<?php echo $row_rsTowpilot['Tow Pilot']; ?>"><?php echo $row_rsTowpilot['Tow Pilot']; ?></div></td>
 -->
                              <td ><div align="center" ><strong><?php echo $row_rsTowpilot['Count(`Tow Pilot`)']; ?></div></td>
                              <td ><div align="center" ><strong><?php echo $row_rsTowpilot['Sum(`Tow Altitude`)']; ?></div></td>
                        </tr>
                        <?php } while ($row_rsTowpilot =mysqli_fetch_assoc($rsTowpilot)); ?>
                  </table>
                  <table width="600" border="0" align="center" cellpadding="3" cellspacing="3" >
                        <?php do { ?>
                        <tr class="fl_style34" >
                              <td width="250" ><div align="center"><span >TOTALS </span></div></td>
                              <td width="150" ><div align="center"><strong><?php echo $row_rsTowPilotTotal['Count(`Tow Pilot`)']; ?></strong></div></td>
                              <td ><div align="center"><strong><?php echo $row_rsTowPilotTotal['Sum(`Tow Altitude`)']; ?></strong></div></td>
                        </tr>
                        <?php } while ($row_rsTowPilotTotal =mysqli_fetch_assoc($rsTowPilotTotal)); ?>
                  </table></td>
      </tr>
      <tr>
            <td height="100" valign="top">&nbsp;
                  <table width="600" border="0" align="center" cellpadding="3" cellspacing="3" >
                        <tr class="fl_style34hd">
                              <td width="250" ><div align="center"><span >PGC MEMBER</span></div></td>
                              <td width="150" ><div align="center"><span >FLIGHTS</span></div></td>
                              <td ><div align="center"><span >TOTAL TIME</span></div></td>
                        </tr>
                        <?php do { ?>
                        <tr class="fl_style34" >
                             <td ><div align="center" ><a href="#" onClick="pdpDetails('2', '<?php echo $row_rsMembers['Pilot1']; ?>','<?php echo $flight_year; ?>')"> <?php echo $row_rsMembers['Pilot1']; ?></a></div></td>       

<!-- 
                              <td ><div align="center" ><a href="pgc_flightlog_lookup_member.php?recordID=<?php echo $row_rsMembers['Pilot1']; ?>"><?php echo $row_rsMembers['Pilot1']; ?></a></div></td>
 -->
                              <td ><div align="center"> <strong><?php echo $row_rsMembers['Count(Glider)']; ?></div></td>
                              <td ><div align="center"><strong><?php echo $row_rsMembers['Sum(`Time`)']; ?></div></td>
                        </tr>
                        <?php } while ($row_rsMembers =mysqli_fetch_assoc($rsMembers)); ?>
                  </table>
                  <table width="600" border="0" align="center" cellpadding="3" cellspacing="3" >
                        <?php do { ?>
                        <tr class="fl_style34" >
                              <td width="250" ><div align="center"><span >TOTALS </span></div></td>
                              <td width="150" ><div align="center"><strong><?php echo $row_rsMemberGtotals['Count(Glider)']; ?></div></td>
                              <td ><div align="center"><strong><?php echo $row_rsMemberGtotals['Sum(`Time`)']; ?></div></td>
                        </tr>
                        <?php } while ($row_rsMemberGtotals =mysqli_fetch_assoc($rsMemberGtotals)); ?>
                  </table></td>
      </tr>
      </table></td>
    </tr>
</table>
	<?php echo "<form id='selectMetricsDetails' action='".get_admin_url()."admin-post.php' method='get'>" ;   ?> 
	 <input type='hidden' name='action' value='pdp-metrics-details' />
	 <input type='hidden' name='pdp_type'  >	
	 <input type='hidden' name='pdp_id'  >	
	 <input type='hidden' name='req_year'  >	
</form>
</div>
</div>
<p>&nbsp;</p>
<p>&nbsp;</p>
</body>
</html>
<?php
mysqli_free_result($Flight);
mysqli_free_result($rsGliders);
mysqli_free_result($rs_instructors);
mysqli_free_result($rsTowpilot);
mysqli_free_result($rsCFIGgrandTot);
mysqli_free_result($rsGLIDERGrandTot);
mysqli_free_result($rsTowPilotTotal);
mysqli_free_result($rsMembers);
mysqli_free_result($rsMemberGtotals);
mysqli_free_result($Glider_log);
mysqli_free_result($Table_Date);
mysqli_free_result($Recordset2);
mysqli_free_result($Recordset1);
?>
 