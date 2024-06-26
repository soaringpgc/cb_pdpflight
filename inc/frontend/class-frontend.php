<?php

namespace CB_PdpFlightlog\Inc\Frontend;
// date_default_timezone_set('America/New_York');
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
  		wp_register_style('jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.1/themes/base/jquery-ui.css');
    	wp_enqueue_style('jquery-ui');

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

//NOTE :NTFS!!!!  enqueue_scripts and add_inline script moved to the shortcode callback so 
// it is not call when NOT needed.!

//         wp_register_script( 'Flight_log_templates',  plugins_url('/cb-pdpflightlog/inc/frontend/js/template.js'));
//    	wp_register_script( 'Flight_log_app',  plugins_url('/cb-pdpflightlog/inc/frontend/js/flight_log_app.js'));
// 		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cb-pdpflightlog-frontend.js', array( 'jquery', 'backbone',
// 			'underscore', 'Flight_log_app', 'Flight_log_templates'), $this->version, false );		

	}
	public function flight_log( $atts = array() ) {

    	$atts = array_change_key_case( (array) $atts, CASE_LOWER );
	    $flight_atts = shortcode_atts(array( 'view_only'=>"true", 'new'=>"false"), $atts);

 		if (isset( $flight_atts['new'] )) {
 			$new_log = $flight_atts['new']==='true' ? true : false ;
 		}

		if ($new_log  === true  ){ 		
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cb-pdpflightlog-frontend.js', array( 'wp-api',  'backbone', 'underscore'
 				), $this->version, false );		
		} else {
		
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cb-pdpflightlog-traditional.js', array( 'wp-api',  'backbone', 'underscore'
 				), $this->version, false );						
		}

  		$dateToBePassed = array(
     		'ajax_url' =>  admin_url('admin-ajax.php'),
     		'post_url' =>  admin_url('admin-post.php'),
    		'root' => esc_url_raw( rest_url() ),
     		'nonce' => wp_create_nonce( 'wp_rest' ),
     		'success' => __( 'Flight Has been updated!', 'your-text-domain' ),
     		'failure' => __( 'Your submission could not be processed.', 'your-text-domain' ),
     		'current_user_id' => get_current_user_id()
 
    		);   	
    	wp_add_inline_script( $this->plugin_name, 'const passed_vars = ' . json_encode ( $dateToBePassed  ), 'before'
    	); 		

		ob_start();
 			if ($new_log){
 				include ('views/html_cb_pdpflightlog_list_edit.php');	
 					
 			} else {								
				include ('views/html_traditional_flight_log.php');		
			}
			
		$output = ob_get_contents();
		ob_end_clean();
		return $output;

	} // flight_log()	
			
	public function flight_metrics( $atts = array() ) {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cb-pdpflightlog-frontend.js', array( 
 				), $this->version, false );		
		ob_start();
