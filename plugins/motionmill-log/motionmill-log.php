<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Log
 Plugin URI:
 Description: Writes data to file.
 Version: 1.0.0
 Author: Maarten Menten
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

if ( ! class_exists( 'MM_Log' ) )
{
	class MM_Log
	{
		const FILE = __FILE__;

		protected $config = array();
		protected $categories = array();

		public function __construct()
		{	
			add_filter( 'motionmill_helpers', array( &$this, 'on_helpers' ) );
			add_filter( 'motionmill_status_messages', array( &$this, 'on_status_messages' ) );
			
			add_action( 'motionmill_init', array( &$this, 'initialize' ), 3 );
		}
		
		public function initialize()
		{
			$this->categories = array
			(
				array
				(
					'id'          => 'uncategorized',
					'title'       => __( 'Uncategorized', Motionmill::TEXTDOMAIN ),
					'description' => ''
				)
			);

			$this->config = apply_filters( 'motionmill_log_config', array
			(
				'file' => plugin_dir_path( self::FILE ) . 'log.txt'
			));

			// creates file
			if ( ! file_exists( $this->config['file'] ) )
			{
				@fopen( $this->config['file'] , 'w' );
			}

			foreach ( apply_filters( 'motionmill_log_categories', $this->categories ) as $data )
			{
				if ( empty( $data['id'] ) )
				{
					continue;
				}

				$this->categories[] = array_merge( array
				(
					'id'          => $data['id'],
					'title'       => $data['id'],
					'description' => ''
				), $data );
			}

			add_action( 'wp_enqueue_scripts', array( &$this, 'on_enqueue_scripts' ) );
		}

		public function get_category( $search )
		{
			return MM_Array::get_element_by( $search, $this->categories );
		}

		public function get_categories( $search = null )
		{
			return MM_Array::get_elements_by( $search, $this->categories );
		}

		public function add( $value, $category = '' )
		{
			$message = array
			(
				'time'     => time(),
				'category' => $category,
				'value'    => $value
			);

			return $this->write( $message );
		}

		protected function write( $data )
		{
			$handle = @fopen( $this->config['file'] , 'a' );

			if ( $handle )
			{
				return ( @fwrite( $handle, json_encode( $data ) . Motionmill::NEWLINE ) !== false );
			}

			return false;
		}

		public function on_helpers( $helpers )
		{
			array_push( $helpers , 'MM_Array' );

			return $helpers;
		}

		public function on_status_messages( $messages )
		{
			$yes = __( 'yes', Motionmill::TEXTDOMAIN );
			$no  = __( 'no', Motionmill::TEXTDOMAIN );

			$data = array
			(
				'exists'   => file_exists( $this->config['file'] ) ? $yes : $no,
				'readable' => is_readable( $this->config['file'] ) ? $yes : $no,
				'writable' => is_writable( $this->config['file'] ) ? $yes : $no
			);

			$count = array_count_values( $data );

			$file = str_replace( ABSPATH , '', $this->config['file'] );

			$messages[] = array
			(
				'id'     => 'motionmill_status_htaccess',
				'text'   => sprintf( __( 'Log file <code>%s</code> exists (%s), is readable (%s) and is writeable (%s).', Motionmill::TEXTDOMAIN ), $file, $data['exists'], $data['readable'], $data['writable'] ),
				'type'   => ( $count[ $yes ] == count( $data ) ) ? 'success' : 'error',
				'author' => 'Motionmill Log'
			);

			return $messages;
		}

		public function on_enqueue_scripts()
		{
			wp_enqueue_style( 'motionmill-log', plugins_url( 'css/style.css', self::FILE ), array() );

			wp_enqueue_script( 'motionmill-log', plugins_url( 'js/scripts.js', self::FILE ), array( 'jquery' ), '1.0.0', true );
		}
	}
}

// registers plugin
if ( ! function_exists('motionmill_plugins_add_log') )
{
	function motionmill_plugins_add_log( $plugins )
	{
		$plugins[] = 'MM_Log';

		return $plugins;
	}

	add_filter( 'motionmill_plugins', 'motionmill_plugins_add_log', 1 );
}

?>
