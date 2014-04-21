<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Body Class
 Plugin URI: http://motionmill.com
 Description: Adds CSS classes to the html body element
 Version: 1.0.0
 Author: Motionmill
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

add_action( 'motionmill_loaded', function(){

if ( ! class_exists('MM_Body_Class') )
{
	class MM_Body_Class extends MM_Plugin
	{
		public function __construct()
		{
			parent::__construct();
		}

		public function initialize()
		{	
			add_filter( 'motionmill_settings_pages', array(&$this, 'on_settings_pages') );
			add_filter( 'motionmill_settings_sections', array(&$this, 'on_settings_sections') );
			add_filter( 'motionmill_settings_fields', array(&$this, 'on_settings_fields') );
		
			add_filter( 'body_class', array(&$this, 'on_body_class') );
		}

		public function on_settings_pages($pages)
		{
			$pages[] = array
			(
				'id' 		  => 'motionmill_body_class',
				'title' 	  => __('Body Class', MM_TEXTDOMAIN),
				'description' => __('', MM_TEXTDOMAIN)
			);

			return $pages;
		}

		public function on_settings_sections($sections)
		{
			$sections[] = array
			(
				'id' 		  => 'motionmill_body_class_general',
				'title' 	  => __('Body Class', MM_TEXTDOMAIN),
				'description' => __('<p>Adds CSS classes to the html body element.</p>', MM_TEXTDOMAIN),
				'page'        => 'motionmill_body_class'
			);

			return $sections;
		}

		public function on_settings_fields($fields)
		{
			$fields[] = array
			(
				'id' 		  => 'language',
				'title' 	  => __( 'Language', MM_TEXTDOMAIN ),
				'description' => __( '', MM_TEXTDOMAIN ),
				'type'		  => 'checkbox',
				'value'       => '1',
				'page'		  => 'motionmill_body_class',
				'section'     => 'motionmill_body_class_general'
			);

			$fields[] = array
			(
				'id' 		  => 'browser',
				'title' 	  => __( 'Browser', MM_TEXTDOMAIN ),
				'description' => __( '', MM_TEXTDOMAIN ),
				'type'		  => 'checkbox',
				'value'       => '1',
				'page'		  => 'motionmill_body_class',
				'section'     => 'motionmill_body_class_general'
			);

			return $fields;
		}

		public function on_body_class($classes)
		{
			$options = $this->_('MM_Settings')->get_option( 'motionmill_body_class' );

			// language
			if ( ! empty( $options['language'] ) )
			{
				$language = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : substr( get_bloginfo('language') , 0, 2 );

				$classes[] = sprintf( 'lang-%s', strtolower($language) );
			}

			// browser
			if ( ! empty( $options['browser'] ) )
			{
				global $is_lynx, $is_gecko, $is_IE, $is_opera, $is_NS4, $is_safari, $is_chrome, $is_iphone;

				if ( $is_lynx )
					$classes[] = 'browser-lynx';
				elseif ( $is_gecko )
					$classes[] = 'browser-gecko';
				elseif( $is_opera )
					$classes[] = 'browser-opera';
				elseif( $is_NS4)
					$classes[] = 'browser-ns4';
				elseif( $is_safari)
					$classes[] = 'browser-safari';
				elseif( $is_chrome)
					$classes[] = 'browser-chrome';
				elseif( $is_IE)
					$classes[] = 'browser-ie';
				else
					$classes[] = 'browser-unknown';
			}

			return $classes;
		}
	}

	// registers plugin
	function motionmill_plugins_add_body_class($plugins)
	{
		array_push($plugins, 'MM_Body_Class');

		return $plugins;
	}
	
	add_filter( 'motionmill_plugins', 'motionmill_plugins_add_body_class', 5 );
}

});

?>