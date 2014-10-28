<?php if ( ! defined( 'ABSPATH' ) ) exit; // exits when accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill
 Plugin URI: https://github.com/addwittz/motionmill
 Description: Motionmill provides tools that facilitates the creation process of WordPress plugins.
 Version: 1.7.0
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
		const TEXTDOMAIN   = 'motionmill';
		const NONCE_NAME   = '_motionmill_nonce';
		const NEWLINE      = "\n";

		static private $instance = null;
		
		protected $plugins   = array();
		protected $helpers   = array();

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
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' ); // needed for <code>get_plugins()</code>

			$this->helpers = array( 'MM_Common', 'MM_HTML', 'MM_Wordpress' );

			/*
			------------------------------------------------------------------------------------------------------------
			Loads plugins
			------------------------------------------------------------------------------------------------------------
			*/

			$active_plugins = array();

			foreach ( $this->get_plugins() as $file => $data )
			{
				require_once( trailingslashit( WP_PLUGIN_DIR ) . $file );

				$active_plugins[] = $file;
			}

			update_option( 'motionmill_active_plugins', $active_plugins );

			/*
			------------------------------------------------------------------------------------------------------------
			Registers plugins
			------------------------------------------------------------------------------------------------------------
			*/

			foreach ( apply_filters( 'motionmill_plugins', $this->plugins ) as $class )
			{
				if ( isset( $this->plugins[ $class ] ) )
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

			foreach ( apply_filters( 'motionmill_helpers', $this->helpers ) as $class )
			{
				if ( isset( $this->helpers[ $class ] ) )
				{
					continue;
				}

				$file = 'class-' . str_replace( '_' , '-', strtolower( $class ) ) . '.php'; // MM_Array => class-mm-array.php

				require_once( plugin_dir_path( self::FILE ) . 'includes/' . $file );

				$this->helpers[ $class ] = true;
			}

			/* ------------------------------------------------------------------------------------------------------ */

			register_activation_hook( self::FILE, array( &$this, 'on_activate' ), 5 );
			register_deactivation_hook( self::FILE, array( &$this, 'on_deactivate' ), 5 );

			add_action( 'wp_enqueue_scripts', array( &$this, 'on_enqueue_scripts' ), 5 );
			add_action( 'admin_enqueue_scripts', array( &$this, 'on_enqueue_scripts' ), 5 );
			
			add_action( 'plugins_loaded', array( &$this, 'load_textdomain' ), 5 );

			add_filter( 'motionmill_settings_pages', array( &$this, 'on_settings_pages' ), 5 );
			
			add_action( 'init', array( &$this, 'on_init' ) );
		}

		public function on_init()
		{
			do_action( 'motionmill_init' ); // prefered hook for plugin initialization
			
			if ( MM_Wordpress::is_multilingual() )
			{
				$this->create_wpml_config_file();
			}
		}

		public function get_plugin( $class )
		{
			return $this->plugins[ $class ];
		}

		public function get_plugins()
		{
			$dir = trailingslashit( dirname( plugin_basename( self::FILE ) ) ) . trailingslashit( 'plugins' );

			$plugins = array();

			foreach ( get_plugins( '/' . $dir ) as $file => $data )
			{	
				// makes file relative to the WordPress plugin directory
				$file = trailingslashit( $dir ) . $file;
				
				$plugins[ $file ] = $data;
			}

			return $plugins;
		}

		public function load_textdomain()
		{
			// loads plugins textdomain
			foreach ( $this->get_plugins() as $file => $data )
			{
				$dir = trailingslashit( dirname( $file ) ) . trailingslashit( 'languages' );

				if ( ! file_exists( trailingslashit( WP_PLUGIN_DIR ) . $dir ) )
				{
					continue;
				}

				load_plugin_textdomain( Motionmill::TEXTDOMAIN, false, $dir );
			}
		}

		public function create_wpml_config_file()
		{
			$file = plugin_dir_path( Motionmill::FILE ) . 'wpml-config.xml';

			if ( file_exists( $file ) )
			{
				return;
			}

			$str = '';
			$str .= '<?xml version="1.0" standalone="yes"?>' . "\n";
			$str .= '<wpml-config></wpml-config>';

			$config = new SimpleXMLElement( $str );

			$config = apply_filters( 'motionmill_wpml_config', $config );

			$config->asXML( $file );
		}

		public function on_activate()
		{
			// triggers plugins activation hook
			foreach ( $this->get_plugins() as $file => $data )
			{
				do_action( 'activate_' . $file );
			}
		}

		public function on_deactivate()
		{
			// triggers plugins deactivation hook
			foreach ( $this->get_plugins() as $file => $data )
			{
				do_action( 'deactivate_' . $file );
			}
		}

		public function on_settings_pages( $pages )
		{
			$pages[] = array
			(
				'id'            => 'motionmill',
				'title'         => __( 'Motionmill', Motionmill::TEXTDOMAIN ),
				'description'   => __( '', Motionmill::TEXTDOMAIN ),
				'parent_slug'   => '',
				'submit_button' => false
			);

			return $pages;
		}

		public function on_enqueue_scripts()
		{	
			// styles
			wp_register_style( 'font-awesome', plugins_url( 'css/font-awesome.min.css', self::FILE ), '4.2.0' );
			wp_register_style( 'jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/black-tie/jquery-ui.css', '1.10.3' );
			wp_register_style( 'motionmill', plugins_url( 'css/style.css', self::FILE ) );
			
			wp_enqueue_style( 'motionmill' );
			
			// scripts
			wp_register_script( 'motionmill-plugins', plugins_url( 'js/plugins.js', self::FILE ), array( 'jquery' ), '1.0.0', false );
			wp_register_script( 'motionmill', plugins_url('js/scripts.js', self::FILE), array( 'jquery', 'motionmill-plugins' ), '1.0.0', false );
			
			wp_localize_script( 'motionmill', 'Motionmill', apply_filters( 'motionmill_javascript_vars', array() ) );
		
			wp_enqueue_script( 'motionmill' );
		}
	}
	
	$motionmill = Motionmill::get_instance();
	$motionmill->initialize();
}

?>
