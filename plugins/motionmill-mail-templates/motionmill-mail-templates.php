<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
Plugin Name: Motionmill Mail templates
Plugin URI: http://www.motionmill.com
Description: Possibility to create and edit mail templates for WordPress mails.
Version: 0.1
Author: Motionmill
Author URI: http://motionmill.com
License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

// loads plugin when motionmill is loaded 
add_action( 'motionmill_loaded', function(){ include('plugin.php'); } );

?>