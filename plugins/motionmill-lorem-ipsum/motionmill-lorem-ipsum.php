<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Lorem Ipsum
 Plugin URI: http://motionmill.com
 Description: Generates Lorem Ipsum via shortcode.
 Version: 1.0.0
 Author: Motionmill
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

add_action( 'motionmill_loaded', function(){

if ( ! class_exists('MM_Roles') )
{
	class MM_Lorem_Ipsum extends MM_Plugin
	{
		public function __construct()
		{
			parent::__construct();
		}

		public function initialize()
		{	
			add_shortcode( 'motionmill-lorem-ipsum', array(&$this, 'on_shortcode') );
		}

		public function on_shortcode($args = array())
		{
			$options = shortcode_atts(array
			(
				'p'  => 3, // number of paragraphs
				'l'  => 'short', // paragraph length: short, medium, long, verylong
				'd'  => 1, // Add <b> and <i> tags
				'a'  => 0, // Add <a>
				'co' => 0, // Add <code> and <pre>
				'ul' => 0, // Add <ul>
				'ol' => 0, // Add <ol>
				'dl' => 0, // Add <dl>
				'bq' => 0, // Add <blockquote>
				'h'  => 0, // Add <h1> through <h6>
				'ac' => 0, // Everything in ALL CAPS
				'pr' => 1, // Remove certain words like 'sex' or 'homo'
			), $args);

			return file_get_contents( 'http://loripsum.net/generate.php?' . http_build_query($options) );
		}
	}

	// registers plugin
	function motionmill_plugins_add_lorem_ipsum($plugins)
	{
		array_push($plugins, 'MM_Lorem_Ipsum');

		return $plugins;
	}
	
	add_filter( 'motionmill_plugins', 'motionmill_plugins_add_lorem_ipsum', 5 );
}

});

?>