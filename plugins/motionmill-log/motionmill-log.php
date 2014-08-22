<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Log
 Plugin URI:
 Description:
 Version: 1.0.0
 Author: Maarten Menten
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

if ( ! class_exists( 'MM_Log' ) )
{
	class MM_Log
	{
		const FILE = __FILE__;

		public function __construct()
		{	
			add_action( 'motionmill_init', array( &$this, 'initialize' ) );
		}

		public function initialize()
		{
			
		}

		public function add( $content )
		{
			$message = array
			(
				'motionmill' => null,
				'content'    => $content
			);

			$this->save( $message );
		}

		protected function save( $message )
		{
			$str = '';

			foreach ( $message as $key => $value )
			{
				if ( $key == 'content' )
				{
					continue;
				}

				if ( $value === null )
				{
					$str .= sprintf( '[%s]', $key );
				}

				else
				{
					$str .= sprintf( '[%s:%s]', $key, $value );
				}

				$str .= ' ';
			}

			$str .= $message['content'];

			error_log( $str );
		}
	}
}

// registers plugin
if ( ! function_exists('motionmill_plugins_add_log') )
{
	function motionmill_plugins_add_log($plugins)
	{
		$plugins[] = 'MM_Log'; // The plugin's class name

		return $plugins;
	}

	add_filter( 'motionmill_plugins', 'motionmill_plugins_add_log' );
}

?>