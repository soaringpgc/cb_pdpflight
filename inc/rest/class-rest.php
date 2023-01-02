<?php
namespace CB_PdpFlightlog\Inc\Rest;
/**
 * The rest functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Cloud_Base
 * @subpackage Cloud_Base/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and examples to create your REST access
 * methods. Don't forget to validate and sanatize incoming data!
 *
 * @package    Cloud_Base
 * @subpackage Cloud_Base/cb-pdpflightlog
 * @author     dave
 * This module is a bridge between the PGC PDP flight long and the Cloudbase flight log
 * PGC has 12 year history of flights recorced in the PDP and report generating modules
 * This plugin acts as a bridge so the old reporting generating will work with new 
 * Cloudbase flight until new report programs can be written. 
 */
class Rest extends \Cloud_Base_Rest {
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

	public function register_routes() {

  	$version = '1';
    $namespace = 'cloud_base/v' . $version;
    $base = 'route';
	 // the extra (?:/ ...  ) makes the parmater optional 
 		register_rest_route( $namespace, '/pdp_flights(?:/(?P<id>[\d]+))?', array (
 			array(
       		'methods'  => \WP_REST_Server::READABLE,
        	// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        	'callback' => array( $this, 'pdp_get_flights' ),
        	// Here we register our permissions callback. The callback is fired before the main callback to check if the current user can access the endpoint.
       		'permission_callback' => array($this, 'cloud_base_dummy_access_check' ),        	
   		 	), array(
       		'methods'  => \WP_REST_Server::CREATABLE,  
        	// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        	'callback' => array( $this, 'pdp_post_flight' ),
        	// Here we register our permissions callback. The callback is fired before the main callback to check if the current user can access the endpoint.
       		'permission_callback' => array($this, 'cloud_base_dummy_access_check' ),  		      	
   		 	), array(
   		 	'methods'  => \WP_REST_Server::EDITABLE,  
        	// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        	'callback' => array( $this, 'pdp_update_flight' ),
        	// Here we register our permissions callback. The callback is fired before the main callback to check if the current user can access the endpoint.
       		'permission_callback' => array($this, 'cloud_base_dummy_access_check' ),  		      	
   		 	), array(
   		 	'methods'  => \WP_REST_Server::DELETABLE,
        	// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        	'callback' => array( $this, 'glider_club_delete_signoff' ),
        	// Here we register our permissions callback. The callback is fired before the main callback to check if the current user can access the endpoint.
       		'permission_callback' => array($this, 'cloud_base_dummy_access_check' ),  		      	
   		 	)) 
   		 );	
    }
      
