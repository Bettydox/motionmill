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
?>