<?php
//require dirname(__DIR__, 7) . '/Connections/PGC.php';
//require_once($filesafe . '/PGC.php');
// global $PGCwp; // database handle for accessing wordpress db
// global $PGCi;  // database handle for PDP external db
global $wpdb; 
$flight_table =  $wpdb->prefix . "cloud_base_pdp_flight_sheet";	 
?>
<?php 

error_reporting(E_ALL);
//ini_set('display_errors', 'On');
//require_once('pgc_check_login.php'); 
//$pgc_table = 'pgc_flightsheet';
?>
<?php

$maxRows_Flightlog = 20;
$pageNum_Flightlog = 0;
if (isset($_GET['req_year'])) {
	$req_year= $_GET['req_year'];
} else {
	$req_year= date('Y');
}
if (isset($_GET['pdp_metrics_page'])) {
	$pdp_metrics_page= $_GET['pdp_metrics_page'];
} else {
	$pdp_metrics_page= $_SERVER['HTTP_REFERER'] ;
}
if (isset($_GET['request_page']) && $_GET['request_page']!="") {
	$request_page= $_GET['request_page'];
} else {
	$request_page='0';
}

if (isset($_GET['pageNum_Flightlog'])) {
  $pageNum_Flightlog = $_GET['pageNum_Flightlog'];
}
$startRow_Flightlog = $request_page * $maxRows_Flightlog;

// $colname_Flightlog = "-1";
if (isset($_GET['pdp_type'])) {
  $pdp_type = $_GET['pdp_type'];
} else{
	$pdp_type = "Glider";
}
if (isset($_GET['pdp_id'])) {
  $pdp_id = $_GET['pdp_id'];
} else{
	$pdp_id = "BG";
}

$sql = $wpdb->prepare("SELECT count(*) FROM {$flight_table} WHERE %i = %s AND Time > 0 AND 
 						flightyear = %d", 
 						str_replace(" ","_",$pdp_type), $pdp_id, $req_year); 
$totalcount = $wpdb->get_var($sql);

$sql = $wpdb->prepare("SELECT * FROM {$flight_table} WHERE %i = %s AND Time > 0 AND 
 						flightyear = %d ORDER BY `Date` DESC LIMIT %d, %d", 
 						str_replace(" ","_",$pdp_type), $pdp_id, $req_year, $startRow_Flightlog, $maxRows_Flightlog); 
$results = $wpdb->get_results($sql);

if (isset($_GET['totalRows_Flightlog'])) {
  $totalRows_Flightlog = $_GET['totalRows_Flightlog'];
} 
else {
//   $all_Flightlog = mysqli_query($PGCi, $query_Flightlog ) ;
  $totalRows_Flightlog =$totalcount;
}
$totalPages_Flightlog = ceil($totalRows_Flightlog/$maxRows_Flightlog)-1;

