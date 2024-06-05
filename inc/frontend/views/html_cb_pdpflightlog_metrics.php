<?php
global $wpdb; // database handle for accessing wordpress db
// global $PGCi;  // database handle for PDP external db
$flight_table =  $wpdb->prefix . "cloud_base_pdp_flight_sheet";	 
//	    echo  $metrcis_atts['title'];
?>
<?php

error_reporting(E_ALL);
////ini_set('display_errors', 'On');
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
// $query_Recordset1 = "SELECT * FROM pgc_flight_tables ORDER by ops_year DESC";
// $Recordset1 = mysqli_query($PGCi, $query_Recordset1 )  or die(mysqli_error($PGCi));
// $row_Recordset1 =mysqli_fetch_assoc($Recordset1);
// $totalRows_Recordset1 = mysqli_num_rows($Recordset1);

$flight_years = $wpdb->get_results('SELECT DISTINCT flightyear FROM ' . $flight_table . ' ORDER BY flightyear DESC'); 

//var_dump($totalRows_Recordset1);
if (isset($pgc_year_session)) 
$pgc_table = $pgc_year_session;
ELSE
$pgc_table = 'pgc_flightsheet';

if (strlen($pgc_table)> 16){
	$pgcyear = substr($pgc_table,16,4);
} else {
	$pgcyear = date('Y');
}
$aircraftTable =  $wpdb->prefix . "cloud_base_aircraft";	

$sql = "SELECT * " . $aircraftTable .  " WHERE valid_until is NULL AND aircraft_type between 0 and 4"; 
$aircraft = $wpdb->get_results($sql); 
 
// $sql= "SELECT Glider, Count(Glider) as gcount, Sum(`Time`) as gtime FROM " .$flight_table. " WHERE  Glider <> '' AND flightyear = " . $pgcyear ." AND Time <> 0 GROUP BY Glider";
// $gliders = $wpdb->get_results($sql); 

// modifited to caculate hours since last 100 hour inspection 
$sql= "SELECT f.Glider,
	SUM(CASE WHEN f.Glider <> '' AND f.flightyear = " . $pgcyear ." AND f.Time <> 0 AND a.valid_until is null THEN f.Time  ELSE 0 END) as gtime,
	SUM(CASE WHEN f.Glider <> '' AND f.flightyear = " . $pgcyear ." AND f.Time <> 0 AND a.valid_until is null THEN 1  ELSE 0 END) as gcount,
	SUM(CASE When f.Glider <> '' AND f.DATE > a.last_100_date AND Time <> 0 THEN f.Time ELSE 0 END) as fr100hr,
	SUM(CASE When f.Glider <> '' AND f.DATE > a.last_100_date AND Time <> 0 THEN 1 ELSE 0 END) as countfr100hr  	  	
	FROM " .$flight_table ." f  JOIN " . $aircraftTable . " a WHERE f.Glider = a.compitition_id GROUP BY f.Glider";
 	
$gliders = $wpdb->get_results($sql); 

$sql= "SELECT Count(Glider) as gcount, Sum(`Time`) as gtime FROM " .$flight_table. " WHERE (Pilot1 <> '' OR Pilot2 <> '') AND Glider <> '' AND flightyear = " . $pgcyear ." AND Time <> 0";
$total_gliders = $wpdb->get_results($sql); 

$sql= "SELECT Pilot2, Count(Pilot2) as gcount, Sum(`Time`) as gtime FROM " .$flight_table. " WHERE  Pilot2 <> '' AND Glider <> '' AND flightyear = " . $pgcyear ." AND Time <> 0 GROUP BY Pilot2";
$instructors = $wpdb->get_results($sql); 

$sql= "SELECT Count(Pilot2) as gcount, Sum(`Time`) as gtime FROM " .$flight_table. " WHERE  Pilot2 <> '' AND Glider <> '' AND flightyear = " . $pgcyear ." AND Time <> 0";
$total_instructors = $wpdb->get_results($sql); 

