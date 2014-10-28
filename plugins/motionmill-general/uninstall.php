<?php if ( ! defined( 'ABSPATH' ) ) exit; // exists when accessed directly

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
{
	return;
}

delete_post_meta_by_key( '_motionmill_wpautop' );

?>