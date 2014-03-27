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

			add_filter( 'manage_pages_columns', array(&$this, 'on_manage_posts_columns') );
			add_filter( 'manage_posts_columns', array(&$this, 'on_manage_posts_columns'), 10, 2 );
			add_action( 'manage_pages_custom_column',  array(&$this, 'on_manage_posts_custom_column'), 10, 2 );
			add_action( 'manage_posts_custom_column',  array(&$this, 'on_manage_posts_custom_column'), 10, 2 );
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
				'title' 	  => __( '', MM_TEXTDOMAIN ),
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
				'id' 		  => 'post_id_column',
				'title' 	  => 'Post ID Column',
				'description' => __( '', MM_TEXTDOMAIN ),
				'type'		  => 'checkbox',
				'value'       => 0,
				'page'		  => 'motionmill_general',
				'section'     => 'motionmill_general_general'
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

		public function on_manage_posts_columns($columns)
		{
			$options = $this->_('MM_Settings')->get_option( 'motionmill_general' );

			if ( empty($options['post_id_column']) )
			{
				return $columns;
			}

			return array_merge(array
		    (
		    	'cb' => $columns['cb'],
		    	'id' => 'ID'
		    ), $columns );
		}

		function on_manage_posts_custom_column($column, $post_id)
		{
			$options = $this->_('MM_Settings')->get_option( 'motionmill_general' );
			
			if ( empty($options['post_id_column']) )
				return;

		    switch ($column)
		    {
		        case 'id': echo $post_id; break;
		    }
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