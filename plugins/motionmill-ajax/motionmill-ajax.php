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
			add_action( 'wp_enqueue_scripts', array(&$this, 'on_enqueue_scripts'), 0 );
			add_action( 'wp_ajax_motionmill_ajax_load_posts', array(&$this, 'on_ajax_load_posts') );
			add_action( 'wp_ajax_nopriv_motionmill_ajax_load_posts', array(&$this, 'on_ajax_load_posts') );
		}

		public function on_enqueue_scripts()
		{	
			wp_enqueue_style( 'motionmill-ajax-style', plugins_url('css/style.css', __FILE__), null, '1.0.0', 'all' );
			
			wp_enqueue_script( 'motionmill-ajax-scripts', plugins_url('js/scripts.js', __FILE__), array('jquery', 'motionmill-plugins'), '1.0.0', false );

			wp_localize_script( 'motionmill-ajax-scripts', 'Motionmill', array
			(
				'ajaxurl' => admin_url('admin-ajax.php')
			));
		}

		public function on_ajax_load_posts()
		{
			$query_vars = isset( $_POST['query_vars'] ) ? $_POST['query_vars'] : array();
			$template   = isset( $_POST['template'] ) ? $_POST['template'] : array('content');

			// makes sure query_vars is array
			if ( ! is_array($query_vars) )
			{
				parse_str($query_vars);
			}

			// makes sure template is array
			if ( ! is_array($template) )
			{
				$template = array($template);
			}

			// sets required vars
			$query_vars = array_merge($query_vars, array
			(
				'post_status' => current_user_can('read_private_posts') ? 'any' : 'publish'
			));

			ob_start();

			// the query
			$the_query = new WP_Query($query_vars);

			// the Loop
			if ( $the_query->have_posts() )
			{
				while ( $the_query->have_posts() )
				{
					$the_query->the_post();
					
					call_user_func_array( 'get_template_part' , $template );
				}
			}
			
			// restores original Post Data
			wp_reset_postdata();

			$html = ob_get_clean();

			echo json_encode(array
			(
				'the_query' => $the_query,
				'html'      => $html
			));

			die();
		}
	}

	// registers plugin
	function motionmill_plugins_add_ajax($plugins)
	{
		array_push($plugins, 'MM_Ajax');

		return $plugins;
	}
	
	add_filter( 'motionmill_plugins', 'motionmill_plugins_add_ajax' );
}

});

?>