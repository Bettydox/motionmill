<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Assets
 Plugin URI: http://motionmill.com
 Description: Loads and manages common Javascript and styles
 Version: 1.0.0
 Author: Motionmill
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

add_action( 'motionmill_loaded', function(){

if ( ! class_exists('MM_Assets') )
{
	class MM_Assets extends MM_Plugin
	{
		public function __construct()
		{
			parent::__construct();
		}

		public function initialize()
		{	
			add_action( 'wp_enqueue_scripts', array(&$this, 'on_enqueue_scripts'), 0 );
		}

		public function on_enqueue_scripts()
		{	
			wp_enqueue_style( 'motionmill-style', plugins_url('css/style.css', __FILE__), null, '1.0.0', 'all' );
			
			wp_enqueue_script( 'motionmill-plugins', plugins_url('js/plugins.js', __FILE__), array('jquery'), '1.0.0', false );
			wp_enqueue_script( 'motionmill-scripts', plugins_url('js/scripts.js', __FILE__), array('jquery', 'motionmill-plugins'), '1.0.0', false );

			wp_localize_script( 'motionmill-scripts', 'Motionmill', array
			(
				'ajaxurl' => admin_url('admin-ajax.php'),
				'lang'    => strtolower( defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : substr( get_bloginfo('language'), 0, 2 ) )
			));
		}
	}

	// registers plugin
	function motionmill_plugins_add_assets($plugins)
	{
		array_push($plugins, 'MM_Assets');

		return $plugins;
	}
	
	add_filter( 'motionmill_plugins', 'motionmill_plugins_add_assets', 0 );
}

});

?>