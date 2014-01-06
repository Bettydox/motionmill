<?php if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

if ( ! class_exists('Motionmill') )
{
	require_once( dirname(__FILE__) . '/motionmill.php');
}

$motionmill = Motionmill::get_instance();
$motionmill->on_uninstall();

?>