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
 * @subpackage Cloud_Base/public
 * @author     Your Name <email@example.com>
 */
 
class Rest extends \WP_REST_Controller {
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $cloud_base    The ID of this plugin.
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

	public function __construct( $plugin_name, $version) {

		$this->plugin_name = 'cb-pdpflightlog';
		if ( defined ( 'PLUGIN_REST_VERSION')){
			$this->rest_version = PLUGIN_REST_VERSION;
		} else {
			$this->rest_version = '1';
		}	
		// you may want base path name to be different from plugin name. 	

		$this->namespace = $this->plugin_name. '/v' .  $this->rest_version; 			
	}
 
 	public function cloud_base_admin_access_check(){
	// put your access requirements here. You might have different requirements for each
	// access method. I'm showing only one here. 
    	if ( !(current_user_can( 'edit_users' ))) {
     	   return new \WP_Error( 'rest_forbidden', esc_html__( 'Sorry, you are not authorized for that.', 'my-text-domain' ), array( 'status' => 401 ) );
    	}
    	// This is a black-listing approach. You could alternatively do this via white-listing, by returning false here and changing the permissions check.
    	return true;	
	}
	public function cloud_base_members_access_check(){
	// put your access requirements here. You might have different requirements for each access method. 
	// can read, at least a subscriber. 	
    	if (  current_user_can( 'read' )) {
    	    return true;
     	}
    	// This is a white-listing approach. You could alternatively do this via black-listing, by returning false here and changing the permissions check.	
    	return new \WP_Error( 'rest_forbidden', esc_html__( 'Sorry, you are not authorized for that.', 'my-text-domain' ), array( 'status' => 401 ) );
	} 
	public function cloud_base_dummy_access_check(){
	// put your access requirements here. You might have different requirements for each
	// access method. I'm showing only one here. 
	// do not use this in production!!!!
	
     	return true;	
	} 	
	public function register_routes() {

  	$version = '1';
    $namespace = 'cloud_base/v' . $version;
    $base = 'route';
	 // the extra (?:/ ...  ) makes the parmater optional 
 		register_rest_route( $namespace, '/pdp_flightlog(?:/(?P<id>[\d]+))?', array (
 			array(
       		'methods'  => \WP_REST_Server::READABLE,
        	// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        	'callback' => array( $this, 'get_flight_data' ),
        	// Here we register our permissions callback. The callback is fired before the main callback to check if the current user can access the endpoint.
       		'permission_callback' => array($this, 'cloud_base_dummy_access_check' ),        	
   		 	), array(
       		'methods'  => \WP_REST_Server::CREATABLE,  
        	// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        	'callback' => array( $this, 'post_flight_data' ),
        	// Here we register our permissions callback. The callback is fired before the main callback to check if the current user can access the endpoint.
       		'permission_callback' => array($this, 'cloud_base_members_access_check' ),  		      	
   		 	), array(
   		 	'methods'  => \WP_REST_Server::EDITABLE,  
        	// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        	'callback' => array( $this, 'put_flight_data' ),
        	// Here we register our permissions callback. The callback is fired before the main callback to check if the current user can access the endpoint.
       		'permission_callback' => array($this, 'cloud_base_members_access_check' ),  		      	
   		 	), array(
   		 	'methods'  => \WP_REST_Server::DELETABLE,
        	// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        	'callback' => array( $this, 'delete_flight_data' ),
        	// Here we register our permissions callback. The callback is fired before the main callback to check if the current user can access the endpoint.
       		'permission_callback' => array($this, 'cloud_base_admin_access_check' ),  		      	
   		 	)) 
   		 );	
    }      
	public function get_flight_data( \WP_REST_Request $request) {		
 		global $wpdb; 
		$flight_table =  $wpdb->prefix . 'cloud_base_pdp_flight_sheet';	
		if(isset( $request['start'])){
			if(isset( $request['end'])){
				$sql = $wpdb->prepare("SELECT * FROM {$flight_table} WHERE `Date`BETWEEN %s AND %s " , $request['start'], $request['end']);									
			} else {
				$sql = $wpdb->prepare("SELECT * FROM {$flight_table} WHERE `Date` = %s" , $request['start']);			 
			}
		} else {
  			$sql = $wpdb->prepare("SELECT * FROM {$flight_table} WHERE `Date` = %s" , date("Y-m-d") );	
		
		}
		if(isset( $request['last'])){
			$sql = $wpdb->prepare("SELECT * FROM {$flight_table} WHERE `flightyear`=%s ORDER BY yearkey DESC LIMIT 1",  
					date("Y"));	
		}
			
		$results  = $wpdb->get_results($sql); 	
		return new \WP_REST_Response ($results); 
	}
//  create new flight 
	public function post_flight_data( \WP_REST_Request $request) {
		
		if (!isset($request['flightyear'])  or  !isset($request['flightyear']) ){
			return new \WP_Error( ' Failed', esc_html__( 'missing parameter(s)', 'my-text-domain' ), array( 'status' => 422) );	 
		}
	
		global $wpdb; 
		$flight_table =  $wpdb->prefix . 'cloud_base_pdp_flight_sheet';	
 
// 		isset($request['id']) 			? $id=$request['id'] 					: $id=null;
		isset($request['flightyear']) 	? $flightyear=$request['flightyear'] 	: $flightyear=date('Y');
  		isset($request['yearkey']) 		? $yearkey=$request['yearkey'] 			: $yearkey=null;
 		isset($request['date']) 		? $date=$request['date'] 				: $date=date('Y-m-d');
		isset($request['glider']) 		? $glider=$request['glider'] 			: $glider=null;
		isset($request['flight_type']) 	? $flight_type=$request['flight_type'] 	: $flight_type=null;
		isset($request['pilot1']) 		? $pilot1=$request['pilot1'] 			: $pilot1=null;
		isset($request['pilot2']) 		? $pilot2=$request['pilot2'] 			: $pilot2=null;
		isset($request['takeoff']) 		? $takeoff=$request['takeoff'] 			: $takeoff=null;
		isset($request['landing']) 		? $landing=$request['landing'] 			: $landing=null;
		isset($request['time']) 		? $time=$request['time'] 				: $time=null;
		isset($request['tow_altitude']) ? $tow_altitude=$request['tow_altitude'] :  $tow_altitude=null;
		isset($request['tow_pilot']) 	? $tow_pilot=$request['tow_pilot'] 		: $tow_pilot=null;
		isset($request['tow_plane']) 	? $tow_plane=$request['tow_plane'] 		: $tow_plane=null;
		isset($request['tow_charge']) 	? $tow_charge=$request['tow_charge'] 	: $tow_charge=null;
		isset($request['notes']) 		? $notes=$request['notes'] 				: $notes=null;

        $data = array( 'flightyear'=>$flightyear, 'yearkey'=>$yearkey, 'Date'=>$date, 'Glider'=> $glider, 'Flight_type'=>$flight_type, 
        	'Pilot1'=>$pilot1, 'Pilot2'=>$pilot2, 'Takeoff'=>$takeoff, 'Landing'=>$landing, 'Time'=>$time, 'Tow_Altitude'=>$tow_altitude, 
        	'Tow_Plane'=>$tow_plane, 'tow_pilot'=>$tow_pilot, 'Tow_Charge'=>$tow_charge, 'Notes'=>$notes ) ;        	
        $result = $wpdb->insert($flight_table, $data); 		
		
 		if($result == '1' ){
			$sql = $wpdb->prepare("SELECT id FROM {$flight_table} WHERE `flightyear`=%s ORDER BY yearkey DESC LIMIT 1",  
					date("Y"));	
			$record_id  = $wpdb->get_var($sql); 	
			return new \WP_REST_Response ($record_id); 				
 		}
	}		
//  update pdp flight sheet. 	
	public function put_flight_data( \WP_REST_Request $request) {
		global $wpdb; 
		$flight_table =  $wpdb->prefix . 'cloud_base_pdp_flight_sheet';	

		if (!isset($request['id']) ){
			return new \WP_Error( ' Failed', esc_html__( 'missing parameter(s)', 'my-text-domain' ), array( 'status' => 422) );	 
		}
 			
 		$fields = array('flightyear', 'yearkey', 'date', 'glider', 'flight_type', 'pilot1', 
 			'pilot2', 'takeoff', 'landing', 'time', 'tow_altitude', 'tow_pilot', 'tow_plane', 
 			'tow_charge', 'notes' );
									
		global $wpdb; 
 		$flight_table =  $wpdb->prefix . 'cloud_base_pdp_flight_sheet';	
 		$record = [];
 		foreach( $fields as $field) {
 			if( isset($request[$field]) ){
 				$record[$field]=$request[$field];		
 			}
 		} 				 		
 		$result = $wpdb->update($flight_table, $record, array('id' =>$request['id']));	// update existing.  		 		
 		return new \WP_REST_Response ($result); 		
	}			
//  delete flilght log. 	
	public function delete_flight_data( \WP_REST_Request $request) {
	// not implmemented. 
// 
// 		global $wpdb; 
// 		$table_name =  $wpdb->prefix . 'cloud_base_pdp_flight_sheet';		
// 		
// 		if (!isset($request['id'])){
// 			return new \WP_Error( 'Id missing', esc_html__( 'Id is required', 'my-text-domain' ), array( 'status' => 400 ) );		
// 		}	
// 		$wpdb->delete($table_name , array('id'=> $request['id']));			
	}	
}