$queryString_Flightlog = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_Flightlog") == false && 
        stristr($param, "totalRows_Flightlog") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_Flightlog = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_Flightlog = sprintf("&totalRows_Flightlog=%d%s", $totalRows_Flightlog, $queryString_Flightlog);
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
.style24 {color: #000000}
.style25 {font-size: 18px; font-weight: bold; color: #000000; }
.style27 {font-size: 18px; font-weight: bold; color: #FFFFFF; }
a:link {
	color: #FFFF9B;
}
a:visited {
	color: #FFFF9B;
}
.style30 {font-size: 16px}
.style31 {font-size: 16px; font-weight: bold; color: #000000; }
.style32 {font-size: 16px; color: #000000; }
-->
</style>
</head>

<body>
<table width="100%" align="center" cellpadding="2" cellspacing="2" bordercolor="#000033" bgcolor="#666666">
    <tr>
        <td><div align="center">
            <table width="100%">
                <tr>
                    <td width="11%">&nbsp;</td>
                  <td width="9%"><div align="center"></div></td>
                    <td width="59%"><div align="center"><span class="style1">PGC DATA PORTAL - FLIGHT SHEET for <?php echo $_GET['pdp_id'] ?> for year: 
                     <?php echo $req_year ?>
                    </span></div></td>
                  <td width="9%" class="style25"><div align="center"></div></td>
                    <td width="12%">&nbsp;</td>
                </tr>
            </table>
            </div></td>
    </tr>
    <tr>
        <td height="481"><table width="100%" height="447" align="center" cellpadding="2" cellspacing="2" bordercolor="#005B5B" bgcolor="#4F5359">
            
            <tr>
                <td height="373" valign="top"><table width="99%" align="center" cellpadding="2" cellspacing="2" bgcolor="#666666">
                                <tr>
                                    <td bgcolor="#66CCFF" class="style1 style24"><div align="center" class="style30">Key</div></td>
                                    <td bgcolor="#66CCFF" class="style25"><div align="center"><span class="style30">Date</span></div></td>
                                    <td bgcolor="#66CCFF" class="style25"><div align="center"><span class="style30">Type</span></div></td>
                                    <td bgcolor="#66CCFF" class="style25"><div align="center" class="style30">Glider</div></td>
                                    <td bgcolor="#66CCFF" class="style25"><div align="center" class="style30">Member Charged </div></td>
                                    <td bgcolor="#66CCFF" class="style25"><div align="center" class="style30">Instructor</div></td>
                                    <td bgcolor="#66CCFF" class="style25"><div align="center" class="style30">Takeoff</div></td>
                                    <td bgcolor="#66CCFF" class="style25"><div align="center" class="style30">Landing</div></td>
                                    <td bgcolor="#66CCFF" class="style25"><div align="center" class="style30">Hours</div></td>
                                    <td bgcolor="#66CCFF" class="style25"><div align="center" class="style30">Tow </div></td>
                                    <td bgcolor="#66CCFF" class="style25"><div align="center" class="style30">Tug</div></td>
                                    <td bgcolor="#66CCFF" class="style25"><div align="center" class="style30">Tow Pilot</div></td>
                                    <td bgcolor="#66CCFF" class="style25"><div align="center" class="style30">Charge</div></td>
                                    <td bgcolor="#66CCFF" class="style25"><div align="center" class="style30">Notes</div></td>
                                </tr>
                                <?php 
                                foreach($results as $flight ){
//      		       var_dump($pilot);
//      		       die();
     		       	echo('<tr>');
     		       		echo('<td bgcolor="#FFFFFF" class="style25"><div align="center" class="style32">'  .$flight->yearkey. '</div></td>');
  						echo('<td bgcolor="#FFFFFF" class="style31">' . $flight->Date. '</div></td>');
  						echo('<td bgcolor="#FFFFFF" class="style31">' . $flight->Flight_Type. '</div></td>');
  						echo('<td bgcolor="#FFFFFF" class="style31">' . $flight->Glider. '</div></td>');
  						echo('<td bgcolor="#FFFFFF" class="style31">' . $flight->Pilot1. '</div></td>');
  						echo('<td bgcolor="#FFFFFF" class="style31">' . $flight->Pilot2. '</div></td>');
  						echo('<td bgcolor="#FFFFFF" class="style25"><div align="center" class="style32">' .  $flight->Takeoff . '</div></td>');
 						echo('<td bgcolor="#FFFFFF" class="style25"><div align="center" class="style32">' .  $flight->Landing . '</div></td>');
 						echo('<td bgcolor="#FFFFFF" class="style25"><div align="center" class="style32">' .  $flight->Time . '</div></td>');
 						echo('<td bgcolor="#FFFFFF" class="style25"><div align="center" class="style32">' .  $flight->Tow_Altitude . '</div></td>');
 						echo('<td bgcolor="#FFFFFF" class="style25"><div align="center" class="style32">' .  $flight->Tow_Plane . '</div></td>');
 						echo('<td bgcolor="#FFFFFF" class="style25"><span align="center" class="style32">' .  $flight->Tow_Pilot . '</span></td>');
 						echo('<td bgcolor="#FFFFFF" class="style25"><div align="center" class="style32">' .  $flight->Tow_Charge . '</div></td>');
						echo('<td bgcolor="#FFFFFF" class="style31"><div align="center" class="style32">' .  $flight->Notes . '</div></td>');

     		       	echo('</tr>');
     		       }   
//      		       	echo('<tr class="fl_style34" ><td><div align="center" >Totals</div></td>')    ;     
//      		       	echo('<td><div align="center" >'. $total_towpilots[0]->gcount .'</div></td>')    ;     
//      		       	echo('<td><div align="center" >'. $total_towpilots[0]->altitude .'</div></td>')    ;  
//      		       	echo('</tr>')    ;                                       
//                                 
                                
 ?>
                    </table>
                    <p>
                    <table border="0" width="50%" align="center">
                        <td width="23%" align="center">Page:</td>
                        	<?php
                         	$max_page = ceil($totalcount/$maxRows_Flightlog);
                         	for ($i =0; $i<$max_page ; $i++ ){
                         		echo '<td width="15px" align="center"  class="fl_style1"> <a   href='.admin_url('admin-post.php').
                         		sprintf("?request_page=%s&action=pdp-metrics-details&req_year=%s&pdp_type=%s&pdp_id=%s&pdp_metrics_page=%s", 
                    				$i,  $req_year, rawurlencode($pdp_type), rawurlencode($pdp_id), $pdp_metrics_page ) .">". $i . '</a></td>';                  				
                         	}
                         	
                        	?>
                        </tr> 

                    </table>
                    </p></td>
            </tr>
            <tr>
                <td height="28"><div align="center"><strong ><a href=<?php echo $pdp_metrics_page ?> >Back to Metrics Page</a</strong></div></td>
            </tr>
        </table></td>
    </tr>
</table>

<p>&nbsp;</p>
<p>&nbsp;</p>
</body>
</html>
<?php
// mysqli_free_result($Flightlog);
?>