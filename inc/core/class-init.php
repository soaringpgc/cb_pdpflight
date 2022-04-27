<?php

namespace CB_PdpFlightlog\Inc\Core;
use CB_PdpFlightlog as NS;
use CB_PdpFlightlog\Inc\Admin as Admin;
use CB_PdpFlightlog\Inc\Frontend as Frontend;

/**
 * The core plugin class.
 * Defines internationalization, admin-specific hooks, and public-facing site hooks.
 *
 * @link       http://pgcsoaring.com
 * @since      1.0.0
 *
 * @author     Philadelphia Glider Council -- Dave Johnson
 */
class Init {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @var      Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_base_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_basename;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The text domain of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $plugin_text_domain;

	/**
	 * Initialize and define the core functionality of the plugin.
	 */
	public function __construct() {

		$this->plugin_name = NS\PLUGIN_NAME;
		$this->version = NS\PLUGIN_VERSION;
				$this->plugin_basename = NS\PLUGIN_BASENAME;
				$this->plugin_text_domain = NS\PLUGIN_TEXT_DOMAIN;

		$this->load_dependencies();
		$this->set_locale();
//		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Loads the following required dependencies for this plugin.
	 *
	 * - Loader - Orchestrates the hooks of the plugin.
	 * - Internationalization_I18n - Defines internationalization functionality.
	 * - Admin - Defines all hooks for the admin area.
	 * - Frontend - Defines all hooks for the public side of the site.
	 *
	 * @access    private
	 */
	private function load_dependencies() {
		$this->loader = new Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Internationalization_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @access    private
	 */
	private function set_locale() {

		$plugin_i18n = new Internationalization_I18n( $this->plugin_text_domain );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @access    private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Admin\Admin( $this->get_plugin_name(), $this->get_version(), $this->get_plugin_text_domain() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		/*
		 * Additional Hooks go here
		 *
		 * e.g.
		 *
		 * //admin menu pages
		 * $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
		 *
		 *  //plugin action links
		 * $this->loader->add_filter( 'plugin_action_links_' . $this->plugin_basename, $plugin_admin, 'add_additional_action_link' );
		 *
		 */
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @access    private
	 */
	private function define_public_hooks() {

		$plugin_public = new Frontend\Frontend( $this->get_plugin_name(), $this->get_version(), $this->get_plugin_text_domain() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );		

		$this->loader->add_action( 'admin_post_pdp-flight-log', $plugin_public, 'pdp_flight_log' );
		$this->loader->add_action( 'admin_post_nopriv_pdp-flight-log', $plugin_public, 'pdp_no_login' );

		$this->loader->add_action( 'admin_post_pdp_flight_log_add', $plugin_public, 'pdp_flight_log_add' );
		$this->loader->add_action( 'admin_post_nopriv_pdp_flight_log_add', $plugin_public, 'pdp_no_login' );

		$this->loader->add_action( 'admin_post_pdp-flight-log-details', $plugin_public, 'pdp_flight_log_details' );
		$this->loader->add_action( 'admin_post_nopriv_pdp-flight-details', $plugin_public, 'pdp_no_login' );

		$this->loader->add_action( 'wp_ajax_pdp_update_time', $plugin_public, 'pdp_update_time' );
		$this->loader->add_action( 'wp_ajax_nopriv_pdp_update_time', $plugin_public, 'pdp_no_login' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Retrieve the text domain of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The text domain of the plugin.
	 */
     public function pdp_flight_log(){ 
     	if (isset($_GET['pgc_year'])) {
     		wp_redirect($_GET['source_page'].'?pgc_year='.$_GET['pgc_year']);
     	}else {
     		wp_redirect($_GET['source_page']);
     	}
     }
     
     public function include_flightlog_list_edit(){
     	include_once( 'partials/html_cb_pdpflightlog_list_edit.php');
     }

      
     
      public function get_plugin_text_domain() {
     		return $this->plugin_text_domain;
      }

}
