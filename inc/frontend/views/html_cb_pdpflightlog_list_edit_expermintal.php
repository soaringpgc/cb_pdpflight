<?php
global $PGCwp; // database handle for accessing wordpress db
global $PGCi;  // database handle for PDP external db

$view_only = true; 
if (isset( $flight_atts['view_only'] )) {
	$view_only = $flight_atts['view_only']==='true' ? true : false ;
	if (!$view_only && !current_user_can('flight_edit')){
		wp_redirect( wp_login_url() );
	}
	if(!$view_only && current_user_can('cb_edit_dues')){
		$flight_updater = true; 
	}	
}
//$view_only= true ;
//$flight_updater = false; 
?>
<?php
error_reporting(E_ALL);
//ini_set('display_errors', 'On');

// date_default_timezone_set('America/New_York');

if(isset($_GET['flight_date'])){
	$pgc_flight_date = preg_replace("([^0-9-])", "", $_GET['flight_date']);
} else {
	$pgc_flight_date = date("Y-m-d");
}
$currentPage = $_SERVER["PHP_SELF"];

$maxRows_Flightlog = 10;
$pageNum_Flightlog = 0;
if (isset($_GET['pageNum_Flightlog'])) {
  $pageNum_Flightlog = $_GET['pageNum_Flightlog'];
}
$startRow_Flightlog = $pageNum_Flightlog * $maxRows_Flightlog;

// $query_Flightlog = sprintf("SELECT * FROM pgc_flightsheet WHERE `Date` = '%s' ORDER BY `Key` DESC", $pgc_flight_date);
// $query_limit_Flightlog = sprintf("%s LIMIT %d, %d", $query_Flightlog, $startRow_Flightlog, $maxRows_Flightlog);
// 
// $Flightlog = mysqli_query($PGCi, $query_limit_Flightlog )  or die(mysqli_error($PGCi));
// $row_Flightlog =mysqli_fetch_assoc($Flightlog);

$sql = $PGCwp->prepare( "SELECT * FROM pgc_flightsheet WHERE `Date` = '%s' ORDER BY `Key` DESC  LIMIT %d, %d",  $pgc_flight_date, $startRow_Flightlog, $maxRows_Flightlog );
$Flightlog = $PGCwp->get_results($sql ); 

$todaycount = $PGCwp->get_var($PGCwp->prepare("SELECT COUNT(*) FROM pgc_flightsheet WHERE `Date` = %s", $pgc_flight_date )) ;
$totalcount = $PGCwp->get_var("SELECT COUNT(*) FROM pgc_flightsheet" ) ;

// if ($row_Flightlog === null ){
// 	$row_Flightlog = array('Key'=>null, 'Glider'=>'N/A', 'Flight_Type'=>'N/A', 'Pilot1'=>'Pilot', 'Pilot2'=>'instructor', 'Takeoff'=>null,
// 	'Landing'=>null, 'Time'=>null, 'Tow Altitude'=>'0', 'Tow Plane'=>null, 'Tow Pilot'=>'tow pilot', 'Tow Charge'=>'0', 'Notes'=>'');
// }

// if (isset($_GET['totalRows_Flightlog'])) {
//   $totalRows_Flightlog = $_GET['totalRows_Flightlog'];
// } else {
//  $all_Flightlog = mysqli_query($PGCi, $query_Flightlog ) ;
//  $totalRows_Flightlog = mysqli_num_rows($all_Flightlog);
//}
$totalPages_Flightlog = ceil($todaycount/$maxRows_Flightlog)-1;

$queryString_Flightlog = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
//     if (stristr($param, "pageNum_Flightlog") == false && 
//         stristr($param, "totalRows_Flightlog") == false) {

    if (stristr($param, "pageNum_Flightlog") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_Flightlog = "&" . htmlentities(implode("&", $newParams));
  }
}
//$queryString_Flightlog = sprintf("&totalRows_Flightlog=%d", $totalRows_Flightlog);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>PGC Data Portal - Flightlog</title>

</head>

