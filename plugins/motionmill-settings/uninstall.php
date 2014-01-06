<?php if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

global $wpdb;

$wpdb->delete( $wpdb->prefix . 'options', "option_name LIKE 'motionmill_%'" );

?>