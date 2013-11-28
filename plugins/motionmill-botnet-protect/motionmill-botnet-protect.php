<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Botnet Protect
 Plugin URI: http://www.motionmill.com
 Description: Protects against Botnet attack directly targeting wp-login.php.
 Author: Motionmill
 Author URI: http://motionmill.com
 Version: 1.0
 License : GPL2
------------------------------------------------------------------------------------------------------------------------
*/

// loads plugin when motionmill is loaded 
add_action( 'motionmill_loaded', create_function('$a', "include('plugin.php');" ) );

?>