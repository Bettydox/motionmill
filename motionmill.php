<?php if ( ! defined( 'ABSPATH' ) ) exit; // exits when accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill
 Plugin URI: https://github.com/addwittz/motionmill
 Description: Motionmill provides tools that facilitates the creation process of WordPress plugins.
 Version: 1.6
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
		const LANGUAGE_DIR = 'languages';
		const OPTION_NAME  = 'motionmill';
		const TEXTDOMAIN   = 'motionmill';
		const NONCE_NAME   = 'motionmill';
		const NEWLINE      = "\n";
		const VERSION      = '1.6';

		static private $instance = null;

		protected $plugins   = array();
		protected $helpers   = array();
		protected $page_hook = '';

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
			/*
			------------------------------------------------------------------------------------------------------------
			Loads assets
			------------------------------------------------------------------------------------------------------------
			*/
			
			require_once( plugin_dir_path( self::FILE ) . 'config.php' );

			require_once( plugin_dir_path( self::FILE ) . trailingslashit( self::INCLUDE_DIR ) . 'common.php' );

			require_once( ABSPATH . 'wp-admin/includes/plugin.php' ); // needed for <codeget_plugins</code> and <codeget_plugin_data</code>
			
			/*
			------------------------------------------------------------------------------------------------------------
			Loads plugins
			------------------------------------------------------------------------------------------------------------
			*/

			$plugins = array();

			foreach ( $this->get_internal_plugins() as $file => $data )
			{
				require_once( trailingslashit( WP_PLUGIN_DIR ) . $file );

				$plugins[] = $file;
			}

			$this->set_option( 'plugins', $plugins );

			/*
			------------------------------------------------------------------------------------------------------------
			Registers plugins
			------------------------------------------------------------------------------------------------------------
			*/

			foreach ( apply_filters( 'motionmill_plugins', array() ) as $class )
			{
				if ( isset( $this->plugins[ $class ] ) )
				{	
					continue;
				}

				$args = isset( $config ) && isset( $config[$class] ) ? $config[$class] : null;

				if ( $args )
				{
					$plugin = new $class( $args );
				}

				else
				{
					$plugin = new $class();
				}

				$this->plugins[ $class ] = $plugin;
			}

			/*
			------------------------------------------------------------------------------------------------------------
			Loads helpers
			------------------------------------------------------------------------------------------------------------
			*/

			foreach ( apply_filters( 'motionmill_helpers', array() ) as $class )
			{
				if ( isset( $this->helpers[ $class ] ) )
				{
					continue;
				}

				$file = 'class-' . str_replace( '_' , '-', strtolower( $class ) ) . '.php'; // MM_Array => class-mm-array.php

				require_once( plugin_dir_path( self::FILE ) . trailingslashit( self::INCLUDE_DIR ) . $file );

				$this->helpers[ $class ] = true;
			}

			/* ------------------------------------------------------------------------------------------------------ */

			register_activation_hook( self::FILE, array( &$this, 'on_activate' ), 5 );
			register_deactivation_hook( self::FILE, array( &$this, 'on_deactivate' ), 5 );

			add_action( 'wp_enqueue_scripts', array( &$this, 'on_enqueue_scripts' ), 5 );
			add_action( 'admin_enqueue_scripts', array( &$this, 'on_enqueue_scripts' ), 5 );
			add_action( 'plugins_loaded', array( &$this, 'load_textdomain' ) );

			add_filter( 'motionmill_settings_pages', array( &$this, 'on_settings_pages' ), 5 );
			add_filter( 'motionmill_validate_plugin', array( &$this, 'is_motionmill_plugin' ), 5 );
			
			do_action( 'motionmill_init' ); // prefered hook for plugin initialization
		}

		public function get_option( $key = null, $default = '' )
		{
			$options = get_option( self::OPTION_NAME, array() );

			if ( ! is_array( $options ) )
			{
				$options = array();
			}

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

		public function set_option( $key, $value )
		{
			$options = $this->get_option();
			
			$options[ $key ] = $value;

			return update_option( self::OPTION_NAME, $options );
		}

		public function delete_option( $key )
		{
			$options = $this->get_option();
			
			if ( isset( $options[ $key ] ) )
			{
				$options[ $key ] = null;

				unset( $options[ $key ] );

				update_option( self::OPTION_NAME, $options );
			}

			return true;
		}

		public function get_plugin( $class )
		{
			if ( isset( $this->plugins[$class] ) )
			{
				return $this->plugins[$class];
			}

			return null;
		}

		public function is_motionmill_plugin( $file )
		{
			if ( strpos( $file, 'motionmill-' ) !== 0  )
			{
				return false;
			}

			return true;
		}

		public function get_plugins( $dir = '' )
		{
			$plugins = array();

			foreach ( get_plugins( '/' . $dir ) as $file => $plugin )
			{
				$valid = apply_filters( 'motionmill_validate_plugin', $file );

				if ( ! $valid )
				{
					continue;
				}

				// makes sure file is relative to WP_PLUGIN_DIR
				if ( $dir )
				{
					$file = trailingslashit( $dir ) . $file;
				}

				$plugins[ $file ] = $plugin;
			}

			return $plugins;
		}

		public function get_internal_plugins()
		{
			$dir = trailingslashit( dirname( plugin_basename( self::FILE ) ) ) . self::PLUGIN_DIR;

			return $this->get_plugins( $dir );
		}

		public function get_external_plugins()
		{
			return $this->get_plugins();
		}

		public function get_all_plugins()
		{
			$plugins = array_merge( $this->get_external_plugins(), $this->get_internal_plugins() );

			uksort( $plugins, array( &$this, 'on_sort_plugins' ) );

			return $plugins;
		}

		public function get_plugin_repository_name( $file )
		{
			return basename( dirname( $file ) );
		}

		public function load_textdomain()
		{
			// loads plugins language folder
			foreach ( $this->get_all_plugins() as $file => $plugin )
			{
				$dir = dirname( $file ) . '/' . self::LANGUAGE_DIR . '/';

				if ( ! file_exists( trailingslashit( WP_PLUGIN_DIR ) . $dir ) )
				{
					continue;
				}

				load_plugin_textdomain( Motionmill::TEXTDOMAIN, false, $dir );
			}
		}

		function on_settings_pages( $pages )
		{
			$pages[] = array
			(
				'id' 		    => 'motionmill',
				'title' 	    => __( 'Motionmill', Motionmill::TEXTDOMAIN ),
				'menu_title'    => __( 'Motionmill', Motionmill::TEXTDOMAIN ),
				'parent_slug'   => '',
				'menu_slug'     => 'motionmill'
			);

			return $pages;
		}

		public function on_sort_plugins( $a, $b )
		{
			return basename( $a ) < basename( $b );
		}

		public function on_activate()
		{
			foreach ( $this->get_internal_plugins() as $file => $data )
			{
				do_action( 'activate_' . $file );
			}

			$this->set_option( 'version', self::VERSION );
		}

		public function on_deactivate()
		{
			foreach ( $this->get_internal_plugins() as $file => $data )
			{
				do_action( 'deactivate_' . $file );
			}
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
