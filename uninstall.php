<?php if ( ! defined( 'ABSPATH' ) ) exit; // exits when accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Checks for valid uninstallation request
------------------------------------------------------------------------------------------------------------------------
*/

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
{
	return;
}

/*
------------------------------------------------------------------------------------------------------------------------
 Uninstallation Process
------------------------------------------------------------------------------------------------------------------------
*/

$active_plugins = get_option( 'motionmill_active_plugins', array() );

if ( is_array( $active_plugins ) )
{
	// loads plugins uninstall file
	foreach ( $active_plugins as $file )
	{
		$uninstall_file = trailingslashit( WP_PLUGIN_DIR ) . trailingslashit( dirname( $file ) ) . basename( __FILE__ );

		if ( ! file_exists( $uninstall_file ) )
		{
			continue;
		}

		include $uninstall_file;
	}
}

delete_option( 'motionmill_active_plugins' );

?>