<?php if ( ! defined( 'ABSPATH' ) ) exit; // exits when accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill
 Plugin URI: https://github.com/addwittz/motionmill
 Description: Motionmill provides tools that facilitates the creation process of WordPress plugins.
 Version: 1.5.8
 Author: Maarten Menten
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

if ( ! class_exists( 'Motionmill' ) )
{
	class Motionmill
	{
		const FILE         = __FILE__;
		const PLUGIN_DIR   = 'plugins';
		const INCLUDE_DIR  = 'includes';
		const OPTION_NAME  = 'motionmill';
		const TEXTDOMAIN   = 'motionmill';
		const NONCE_NAME   = 'motionmill';
		const NEWLINE      = "\n";
		const VERSION      = '1.5.8';

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
			require_once( $this->get_absolute_path() . 'config.php' );
			require_once( $this->get_absolute_path( self::INCLUDE_DIR ) . 'common.php' );
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' ); // needed for <codeget_plugins</code> and <codeget_plugin_data</code>

			/*
			------------------------------------------------------------------------------------------------------------
			Loads plugins
			------------------------------------------------------------------------------------------------------------
			*/

			$plugins = array();

			foreach ( $this->get_plugins_data() as $file => $data )
			{
				require_once( trailingslashit( WP_PLUGIN_DIR ) . $file );

				$plugins[] = $file;

				$this->log( 'plugin %s loaded.', $file );
			}

			$this->set_option( 'plugins', $plugins );

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

				$this->log( 'plugin %s registered.', $class );
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

				$this->log( 'helper %s registered.', $class );
			}

			/* ------------------------------------------------------------------------------------------------------ */

			register_activation_hook( self::FILE, array( &$this, 'on_activate' ), 5 );
			register_deactivation_hook( self::FILE, array( &$this, 'on_deactivate' ), 5 );

			add_action( 'admin_menu', array( &$this, 'on_admin_menu' ), 5 );
			add_action( 'admin_bar_menu', array( &$this, 'on_admin_bar_menu' ), 100 );

			add_action( 'wp_enqueue_scripts', array( &$this, 'on_enqueue_scripts' ), 5 );
			add_action( 'admin_enqueue_scripts', array( &$this, 'on_enqueue_scripts' ), 5 );

			add_action( 'plugins_loaded', array( &$this, 'load_textdomain' ) );

			do_action( 'motionmill_init' ); // prefered hook for plugin initialization

			$this->initialized = true;
		}
		
		public function get_option( $key = null, $default = '', $group = 'general' )
		{
			$options = $this->get_options( $group );

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

		public function get_options( $group = '' )
		{
			$options = (array) get_option( self::OPTION_NAME, array() );

			if ( ! $group )
			{
				return $options;
			}

			if ( isset( $options[ $group ] ) )
			{
				return $options[ $group ];
			}

			return array();
		}

		public function set_option( $key, $value, $group = 'general' )
		{
			if ( ! $group )
			{
				return false;
			}

			$options = $this->get_options();

			if ( ! isset( $options[ $group ] ) )
			{
				$options[ $group ] = array();
			}

			$options[ $group ][ $key ] = $value;

			return update_option( self::OPTION_NAME, $options );
		}

		public function get_plugin( $class )
		{
			if ( isset( $this->plugins[$class] ) )
			{
				return $this->plugins[$class];
			}

			return null;
		}

		public function get_plugins_data( $type = 'intern' )
		{
			$types = func_get_args();
			
			if ( count( $types ) == 0 )
			{
				$types[] = 'intern';
			}

			// relative to WP_PLUGIN_DIR
			$dirs = array
			(
				'intern' => '/' . $this->get_relative_path( self::PLUGIN_DIR ),
				'extern' => ''
			);

			$dirs = array_intersect_key( $dirs , array_flip( $types ) );

			$data = array();

			foreach ( $dirs as $dir )
			{
				foreach ( get_plugins( $dir ) as $file => $plugin_data )
				{
					if ( stripos( dirname($file), 'motionmill-' ) !== 0 )
					{
						continue;
					}

					// makes sure file is relative to WP_PLUGIN_DIR
					$file = trailingslashit( ltrim( $dir, '/' ) ) . $file;

					$data[ $file ] = $plugin_data;
				}
			}

			uksort( $data, function( $a, $b )
			{
				return basename( $a ) > basename( $b );
			});

			return $data;
		}

		public function get_absolute_path( $suffix = '' )
		{
			$path = plugin_dir_path( self::FILE );

			if ( $suffix != '' )
			{
				$path .= trailingslashit( ltrim( $suffix, '/' ) );
			}

			return $path;
		}

		public function get_relative_path( $suffix = '' )
		{
			return plugin_basename( $this->get_absolute_path( $suffix ) );
		}

		public function log( $value )
		{
			$args = func_get_args();

			if ( count( $args) > 1 )
			{
				$message = call_user_func_array( 'sprintf' , $args );
			}

			else
			{
				$message = $args[0];
			}

			$message = sprintf( '[motionmill] %s', $message );

			return error_log( $message );
		}

		public function load_textdomain()
		{
			foreach ( $this->get_plugins_data( 'intern', 'extern' ) as $file => $plugin )
			{
				$dir = dirname( $file ) . '/languages/';

				if ( ! file_exists( trailingslashit( WP_PLUGIN_DIR ) . $dir ) )
				{
					continue;
				}

				load_plugin_textdomain( Motionmill::TEXTDOMAIN, false, $dir );
			}
		}

		public function on_activate()
		{
			foreach ( $this->get_plugins_data() as $file => $data )
			{
				do_action( 'activate_' . $file );
			}

			$this->set_option( 'version', self::VERSION );
		}

		public function on_deactivate()
		{
			foreach ( $this->get_plugins_data() as $file => $data )
			{
				do_action( 'deactivate_' . $file );
			}
		}
		
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
