<?php
/**
* Plugin Name: Motionmill General
* Plugin URI:
* Description: Add favicon, HTML &lt;body&gt; tag parameters and much more...
* Version: 1.0.0
* Author: Maarten Menten
* Author URI: http://motionmill.com
* License: GPL2
*/

if ( ! class_exists('MM_General') )
{
	class MM_General
	{
		protected $motionmill = null;

		public function __construct()
		{
			add_action( 'motionmill_init', array( &$this, 'initialize' ) );
		}

		public function initialize()
		{
			$this->motionmill = Motionmill::get_instance();
			
			add_filter( 'motionmill_settings_pages', array(&$this, 'on_settings_pages') );
			add_filter( 'motionmill_settings_sections', array(&$this, 'on_settings_sections') );
			add_filter( 'motionmill_settings_fields', array(&$this, 'on_settings_fields') );
			
			add_action( 'wp_head', array( &$this, 'on_wp_head' ) );
			add_action( 'wp_enqueue_scripts', array( &$this, 'on_enqueue_scripts' ) );

			add_filter( 'body_class', array( &$this, 'on_body_class' ) );
			add_filter( 'mce_css', array( &$this, 'on_mce_css' ) );
			add_filter( 'excerpt_length', array( &$this, 'on_excerpt_length' ) );

			add_filter( 'motionmill_javascript_vars', array( &$this, 'on_javascript_vars' ) );

			register_activation_hook( __FILE__, array( &$this, 'on_activate' ) );
			register_deactivation_hook( __FILE__, array( &$this, 'on_deactivate' ) );
		}

		public function on_activate()
		{
			error_log( __CLASS__ . '::' . __FUNCTION__ );
		}

		public function on_deactivate()
		{
			error_log( __CLASS__ . '::' . __FUNCTION__ );
		}

		public function get_option( $key = null, $default = '' )
		{
			return $this->motionmill->get_plugin('MM_Settings')->get_option( 'motionmill_general', $key, $default );
		}

		public function on_settings_pages($pages)
		{
			$pages[] = array
			(
				'id'          => 'motionmill_general',
				'title'       => __('General', Motionmill::TEXTDOMAIN),
				'description' => __('Add favicon, HTML &lt;body&gt; tag parameters and much more...', Motionmill::TEXTDOMAIN),
				'priority'    => 10
			);

			return $pages;
		}

		public function on_settings_sections($sections)
		{
			// favicon

			$sections[] = array
			(
				'id' 		  => 'motionmill_general_favicon',
				'title' 	  => __( 'Favicon', Motionmill::TEXTDOMAIN ),
				'description' => __( "Also known as a Web site icon or bookmark icon. Browsers that provide favicon support typically display a favicon in the browser's address bar.", Motionmill::TEXTDOMAIN ),
				'page'        => 'motionmill_general'
			);

			// body class

			$sections[] = array
			(
				'id' 		  => 'motionmill_general_body_class',
				'title' 	  => __( 'Body class', Motionmill::TEXTDOMAIN ),
				'description' => __( 'Adds extra information to the HTML &lt;body&gt; tag. Most commonly used by developers for styling or scripting purposes.', Motionmill::TEXTDOMAIN ),
				'page'        => 'motionmill_general'
			);

			// excerpt

			$sections[] = array
			(
				'id' 		  => 'motionmill_general_excerpt',
				'title' 	  => __( 'Excerpt', Motionmill::TEXTDOMAIN ),
				'description' => __( '', Motionmill::TEXTDOMAIN ),
				'page'        => 'motionmill_general'
			);

			// editor

			$sections[] = array
			(
				'id' 		  => 'motionmill_general_editor',
				'title' 	  => __( 'Rich Text Editor', Motionmill::TEXTDOMAIN ),
				'description' => __( '', Motionmill::TEXTDOMAIN ),
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
				'title' 	  => __( 'Image', Motionmill::TEXTDOMAIN ),
				'description' => __( 'Most commonly 16×16 pixels', Motionmill::TEXTDOMAIN ),
				'type'		  => 'media',
				'value'       => '',
				'page'		  => 'motionmill_general',
				'section'     => 'motionmill_general_favicon'
			);

			// body class

			$fields[] = array
			(
				'id' 		  => 'body_class_language',
				'title' 	  => __( 'Language', Motionmill::TEXTDOMAIN ),
				'description' => __( 'Sets the language of the current page. ( class: lang-%s )', Motionmill::TEXTDOMAIN ),
				'type'		  => 'checkbox',
				'value'       => 0,
				'page'		  => 'motionmill_general',
				'section'     => 'motionmill_general_body_class'
			);

			$fields[] = array
			(
				'id' 		  => 'body_class_post_count',
				'title' 	  => __( 'Post count', Motionmill::TEXTDOMAIN ),
				'description' => __( 'Counts the posts on the current page. ( class: post-count-%s )', Motionmill::TEXTDOMAIN ),
				'type'		  => 'checkbox',
				'value'       => 0,
				'page'		  => 'motionmill_general',
				'section'     => 'motionmill_general_body_class'
			);

			$fields[] = array
			(
				'id' 		  => 'body_class_javascript',
				'title' 	  => __( 'Javascript', Motionmill::TEXTDOMAIN ),
				'description' => __( 'When Javascript is active. ( class: js )', Motionmill::TEXTDOMAIN ),
				'type'		  => 'checkbox',
				'value'       => 0,
				'page'		  => 'motionmill_general',
				'section'     => 'motionmill_general_body_class'
			);

			// excerpt

			$fields[] = array
			(
				'id' 		  => 'excerpt_length',
				'title' 	  => __( 'Length', Motionmill::TEXTDOMAIN ),
				'description' => __( 'The length of a single-post <a href="http://codex.wordpress.org/Excerpt" target="_blank">excerpt</a>.', Motionmill::TEXTDOMAIN ),
				'type'		  => 'textfield',
				'value'       => 20,
				'page'		  => 'motionmill_general',
				'section'     => 'motionmill_general_excerpt'
			);

			// editor

			$fields[] = array
			(
				'id' 		  => 'mce_css',
				'title' 	  => __( 'CSS', Motionmill::TEXTDOMAIN ),
				'description' => __( 'Additional CSS file URLs for the rich text editor. (One entry per line)', Motionmill::TEXTDOMAIN ),
				'type'		  => 'textarea',
				'value'       => '',
				'page'		  => 'motionmill_general',
				'section'     => 'motionmill_general_editor'
			);

			return $fields;
		}

		public function on_wp_head()
		{
			// favicon
			if ( trim( $this->get_option( 'favicon' ) ) != '' )
			{
				$extension = strtolower( pathinfo( $this->get_option( 'favicon' ), PATHINFO_EXTENSION ) );
				
				switch ( $extension )
				{
					case 'ico' : $type = 'image/x-icon'; break;
					default : $type = 'image/' . $extension;
				}

				printf( '<link rel="shortcut icon" href="%s" type="%s">', esc_attr( $this->get_option( 'favicon' ) ), esc_attr($type) );
			}
		}
		
		public function on_body_class($classes)
		{
			// language
			if ( $this->get_option( 'body_class_language' ) )
			{
				$classes[] = sprintf( 'lang-%s', strtolower( $this->get_language_code() ) );
			}

			// post count
			if ( $this->get_option( 'body_class_post_count' ) )
			{
				global $wp_query;

				$classes[] = sprintf( 'post-count-%s' , $wp_query->post_count );
			}

			return $classes;
		}

		public function on_excerpt_length($length)
		{
			return $this->get_option( 'excerpt_length', $length );
		}

		public function on_mce_css($mce_css)
		{
			$my_css = $this->get_option( 'mce_css' );
			$my_css = explode( "\n", $my_css );
			$my_css = array_filter( $my_css );
			$my_css = array_values( $my_css );

			if ( ! empty( $my_css ) )
			{
				$mce_css = rtrim( $mce_css, ',' ) . ',' . implode( ',', $my_css );
			}

			return $mce_css;
		}

		public function on_comments_open( $open, $post_id )
		{
			$post_types = (array) $this->get_option( 'comments_post_types' );

			$post_type = get_post_type( $post_id );

			if ( ! in_array( $post_type, $post_types ) )
			{
				return false;
			}

		    return true;
		}

		public function on_enqueue_scripts()
		{
			wp_enqueue_script( 'motionmill-general', plugins_url( 'js/scripts.js', __FILE__ ), array( 'jquery' ), '1.0.0', false );
		
			wp_localize_script( 'motionmill-general', 'mm_general_options', $this->get_option() );
		}

		public function on_javascript_vars( $vars )
		{
			return array_merge( $vars, array
			(
				'lang' => $this->get_language_code()
			));
		}

		protected function get_language_code()
		{
			if ( defined( 'ICL_LANGUAGE_CODE' ) )
			{
				return ICL_LANGUAGE_CODE;
			}

			return substr( get_bloginfo('language') , 0, 2 );
		}
	}

	// registers plugin
	function motionmill_plugins_add_general($plugins)
	{
		$plugins[] = 'MM_General';

		return $plugins;
	}
	
	add_filter( 'motionmill_plugins', 'motionmill_plugins_add_general' );
}

?>