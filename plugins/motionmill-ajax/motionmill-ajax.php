<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Ajax
 Plugin URI: http://motionmill.com
 Description:
 Version: 1.0.0
 Author: Motionmill
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

add_action( 'motionmill_loaded', function(){

if ( ! class_exists('MM_Ajax') )
{
	class MM_Ajax extends MM_Plugin
	{
		public function __construct()
		{
			parent::__construct();
		}

		public function initialize()
		{	
			add_action( 'wp_ajax_motionmill_get_posts', array(&$this, 'get_posts') );
			add_action( 'wp_ajax_nopriv_motionmill_get_posts', array(&$this, 'get_posts') );

			add_action( 'wp_ajax_motionmill_get_post_meta', array(&$this, 'get_post_meta') );
			add_action( 'wp_ajax_nopriv_motionmill_get_post_meta', array(&$this, 'get_post_meta') );
		}

		public function get_posts()
		{
			$args = isset( $_REQUEST['args'] ) ? $_REQUEST['args'] : '';

			// makes sure args is array
			if ( ! is_array($args) )
			{
				parse_str( $args, $args );
			}

			// makes sure only published posts are returned
			$args['post_status'] = 'publish';
			
			$the_query = new WP_Query( $args );

			echo json_encode($the_query);

			die();				
		}

		public function get_post_meta()
		{
			$post_id = isset( $_REQUEST['post_id'] ) ? $_REQUEST['post_id'] : 0;

			echo json_encode( get_post_meta($post_id) );

			die();				
		}
	}

	// registers plugin
	function motionmill_plugins_add_ajax($plugins)
	{
		array_push($plugins, 'MM_Ajax');

		return $plugins;
	}
	
	add_filter( 'motionmill_plugins', 'motionmill_plugins_add_ajax', 5 );
}

});

?>