	public function pdp_get_flights( \WP_REST_Request $request) {
	

 	$requestA = new \WP_REST_Request('GET', '/cloud_base/v1/flights');	
// 	return new \WP_REST_Response (( $request['flight_number']));	
	$fn = $request['flight_number'] ;

// $requestA->set_param('flight_number', $fn);
$requestA->set_query_params($request);
//$requestA->set_param('flight_number', $fn);
$response = rest_do_request($requestA);

//return new \WP_REST_Response($response);
$server = rest_get_server();
$flights = $server->response_to_data( $response, false );

return new \WP_REST_Response ($flights);		
	
	
		global $PGCwp; 
		global $wpdb;
		$table_name =  'pgc_flightsheet';
		$wp_table_name = 'wp_cloud_base_flight_sheet'; 
		
		$maxRows = isset($request['maxrows']) ? $request['maxrows'] : 10;
		$pageNum = isset($request['pageNum']) ? $request['pageNum'] : 0 ;
		$startRow = $pageNum * $maxRows; 
		
		$select_string ='s.`Key`, s.Date, s.Glider, s.Flight_Type, s.Pilot1, s.Pilot2, s.Takeoff, s.Landing, s.Time, s.`Tow Altitude` as tow_alitude, s.`Tow Plane` as tow_plane, s.`Tow Pilot` as tow_pilot, s.`Tow Charge` as tow_charge, s.Notes, s.Ip, s.email, s.mail_count, s.cfig_train';
	    $select_string2 = 'w.id, w.flightyear, w.flight_number, w.aircraft_id, w.pilot_id, w.flight_fee_id, w.total_charge, w.start_time, w.end_time, w.instructor_id, w.tow_plane_id, w.tow_pilot_id w.notes';

// process filters.  	  
 	    $filters_valid = array( 'Date'=>'date', 'Glider'=>'glider', 'Flight_Type'=>'flight_type', 'Pilot1'=>'pilot1', 
			'Pilot2'=>'pilot2', 'Tow Altitude'=>'tow_altitude', 'Tow Plane'=>'tow_plane', 'Tow Pilot'=>'tow_pilot' );
		$valid_filters = array_flip($filters_valid);
	    $filter_string = $this->pdp_select_filters($request, $valid_filters);
		
 		if(isset($request['id'])){
 			$sql = $PGCwp->prepare("SELECT {$select_string} FROM {$table_name} s WHERE {$filter_string} AND `key` = %d" ,  $request['id'] );		

 	//		$sql = $PGCwp->prepare("SELECT * FROM  $table_name WHERE `Key` = '%d'", $request['id']);
 		}else {
 		  	$sql = "SELECT {$select_string} FROM {$table_name} s WHERE {$filter_string} ORDER BY `Key` DESC ";	
 		
//			$sql = $PGCwp->prepare("SELECT * FROM $table_name WHERE `Date` = '%s' ORDER BY `Key` DESC LIMIT %d, %d", date("Y-m-d"), $maxRows,  $startRow);
		}
//	  	return new \WP_REST_Response ($sql);

		$items = $PGCwp->get_results($sql);
	  	return new \WP_REST_Response ($items);
	}
//  create new flight
	public function pdp_post_flight( \WP_REST_Request $request) {
		global $PGCwp; 
		$table_name =  'pgc_flightsheet';
		$today = date("Y-m-d");
		$flight_data = array( 'Date'=>'date', 'Glider'=>'glider', 'Flight_Type'=>'flight_Type', 'Pilot1'=>'pilot1', 
			'Pilot2'=>'pilot2', 'Takeoff'=>'takeoff', 'Landing'=>'landing', 'Time'=>'time', 'Tow Altitude'=>'tow_altitude', 
			'Tow Plane'=>'tow_plane', 'Tow Pilot'=>'tow_pilot', 'Tow Charge'=>'tow_charge', 'Notes'=>'notes', 
			'Ip'=>'ip', 'email'=>'email', 'mail_count'=>'mail_count', 'cfig_train'=>'cfig_train');	
		
		$insert_array = array();
		foreach ($flight_data as $key =>$value){
			if(isset($request[$value])){
				$insert_array += [$key=>$request[$value]];
			}
		}
		if (!empty($insert_array)){
			// if no date was supplied but other data is set, use today's date. 
			if (!isset($insert_array['Date'])){
				$insert_array += ['Date'=>date("Y-m-d")];
			}
			$result = $PGCwp->insert($table_name, $insert_array );		
		}
		if ($result ){
			return new \WP_REST_Response ($items);
		} else {
			return new \WP_Error( 'Insert Failed', esc_html__( 'Unable to add Flight', 'my-text-domain' ), array( 'status' => 204 ) );
		}
	}	
//  update flight. 	
	public function pdp_update_flight( \WP_REST_Request $request) {
		global $PGCwp; 
		$table_name =  'pgc_flightsheet';
		
		if (!isset($request['id'])){
			return new \WP_Error( 'Id missing', esc_html__( 'Id is required', 'my-text-domain' ), array( 'status' => 400 ) );		
		}		
		$flight_data = array( 'Date'=>'date', 'Glider'=>'glider', 'Flight_Type'=>'flight_type', 'Pilot1'=>'pilot1', 
			'Pilot2'=>'pilot2', 'Takeoff'=>'takeoff', 'Landing'=>'landing', 'Time'=>'time', 'Tow Altitude'=>'tow_altitude', 
			'Tow Plane'=>'tow_plane', 'Tow Pilot'=>'tow_pilot', 'Tow Charge'=>'tow_charge', 'Notes'=>'notes', 
			'Ip'=>'ip', 'email'=>'email', 'mail_count'=>'mail_count', 'cfig_train'=>'cfig_train');	
		
		$update_array = array();
		foreach ($flight_data as $key =>$value){
			if(isset($request[$value])){
				$update_array += [$key=>$request[$value]];
			}
		}
		
		if (!empty($update_array)){
			$result = $PGCwp->update($table_name, $update_array, array('Key'=>$request['id'] ));		
		}		
		if ($result ){
			return new \WP_REST_Response ($result);
		} else {
			return new \WP_Error( 'Update Failed', esc_html__( 'Unable to update Flight', 'my-text-domain' ), array( 'status' => 204 ) );
		}
	}			
//  delete flight. 	
	public function glider_club_delete_signoff( \WP_REST_Request $request) {
	// NOt implemented. 
	
		global $PGCwp; 
		$table_name =  'pgc_flightsheet';
		
		if (!isset($request['id'])){
			return new \WP_Error( 'Id missing', esc_html__( 'Id is required', 'my-text-domain' ), array( 'status' => 400 ) );		
		}		
		return new \WP_Error( 'rest_api_sad', esc_html__( 'Something went horribly wrong.', 'my-text-domain' ), array( 'status' => 500 ) );
	}	
	public function pdp_select_filters($request, $valid_filters){
	  global $wpdb;
	  $filter_string = "1 ";
	  $valid_keys = array_keys($valid_filters );		  
	  foreach($valid_keys as $key ){
	  	if(!empty($request[$key]) ){
	  		$filter_string = $filter_string . ' AND '. $valid_filters[$key] .'='.  $wpdb->prepare('%s' , $request[$key]);
	  	}
	  }
	return($filter_string);
	}	
}
