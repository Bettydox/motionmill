<?php if ( ! defined('ABSPATH') ) exit; // Exits when accessed directly

if ( ! function_exists( 'MM' ) )
{
	function MM( $plugin = null )
	{
		$motionmill = Motionmill::get_instance();

		if ( ! $plugin )
		{
			return $motionmill;
		}

		$prefix = 'MM_';

		if ( strpos( $plugin, $prefix ) !== 0 )
		{
			$plugin = $prefix . $plugin;
		}

		return $motionmill->get_plugin( $plugin );
	}
}

?>