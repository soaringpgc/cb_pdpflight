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
			
		$resutls  = $wpdb->get_results($sql); 	
		return new \WP_REST_Response ($resutls); 
	}
//  create new flight 
	public function post_flight_data( \WP_REST_Request $request) {
		global $wpdb; 
		$table_name =  $wpdb->prefix . 'cloud_base_field_duty';
		
	// need calendar_id, trade_id, and member_id  	
		if(isset($request['calendar_id']) && isset($request['trade_id']) && isset($request['member_id'])){	  
  			$sql = $wpdb->prepare("SELECT id FROM {$table_name} WHERE `calendar_id` = %d AND trade_id = %d" , $request['calendar_id'], $request['trade_id'] );	
			$id = $wpdb->get_var($sql); 
 			if( $id == null ){
	  			$record = array( 'calendar_id'=>  $request['calendar_id'], 'trade_id'=> $request['trade_id'], 'member_id'=>  $request['member_id']);	 			  	 
 				$result = $wpdb->insert($table_name, $record);				
 			} else {
 				return new \WP_Error( 'duplicate', esc_html__( 'member already assigned', 'my-text-domain' ), array( 'status' => 409) );
 			} 
 		    return new \WP_REST_Response ( $result); 		
		} else {
			return new \WP_Error( ' Failed', esc_html__( 'missing parameter(s)', 'my-text-domain' ), array( 'status' => 422) );
		}
	}	
	
//  update field_duty. 	
	public function put_flight_data( \WP_REST_Request $request) {
		global $wpdb; 
 		$calendar_name   =  $wpdb->prefix . 'cloud_base_calendar';
		$field_name      =  $wpdb->prefix . 'cloud_base_field_duty';

		$member = null;
	  	if (isset($request['member_id']) &&  !($request['member_id'] ==0 )) { // get id of the member leave at null if zero 
			$member = $request['member_id'] ;
		}
		if (isset($request['id'])){
			$sql = $wpdb->prepare("SELECT id FROM {$field_name} WHERE `id` = %d" ,  $request['id']);	
 	 		$id = $wpdb->get_var($sql); 
 			if( $id == null ){
 				return new \WP_Error( 'Failed', esc_html__( 'Not Found', 'my-text-domain' ), array( 'status' => 404) );	     
 			} else {
 				$record = array( 'member_id'=>$member );		// update record 		 	 						
 				$result = $wpdb->update($field_name, $record, array('id' => $id ));	// update existing. 
 				$sql = $wpdb->prepare("SELECT * FROM {$field_name} WHERE `id` = %d" ,  $request['id']);	
 	 			$result = $wpdb->get_results($sql); 
 				return new \WP_REST_Response ( $result); 	 
 			}
					
		} elseif (isset($request['date']) && isset($request['trade_id']) ){ // get id of the date		
 	   		$sql = $wpdb->prepare("SELECT id FROM {$calendar_name} WHERE `calendar_date` = %s" ,  $request['date']);	
 	 		$id = $wpdb->get_var($sql); 
 			if( $id == null ){
 				return new \WP_Error( ' Failed', esc_html__( 'Not Found', 'my-text-domain' ), array( 'status' => 404) );	     
 			} else{
  			 	 $sql = $wpdb->prepare("SELECT id FROM {$field_name} WHERE `calendar_id` = %d AND `trade` = %d " , $id, $request['trade_id'] );	 	
 				 $fid = $wpdb->get_var($sql); // get field duty record. 	
				 if( $fid == null ){
 				 	if ( $request['trade_id'] != "1" ){
 						return new \WP_Error( ' Failed', esc_html__( 'Not Found', 'my-text-domain' ), array( 'status' => 404) );	
 					}  else {
 					 	$record = array('calendar_id'=> $id ,'trade'=> $request['trade_id'], 'member_id'=>$member );// new record 			
 						$result = $wpdb->insert($field_name, $record);	 // add new 
 //							return new \WP_REST_Response ( $wpdb->last_query); 	
 					} 					  
  				 } else{
 				   	$record = array('trade'=> $request['trade_id'], 'member_id'=>$member );		// update record 		 	 						
 					$result = $wpdb->update($field_name, $record, array('id' => $fid ));	// update existing. 
 				}
 			}
		   	return new \WP_REST_Response ( $result); 	 	
	     } else {	     
			return new \WP_Error( ' Failed', esc_html__( 'missing parameter(s)', 'my-text-domain' ), array( 'status' => 422) );	     
  	   } 
	}			
//  delete field_duty. 	
	public function delete_flight_data( \WP_REST_Request $request) {

		global $wpdb; 
		$table_name =  $wpdb->prefix . 'loud_base_field_duty';		
		
		if (!isset($request['id'])){
			return new \WP_Error( 'Id missing', esc_html__( 'Id is required', 'my-text-domain' ), array( 'status' => 400 ) );		
		}	
		$wpdb->delete($table_name , array('id'=> $request['id']));			
	}	
}
