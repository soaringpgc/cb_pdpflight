<?php

namespace CB_PdpFlightlog\Inc\Frontend;

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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cb-pdpflightlog-frontend.js', array( 'jquery' ), $this->version, false );		
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

// 		$defaults['loop-template'] 	= $this->plugin_name . '-loop';
// 		$defaults['order'] 			= 'date';
// 		$defaults['quantity'] 		= 100;
// 		$args						= shortcode_atts( $defaults, $atts, 'nowhiring' );
// 		$shared 					= new Now_Hiring_Shared( $this->plugin_name, $this->version );
// 		$items 						= $shared->get_openings( $args );
// 
// 		if ( is_array( $items ) || is_object( $items ) ) {

//			include now_hiring_get_template( $args['loop-template'] );
			include ('views/html_cb_pdpflightlog_list_edit.php');

// 		} else {
// 
// 			echo $items;
// 
// 		}

		$output = ob_get_contents();

		ob_end_clean();

		return $output;

	} // flight_log()	
	public function flight_metrics( $atts = array() ) {

		ob_start();

// 		$defaults['loop-template'] 	= $this->plugin_name . '-loop';
// 		$defaults['order'] 			= 'date';
// 		$defaults['quantity'] 		= 100;
// 		$args						= shortcode_atts( $defaults, $atts, 'nowhiring' );
// 		$shared 					= new Now_Hiring_Shared( $this->plugin_name, $this->version );
// 		$items 						= $shared->get_openings( $args );
// 
// 		if ( is_array( $items ) || is_object( $items ) ) {

//			include now_hiring_get_template( $args['loop-template'] );
			include views/html_cb_pdpflightlog_metrics.php;

// 		} else {
// 
// 			echo $items;
// 
// 		}

		$output = ob_get_contents();

		ob_end_clean();

		return $output;

	} // flight_metrics()	
		/**
		 * This function adds a new fligh to the flight log.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
     public function pdp_flight_log_add(){
        require dirname(__DIR__, 6) . '/Connections/PGC.php';
         
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
		 * This function brings up the flight details page.
		 *
		 */
     public function pdp_flight_log_details(){ 
     	if (isset($_GET['key'])) {
     		include_once( 'views/html_cb_pdpflightlog_update.php');
     	}else {
     		wp_redirect($_GET['source_page']);
     	}
     }

		/**
		 * This function brings up the flight details page.
		 *
		 */
     public function pdp_update_time(){
        require dirname(__DIR__, 6) . '/Connections/PGC.php';
    
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
	 * Registers all shortcodes at once
	 *
	 * @return [type] [description]
	 */
	public function register_shortcodes() {

		add_shortcode( 'flight_log', array( $this, 'flight_log' ) );
		add_shortcode( 'flight_metrics', array( $this, 'flight_metrics' ) );

	} // register_shortcodes()
		/**
		 * This function redirects to the longin page if the user is not logged in.
		 *
		 */
     public function pdp_no_login(){
     	wp_redirect(home_url());
     } //
     

}
