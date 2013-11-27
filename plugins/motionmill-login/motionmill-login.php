<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Login
 Plugin URI: http://motionmill.com
 Description: Customizes the WordPress login page.
 Version: 0.2
 Author: Motionmill
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

// loads plugin when motionmill is instantiated 
add_action( 'motionmill_loaded', function(){ include('plugin.php'); } );

?>