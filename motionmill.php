<?php if ( ! defined('ABSPATH') ) exit; // Exits when accessed directly
/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill
 Plugin URI: http://motionmill.com
 Description: Motionmill's HQ
 Version: 1.1.2
 Author: Motionmill
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/
if ( ! class_exists('Motionmill') )
{
	define( 'MM_FILE', __FILE__ );
	define( 'MM_ABSPATH', plugin_dir_path(MM_FILE) );
	define( 'MM_INCLUDE_DIR', MM_ABSPATH . 'includes/' );
	define( 'MM_PLUGIN_DIR', MM_ABSPATH . 'plugins/' );
	define( 'MM_TEXTDOMAIN', 'motionmill' );

	class Motionmill
	{
		private static $instance = null;
		public $page_slug = null;

		public static function get_instance()
		{
			if ( ! self::$instance )
			{
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function __construct()
		{
			require_once( MM_INCLUDE_DIR . 'class-mm-helper.php' );

			// loads plugins
			require_once( MM_INCLUDE_DIR . 'class-mm-plugin.php' );

			foreach ( $this->get_plugin_files() as $file )
			{
				require_once( MM_PLUGIN_DIR . $file );
			}

			add_action( 'init', array(&$this, 'initialize'), 0 );

			do_action( 'motionmill_loaded' );
		}

		public function initialize()
		{
			// registers plugins
			foreach ( apply_filters( 'motionmill_plugins', array() ) as $plugin )
			{
				if ( isset($this->plugins[$plugin]) )
					continue;

				if ( ! class_exists($plugin) )
				{
					trigger_error( sprintf('Plugin class %s could not be found', $plugin) , E_USER_NOTICE );

					continue;
				}

				$parents = class_parents($plugin);

				if ( ! isset($parents['MM_Plugin']) )
				{
					trigger_error( sprintf('Plugin %s is not a child of MM_Plugin', $plugin) , E_USER_NOTICE );

					continue;
				}

				$this->plugins[ $plugin ] = new $plugin();
			}

			add_action( 'admin_menu', array(&$this, 'on_admin_menu'), 0 );

			do_action( 'motionmill_init' );

			// let others set the default submenu page
			$this->page_slug = apply_filters( 'motionmill_page_slug', null );
		}

		public function get_plugin($class)
		{
			return isset( $this->plugins[$class] ) ? $this->plugins[$class] : null;
		}

		public function on_admin_menu()
		{
			if ( ! $this->page_slug )
				return;

			add_menu_page( __( 'Motionmill', MM_TEXTDOMAIN ), __( 'Motionmill', MM_TEXTDOMAIN ), 'manage_options', $this->page_slug, create_function('$a', '') );
		
			do_action( 'motionmill_admin_menu' );
		}

		public function on_uninstall()
		{
			if ( ! defined('WP_UNINSTALL_PLUGIN') )
				return;

			if ( WP_UNINSTALL_PLUGIN != plugin_basename(MM_FILE) ) 
				return;

			// loads plugins uninstall.php file
			foreach ( $this->get_plugin_files() as $file )
			{
				$uninstall = MM_PLUGIN_DIR . trim( dirname($file), '/' ) . '/uninstall.php';

				if ( file_exists($uninstall) )
				{
					include( $uninstall );
				}
			}
		}

		private function get_plugin_files()
		{
			// path structure: motionmill-{slug}/motionmill-{slug}.php

			$plugins = array();

			if ( $fh = opendir( MM_PLUGIN_DIR ) )
			{
				while ( ($dir = readdir($fh)) !== false )
				{
					if ( ! is_dir( MM_PLUGIN_DIR . $dir) )
						continue;
	        		
					if ( in_array($dir, array('.', '..')) )
						continue;

					$file = $dir . '/' . $dir . '.php';

					if ( ! file_exists(MM_PLUGIN_DIR . $file) )
						continue;

	        		$plugins[] = $file;
	   			}
			}

   			return $plugins;
		}
	}

	$motionmill = Motionmill::get_instance();
}

?>