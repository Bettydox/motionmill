<?php if ( ! defined( 'ABSPATH' ) ) exit; // exits when accessed directly

class MM_Loader
{
	public function __construct()
	{
		
	}

	public function load_class( $class, $plugin = null )
	{
		if ( class_exists( $class ) )
		{
			return;
		}

		if ( ! $plugin )
		{
			$plugin = Motionmill::FILE;
		}

		if ( ! is_dir( $plugin ) )
		{
			$dir = dirname( $plugin );
		}

		else
		{
			$dir = $plugin;
		}

		$name = str_replace( '_' , '-', strtolower( $class ) );

		$file = sprintf( '%s/includes/class-%s.php', $dir, $name );
	
		require_once( $file );
	}
}

?>