<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill General
 Plugin URI: http://motionmill.com
 Description: 
 Version: 1.0.0
 Author: Motionmill
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

add_action( 'motionmill_loaded', function(){

if ( ! class_exists('MM_General') )
{
	class MM_General extends MM_Plugin
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

			add_action( 'wp_head', array(&$this, 'on_wp_head') );
			add_filter( 'body_class', array(&$this, 'on_body_class') );
		}

		public function on_settings_pages($pages)
		{
			$pages[] = array
			(
				'id' 		  => 'motionmill_general',
				'title' 	  => __( 'General', MM_TEXTDOMAIN ),
				'description' => __( '', MM_TEXTDOMAIN )
			);

			return $pages;
		}

		public function on_settings_sections($sections)
		{
			$sections[] = array
			(
				'id' 		  => 'motionmill_general_general',
				'title' 	  => __( 'General', MM_TEXTDOMAIN ),
				'description' => __( '', MM_TEXTDOMAIN ),
				'page'        => 'motionmill_general'
			);

			$sections[] = array
			(
				'id' 		  => 'motionmill_general_body_class',
				'title' 	  => __( 'Body Class', MM_TEXTDOMAIN ),
				'description' => __( '', MM_TEXTDOMAIN ),
				'page'        => 'motionmill_general'
			);

			return $sections;
		}

		public function on_settings_fields($fields)
		{
			$fields[] = array
			(
				'id' 		  => 'favicon',
				'title' 	  => __( 'Favicon', MM_TEXTDOMAIN ),
				'description' => __( '', MM_TEXTDOMAIN ),
				'type'		  => 'media',
				'value'       => '',
				'page'		  => 'motionmill_general',
				'section'     => 'motionmill_general_general'
			);

			$fields[] = array
			(
				'id' 		  => 'body_class_language',
				'title' 	  => __( 'Language', MM_TEXTDOMAIN ),
				'description' => __( 'Create class lang-LANGUAGE_CODE e.g. lang-en-US', MM_TEXTDOMAIN ),
				'type'		  => 'checkbox',
				'value'       => 0,
				'page'		  => 'motionmill_general',
				'section'     => 'motionmill_general_body_class'
			);

			return $fields;
		}

		public function on_wp_head()
		{
			$options = $this->_('MM_Settings')->get_option( 'motionmill_general' );

			// favicon
			if ( trim( $options['favicon'] ) != '' )
			{
				$extension = strtolower( pathinfo( $options['favicon'], PATHINFO_EXTENSION ) );
				
				switch ( $extension )
				{
					case 'ico' : $type = 'image/x-icon'; break;
					default : $type = 'image/' . $extension;
				}

				printf( '<link rel="shortcut icon" href="%s" type="%s">', esc_attr( $options['favicon'] ), esc_attr($type) );
			}
		}

		public function on_body_class($classes)
		{
			$options = $this->_('MM_Settings')->get_option( 'motionmill_general' );

			if ( ! empty( $options['body_class_language'] ) )
			{
				$language = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : get_bloginfo('language');

				$classes[] = sprintf( 'lang-%s', $language );
			}

			return $classes;
		}
	}

	// registers plugin
	function motionmill_plugins_add_general($plugins)
	{
		array_push($plugins, 'MM_General');

		return $plugins;
	}
	
	add_filter( 'motionmill_plugins', 'motionmill_plugins_add_general', 4 );
}

});

?>