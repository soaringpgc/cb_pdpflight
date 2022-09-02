<?php

namespace CB_PdpFlightlog\Inc\Frontend;
date_default_timezone_set('America/New_York');
//exit(date_default_timezone_get());
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @link       http://pgcsoaring.com
 * @since      1.0.0
 *
 * @author    Philadelphia Glider Council -- Dave Johnson
 */
class Frontend {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The text domain of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_text_domain    The text domain of this plugin.
	 */
	private $plugin_text_domain;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since       1.0.0
	 * @param       string $plugin_name        The name of this plugin.
	 * @param       string $version            The version of this plugin.
	 * @param       string $plugin_text_domain The text domain of this plugin.
	 */
	public function __construct( $plugin_name, $version, $plugin_text_domain ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->plugin_text_domain = $plugin_text_domain;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cb-pdpflightlog-frontend.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . 'flightlog', plugin_dir_url( __FILE__ ) . 'css/cb-public-flightlog.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
        wp_register_script( 'Flight_log_templates',  plugins_url('/cb-pdpflightlog/inc/frontend/js/template.js'));
    	wp_register_script( 'Flight_log_app',  plugins_url('/cb-pdpflightlog/inc/frontend/js/flight_log_app.js'));

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cb-pdpflightlog-frontend.js', array( 'jquery', 'backbone',
			'underscore', 'Flight_log_app', 'Flight_log_templates'), $this->version, false );		
    	wp_localize_script( $this->plugin_name, 'PDP_FLIGHT_SUBMITTER', array(
    		'ajax_url' =>  admin_url('admin-ajax.php'),
    		'root' => esc_url_raw( rest_url() ),
     		'nonce' => wp_create_nonce( 'wp_rest' ),
     		'success' => __( 'Flight Has been updated!', 'your-text-domain' ),
     		'failure' => __( 'Your submission could not be processed.', 'your-text-domain' ),
     		'current_user_id' => get_current_user_id()
    		)	
    	);	

	}
	public function flight_log( $atts = array() ) {
		ob_start();
	    	$atts = array_change_key_case( (array) $atts, CASE_LOWER );
	    	$flight_atts = shortcode_atts(array( 'view_only'=>"true", 'new'=>"false"), $atts);
			$new_log = false;
			
 			if (isset( $flight_atts['new'] )) {
 				$new_log = $flight_atts['new']==='true' ? true : false ;
 			}
 			if ($new_log){
 				include ('views/html_cb_pdpflightlog.php');		
 					
 			} else {				
				include ('views/html_cb_pdpflightlog_list_edit.php');	
			}
			
		$output = ob_get_contents();

		ob_end_clean();

		return $output;

	} // flight_log()	
	
	
	public function pdp_flight_log(){ 
     	if (isset($_GET['pgc_year'])) {
     		wp_redirect($_GET['source_page'].'?pgc_year='.$_GET['pgc_year']);
     	}elseif (isset($_GET['flight_date']) ) {
     		wp_redirect($_GET['source_page'].'?flight_date='.$_GET['flight_date']);
     	} else {
     		wp_redirect($_GET['source_page']);
     	}
     }
			
	public function flight_metrics( $atts = array() ) {

		ob_start();
//      Experment in passing parameters in shortcodes. parameters are passed in the 
//      $atts array, below code sets up defaults and overrides defaults with passed 
//      values. "shortcode_atts()" thinking of using this as a switch to control
//      how Shortcode functions. 
// 
	    $atts = array_change_key_case( (array) $atts, CASE_LOWER );
	    $metrcis_atts = shortcode_atts(array( 'title'=>"dummy title"), $atts, $tag );
 
			include ('views/html_cb_pdpflightlog_metrics.php');

		$output = ob_get_contents();

		ob_end_clean();

		return $output;

	} // flight_metrics()	
		/**
		 * This function adds a new fligh to the flight log.
		 *
		 * The Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
     public function pdp_flight_log_add(){
        global $PGCwp; // database handle for accessing wordpress db
		global $PGCi;  // database handle for PDP external db
		if ( !current_user_can('flight_edit')){
			wp_redirect( wp_login_url() );
		}
         // tow pilot and tug are stored in database as it changes less often than pilot. 
        $LastPilot ="";
        $query_Recordset1 = "SELECT LastPilot, TowPlane FROM pgc_flightlog_lastpilot";
        $Recordset1 = mysqli_query($PGCi, $query_Recordset1 )  or die(mysqli_error($PGCi));         
        $row_Recordset1 =mysqli_fetch_assoc($Recordset1);
        $totalRows_Recordset1 = mysqli_num_rows($Recordset1);
        $LastPilot = $row_Recordset1['LastPilot'];
        $Tow_Plane = $row_Recordset1['TowPlane'];
     	$PGCwp->insert('pgc_flightsheet', array( 'Date'=>date("Y-m-d"), 'Tow Plane'=>$Tow_Plane, 'Tow Pilot'=>$LastPilot, 'Time'=>"0.0" ));
      	wp_redirect($_GET['source_page']);
     	exit();
     }	// pdp_flight_log_add()
	/**
	 * This function brings up the flight details page. This is where glider, pilot
	 * instructor, tow pilot and tug are selected. Also corrections can be make to 
	 * take off/landing time and tow alitude. 
	 */
     public function pdp_flight_log_details(){ 
     	if (isset($_GET['key'])) {
     		include_once( 'views/html_cb_pdpflightlog_update.php');
     	}else {
     		wp_redirect($_GET['source_page']);
     	}
     } //pdp_flight_log_details()
 /**
 * This function updates the takeoff and landing time. 
 *  if varable $_POST['start'] is "1" (true), it updates the take off time if 
 *  anything else it update landing time. It is called via admin-ajax and javascript. 
 *
 */
     public function pdp_update_time(){
		global $PGCwp; // database handle for accessing wordpress db
		global $PGCi;  // database handle for PDP external db
    
     	if (isset($_POST['key'])) {
     		$key = $_POST['key'];
     		if($_POST['start'] == '1'){
     			$PGCwp->update('pgc_flightsheet', array('Takeoff'=> $_POST['thetime']), array('Key'=> $key)); 
     		} else {
     			$sql = $PGCwp->prepare( "SELECT `Takeoff` FROM  pgc_flightsheet WHERE `Key` = %d", $key);
     			$start_time = \DateTime::createFromFormat('H:i:s', $PGCwp->get_var($sql));			
     			$landing_time =\DateTime::createFromFormat('H:i:s', $_POST['thetime']);
     			$delta = $landing_time->diff($start_time);
     			$dec_delta = round($delta->h + $delta->i/60, 2, PHP_ROUND_HALF_UP); 		
     			$PGCwp->update('pgc_flightsheet', array('Landing'=> $_POST['thetime'], 'Time'=>$dec_delta), array('Key'=> $key)); 
     		}
      	}		
     } //pdp_update_time()    
