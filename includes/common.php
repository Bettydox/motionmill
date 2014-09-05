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

if ( ! class_exists('MM_Wordpress') )
{
	class MM_Common
	{
		public static function url_exists( $url )
		{
			$headers = @get_headers( $url );

			if( empty( $headers ) || $headers[0] == 'HTTP/1.1 404 Not Found')
			{
	   			return false;
	   		}

	   		return true;
	   	}
	}
}

?>