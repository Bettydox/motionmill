<<<<<<< HEAD
<?php if ( ! defined( 'ABSPATH' ) ) exit; // exits when accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill
 Plugin URI:
 Description: Motionmill provides tools that facilitates the creation process of WordPress plugins.
 Version: 1.4.1
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
		const VERSION     = '1.4.1';

		static private $instance = null;

		protected $initialized = false;
		protected $plugins = array();
		protected $helpers = array();

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

			foreach ( (array) $this->get_option( 'active_plugins' ) as $file )
			{
				$path = trailingslashit( WP_PLUGIN_DIR ) . $file;

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

			add_action( 'wp_enqueue_scripts', array( &$this, 'on_enqueue_scripts' ), 5 );
			add_action( 'admin_enqueue_scripts', array( &$this, 'on_enqueue_scripts' ), 5 );

			add_filter( 'motionmill_javascript_vars', array( &$this, 'on_javascript_vars'), 5 );
			add_filter( 'motionmill_settings_options', array( &$this, 'on_settings_options'), 5 );
			add_filter( 'motionmill_settings_pages', array( &$this, 'on_settings_pages'), 5 );

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
			$options = get_option( self::OPTION_NAME, array() );

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
			$options = $this->get_option();

			$options[ $key ] = $value;

			return update_option( self::OPTION_NAME, $options );
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
			$this->set_option( 'version', self::VERSION );

			$active_plugins = array();

			foreach ( $this->get_plugins() as $file => $plugin )
			{
				$active_plugins[] = $file;

				do_action( 'activate_' . $file );
			}

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
		 * On Enqueue Scripts
		 *
		 * @return void
		 */

		public function on_enqueue_scripts()
		{	
			// styles
			wp_register_style( 'motionmill', plugins_url('css/style.css', __FILE__), null, '1.0.0', 'all' );
			
			wp_enqueue_style( 'motionmill' );
			
			// scripts
			wp_register_script( 'motionmill-plugins', plugins_url('js/plugins.js', __FILE__), array( 'jquery' ), '1.0.0', false );
			wp_register_script( 'motionmill', plugins_url('js/scripts.js', __FILE__), array( 'jquery', 'motionmill-plugins' ), '1.0.0', false );
			wp_localize_script( 'motionmill', 'Motionmill', apply_filters( 'motionmill_javascript_vars', array() ) );
		
			wp_enqueue_script( 'motionmill' );
		}

		/* ---------------------------------------------------------------------------------------------------------- */

		/**
		 * On Javascript Vars
		 *
		 * @return void
		 */

		public function on_javascript_vars( $vars )
		{
			return array_merge( $vars, array
			(
				'ajaxurl' => admin_url( 'admin-ajax.php' )
			));
		}

		/* ---------------------------------------------------------------------------------------------------------- */

		/**
		 * On Settings Options
		 *
		 * @return void
		 */

		public function on_settings_options( $options )
		{
			return array_merge( $options, array
			(
				'page_capability'    => 'manage_options',
				'page_parent_slug'   => 'motionmill',
				'page_option_format' => 'motionmill_settings_page-%s-options',
				'page_admin_bar'     => true,
				'page_submit_button' => true,
				'page_title_prefix'  => __( 'Motionmill - ', Motionmill::TEXTDOMAIN )
			));
		}

		/* ---------------------------------------------------------------------------------------------------------- */

		/**
		 * On Settings Pages
		 *
		 * @return void
		 */

		public function on_settings_pages( $pages )
		{
			$pages[] = array
			(
				'id' 		    => 'motionmill',
				'title' 	    => __( 'Dashboard', Motionmill::TEXTDOMAIN ),
				'menu_title'    => __( 'Motionmill', Motionmill::TEXTDOMAIN ),
				'parent_slug'   => '',
				'submit_button' => false
			);

			return $pages;
		}
	}

	$motionmill = Motionmill::get_instance();
	$motionmill->initialize();
}

=======
<?php
if( !defined('ABSPATH') ) exit; // Exits when accessed directly
/*
 ------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill
 Plugin URI:
 Description: Motionmill's HQ
 Version: 1.3.7
 Author: Motionmill
 Author URI: http://motionmill.com
 License: GPL2
 ------------------------------------------------------------------------------------------------------------------------
 */

