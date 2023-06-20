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
		date_default_timezone_set('America/New_York');	
 		global $wpdb; 
		$flight_table =  $wpdb->prefix . 'cloud_base_pdp_flight_sheet';	
		if(isset( $request['start'])){
			if(isset( $request['end'])){
				$sql = $wpdb->prepare("SELECT * FROM {$flight_table} WHERE `Date`BETWEEN %s AND %s " , $request['start'], $request['end']);									
			} else {
				$sql = $wpdb->prepare("SELECT * FROM {$flight_table} WHERE `Date` = %s" , $request['start']);			 
			}
		} else {
  			$sql = $wpdb->prepare("SELECT * FROM {$flight_table} WHERE `Date` = %s ORDER BY yearkey DESC" , date("Y-m-d") );	
		
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
		 global $wpdb; 
// 		if (!isset($request['flightyear'])  or  !isset($request['flightyear']) ){
// 			return new \WP_Error( ' Failed', esc_html__( 'missing parameter(s)', 'my-text-domain' ), array( 'status' => 422) );	 
// 		}
		$flight_table =  $wpdb->prefix . 'cloud_base_pdp_flight_sheet';	
		$fee_table =  $wpdb->prefix . 'cloud_base_tow_fees';	
// 		$sql = $wpdb->prepare("SELECT yearkey FROM {$flight_table} WHERE `flightyear`=%s ORDER BY yearkey DESC LIMIT 1",  date("Y"));	
// 		$yearkey = $wpdb->get_var($sql); 	
		if (!isset($request['yearkey']) ){
			return new \WP_Error( 'missing yearkey', esc_html__( 'missing yearkey.', 'my-text-domain' ), array( 'status' => 400 ) );
		} 
		$yearkey = $request['yearkey'];
 
// 		isset($request['id']) 			? $id=$request['id'] 					: $id=null;
		isset($request['flightyear']) 	? $flightyear=$request['flightyear'] 	: $flightyear=date('Y');
//   		isset($request['yearkey']) 		? $yearkey=$request['yearkey'] 			: $yearkey=null;
 		isset($request['Date']) 		? $date=$request['Date'] 				: $date=date('Y-m-d');
		isset($request['Glider']) 		? $glider=$request['Glider'] 			: $glider=null;
		isset($request['Flight_Type']) 	? $flight_type=$request['Flight_Type'] 	: $flight_type=null;
		isset($request['Pilot1']) 		? $pilot1=$request['Pilot1'] 			: $pilot1=null;
		isset($request['Pilot2']) 		? $pilot2=$request['Pilot2'] 			: $pilot2=null;
		isset($request['Takeoff']) 		? $takeoff=$request['Takeoff'] 			: $takeoff='00:00:00';
		isset($request['Landing']) 		? $landing=$request['Landing'] 			: $landing='00:00:00';
		isset($request['Time']) 		? $time=$request['Time'] 				: $time='0.0';
		isset($request['Tow_Altitude']) ? $tow_altitude=$request['Tow_Altitude'] :  $tow_altitude=null;
		isset($request['Tow_Pilot']) 	? $tow_pilot=$request['Tow_Pilot'] 		: $tow_pilot=null;
		isset($request['Tow_Plane']) 	? $tow_plane=$request['Tow_Plane'] 		: $tow_plane=null;
// 		isset($request['Tow_Charge']) 	? $tow_charge=$request['Tow_Charge'] 	: $tow_charge=null;
		isset($request['Notes']) 		? $notes=$request['Notes'] 				: $notes=null;

		$sql = $wpdb->prepare("SELECT charge FROM {$fee_table} WHERE `altitude`=%s", $request['Tow_Altitude']);	
		$charge = $wpdb->get_var($sql);
		$charge == null ? $tow_charge=999 : $tow_charge = $charge; 


        $data = array( 'flightyear'=>$flightyear, 'yearkey'=>$yearkey, 'Date'=>$date, 'Glider'=> $glider, 'Flight_type'=>$flight_type, 
        	'Pilot1'=>$pilot1, 'Pilot2'=>$pilot2, 'Takeoff'=>$takeoff, 'Landing'=>$landing, 'Time'=>$time, 'Tow_Altitude'=>$tow_altitude, 
        	'Tow_Plane'=>$tow_plane, 'tow_pilot'=>$tow_pilot, 'Tow_Charge'=>$tow_charge, 'Notes'=>$notes ) ;        	

		$sql = $wpdb->prepare("SELECT id FROM {$flight_table} WHERE `yearkey`=%s AND `flightyear`=%s", $yearkey, $flightyear);	
		$old_id  = $wpdb->get_var($sql ); 			
		if ( $old_id != null ){
			$result = $wpdb->update($flight_table, $data, array('id' =>$old_id ));	// update existing.  			
		} else {	
        	$result = $wpdb->insert($flight_table, $data); 		
		}		
 		if($result == '1' ){
			$sql = $wpdb->prepare("SELECT * FROM {$flight_table} WHERE `yearkey`=%s AND `flightyear`=%s", $yearkey, $flightyear);	
			$record_id  = $wpdb->get_results($sql); 	
			
   			wp_send_json($record_id, 201);				
//   			return new \WP_REST_Response ($record_id); 				
 		} else {
 			return new \WP_Error( 'Insert Failed', esc_html__( 'Insert failed. ', 'my-text-domain' ), array( 'status' => 500 ) ); 
 		}
	}		
