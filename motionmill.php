<?php if ( ! defined( 'ABSPATH' ) ) exit; // exits when accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill
 Plugin URI:
 Description: Motionmill provides tools that facilitates the creation process of WordPress plugins.
 Version: 1.5.0
 Author: Maarten Menten
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

if ( ! class_exists( 'Motionmill' ) )
{
	class Motionmill
	{
		const FILE        = __FILE__;
		const OPTION_NAME = 'motionmill';
		const PLUGIN_DIR  = 'plugins';
		const INCLUDE_DIR = 'includes';
		const TEXTDOMAIN  = 'motionmill';
		const NONCE_NAME  = 'motionmill';
		const NEWLINE     = "\n";
		const VERSION     = '1.0.0';

		static private $instance = null;

		protected $initialized = false;
		protected $plugins     = array();
		protected $helpers     = array();
		protected $page_hook   = '';

		static public function get_instance()
		{
			if ( self::$instance == null )
			{
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function __construct()
		{	

		}

		public function initialize()
		{
			if ( $this->initialized ) return;

			/*
			------------------------------------------------------------------------------------------------------------
			Loads assets
			------------------------------------------------------------------------------------------------------------
			*/

			if ( is_admin() )
			{
				require_once( $this->get_absolute_path( self::INCLUDE_DIR ) . 'motionmill-menu-page-dashboard.php' );
			}

			else
			{
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' ); // needed for get_plugins() in front
			}

			/*
			------------------------------------------------------------------------------------------------------------
			Loads plugins
			------------------------------------------------------------------------------------------------------------
			*/

			foreach ( $this->get_option( 'active_plugins', array() ) as $file )
			{
				$path = trailingslashit( WP_PLUGIN_DIR ) . $file;

				if ( ! file_exists( $path ) )
				{
					continue;
				}

				require_once( $path );
			}

			/*
			------------------------------------------------------------------------------------------------------------
			Registers plugins
			------------------------------------------------------------------------------------------------------------
			*/

			foreach ( apply_filters( 'motionmill_plugins', array() ) as $class )
			{
				if ( isset( $this->plugins[$class] ) )
				{	
					continue;
				}

				$this->plugins[ $class ] = new $class();
			}

			/*
			------------------------------------------------------------------------------------------------------------
			Loads helpers
			------------------------------------------------------------------------------------------------------------
			*/

			foreach ( apply_filters( 'motionmill_helpers', array() ) as $class )
			{
				if ( isset( $this->helpers[$class] ) )
				{
					continue;
				}

				$file = 'class-' . str_replace( '_' , '-', strtolower( $class ) ) . '.php'; // MM_Array => class-mm-array.php

				require_once( $this->get_absolute_path( self::INCLUDE_DIR ) . $file );

				$this->helpers[ $class ] = true;
			}

			/* ------------------------------------------------------------------------------------------------------ */

			register_activation_hook( self::FILE, array( &$this, 'on_activate' ), 5 );
			register_deactivation_hook( self::FILE, array( &$this, 'on_deactivate' ), 5 );

			add_action( 'admin_menu', array( &$this, 'on_admin_menu' ), 5 );
			add_action( 'admin_bar_menu', array( &$this, 'on_admin_bar_menu' ), 100 );

			add_action( 'wp_enqueue_scripts', array( &$this, 'on_enqueue_scripts' ), 5 );
			add_action( 'admin_enqueue_scripts', array( &$this, 'on_enqueue_scripts' ), 5 );

			do_action( 'motionmill_init' ); // prefered hook for plugin initialization

			$this->initialized = true;

		}

		/* ---------------------------------------------------------------------------------------------------------- */

		/**
		 * Get Option
		 *
		 * Returns an option from the database.
		 *
		 * @return mixed
		 */

		public function get_option( $key = null, $default = '' )
		{
			$options = (array) get_option( self::OPTION_NAME, array() );

			if ( $key == null )
			{
				return $options;
			}

			if ( isset( $options[$key] ) )
			{
				return $options[$key];
			}

			return $default;
		}

		/* ---------------------------------------------------------------------------------------------------------- */

		/**
		 * Set Option
		 *
		 * saves an option to the database.
		 *
		 * @return void
		 */

		public function set_option( $key, $value )
		{
			// recursive
			if ( is_array( $key ) )
			{
				foreach ( $key as $option_name => $option_value )
				{
					$this->set_option( $option_name, $option_value );
				}

				return;
			}

			$options = $this->get_option();

			$options[ $key ] = $value;

			update_option( self::OPTION_NAME, $options );
		}

		/* ---------------------------------------------------------------------------------------------------------- */

		/**
		 * Get Plugin
		 *
		 * Returns a registered plugin
		 *
		 * @return mixed An object or null if not found
		 */

		public function get_plugin( $class )
		{
			if ( isset( $this->plugins[$class] ) )
			{
				return $this->plugins[$class];
			}

			return null;
		}

		/* ---------------------------------------------------------------------------------------------------------- */

		/**
		 * Get Plugins
		 *
		 * Returns plugin meta data
		 *
		 * @return Array
		 */

		public function get_plugins()
		{
			$dir = $this->get_relative_path( self::PLUGIN_DIR );

			$plugins = get_plugins( '/' . $dir );

			$a = array();

			// makes file relative to WP_PLUGIN_DIR
			foreach ( $plugins as $file => $plugin )
			{
				$key = trailingslashit( $dir ) . $file;
			
				$a[ $key ] = $plugin;
			}

			return $a;
		}

		/* ---------------------------------------------------------------------------------------------------------- */

		/**
		 * Get External Plugins
		 *
		 * Returns all motionmill plugins in the WordPress plugin directory.
		 *
		 * @return Array
		 */

		public function get_external_plugins()
		{
			$plugins = array();

			foreach ( (array) get_plugins() as $file => $plugin )
			{
				if ( stripos( $file, 'motionmill-' ) === false )
				{
					continue;
				}

				if ( ! in_array( $file , (array) get_option( 'active_plugins' )) )
				{
					continue;
				}

				$plugins[ $file ] = $plugin;
			}

			return $plugins;
		}

		/* ---------------------------------------------------------------------------------------------------------- */

		/**
		 * Get All Plugins
		 *
		 * Returns all motionmill plugins.
		 *
		 * @return Array
		 */

		public function get_all_plugins()
		{
			$plugins = array_merge( $this->get_plugins(), $this->get_external_plugins() );

			ksort( $plugins, SORT_STRING );

			return $plugins;
		}

		/* ---------------------------------------------------------------------------------------------------------- */

		/**
		 * Get Absolute Path
		 *
		 * Returns the server directory path to this file
		 *
		 * @return String
		 */

		public function get_absolute_path( $suffix = '' )
		{
			$path = plugin_dir_path( self::FILE );

			if ( $suffix != '' )
			{
				$path .= trailingslashit( ltrim( $suffix, '/' ) );
			}

			return $path;
		}

		/* ---------------------------------------------------------------------------------------------------------- */

		/**
		 * Get Relative Path
		 *
		 * Returns the server path starting from this directory
		 *
		 * @return String
		 */

		public function get_relative_path( $suffix = '' )
		{
			return plugin_basename( $this->get_absolute_path( $suffix ) );
		}

		/* ---------------------------------------------------------------------------------------------------------- */

		/**
		 * On Activate
		 *
		 * Called when this plugin is activated
		 *
		 * Triggers plugins activation hook.
		 *
		 * @return void
		 */

		public function on_activate()
		{
			$active_plugins = array();

			foreach ( $this->get_plugins() as $file => $plugin )
			{
				$active_plugins[] = $file;

				do_action( 'activate_' . $file );
			}

			$this->set_option( 'version', self::VERSION );
			$this->set_option( 'active_plugins', $active_plugins );
		}

		/* ---------------------------------------------------------------------------------------------------------- */

		/**
		 * On Dectivate
		 *
		 * Called when this plugin is deactivated
		 *
		 * Triggers plugins deactivation hook.
		 *
		 * @return void
		 */

		public function on_deactivate()
		{
			foreach ( (array) $this->get_option( 'active_plugins' ) as $file )
			{
				do_action( 'deactivate_' . $file );
			}

			$this->set_option( 'active_plugins', array() );
		}

		/* ---------------------------------------------------------------------------------------------------------- */

		/**
		 * On Admin Menu
		 *
		 * @return void
		 */

		public function on_admin_menu()
		{
			$page = apply_filters( 'motionmill_menu_page', array
			(
				'title'      => __( 'Motionmill', self::TEXTDOMAIN ),
				'menu_title' => __( 'Motionmill', self::TEXTDOMAIN ),
				'capability' => 'manage_options',
				'menu_slug'  => 'motionmill',
				'function'   => null,
				'icon_url'   => '',
				'position'   => null
			));

			if ( ! $page )
			{
				return;
			}

			$this->page_hook = add_menu_page( $page['title'], $page['menu_title'], $page['capability'], $page['menu_slug'], $page['function'], $page['icon_url'], $page['position'] );
		}

		public function on_admin_bar_menu()
		{
			global $wp_admin_bar;
    		
    		if ( ! is_super_admin() || ! is_admin_bar_showing() )
    		{
    			return;
    		}

    		$wp_admin_bar->add_menu(array
			(
				'id'     => 'motionmill',
				'meta'   => array(),
				'title'  => __( 'Motionmill', Motionmill::TEXTDOMAIN ),
				'href'   => admin_url( 'admin.php?page=motionmill' ),
				'parent' => ''
		    ));
		}

		/* ---------------------------------------------------------------------------------------------------------- */

		/**
		 * On Enqueue Scripts
		 *
		 * @return void
		 */

		public function on_enqueue_scripts()
		{	
			// styles
			wp_register_style( 'motionmill', plugins_url( 'css/style.css', __FILE__ ), null, '1.0.0', 'all' );
			
			wp_enqueue_style( 'motionmill' );
			
			// scripts
			wp_register_script( 'motionmill-plugins', plugins_url( 'js/plugins.js', __FILE__ ), array( 'jquery' ), '1.0.0', false );
			wp_register_script( 'motionmill', plugins_url('js/scripts.js', __FILE__), array( 'jquery', 'motionmill-plugins' ), '1.0.0', false );
			wp_localize_script( 'motionmill', 'Motionmill', apply_filters( 'motionmill_javascript_vars', array() ) );
		
			wp_enqueue_script( 'motionmill' );
		}
	}

	$motionmill = Motionmill::get_instance();
	$motionmill->initialize();
}

?>