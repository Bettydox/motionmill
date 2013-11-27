<?php

// checks if request is comming from WordPress
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit;

// checks if this plugin needs to be uninstalled
if ( WP_UNINSTALL_PLUGIN != plugin_basename(__FILE__) ) 
    exit;

// notifies observers
do_action( 'motionmill_uninstall' );

?>