$sql= "SELECT Tow_Pilot, Count(Tow_Pilot) as gcount, Sum(`Tow_Altitude`) as altitude FROM " .$flight_table. " WHERE  Tow_Pilot <> '' AND Glider <> '' AND flightyear = " . $pgcyear ." AND Time <> 0 GROUP BY Tow_Pilot";
$towpilots = $wpdb->get_results($sql); 

$sql= "SELECT  Count(Tow_Pilot) as gcount, Sum(`Tow_Altitude`) as altitude FROM " .$flight_table. " WHERE (Pilot1 <> '' OR Pilot2 <> '') AND Glider <> '' AND `Time` <> 0 AND flightyear = " . $pgcyear ;
$total_towpilots = $wpdb->get_results($sql); 

$sql= "SELECT  Count(Glider) as gcount, Sum(`Time`) as gtime FROM " .$flight_table. " WHERE (Pilot1 <> '' OR Pilot2 <> '') AND Glider <> '' AND flight_type = 'AOF' AND `Time` <> 0 AND flightyear = " . $pgcyear ;
$total_AOF = $wpdb->get_results($sql); 

$sql= "SELECT Pilot1, Count(Pilot1) as gcount, Sum(`Time`) as gtime FROM " .$flight_table. " WHERE  Pilot1 <> '' AND Glider <> '' AND flightyear = " . $pgcyear ." AND Time <> 0 GROUP BY Pilot1";
$members = $wpdb->get_results($sql); 

$sql= "SELECT Count(Pilot1) as gcount, Sum(`Time`) as gtime FROM " .$flight_table. " WHERE  Pilot1 <> '' AND Glider <> '' AND flightyear = " . $pgcyear ." AND Time <> 0";
$total_members = $wpdb->get_results($sql); 

