<?php

/**
 * Plugin Name: sjf-log-js-errors
 * Plugin URI: http://scottfennell.org
 * Description: Logs javascript errors to a page in wp-admin.
 * Version: 0.1
 * Author: Scott Fennell
 * Author URI: http://scottfennell.org
 * License: GPL2
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) { die; }

// A constant to define the path to this plugin file.
define( 'SJF_LJE_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );

// A constant to define the url to this plugin file.
define( 'SJF_LJE_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );

/**
 * Instantiate the plugin class.
 */
function sjf_lje_init() {
	new SJF_Log_JS_Errors();
}
add_action( 'init', 'sjf_lje_init' );

/**
 * The main plugin class.  Loads javascript to do ajax read more.
 */
class SJF_Log_JS_Errors {

	// Incement this on every update in order to force the browser to update scripts.
	var $version = '0.1';

	// This will be the slug for the log page.
	var $log_page_slug = 'sjf-log-js-errors';

	/**
	 * Adds actions for our class methods.
	 */
	function __construct() {

        // Enqueue the script to power the logs.
        wp_enqueue_script( 'sjf_lje',
            plugin_dir_url( __FILE__ ) . 'js/script.js',
            array( 'jquery' ),
            $this -> version,
            false
        );

        // Pass our script to the WordPress Ajax API.
        add_action( 'template_redirect', array( $this, 'localize_script' ) );
		add_action( 'admin_init', array( $this, 'localize_script' ) );

        // Ajax handler for non logged in users.
        add_action( 'wp_ajax_nopriv_sjf_lje_ajax', array( $this, 'sjf_lje_ajax' ) );

        // Ajax handler for logged in users.
        add_action( 'wp_ajax_sjf_lje_ajax', array( $this, 'sjf_lje_ajax' ) );

        // Add a dummy error, for testing purposes.  Uncomment these for testing.
		// add_action( 'wp_footer', array( $this, 'error' ) );
		// add_action( 'admin_footer', array( $this, 'error' ) );

        // Create the logs page.
		add_action( 'init', array( $this, 'create_log_page' ) );
		add_action( 'admin_init', array( $this, 'create_log_page' ) );

	}

    /**
     * Enqueues the main js file for the plugin,
     */
	function localize_script() {

        // Get current page protocol.
        $protocol = isset( $_SERVER[ 'HTTPS' ] ) ? 'https://' : 'http://';

        // Output admin-ajax.php URL with same protocol as current page.
        $params = array(
          'ajaxurl' => admin_url( 'admin-ajax.php', $protocol )
        );

        // Grab JS values from the script we just registered.
        wp_localize_script( 'sjf_lje', 'sjf_lje', $params );
    }

    /**
     * This function is hooked to the WP Ajax APi and logs JS errors to the logs page.
     */
	function sjf_lje_ajax() {
        
     	// We are expecting a post var for 'action'.
        if( ! isset( $_POST[ 'action' ] ) ) { return false; }
		if( $_POST[ 'action' ] != 'sjf_lje_ajax' ) { return false; }

		// We are expecting a post var for 'log'.
		if( ! isset( $_POST[ 'log' ] ) ) { return false; }

		// Sanitize the log data.
		$log = wp_kses_post( $_POST[ 'log' ] );

		// Grab the name of the logs page.
		$post_name = $this -> log_page_slug;

		// Grab the object for the logs page.
		$log_page = get_page_by_path( $post_name );
		if( ! $log_page ) { return false; }

		// Grab the ID for the logs page.
		$post_id = absint( $log_page -> ID );

		// Grab the content for the logs page.
		$post_content = wp_kses_post( $log_page -> post_content );

		// Add this log entry to the content.
		$post_content .= '<p>' . $log . '</p>';

		// Update the post.
		$post = array(
			'ID'			=> $post_id,
			'post_content'	=> $post_content,
		);
		$updated = wp_update_post( $post );

		// This is necessary to avoid outputting a "0" after any ajax call in WordPress.
		die();

    }

	/**
	 * Create a sample JS error, to test the logging.
	 */
	function error() {
		?>
			<script>
				error;
			</script>
		<?php
	}

	/**
	 * Create a page to log errors.
	 */
	function create_log_page() {

		// Grab the name for the logs page.
		$post_name = $this -> log_page_slug;

		// If it already exists, bail.
		$maybe_already_exists = get_page_by_path( $post_name );
		if( $maybe_already_exists ) { return false; }

		// Start the page with some explanatory text.
		$post_content = '<p>' . esc_html__( 'This page is created by the SJF Log JS Error plugin.  Do not edit or delete this page.', 'sjf_lje' ) . '</p>';

		// Give the page a title.
		$post_title = esc_html__( 'This page is created by the SJF Log JS Error plugin.  Do not edit or delete this page.', 'sjf_lje' );

		// Create the page.
		$post = array(
			'post_title'	=> $post_title,
			'post_name'		=> $post_name,
			'post_content'	=> $post_content,
			'post_type'		=> 'page',	
			'post_status'	=> 'publish',
		);
		$inserted = wp_insert_post( $post );

	}

// End class SJF_Log_JS_Errors
}

?>