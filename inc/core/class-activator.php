<?php

namespace CB_PdpFlightlog\Inc\Core;

/**
 * Fired during plugin activation
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @link       http://pgcsoaring.com
 * @since      1.0.0
 *
 * @author     Philadelphia Glider Council -- Dave Johnson
 **/
class Activator {

	/**
	 * Short Description.
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

			$min_php = '5.6.0';

		// Check PHP Version and deactivate & die if it doesn't meet minimum requirements.
		if ( version_compare( PHP_VERSION, $min_php, '<' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( 'This plugin requires a minmum PHP Version of ' . $min_php );
		}
		if( !class_exists( 'Cloud_Base_Admin' ) ) {
       		deactivate_plugins( plugin_basename( __FILE__ ) );
        	wp_die( __( 'Please install and Activate Cloud Base.', 'cb-pdpflightlog' ), 'Plugin dependency check', array( 'back_link' => true ) );
    	}
//      create_fl_database() ;	
//      copy_pdp_flights()	;
	}
}

function create_fl_database(){
   	global $wpdb;
   	$charset_collate = $wpdb->get_charset_collate();
//    	$db_version = 0.1;
   	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$table_name = $wpdb->prefix . "cloud_base_pdp_flight_sheet";
	// create flight sheet table
	$sql = "CREATE TABLE ". $table_name ." (
 	  	id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
 	  	flightyear smallint(6),
 	    yearkey int(10) UNSIGNED NOT NULL,
  		Date date DEFAULT NULL, 
  		Glider varchar(10) DEFAULT NULL,
  		Flight_Type char(5) DEFAULT NULL,
  		Pilot1 varchar(30) DEFAULT NULL,
  		Pilot2 varchar(30) DEFAULT NULL,
  		Takeoff time DEFAULT NULL,
  		Landing time DEFAULT NULL,
  		Time decimal(5,2) DEFAULT NULL,
  		Tow_Altitude varchar(5) DEFAULT '5000',
  		Tow_Plane varchar(10) DEFAULT NULL,
  		Tow_Pilot varchar(30) DEFAULT NULL,
  		Tow_Charge decimal(5,2) DEFAULT NULL,
  		Notes varchar(250) DEFAULT NULL,
  		ip char(20) DEFAULT NULL,
  		email varchar(45) DEFAULT NULL,
  		mail_count int(1) DEFAULT NULL,
	  PRIMARY KEY (id)
      );" . $charset_collate  . ";";
	dbDelta($sql);

	//  Set the version of the Database
// 	update_option("flight_log_db_version", $db_version);
	}

function copy_pdp_flights(){
   	global $wpdb;
   	global $PGCi;  // database handle for PDP external db
	$wp_pdp_flight_log = $wpdb->prefix . "cloud_base_pdp_flight_sheet";

	$flight_years = array( '2010', '2011', '2012', '2013', '2014', '2015', '2016', '2017', '2018', '2019', '2020', '2021', '2022'); 
	foreach($flight_years as $year){
		$sql = "SELECT * from pgc_flightsheet_" . $year; 
		$flights =  mysqli_query($PGCi, $sql );
		foreach($flights as $flight ) {
			$data= array( 
				'flightyear'   => $year,
				'yearkey'      => $flight['Key'],
				'Date' 		   => $flight['Date'],
				'Glider' 	   => $flight['Glider'],
				'Flight_Type'  => $flight['Flight_Type'],
				'Pilot1'   	   => $flight['Pilot1'],
				'Pilot2' 	   => $flight['Pilot2'],
				'Takeoff' 	   => $flight['Takeoff'],
				'Landing' 	   => $flight['Landing'],
				'Time' 		   => $flight['Time'],
				'Tow_Altitude' => $flight['Tow Altitude'],
				'Tow_Plane'    => $flight['Tow Plane'],
				'Tow_Pilot'    => $flight['Tow Pilot'],
				'Tow_Charge'   => $flight['Tow Charge'],
				'Notes' 	   => $flight['Notes'],
				'ip' 		   => $flight['ip'],
				'email' 	   => $flight['email'],
				'mail_count'   => $flight['mail_count']	
			); 						
			$wpdb->insert($wp_pdp_flight_log , $data );		
		}	
		$sql = "SELECT * from pgc_flightsheet"; // current year 2023
		$flights =  mysqli_query($PGCi, $sql );
			$data= array( 
				'flightyear'   => '2023',
				'yearkey'      => $flight['Key'],
				'Date' 		   => $flight['Date'],
				'Glider' 	   => $flight['Glider'],
				'Flight_Type'  => $flight['Flight_Type'],
				'Pilot1'   	   => $flight['Pilot1'],
				'Pilot2' 	   => $flight['Pilot2'],
				'Takeoff' 	   => $flight['Takeoff'],
				'Landing' 	   => $flight['Landing'],
				'Time' 		   => $flight['Time'],
				'Tow_Altitude' => $flight['Tow Altitude'],
				'Tow_Plane'    => $flight['Tow Plane'],
				'Tow_Pilot'    => $flight['Tow Pilot'],
				'Tow_Charge'   => $flight['Tow Charge'],
				'Notes' 	   => $flight['Notes'],
				'ip' 		   => $flight['ip'],
				'email' 	   => $flight['email'],
				'mail_count'   => $flight['mail_count']	
			); 						
			$wpdb->insert($wp_pdp_flight_log , $data );			
	}
}