/**
 * 
 *  The function brings up the flight metrics summery page. 
 * 
 */
	public function pdp_flight_metrics(){ 
    	if (isset($_GET['pgc_year'])) {
    		wp_redirect($_GET['source_page'].'?pgc_year='.$_GET['pgc_year']);
    		exit();
    	}else {
    		wp_redirect($_GET['source_page']);
    		exit();
    	}
    } // pdp_flight_metrics()
/**
 * 
 *  This function brings up meterics details. Main page displays summary data.
 *  This function displays details of Pilot, istructor, tow pilot, Glider or Tow Plane.
 */
	Public function pdp_metrics_details(){ 
    	if (isset($_GET['pdp_type']) && isset($_GET['pdp_id'])) {
    	include_once( 'views/html_cb_pdpflightlog_lookup.php');
    //		$redirect = plugin_dir_url(__FILE__) ."partials/pgc_flightlog_lookup.php?pgc_type=".$_GET['pdp_type']."&pgc_id=". $_GET['pdp_id']."&req_year=". $_GET['req_year'];
    //		wp_redirect($redirect);
    	}else {
    		wp_redirect($_GET['source_page']);
    		exit();
    	}
    } // pdp_metrics_details()
/**
 *  This function exports the year to date flights in .xls format.
 *  primaraly used by the Treasurer to download the flights for billing
 *  Very important! 
 */     
    public function pdp_export_data(){
    	global $PGCi;
         $filename = "pgc_flight_activity_" . date('Ymd') . ".xls";
       
         header("Content-Disposition: attachment; filename=\"$filename\"");
         header("Content-Type: application/vnd.ms-excel");
         header("Pragma: no-cache");
         header("Expires: 0");
       
         $flag = false;
         $result = mysqli_query($PGCi, "Select * From  pgc_flightsheet") or die('Query failed!');
         
        while($row = mysqli_fetch_assoc($result)) {
           if(!$flag) {
             // display field/column names as first row
             echo implode("\t", array_keys($row)) . "\r\n";  //https:wordpress.stackexchange.com/tags
             $flag = true;
           }
           array_walk($row,  array( $this ,'cleanData'));
           echo implode("\t", array_values($row)) . "\r\n";   
         }
         exit;
    } //pdp_export_data()
               	
	/**
	 * Registers all shortcodes at once
	 *
	 * @return [type] [description]
	 */
	public function register_shortcodes() {

		add_shortcode( 'cb_pgc_flight_log', array( $this, 'flight_log' ) );
		add_shortcode( 'cb_pgc_flight_metrics', array( $this, 'flight_metrics' ) );

	} // register_shortcodes()
	/**
	 * This function redirects to the longin page if the user is not logged in.
	 *
	 */
     public function pdp_no_login(){
     	wp_redirect(home_url());
     } //
     public function cleanData(&$str)
     {
        // escape tab characters
        $str = preg_replace("/\t/", "\\t", $str);
        // escape new lines
        $str = preg_replace("/\r?\n/", "\\n", $str);
        // convert 't' and 'f' to boolean values
        if($str == 't') $str = 'TRUE';
        if($str == 'f') $str = 'FALSE';
    
        // force certain number/date formats to be imported as strings
        if(preg_match("/^0/", $str) || preg_match("/^\+?\d{8,}$/", $str) || preg_match("/^\d{4}.\d{1,2}.\d{1,2}/", $str)) {
          $str = "'$str";
        }
    
        // escape fields that include double quotes
        if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
     }     
}