<body id="cb_pdp_body">
<table width:"100%"; height:"100%" align="center" cellpadding="2" cellspacing="2" bordercolor="#000033" bgcolor="#666666">
  <tr width="100%">
        <th><div align="center">
            <table width="100%">
                <tr>   
                	<td width="5%"> </td>                
 					<td width="8%" <?php if ( !$view_only ){ echo ' bgcolor="#FF9900"'; } ?> >
 						<div align="center">
 							<span class="fl_style25">
							<?php if ( !$view_only ){ 					
								echo ('<form action="'. admin_url("admin-post.php").'" method="get">');
                    			echo ('<input type="hidden" name="action" value="pdp_flight_log_add">');
                    	//		echo ('<input type="hidden" name="page_id" value=' . get_the_id(). '>');
                    			echo ('<input type="hidden" name="source_page" value="' );
                    			echo  the_permalink() ;	
                    			echo ('"><input type="submit" value="Add Flight">
                 			</form>');
                 			} ?> 
							</span>
						</div>
					</td>
                    <td width="73%"><div align="center"><span class="fl_style1"> PGC DATA PORTAL - FLIGHT SHEET for <?php echo$pgc_flight_date ?></span></div></td>
             		<?php if ( $view_only )  {     
             			echo ('<td></td>');
             		} else {
             		//	echo ('<td width="8%" bgcolor="#006633"><div align="center"><a href="pgc_flightsheet_help.php" class="fl_style25">HELP</a></div></td> ' );
             			echo ('<td width="8%" bgcolor="#006633"><div align="center"><button class="open">Help</button></div></td> ' );
             		}
             		?>
             	
                    <td width="5%"class="fl_style1"><?php echo 'TDA: ' . $todaycount ?> </td>    
                 
                                 		<!--Creates the popup body-->
<div class="popup-overlay">
<!--Creates the popup content-->
    <h4>PGC PDP Help<button class="close">Close</button>  </h4> 
 <div class="popup-content">

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
      </tr>                                  
    	<?php if ( $flight_updater )  {  
    	// show edit bar if admin or treasurer. 
    		echo ('<tr class="fl_style1"><td colspan="2">To Edit Enter:</td><td colspan="4"> <div align="center" style="float:left;">');		
 					echo ('<form action="'. admin_url("admin-post.php").'" method="get">');
           			echo ('<input type="hidden" name="action" value="pdp-flight-log-details">');
           	     	echo ('<input type="number" min="1" max="'.$totalcount .'"name="key" >');
//           			echo ('<input type="hidden" name="source_page" value="' );
//           			echo  the_permalink() ;	
           			echo ('"><input type="submit" value="Enter Flight No.">OR </form></div>');   
           	echo ('<div align="center" style="float:left;">')	;	
           			echo ('<form action="'. admin_url("admin-post.php").'" method="get">');
           			echo ('<input type="hidden" name="action" value="pdp-flight-log">');
           	     	echo ('<input type="date" name="flight_date" >');
           			echo ('<input type="hidden" name="source_page" value="' );
           			echo  the_permalink() ;	
           			echo ('"><input type="submit" value="Enter Date."> </form></div>');                  		   		
    		echo (' </tr>');    		
    		} ?>                           
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
                            <?php if ( !$view_only )  {  
                            	echo ('<td width="1" bgcolor="#66CCFF" class="fl_style25"><div align="center"></div></td>');
                            } ?>
                            <td bgcolor="#66CCFF" class="fl_style25"><div align="center">Takeoff</div></td>
                            <?php if ( !$view_only )  {  
                            	echo ('<td width="1" bgcolor="#66CCFF" class="fl_style25"></td>');
                            } ?> 
                            <td bgcolor="#66CCFF" class="fl_style25"><div align="center">Landing</div></td>
                            <td bgcolor="#66CCFF" class="fl_style25"><div align="center">Hours</div></td>
                            <td bgcolor="#66CCFF" class="fl_style25"><div align="center">Tow </div></td>
                            <td bgcolor="#66CCFF" class="fl_style25"><div align="center">Tug</div></td>
                            <td bgcolor="#66CCFF" class="fl_style25"><div align="center">Tow Pilot</div></td>
                            <td bgcolor="#66CCFF" class="fl_style25"><div align="center">Charge</div></td>
                            <td bgcolor="#66CCFF" class="fl_style25"><div align="center">Notes</div></td>
                        </tr>
<!-- 
                        <?php // if ($row_Flightlog != null ){ do { ?>
 -->
						<?php if ($Flightlog != null ){ foreach( $Flightlog as $row_Flightlog){ ?>
                        <tr >
                             <td bgcolor="#999999" class="fl_style25"><div align="center">  
                             <?php if ( !$view_only )  {                                                                                        
                             ?>                                                                                          
                       		    <form action="<?php echo admin_url('admin-post.php'); ?>" method="get">
                         	    	<input type="hidden" name="action" value="pdp-flight-log-details">
                         	    	<input type='hidden' name='key' value='<?php echo $row_Flightlog->Key; ?>' >	
                         	    	<input type='hidden' name='source_page' value='<?php  the_permalink() ?>' >	 	 
                         	    	<input type="submit" value="<?php echo $row_Flightlog->Key; ?>">
                      		    </form>
                      		 <?php  } else{ 
                      		 	echo $row_Flightlog->Key;
                      		 	}
                      		 ?>  
                             </div></td>
                             <td bgcolor="#FFFFFF" class="fl_style25" align="center"><?php echo $row_Flightlog->Glider; ?></td>
                             <td bgcolor="#FFFFFF" class="fl_style25"><div align="center"><?php echo $row_Flightlog->Flight_Type; ?></div></td>
                             <td bgcolor="#FFFFFF" class="fl_style25"><?php echo $row_Flightlog->Pilot1; ?></td>
                             <td bgcolor="#FFFFFF" class="fl_style25"><?php echo $row_Flightlog->Pilot2; ?></td>
                             <?php if ( !$view_only )  {  
                                 echo ('<td bgcolor="#FFFFFF" ><button type="button"') ;
                                 echo ($row_Flightlog->Key); 
                                 echo (' align="center" class="pdp_update_time button-flightlog button-start" value="'); 
                                 echo ($row_Flightlog->Key.'" data-start=1 ></button></td>');
                               }; ?> 
                             <td bgcolor="#FFFFFF" class="fl_style25"><div align="center"><?php echo $row_Flightlog->Takeoff; ?></div></td>

                             <?php if ( !$view_only )  {  
                                 echo ('<td bgcolor="#FFFFFF" ><button type="button"') ;
                                 echo ($row_Flightlog->Key); 
                                 echo (' align="center" class="pdp_update_time button-flightlog button-stop" value="'); 
                                 echo ($row_Flightlog->Key.'" data-start="0"></button></td>');
								}; ?> 
                       
                             <td bgcolor="#FFFFFF" class="fl_style25"><div align="center"><?php echo $row_Flightlog->Landing; ?></div></td>
                             <td bgcolor="#FFFFFF" class="fl_style25"><div align="center"><?php echo $row_Flightlog->Time; ?></div></td>
                             <td bgcolor="#FFFFFF" class="fl_style25"><div align="center"><?php echo $row_Flightlog->{'Tow Altitude'}; ?></div></td>
                             <td bgcolor="#FFFFFF" class="fl_style25"><div align="center"><?php echo $row_Flightlog->{'Tow Plane'}; ?></div>                                    </td>
                             <td bgcolor="#FFFFFF" class="fl_style25"><?php echo $row_Flightlog->{'Tow Pilot'}; ?></td>
                             <td bgcolor="#FFFFFF" class="fl_style25"><?php echo $row_Flightlog->{'Tow Charge'}; ?></td>
                             <td width="20" nowrap="nowrap" bgcolor="#FFFFFF" class="fl_style25"><?php echo substr($row_Flightlog->Notes,0,10); ?></td>
                         </tr>
                         <?php };} ?>
                     </table>
                    <p>
                    <table border="0" width="50%" align="center">
                        <tr>
                            <td width="23%" align="center" class="fl_style1"><?php if ($pageNum_Flightlog > 0) { // Show if not first page ?>
                                        <span class="fl_style1"><strong><a href="<?php printf("%s?pageNum_Flightlog=%d", remove_query_arg("pageNum_Flightlog"), 0); ?>"class="fl_style1">Top</a>
                                        <?php } // Show if not first page ?>
                            </td>
                            <td width="31%" align="center" class="fl_style1"><?php if ($pageNum_Flightlog > 0) { // Show if not first page ?>
                                        <a href="<?php printf("%s?pageNum_Flightlog=%d", remove_query_arg("pageNum_Flightlog"), max(0, $pageNum_Flightlog - 1)); ?>" class="fl_style1">Previous</a>
                                        <?php } // Show if not first page ?>                            
                           </td>
                            <td width="23%" align="center" class="fl_style1"><?php if ($pageNum_Flightlog < $totalPages_Flightlog) { // Show if not last page ?>
                                        <a href="<?php  printf("%s?pageNum_Flightlog=%d", remove_query_arg("pageNum_Flightlog"), min($totalPages_Flightlog, $pageNum_Flightlog + 1)); ?>"class="fl_style1">Next</a>
                                        <?php } // Show if not last page ?>                           
                            </td>
                            <td width="23%" align="center" class="fl_style1"><?php if ($pageNum_Flightlog < $totalPages_Flightlog) { // Show if not last page ?>
                                        <a href="<?php  printf("%s?pageNum_Flightlog=%d", remove_query_arg("pageNum_Flightlog"), $totalPages_Flightlog); ?>"class="fl_style1">Bottom</a>
                                        <?php } // Show if not last page ?>                            
                            </td>
                        </tr>
                    </table>
                    </p></td>
            </tr>
            <tr>
                <td height="28"><div align="center"><strong class="fl_style3"><a class="fl_style16"></a></strong></div></td>
            </tr>
        </table>
      </td>
    </tr> 
</table>
<p>&nbsp;</p>
<p>&nbsp;</p>
</body>
</html>
<?php
?>