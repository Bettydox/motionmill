<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Log
 Plugin URI: http://motionmill.com
 Description: Enables Logging. A log overview is available in the admin.
 Version: 0.1
 Author: Motionmill
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

// loads plugin when motionmill is loaded 
add_action( 'motionmill_loaded', function(){ include('plugin.php'); } );

?>