//  update pdp flight sheet. 	?yearkey=193&flightyear=2023
	public function put_flight_data( \WP_REST_Request $request) {
		global $wpdb; 
		$flight_table =  $wpdb->prefix . 'cloud_base_pdp_flight_sheet';	
		$fee_table =  $wpdb->prefix . 'cloud_base_tow_fees';	
 		$record = [];
// 		if(isset($request['Pilot1'])){
// 		// get user id from user name. 	
// 			[$last_name, $first_name] = explode(', ', $request['Pilot1']);
// 			$sql = $wpdb->prepare(  "Select f.user_id from $wpdb->usermeta f INNER JOIN $wpdb->usermeta l on f.user_id = l.user_id  WHERE f.meta_key = 'first_name' AND f.meta_value = %s AND l.meta_key = 'last_name' AND l.meta_value = %s", $first_name,  $last_name);
// 			$user_id = $wpdb->get_var( $sql);
// 		}
		
		if(isset( $request['Tow_Altitude'])){
			$sql = $wpdb->prepare("SELECT charge FROM {$fee_table} WHERE `altitude`=%s", $request['Tow_Altitude']);	
			$charge = $wpdb->get_var($sql);
			$charge == null ? $record['Tow_Charge']=999 : $record['Tow_Charge'] = $charge; 
		}

		if (!isset($request['id']) ){
			return new \WP_Error( ' Failed', esc_html__( 'missing parameter(s)', 'my-text-domain' ), array( 'status' => 422) );	 
		}
 		$id = stripslashes_deep($request['id']); //added stripslashes_deep which removes WP escaping.
	
 		$fields = array('flightyear', 'yearkey', 'Date', 'Glider', 'Flight_Type', 'Pilot1', 
 			'Pilot2', 'Takeoff', 'Landing', 'Time', 'Tow_Altitude', 'Tow_Pilot', 'Tow_Plane', 
 			 'Notes' );
									
		global $wpdb; 
 		$flight_table =  $wpdb->prefix . 'cloud_base_pdp_flight_sheet';	

 		foreach( $fields as $field) {
 			if( isset($request[$field]) ){
 				$record[$field]=$request[$field];		
 			}
 		} 			 		 		
 		$result = $wpdb->update($flight_table, $record, array('id' =>$id ));	// update existing.  	
 			
 		$sql = $wpdb->prepare("SELECT * FROM {$flight_table} WHERE `id`=%d", $id);		
 		 			
		$results  = $wpdb->get_results($sql); 	
		// send member notification of new flight. 
		if($results[0]->mail_count==0 && $results[0]->Takeoff != "00:00:00" && $results[0]->Landing != "00:00:00" && $results[0]->Tow_Altitude != '' ){

			[ $last_name, $first_name] = explode(', ', $results[0]->Pilot1);
			$sql = $wpdb->prepare(  "Select f.user_id from $wpdb->usermeta f INNER JOIN $wpdb->usermeta l on f.user_id = l.user_id  WHERE f.meta_key = 'first_name' AND f.meta_value = %s AND l.meta_key = 'last_name' AND l.meta_value = %s", $first_name,  $last_name);
			$user_id = $wpdb->get_var( $sql);
	
			$intro = "\n" . "This message was generated by the PGC Flight logging system. A new flight has been recorded to your account" . "\n\n". "Please check your personal flight log on line to verify.". "\n\n ";								 
            $intro = $intro . "https://www.pgcsoaring.com/personal-flight-log/\n\n";
			$user_info = get_userdata( $user_id);
		
			$to = $user_info->user_email;
			$subject = "PGC Flightlog - New Flight ";
			$headers = array( 'FROM: soaringpgc@gmail.com; Content-type: text/html; ');
			if($_SERVER["SERVER_NAME"] != 'localhost'){
	  			$sent = wp_mail($to, $subject, $intro, $headers);	
			}
		}		 		 		
 		return new \WP_REST_Response ($results); 		
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
