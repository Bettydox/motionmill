<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill General
 Plugin URI: http://motionmill.com
 Description: General settings.
 Version: 1.0.1
 Author: Motionmill
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

// checks if motionmill plugin is loaded
add_action( 'motionmill_loaded', function(){

if ( ! class_exists('MM_General') )
{
	class MM_General extends MM_Plugin
	{
		protected $options = array();

		public function initialize()
		{	
			add_action( 'wp_head', array( &$this, 'on_wp_head' ) );
			add_action( 'wp_enqueue_scripts', array( &$this, 'on_enqueue_scripts' ) );

			add_filter( 'body_class', array( &$this, 'on_body_class' ) );
			add_filter( 'mce_css', array( &$this, 'on_mce_css' ) );
			add_filter( 'excerpt_length', array( &$this, 'on_excerpt_length' ) );
		}

		public function on_excerpt_length($length)
		{
			$options = $this->motionmill->get_plugin('MM_Settings')->get_option( 'motionmill_general' );
				
			$options['excerpt_length'];
		}

		public function on_mce_css($mce_css)
		{
			$options = $this->motionmill->get_plugin('MM_Settings')->get_option( 'motionmill_general' );

			$my_css = $options['mce_css'];
			$my_css = explode( "\n", $my_css );
			$my_css = array_filter( $my_css );
			$my_css = array_values( $my_css );

			if ( ! empty( $my_css ) )
			{
				$mce_css = rtrim( $mce_css, ',' ) . ',' . implode( ',', $my_css );
			}

			return $mce_css;
		}

		public function on_login_redirect($redirect_to, $request, $user)
		{
			$options = $this->motionmill->get_plugin('MM_Settings')->get_option( 'motionmill_general' );

			return $options['login_redirect'];
		}

		public function on_private_title_format($format)
		{
			$options = $this->motionmill->get_plugin('MM_Settings')->get_option( 'motionmill_general' );

			return $options['private_title_format']; 
		}

		public function on_settings_pages($pages)
		{
			$pages[] = array
			(
				'id'          => 'motionmill_general',
				'title'       => __('General', Motionmill::TEXT_DOMAIN),
				'description' => __('General settings.', Motionmill::TEXT_DOMAIN)
			);

			return $pages;
		}

		public function on_settings_sections($sections)
		{
			// favicon

			$sections[] = array
			(
				'id' 		  => 'motionmill_general_favicon',
				'title' 	  => __( 'Favicon', Motionmill::TEXT_DOMAIN ),
				'description' => __( "Also known as a Web site icon or bookmark icon. Browsers that provide favicon support typically display a favicon in the browser's address bar.", Motionmill::TEXT_DOMAIN ),
				'page'        => 'motionmill_general'
			);

			// body class

			$sections[] = array
			(
				'id' 		  => 'motionmill_general_body_class',
				'title' 	  => __( 'Body class', Motionmill::TEXT_DOMAIN ),
				'description' => __( 'Adds extra information to the HTML &lt;body&gt; tag. Most commonly used by developers for styling or scripting purposes.', Motionmill::TEXT_DOMAIN ),
				'page'        => 'motionmill_general'
			);

			// excerpt

			$sections[] = array
			(
				'id' 		  => 'motionmill_general_excerpt',
				'title' 	  => __( 'Excerpt', Motionmill::TEXT_DOMAIN ),
				'description' => __( '', Motionmill::TEXT_DOMAIN ),
				'page'        => 'motionmill_general'
			);

			// editor

			$sections[] = array
			(
				'id' 		  => 'motionmill_general_editor',
				'title' 	  => __( 'Rich Text Editor', Motionmill::TEXT_DOMAIN ),
				'description' => __( '', Motionmill::TEXT_DOMAIN ),
				'page'        => 'motionmill_general'
			);

			return $sections;
		}

		public function on_settings_fields($fields)
		{
			// favicon

			$fields[] = array
			(
				'id' 		  => 'favicon',
				'title' 	  => __( 'Image', Motionmill::TEXT_DOMAIN ),
				'description' => __( 'Most commonly 16×16 pixels', Motionmill::TEXT_DOMAIN ),
				'type'		  => 'media',
				'value'       => '',
				'page'		  => 'motionmill_general',
				'section'     => 'motionmill_general_favicon'
			);

			// body class

			$fields[] = array
			(
				'id' 		  => 'body_class_language',
				'title' 	  => __( 'Language', Motionmill::TEXT_DOMAIN ),
				'description' => __( 'Sets the language of the current page. ( class: lang-%s )', Motionmill::TEXT_DOMAIN ),
				'type'		  => 'checkbox',
				'value'       => 0,
				'page'		  => 'motionmill_general',
				'section'     => 'motionmill_general_body_class'
			);

			$fields[] = array
			(
				'id' 		  => 'body_class_post_count',
				'title' 	  => __( 'Post count', Motionmill::TEXT_DOMAIN ),
				'description' => __( 'Counts the posts on the current page. ( class: post-count-%s )', Motionmill::TEXT_DOMAIN ),
				'type'		  => 'checkbox',
				'value'       => 0,
				'page'		  => 'motionmill_general',
				'section'     => 'motionmill_general_body_class'
			);

			$fields[] = array
			(
				'id' 		  => 'body_class_javascript',
				'title' 	  => __( 'Javascript', Motionmill::TEXT_DOMAIN ),
				'description' => __( 'When Javascript is active. ( class: js )', Motionmill::TEXT_DOMAIN ),
				'type'		  => 'checkbox',
				'value'       => 0,
				'page'		  => 'motionmill_general',
				'section'     => 'motionmill_general_body_class'
			);

			// excerpt

			$fields[] = array
			(
				'id' 		  => 'excerpt_length',
				'title' 	  => __( 'Length', Motionmill::TEXT_DOMAIN ),
				'description' => __( 'The length of a single-post excerpt.', Motionmill::TEXT_DOMAIN ),
				'type'		  => 'textfield',
				'value'       => 20,
				'page'		  => 'motionmill_general',
				'section'     => 'motionmill_general_excerpt'
			);

			// editor

			$fields[] = array
			(
				'id' 		  => 'mce_css',
				'title' 	  => __( 'CSS', Motionmill::TEXT_DOMAIN ),
				'description' => __( 'Additional CSS file URLs for the rich text editor. (One entry per line)', Motionmill::TEXT_DOMAIN ),
				'type'		  => 'textarea',
				'class'       => 'large-text code',
				'value'       => 20,
				'page'		  => 'motionmill_general',
				'section'     => 'motionmill_general_editor'
			);

			return $fields;
		}

		public function on_wp_head()
		{
			$options = $this->motionmill->get_plugin('MM_Settings')->get_option( 'motionmill_general' );

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
			$options = $this->motionmill->get_plugin('MM_Settings')->get_option( 'motionmill_general' );

			// language
			if ( ! empty( $options['body_class_language'] ) )
			{
				$language = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : substr( get_bloginfo('language') , 0, 2 );

				$classes[] = sprintf( 'lang-%s', strtolower($language) );
			}

			// post count
			if ( ! empty( $options['body_class_post_count'] ) )
			{
				global $wp_query;

				$classes[] = sprintf( 'post-count-%s' , $wp_query->post_count );
			}

			return $classes;
		}

		public function on_enqueue_scripts()
		{
			$options = $this->motionmill->get_plugin('MM_Settings')->get_option( 'motionmill_general' );

			wp_enqueue_script( 'motionmill-general', plugins_url( 'js/scripts.js', __FILE__ ), array( 'jquery' ), '1.0.0', false );
		
			wp_localize_script( 'motionmill-general', 'mm_general_options', $options );
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