//      Experment in passing parameters in shortcodes. parameters are passed in the 
//      $atts array, below code sets up defaults and overrides defaults with passed 
//      values. "shortcode_atts()" thinking of using this as a switch to control
//      how Shortcode functions. 
// 
	    $atts = array_change_key_case( (array) $atts, CASE_LOWER );
	    $metrcis_atts = shortcode_atts(array( 'title'=>"dummy title"), $atts );
 
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
//      	global $wpdb;
//      	$flight_table =  $wpdb->prefix . "cloud_base_pdp_flight_sheet";	 
// modified to use REST call 

		if ( !current_user_can('flight_edit')){
			wp_redirect( wp_login_url() );
		}
  		$request = new \WP_REST_Request('POST', '/cloud_base/v1/pdp_flightlog');
  		$response = rest_do_request($request);

      	wp_redirect($_GET['source_page']);
     	exit();
     }	// pdp_flight_log_add()
	/**
	 * This function brings up the flight details page. This is where glider, pilot
	 * instructor, tow pilot and tug are selected. Also corrections can be make to 
	 * take off/landing time and tow alitude. 
	 */
     public function pdp_flight_log_details(){ 
     	if (isset($_GET['id'])) {
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
		global $wpdb; 
		$flight_table =  $wpdb->prefix . 'cloud_base_pdp_flight_sheet';		
    
     	if (isset($_POST['key'])) {
     		$key = $_POST['key'];
     		if($_POST['start'] == '1'){
 				$wpdb->update($flight_table, array('Takeoff'=> $_POST['thetime']), array('id'=> $key)); 
     		} else {
     			$sql = $wpdb->prepare( "SELECT `Takeoff` FROM  {$flight_table} WHERE `id` = %d", $key);
     			$start_time = \DateTime::createFromFormat('H:i:s', $wpdb->get_var($sql));			
     			$landing_time =\DateTime::createFromFormat('H:i:s', $_POST['thetime']);
     			$delta = $landing_time->diff($start_time);
     			$dec_delta = round($delta->h + $delta->i/60, 2, PHP_ROUND_HALF_UP); 		
     			$wpdb->update($flight_table, array('Landing'=> $_POST['thetime'], 'Time'=>$dec_delta), array('id'=> $key)); 
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
		global $wpdb; 
		$flight_table =  $wpdb->prefix . 'cloud_base_pdp_flight_sheet';		

//     	global $PGCi;
//     	
     	if (isset($_POST['flight_year']) && $_POST['flight_year'] != date("Y") && $_POST['flight_year'] != "") {
    			$flight_year = $_POST['flight_year'];
//    			$sql_query = "Select * From  pgc_flightsheet". "_". $flight_year;
  		} else {
   			$flight_year =  date('Y');
//   			$sql_query = "Select * From  pgc_flightsheet"; 
  		}   
 		$sql = $wpdb->prepare( "SELECT * FROM {$flight_table} WHERE flightyear = %d", $flight_year);
//  		$result = mysqli_query($PGCi, $sql_query) or die('Query failed!');	    	
		$result = $wpdb->get_results($sql, ARRAY_A);
        $filename = "pgc_flight_activity_" . $flight_year . ".xls";
    
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        header("Pragma: no-cache");
     	header("Expires: 0");

		$output = fopen('php://output', 'w');
             // display field/column names as first row 
		$existing_columns = $wpdb->get_col("DESC {$flight_table}", 0);
        fputcsv( $output, $existing_columns);

        foreach($result as $values){
        	fputcsv( $output, $values);

         }        
        
    } //pdp_export_data()
	public function pdp_flight_log($atts = array() ){
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );
 		global $wpdb; 
		$flight_table =  $wpdb->prefix . 'cloud_base_pdp_flight_sheet';	
		$sql = $wpdb->prepare("SELECT yearkey FROM {$flight_table} WHERE `flightyear`=%s ORDER BY yearkey DESC LIMIT 1",  
					date("Y"));				
		$last_yearkey = $wpdb->get_var($sql); 	
    	wp_register_script( 'flight_log_templates',  plugins_url('/cb_pdpflightlog/inc/frontend/js/template.js'));
    	wp_register_script( 'backbone_getters',  plugins_url('/cb_pdpflightlog/inc/libraries/backbone.getters.setters.js'));
    	
//     	wp_register_script('dualStorage','https://cdnjs.cloudflare.com/ajax/libs/Backbone.dualStorage/1.4.1/backbone.dualstorage.min.js');
// 	    wp_register_script( 'validation',  plugins_url('/cloudbase/includes/backbone-validation-min.js'));	
	    
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cb_pdp_flightlog.js', array( 'wp-api',  'backbone', 'underscore', 'backbone_getters',
		'validation', 'flight_log_templates', 'jquery-ui-datepicker'), $this->version, false );

// 		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/flight_log.js', array( 'wp-api',  'backbone', 'underscore', 
// 		'validation', 'flight_log_templates', 'jquery-ui-datepicker'), $this->version, false );


    		$dateToBePassed = array(
 				'root' => esc_url_raw( rest_url() ),
 				'nonce' => wp_create_nonce( 'wp_rest' ),
 				'success' => __( 'Data Has been updated!', 'your-text-domain' ),
 				'failure' => __( 'Your submission could not be processed.', 'your-text-domain' ),
 				'current_user_id' => get_current_user_id(),
 				'last_yearkey' =>  $last_yearkey	    	
     		);   	
     		wp_add_inline_script( $this->plugin_name, 'const cloud_base_public_vars = ' . json_encode ( $dateToBePassed  ), 'before'
     		);


		include_once 'views/html_cb_pdp_flightlog.php';
//		return display_flights();
	}   // pdp_flight_log
	public function personal_log($atts = array() ){
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );
		include_once 'views/html_cb_personal_log.php';

	}   // personal_log		
	/**
	 * Registers all shortcodes at once
	 *
	 * @return [type] [description]
	 */
	public function register_shortcodes() {

		add_shortcode( 'cb_pgc_flight_log', array( $this, 'flight_log' ) );
		add_shortcode( 'cb_pgc_flight_metrics', array( $this, 'flight_metrics' ) );
		add_shortcode( 'pdp_flight_log', array( $this, 'pdp_flight_log' ) );
		add_shortcode( 'personal_log', array( $this, 'personal_log' ) );

	} // register_shortcodes()
	/**
	 * This function redirects to the longin page if the user is not logged in.
	 *
	 */
     public function pdp_no_login(){
     	wp_redirect(home_url());
     } //
}
