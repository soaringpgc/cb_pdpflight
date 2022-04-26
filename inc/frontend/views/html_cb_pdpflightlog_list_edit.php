<?php
namespace pdp\flight\log;
require dirname(__DIR__, 7) . '/Connections/PGC.php';
//require_once($filesafe . '/PGC.php');
?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

?>
<?php 
date_default_timezone_set('America/New_York');
$pgc_flight_date = date("Y-m-d");
// $_SESSION['$Logdate'] = date("Y-m-d");
// $_SESSION['last_query'] = "http://" .  $_SERVER["SERVER_NAME"] . $_SERVER["PHP_SELF"] . "?" . $_SERVER['QUERY_STRING']; ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
//  

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
}

$currentPage = $_SERVER["PHP_SELF"];

$maxRows_Flightlog = 10;
$pageNum_Flightlog = 0;
if (isset($_GET['pageNum_Flightlog'])) {
  $pageNum_Flightlog = $_GET['pageNum_Flightlog'];
}
$startRow_Flightlog = $pageNum_Flightlog * $maxRows_Flightlog;

// $colname_Flightlog = "-1";
// if (isset($_SESSION['$Logdate'])) {
// 
//    $colname_Flightlog  = $_SESSION['$Logdate'];
// }
$query_Flightlog = sprintf("SELECT * FROM pgc_flightsheet WHERE `Date` = '%s' ORDER BY `Key` DESC", $pgc_flight_date);
$query_limit_Flightlog = sprintf("%s LIMIT %d, %d", $query_Flightlog, $startRow_Flightlog, $maxRows_Flightlog);

$Flightlog = mysqli_query($PGCi, $query_limit_Flightlog )  or die(mysqli_error($PGCi));
$row_Flightlog =mysqli_fetch_assoc($Flightlog);

// if ($row_Flightlog === null ){
// 	$row_Flightlog = array('Key'=>null, 'Glider'=>'N/A', 'Flight_Type'=>'N/A', 'Pilot1'=>'Pilot', 'Pilot2'=>'instructor', 'Takeoff'=>null,
// 	'Landing'=>null, 'Time'=>null, 'Tow Altitude'=>'0', 'Tow Plane'=>null, 'Tow Pilot'=>'tow pilot', 'Tow Charge'=>'0', 'Notes'=>'');
// }