$flight_year  = $pgcyear;

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
                    <input type="hidden" name="flight_year" value=<?php echo $flight_year. " " ?>>
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
                    foreach($flight_years as $year){		
                    	echo '<option value="pgc_flightsheet_'.$year->flightyear .'">' .$year->flightyear .'</option>';
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
		foreach($flight_years as $year){						
			$sql = $wpdb->prepare ("SELECT Count(Glider) as gcount, 
						Sum(`Time`) as ttime, 
						COUNT(distinct `Date`) as odays, 
						sum( case when Tow_Pilot = 'PGC Winch' then 1 else 0 end) AS winch, 
						sum( case when Pilot2 != '' then 1 else 0 end ) AS instruction 
						FROM {$flight_table}  WHERE (Pilot1 <> '' OR Pilot2 <> '') AND Glider <> '' AND flightyear = %d ", $year->flightyear); 

			$result = $wpdb->get_results($sql);
            echo  '<tr class="fl_style34"><td align="center" > <a href="#" onClick="pdpJumpTo( \'pgc_flightsheet_'.$year->flightyear.'\')" class="style3"><span >'.$year->flightyear.' </span></a></td>';
            echo  '<td align="center" >'. $result[0]->gcount . '</td>';
            echo  '<td align="center" >'. $result[0]->instruction . '</td>';
          	echo  '<td align="center" >'. $result[0]->winch  . '</td>';		
          	echo  '<td align="center" >'. $result[0]->ttime  . '</td>';		
            echo  '<td align="center" >'. $result[0]->odays  . '</td>';		
            echo  '<td align="center" >'. round($result[0]->gcount/$result[0]->odays,1 ) . '</td>';			
		}
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
        <tr class="fl_style34" >
               <td ><div align="center" ><a href="#" onClick="pdpDetails('8', 'AOF','<?php echo $flight_year; ?>')"> AOF FLIGHTS </a></div></td>       
            <td width="150" ><div align="center"><strong><?php echo  $total_AOF[0]->gcount ; ?></strong></div>
                  <div align="center"></div></td>
            <td ><div align="center" ><strong><?php echo $total_AOF[0]->gtime; ?></strong></div></td><td></td>
        </tr>
                     
            <tr class="fl_style34hd">
               <td width="250" ><div align="center" >PGC GLIDER </div></td>
               <td width="150" ><div align="center" >FLIGHTS</div></td>
               <td ><div align="center" >TOTAL HOURS </div></td>
               <td ><div align="center" >HOURS since 100</div></td>
            </tr>
            <?php 
            foreach($gliders as $glider){
            	echo('<tr class="fl_style34"> ');
            	echo('<td ><div align="center" ><a href="#" onClick="pdpDetails(`0`, `' . $glider->Glider. '`,`'. $flight_year. '`)">  ' .$glider->Glider. '</a></div></td>');
            	echo('<td ><div align="center" >' . $glider->gcount. '</div></td>');
            	echo('<td ><div align="center" >' . $glider->gtime .'</div></td>');
            	echo('<td ><div align="center" >' . $glider->fr100hr .'</div></td>');
            	echo('</tr>');
            }   
            	echo('<tr class="fl_style34" ><td><div align="center" >Totals</div></td>')    ;     
            	echo('<td><div align="center" >'. $total_gliders[0]->gcount .'</div></td>')    ;     
            	echo('<td><div align="center" >'. $total_gliders[0]->gtime .'</div></td>')    ;  
            	echo('</tr>')
			 ?>
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
                        <?php 
 
     		       foreach($instructors as $instructor){
     		       	echo('<tr class="fl_style34"> ');
     		       	echo('<td ><div align="center" ><a href="#" onClick="pdpDetails(`1`, `' .$instructor->Pilot2. '`,`'. $flight_year. '`)">  ' .$instructor->Pilot2. '</a></div></td>');
     		       	echo('<td ><div align="center" >' . $instructor->gcount. '</div></td>');
     		       	echo('<td ><div align="center" >' . $instructor->gtime .'</div></td>');
     		       	echo('</tr>');
     		       }   
     		       	echo('<tr class="fl_style34" ><td><div align="center" >Totals</div></td>')    ;     
     		       	echo('<td><div align="center" >'. $total_instructors[0]->gcount .'</div></td>')    ;     
     		       	echo('<td><div align="center" >'. $total_instructors[0]->gtime .'</div></td>')    ;  
     		       	echo('</tr>')    ;                                          
     		        ?>
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
                        <?php 
                         
     		       foreach($towpilots as $pilot){
//      		       var_dump($pilot);
//      		       die();
     		       	echo('<tr class="fl_style34"> ');
     		       	echo('<td ><div align="center" ><a href="#" onClick="pdpDetails(`3`, `' .$pilot->Tow_Pilot. '`,`'. $flight_year. '`)">  ' .$pilot->Tow_Pilot. '</a></div></td>');
     		       	echo('<td ><div align="center" >' . $pilot->gcount. '</div></td>');
     		       	echo('<td ><div align="center" >' . $pilot->altitude .'</div></td>');
     		       	echo('</tr>');
     		       }   
     		       	echo('<tr class="fl_style34" ><td><div align="center" >Totals</div></td>')    ;     
     		       	echo('<td><div align="center" >'. $total_towpilots[0]->gcount .'</div></td>')    ;     
     		       	echo('<td><div align="center" >'. $total_towpilots[0]->altitude .'</div></td>')    ;  
     		       	echo('</tr>')    ;                                          

 					?>
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
                        <?php
                          
     		       foreach($members as $pilot){
     		       	echo('<tr class="fl_style34"> ');
     		       	echo('<td ><div align="center" ><a href="#" onClick="pdpDetails(`2`, `' .$pilot->Pilot1. '`,`'. $flight_year. '`)">  ' .$pilot->Pilot1. '</a></div></td>');
     		       	echo('<td ><div align="center" >' . $pilot->gcount. '</div></td>');
     		       	echo('<td ><div align="center" >' . $pilot->gtime .'</div></td>');
     		       	echo('</tr>');
     		       }   
     		       	echo('<tr class="fl_style34" ><td><div align="center" >Totals</div></td>')    ;     
     		       	echo('<td><div align="center" >'. $total_members[0]->gcount .'</div></td>')    ;     
     		       	echo('<td><div align="center" >'. $total_members[0]->gtime .'</div></td>')    ;  
     		       	echo('</tr>')    ;                           
                                            
					 ?>
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

// mysqli_free_result($Table_Date);
// mysqli_free_result($Recordset2);
// mysqli_free_result($Recordset1);
?>
 