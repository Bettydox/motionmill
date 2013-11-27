<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill
 Plugin URI: http://www.motionmill.com
 Description: Motionmill's HQ
 Version: 1.0.0
 Author: Motionmill
 Author URI: http://www.motionmill.com
------------------------------------------------------------------------------------------------------------------------
*/

define( 'MM_FILE', __FILE__ );
define( 'MM_ABSPATH', plugin_dir_path(MM_FILE) );
define( 'MM_INCPATH', MM_ABSPATH . 'includes/' );
define( 'MM_PLUGIN_DIR', MM_ABSPATH . 'plugins' );
define( 'MM_TEXTDOMAIN', 'motionmill' );
define( 'MM_NONCE', '_mmnonce' );

require_once( MM_INCPATH . 'common.php' );

if ( ! class_exists('Motionmill') )
{
	class Motionmill
	{
		public $menu_page  = 'motionmill_settings';
		protected $plugins = array();
		protected $helpers = array();

		public function __construct()
		{
			add_action( 'init', array(&$this, 'initialize'), 0 );

			do_action( 'motionmill' );
		}

		public function initialize()
		{
			// loads plugins -------------------------------------------------------------------------------------------

			require_once( MM_INCPATH . 'mm-plugin.php' );

			foreach ( $this->get_plugin_files() as $file )
			{
				require_once( MM_PLUGIN_DIR . $file );
			}

			do_action( 'motionmill_loaded' );

			// registers plugins ---------------------------------------------------------------------------------------

			foreach ( apply_filters( 'motionmill_plugins', array() ) as $class )
			{
				// checks if already registered
				if ( isset($this->plugins[$class]) )
				{
					trigger_error( sprintf('plugin %s already registered.', $class), E_USER_NOTICE );
					continue;
				}

				// checks if class exists
				if ( ! class_exists($class) )
				{
					trigger_error( sprintf('plugin %s could not be found.', $class), E_USER_NOTICE );
					continue;
				}

				// checks if class is child of Motionmill plugin
				$class_parents = class_parents($class);
				if ( ! isset($class_parents['MM_Plugin']) )
				{
					trigger_error( sprintf('plugin %s must subclass MM_Plugin.', $class), E_USER_WARNING );
					continue;
				}

				// instantiates and registers plugin
				$this->plugins[ $class ] = new $class();
			}

			// loads helpers -------------------------------------------------------------------------------------------

			foreach ( apply_filters( 'motionmill_helpers', array() ) as $name )
			{
				// checks if already registered
				if ( isset($this->helpers[$name]) )
					continue;

				$file = sprintf( '/helpers/%s-helper.php', $name );
				
				// checks if file exists
				if ( ! file_exists( MM_INCPATH . $file ) )
				{
					trigger_error( sprintf('helper %s does not exist.', $name), E_USER_WARNING );
					continue;
				}

				// loads file
				include MM_INCPATH . $file;

				// registers helper
				$this->helpers[$name] = true;
			}

			// ---------------------------------------------------------------------------------------------------------

			// subscribes for events
			add_action( 'admin_menu', array(&$this, 'on_admin_menu'), 0 );

			// notifies observers that we are initialized
			do_action( 'motionmill_init' );
		}

		public function get_plugin($class)
		{
			return isset( $this->plugins[$class] ) ? $this->plugins[$class] : null;
		}
		
		public function on_admin_menu()
		{
			add_menu_page( __('Motionmill', MM_TEXTDOMAIN), __('Motionmill', MM_TEXTDOMAIN), 'manage_options', $this->menu_page, null );

			do_action( 'motionmill_admin_menu' );
		}

		protected function get_plugin_files()
		{
			// path structure: motionmill-{slug}/motionmill-{slug}.php

			$plugins = array();

			if ( $fh = opendir( MM_PLUGIN_DIR ) )
			{
				while ( ($dir = readdir($fh)) !== false )
				{
					if ( ! is_dir( MM_PLUGIN_DIR . '/' . $dir) )
						continue;
	        		
					if ( in_array($dir, array('.', '..')) )
						continue;

					$file = '/' . $dir . '/' . $dir . '.php';

					if ( ! file_exists(MM_PLUGIN_DIR . $file) )
						continue;

	        		$plugins[] = $file;
	   			}
			}

   			return $plugins;
		}
	}

	$motionmill = &mm_get_instance();
}

?>