if (isset($_GET['totalRows_Flightlog'])) {
  $totalRows_Flightlog = $_GET['totalRows_Flightlog'];
} else {
  $all_Flightlog = mysqli_query($PGCi, $query_Flightlog ) ;
  $totalRows_Flightlog = mysqli_num_rows($all_Flightlog);
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
<style>
.fl_style1 {	font-size: 18px;
	font-weight: bold;
}
body {
	background-color: #333333;
}
body,td,th {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
	color: black;
}
tr{
 white-space: nowrap
}
.fl_style3 {font-size: 16px; font-weight: bold; }
.fl_style16 {color: #CCCCCC; }
.fl_style24 {color: #000000}
.fl_style25 {font-size: 18px; font-weight: bold; color: #000000; }
.fl_style27 {font-size: 18px; font-weight: bold; color: #555555; }
a:link {
	color: #FFFF9B;
}
a:visited {
	color: #FFFF9B;
}
.fl_style29 {
	color: #7DFF7D;
	font-weight: bold;
	font-fl_style: italic;
	font-size: 16px;
}
.fl_style30 {
	color: #FFFFFF;
	font-weight: bold;
	font-fl_style: italic;
}
</style>
</head>

<body>
<table width:"100%"; height:"100%" align="center" cellpadding="2" cellspacing="2" bordercolor="#000033" bgcolor="#666666">
  <tr width="100%">
        <th><div align="center">
            <table width="100%">
                <tr>   
                	<td width="5%"> </td>                
 					<td width="8%" bgcolor="#FF9900">
 						<div align="center">
 							<span class="fl_style25">
                  			<form action="<?php echo admin_url('admin-post.php'); ?>" method="get">
                    			<input type="hidden" name="action" value="pdp_flight_log_add">
                    			<input type='hidden' name='source_page' value='<?php  the_permalink() ?>' >	 	 
                    			<input type="submit" value="Add Flight">
                 			</form>
							</span>
						</div>
					</td>
                    <td width="73%"><div align="center"><span class="fl_style1"> PGC DATA PORTAL - FLIGHT SHEET for <?php echo$pgc_flight_date ?></span></div></td>
                    <td width="8%" bgcolor="#006633"><div align="center"><a href="pgc_flightsheet_help.php" class="fl_style25">HELP</a></div></td>
                    <td width="5%"> </td>    
                </tr>
            </table>
            </div></td>
    </tr>
    <tr>
        <td height="481">
          <table width="100%" height="447" align="center" cellpadding="2" cellspacing="2" bordercolor="#005B5B" bgcolor="#4F5359">  
            <tr width="100%" >
                <td height="373" colspan="5" valign="top">
                   <table width="99%" align="center" cellpadding="2" cellspacing="2" bgcolor="#000066">
                        <tr  width="100%" white-space: nowrap>
                            <td bgcolor="#66CCFF" class="fl_style1 fl_style24"><div align="center">Flight</div></td>
                            <td bgcolor="#66CCFF" class="fl_style25"><div align="center">GLDR</div></td>
                            <td bgcolor="#66CCFF" class="fl_style25"><div align="center">Type</div></td>
                            <td bgcolor="#66CCFF" class="fl_style25"  ><div align="center">Member</div></td>
                            <td bgcolor="#66CCFF" class="fl_style25" "><div align="center">Instructor</div></td>
                            <td width="1" bgcolor="#66CCFF" class="fl_style25"><div align="center"></div></td>
                            <td bgcolor="#66CCFF" class="fl_style25"><div align="center">Takeoff</div></td>
                            <td width="1" bgcolor="#66CCFF" class="fl_style25"></td>
                            <td bgcolor="#66CCFF" class="fl_style25"><div align="center">Landing</div></td>
                            <td bgcolor="#66CCFF" class="fl_style25"><div align="center">Hours</div></td>
                            <td bgcolor="#66CCFF" class="fl_style25"><div align="center">Tow </div></td>
                            <td bgcolor="#66CCFF" class="fl_style25"><div align="center">Tug</div></td>
                            <td bgcolor="#66CCFF" class="fl_style25"><div align="center">Tow Pilot</div></td>
                            <td bgcolor="#66CCFF" class="fl_style25"><div align="center">Charge</div></td>
                            <td bgcolor="#66CCFF" class="fl_style25"><div align="center">Notes</div></td>
                        </tr>
                        <?php if ($row_Flightlog != null ){ do { ?>
                        <tr >
                             <td bgcolor="#999999" class="fl_style25"><div align="center">                                
                       		    <form action="<?php echo admin_url('admin-post.php'); ?>" method="get">
                         	    	<input type="hidden" name="action" value="pdp-flight-log-details">
                         	    	<input type='hidden' name='key' value='<?php echo $row_Flightlog['Key']; ?>' >	
                         	    	<input type='hidden' name='source_page' value='<?php  the_permalink() ?>' >	 	 
                         	    	<input type="submit" value="<?php echo $row_Flightlog['Key']; ?>">
                      		    </form>
                             </div></td>
                             <td bgcolor="#FFFFFF" class="fl_style25" align="center"><?php echo $row_Flightlog['Glider']; ?></td>
                             <td bgcolor="#FFFFFF" class="fl_style25"><div align="center"><?php echo $row_Flightlog['Flight_Type']; ?></div></td>
                             <td bgcolor="#FFFFFF" class="fl_style25"><?php echo $row_Flightlog['Pilot1']; ?></td>
                             <td bgcolor="#FFFFFF" class="fl_style25"><?php echo $row_Flightlog['Pilot2']; ?></td>
                             <td bgcolor="#FFFFFF" class="fl_style25"><button <?php echo $row_Flightlog['Key']; ?> align="center" class="pdp_update_time" value="<?php echo $row_Flightlog['Key']; ?>" data-start=1> <img src=<?php echo plugin_dir_url('FILE' )?>pdp_flightlog/flightlog_images/Takeoff.jpg alt="Takeoff" width="25" height="24" border="0" /></button></td>
                             <td bgcolor="#FFFFFF" class="fl_style25"><div align="center"><?php echo $row_Flightlog['Takeoff']; ?></div></td>
                             <td bgcolor="#FFFFFF" class="fl_style25"><button <?php echo $row_Flightlog['Key']; ?> align="center" class="pdp_update_time" value="<?php echo $row_Flightlog['Key']; ?>" data-start=0> <img src=<?php echo plugin_dir_url('FILE' )?>pdp_flightlog/flightlog_images/Landing.jpg alt="Landing" width="25" height="24" border="0" /></button></td>
                             <td bgcolor="#FFFFFF" class="fl_style25"><div align="center"><?php echo $row_Flightlog['Landing']; ?></div></td>
                             <td bgcolor="#FFFFFF" class="fl_style25"><div align="center"><?php echo $row_Flightlog['Time']; ?></div></td>
                             <td bgcolor="#FFFFFF" class="fl_style25"><div align="center"><?php echo $row_Flightlog['Tow Altitude']; ?></div></td>
                             <td bgcolor="#FFFFFF" class="fl_style25"><div align="center"><?php echo $row_Flightlog['Tow Plane']; ?></div>                                    </td>
                             <td bgcolor="#FFFFFF" class="fl_style25"><?php echo $row_Flightlog['Tow Pilot']; ?></td>
                             <td bgcolor="#FFFFFF" class="fl_style25"><?php echo $row_Flightlog['Tow Charge']; ?></td>
                             <td width="20" nowrap="nowrap" bgcolor="#FFFFFF" class="fl_style25"><?php echo substr($row_Flightlog['Notes'],0,25); ?></td>
                         </tr>
                         <?php } while ($row_Flightlog = mysqli_fetch_assoc($Flightlog));} ?>
                     </table>
                    <p>

                    <table border="0" width="50%" align="center">
                        <tr>
                            <td width="23%" align="center" class="fl_style27"><?php if ($pageNum_Flightlog > 0) { // Show if not first page ?>
                                        <span class="fl_style1"><strong><a href="<?php printf("%s?pageNum_Flightlog=%d%s", $currentPage, 0, $queryString_Flightlog); ?>">Top</a>
                                        <?php } // Show if not first page ?></td>
                            <td width="31%" align="center" class="fl_style27"><?php if ($pageNum_Flightlog > 0) { // Show if not first page ?>
                                        <a href="<?php printf("%s?pageNum_Flightlog=%d%s", $currentPage, max(0, $pageNum_Flightlog - 1), $queryString_Flightlog); ?>" class="fl_style1">Previous</a>
                                        <?php } // Show if not first page ?>                            </td>
                            <td width="23%" align="center" class="fl_style27"><?php if ($pageNum_Flightlog < $totalPages_Flightlog) { // Show if not last page ?>
                                        <a href="<?php printf("%s?pageNum_Flightlog=%d%s", $currentPage, min($totalPages_Flightlog, $pageNum_Flightlog + 1), $queryString_Flightlog); ?>">Next</a>
                                        <?php } // Show if not last page ?>                            </td>
                            <td width="23%" align="center" class="fl_style27"><?php if ($pageNum_Flightlog < $totalPages_Flightlog) { // Show if not last page ?>
                                        <a href="<?php printf("%s?pageNum_Flightlog=%d%s", $currentPage, $totalPages_Flightlog, $queryString_Flightlog); ?>">Bottom</a>
                                        <?php } // Show if not last page ?>                            </td>
                        </tr>
                    </table>
                    </p></td>
            </tr>
            <tr>
                <td height="28"><div align="center"><strong class="fl_style3"><a class="fl_style16"></a></strong></div></td>
            </tr>
        </table></td>
    </tr> 
  
</table>
<p>&nbsp;</p>
<p>&nbsp;</p>
</body>
</html>
<?php
mysqli_free_result($Flightlog);

/*mysqli_free_result($Recordset1);*/
?>