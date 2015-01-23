<?php if ( ! defined( 'ABSPATH' ) ) exit; // exits when accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill
 Plugin URI: https://github.com/addwittz/motionmill
 Description: Motionmill provides tools that facilitates the creation process of WordPress plugins.
 Version: 1.7.3
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
		const VERSION      = '1.7.2';

		static private $instance = null;
		
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
			// loads assets
			
			require_once( plugin_dir_path( self::FILE ) . 'includes/common.php' );
			require_once( plugin_dir_path( self::FILE ) . 'includes/class-mm-loader.php' );

			// stops when this plugin is uninstalling

			if ( defined( 'WP_UNINSTALL_PLUGIN' ) )
			{
				return;
			}

			do_action( 'motionmill_loaded' );

			// loads plugins

			foreach ( MM( 'Plugins' )->get_plugins() as $plugin )
			{
				MM( 'Plugins' )->load( $plugin );
			}

			// registers plugins

			foreach ( apply_filters( 'motionmill_plugins', array() ) as $class )
			{
				MM( $class ); // registers and instantiates
			}

			// plugins can initialize from here
			
			do_action( 'motionmill_init' );

			// hooks

			register_activation_hook( self::FILE, array( &$this, 'activate' ), 5 );
			register_deactivation_hook( self::FILE, array( &$this, 'deactivate' ), 5 );

			add_filter( 'admin_menu', array( &$this, 'add_menu_page' ), 5 );
			add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ), 5 );
			add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_scripts' ), 5 );
			add_action( 'plugins_loaded', array( &$this, 'load_textdomain' ), 5 );
			add_action( 'update_option_motionmill_version', array( &$this, 'update' ), 5, 2 );
			
		}

		public function add_menu_page()
		{
			add_menu_page( __( 'Motionmill', Motionmill::TEXTDOMAIN ), __( 'Motionmill', Motionmill::TEXTDOMAIN ), 'manage_options', 'motionmill' );
		}

		public function load_textdomain()
		{			
			foreach ( MM( 'Plugins' )->get_plugins() as $plugin )
			{
				MM( 'Plugins' )->load_textdomain( $plugin );
			}
		}

		public function activate()
		{
			foreach ( MM( 'Plugins' )->get_plugins() as $plugin )
			{
				MM( 'Plugins' )->activate( $plugin );
			}

			update_option( 'motionmill_version', self::VERSION );
		}

		public function deactivate()
		{
			foreach ( MM( 'Plugins' )->get_plugins() as $plugin )
			{
				MM( 'Plugins' )->deactivate( $plugin );
			}
		}

		public function uninstall()
		{
			if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
			{
				return;
			}

			// uninstalls plugins

			foreach ( MM( 'Plugins' )->get_plugins() as $plugin )
			{
				MM( 'Plugins' )->uninstall( $plugin );
			}

			// deletes all options that starts with 'motionmill'

			global $wpdb;

			$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'motionmill%';" );
		}

		public function update( $old_version, $new_version )
		{
			if ( version_compare( $old_version, $new_version, '>=' ) )
			{
				return;
			}

			do_action( 'motionmill_update', $old_version, $new_version );
		}

		public function enqueue_scripts()
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
