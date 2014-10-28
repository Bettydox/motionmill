<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Ajax
 Plugin URI: https://github.com/addwittz/motionmill/tree/master/plugins/motionmill-ajax
 Description: Manages Ajax calls
 Version: 1.0.0
 Author: Maarten Menten
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

if ( ! class_exists( 'MM_Ajax' ) )
{
	class MM_Ajax
	{
		const FILE = __FILE__;

		protected $config = array();
		protected $methods = array();

		public function __construct()
		{	
			add_filter( 'motionmill_helpers', array( &$this, 'on_helpers' ) );
			add_filter( 'motionmill_javascript_vars', array(&$this, 'on_javascript_vars') );

			add_action( 'motionmill_init', array( &$this, 'initialize' ), 1 );
		}

		public function initialize()
		{
			$this->config = apply_filters( 'motionmill_ajax_config', array
			(
				'action' => 'motionmill'
			));

			foreach( apply_filters( 'motionmill_ajax_methods', array() ) as $data )
			{
				if ( empty( $data['id'] ) )
				{
					continue;
				}

				$this->methods[] = array_merge( array
				(
					'id' 	   => $data['id'],
					'callback' => ''
				), $data );
			}
			
			add_action( 'wp_enqueue_scripts', array( &$this, 'on_enqueue_scripts' ), 5 );
			add_action( 'admin_enqueue_scripts', array( &$this, 'on_enqueue_scripts' ), 5 );

			add_action( sprintf( 'wp_ajax_%s', $this->config['action'] ), array( &$this, 'on_ajax_call' ) );
			add_action( sprintf( 'wp_ajax_nopriv_%s', $this->config['action'] ), array( &$this, 'on_ajax_call' ) );
		}

		public function on_javascript_vars( $vars )
		{
			return array_merge(array
			(
				'ajaxurl'    => admin_url( 'admin-ajax.php' ),
				'ajaxEvent'  => $this->config['action'],
			), $vars);
		}

		public function on_ajax_call()
		{
			$args = array_merge( array
			(
				'method' => '',
				'args'   => null

			), $_REQUEST );

			$method = MM_Array::get_element_by( array( 'id' => $args[ 'method' ] ), $this->methods );

			if ( ! $method )
			{
				wp_send_json_error( sprintf( __( "Method '%s' could not be found.", Motionmill::TEXTDOMAIN ), $args[ 'method' ] ) );
			}

			$callback = $method['callback'];

			if ( ! $callback || ! is_callable( $callback ) )
			{
				wp_send_json_error( sprintf( __( "Method '%s' is not callable.", Motionmill::TEXTDOMAIN ), $args[ 'method' ] ) );
			}

			$data = null;

			if ( ! is_array( $args['args'] ) )
			{
				$data = @call_user_func( $callback );
			}

			else
			{
				$data = @call_user_func_array( $callback , $args['args'] );
			}

			if ( is_wp_error( $data ) )
			{
				wp_send_json_error( $data->get_error_message() );
			}

			wp_send_json_success( $data );
		}

		public function on_enqueue_scripts()
		{
			wp_enqueue_style( 'motionmill-ajax', plugins_url( 'css/style.css', self::FILE ) );

			wp_enqueue_script( 'motionmill-ajax', plugins_url( 'js/scripts.js', self::FILE ), array( 'motionmill', 'jquery' ), '1.0.0', true );
		}

		public function on_helpers( $helpers )
		{
			array_push( $helpers , 'MM_Array' );

			return $helpers;
		}
	}
}

// registers plugin
if ( ! function_exists('motionmill_plugins_add_ajax') )
{
	function motionmill_plugins_add_ajax( $plugins )
	{
		$plugins[] = 'MM_Ajax';

		return $plugins;
	}

	add_filter( 'motionmill_plugins', 'motionmill_plugins_add_ajax', 99 );
}

?>
