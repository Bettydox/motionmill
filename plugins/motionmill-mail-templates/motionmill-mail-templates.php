<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Mail Templates
 Plugin URI: http://motionmill.com
 Description: Possibility to create and edit mail templates for WordPress mails.
 Version: 0.1
 Author: Motionmill
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

// loads plugin when motionmill is instantiated 
add_action( 'motionmill_loaded', create_function('$a', "include('plugin.php');" ) );

?>