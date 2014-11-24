<?php if ( ! defined( 'ABSPATH' ) ) exit; // exits when accessed directly

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
{
	return;
}

require_once( dirname( __FILE__ ) . '/motionmill.php' );

$motionmill->uninstall();

?>