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

// loads plugins uninstall.php file

$options = get_option( 'motionmill', array() );

if ( isset( $options['plugins'] ) && is_array( $options['plugins'] ) )
{
	$plugins = $options['plugins'];

	foreach ( $plugins as $file )
	{
		$path = trailingslashit( WP_PLUGIN_DIR ) . dirname( $file ) . '/uninstall.php';

		if ( ! file_exists( $path ) )
		{
			continue;
		}

		include( $path );
	}
}

delete_option( 'motionmill' );

?>