if( ! class_exists('Motionmill') )
{
	class Motionmill
	{
		const FILE 		   = __FILE__;
		const TEXT_DOMAIN  = 'motionmill';
		const NONCE_NAME   = 'motionmill';
		const PLUGINS_DIR  = 'plugins';
		const INCLUDES_DIR = 'includes';
		
		static private $instance = null;
		
		private $log = null;
		private $plugins  = array();
		private $helpers  = array();
		public $page_slug = null;

		static public function get_instance()
		{
			if( !self::$instance )
			{
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function __construct()
		{
			// loads assets

			require_once( plugin_dir_path( self::FILE ) . self::INCLUDES_DIR . '/class-mm-log.php' );
			require_once( plugin_dir_path( self::FILE ) . self::INCLUDES_DIR . '/class-mm-plugin.php' );

			$this->log = new MM_Log();

			// loads plugins
			foreach( $this->get_plugin_files() as $file )
			{
				require_once( trailingslashit( plugin_dir_path(self::FILE) . self::PLUGINS_DIR ) . $file );
			}

			add_action('init', array(&$this, 'initialize'), 0);

			$this->log->add( 'loaded' );

			do_action('motionmill_loaded');
		}

		public function initialize()
		{
			// registers plugins
			foreach ( apply_filters( 'motionmill_plugins', array() ) as $class )
			{
				if ( isset($this->plugins[$class]) )
					continue;

				if ( ! class_exists($class) )
				{
					trigger_error( sprintf('Plugin class %s could not be found', $class) , E_USER_NOTICE );

					continue;
				}

				$parents = class_parents($class);
			
				if ( ! isset($parents['MM_Plugin']) )
				{
					trigger_error( sprintf('Plugin %s is not a child of MM_Plugin', $class) , E_USER_NOTICE );

					continue;
				}

				$this->plugins[ $class ] = new $class();
			}

			$this->log->add( 'plugins: %s.', implode( ', ', array_keys($this->plugins) ) );

			// registers helpers
			foreach ( apply_filters( 'motionmill_helpers', array() ) as $class )
			{
				if ( isset($this->helpers[$class]) )
					continue;

				// class: MM_Helper => file: class-mm-helper.php
				$name = strtolower( str_replace('_', '-', $class ) );

				require_once( plugin_dir_path(self::FILE) . 'includes/class-' . $name . '.php' );

				if ( ! class_exists($class) )
				{
					trigger_error( sprintf('Plugin class %s could not be found', $class) , E_USER_NOTICE );

					continue;
				}

				$this->helpers[ $class ] = $class;
			}

			$this->log->add( 'helpers: %s.', implode( ', ', array_keys($this->helpers) ) );

			do_action( 'motionmill_plugins_loaded' );

			add_action( 'admin_menu', array( &$this, 'on_admin_menu' ), 0 );
			add_action( 'admin_bar_menu', array( &$this, 'on_admin_bar_menu' ), 100 );
			add_action( 'wp_enqueue_scripts', array( &$this, 'on_enqueue_scripts' ), 0 );
			add_action( 'admin_enqueue_scripts', array( &$this, 'on_enqueue_scripts' ), 0 );

			$this->log->add( 'initialized' );

			do_action('motionmill_init');
			
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

			add_menu_page( __( 'Motionmill', Motionmill::TEXT_DOMAIN ), __( 'Motionmill', Motionmill::TEXT_DOMAIN ), 'manage_options', $this->page_slug, create_function('$a', '') );
		
			do_action( 'motionmill_admin_menu' );
		}

		public function on_admin_bar_menu()
		{
			global $wp_admin_bar;

		    if ( ! is_super_admin() || ! is_admin_bar_showing() || is_admin() )
		        return;

		    $cap = apply_filters( 'motionmill_admin_bar_menu_cap', 'manage_options' );

		    if ( ! current_user_can($cap) )
		    	return;

		    if ( ! $this->page_slug )
		    	return;

		    $wp_admin_bar->add_menu(array
		    (
				'id'     => 'motionmill',
				'meta'   => array(),
				'title'  => __( 'Motionmill', self::TEXT_DOMAIN ),
				'href'   => admin_url( 'admin.php?page=' . $this->page_slug ),
				'parent' => false
			));

			foreach ( apply_filters( 'motionmill_admin_bar_menu_items', array() ) as $data )
			{
				if ( empty($data['parent']) )
				{
					$data['parent'] = 'motionmill';
				}

				$wp_admin_bar->add_menu($data);
			}
		}

		public function on_enqueue_scripts()
		{	
			// styles
			wp_enqueue_style( 'motionmill-style', plugins_url('css/style.css', __FILE__), null, '1.0.0', 'all' );
			
			// scripts
			wp_register_script( 'motionmill-ajaxform', plugins_url('js/jquery.ajaxform.js', __FILE__), array('jquery'), '1.0.0', false );

			wp_enqueue_script( 'motionmill-plugins', plugins_url('js/plugins.js', __FILE__), array('jquery'), '1.0.0', false );
			wp_enqueue_script( 'motionmill-scripts', plugins_url('js/scripts.js', __FILE__), array('jquery', 'motionmill-plugins'), '1.0.0', false );

			wp_localize_script( 'motionmill-scripts', 'Motionmill', apply_filters( 'motionmill_javascript_vars', array
			(
				'ajaxurl' => admin_url('admin-ajax.php'),
				'lang'    => strtolower( defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : substr( get_bloginfo('language'), 0, 2 ) )
			)));
		}

		public function on_uninstall()
		{
			if ( ! defined('WP_UNINSTALL_PLUGIN') )
				return;

			if ( WP_UNINSTALL_PLUGIN != plugin_basename( self::FILE ) ) 
				return;

			// loads plugins uninstall.php file
			foreach ( $this->get_plugin_files() as $file )
			{
				$uninstall = plugin_dir_path(self::FILE) . trailingslashit( dirname($file) ) . 'uninstall.php';

				if ( file_exists($uninstall) )
				{
					include( $uninstall );
				}
			}
		}

		private function get_plugin_files()
		{
			$dir = trailingslashit( plugin_dir_path(self::FILE) . self::PLUGINS_DIR );

			$plugins = array();

			if ( $fh = opendir( $dir ) )
			{
				while ( ( $entry = readdir($fh) ) !== false )
				{
					if ( ! is_dir( $dir . $entry) )
						continue;
	        		
					if ( in_array( $entry, array('.', '..') ) )
						continue;

					$file = $entry . '/' . $entry . '.php';

					if ( ! file_exists( $dir . $file ) )
						continue;

	        		$plugins[] = $file;
	   			}
			}

   			return $plugins;
		}

	}

	$motionmill = Motionmill::get_instance();
}
>>>>>>> FETCH_HEAD
?>