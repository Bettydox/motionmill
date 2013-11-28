<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Dashboard Widget
 Plugin URI: http://motionmill.com
 Description: Creates an editable widget on the dashboard.
 Version: 1.0
 Author: Motionmill
 Author URI: http://motionmill.com
 License : GPL2
------------------------------------------------------------------------------------------------------------------------
*/

// loads plugin when motionmill is loaded 
add_action( 'motionmill_loaded', create_function('$a', "include('plugin.php');" ) );

?>