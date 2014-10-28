<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill General
 Plugin URI: https://github.com/addwittz/motionmill/tree/master/plugins/motionmill-general
 Description: Handles general WordPress settings.
 Version: 1.0.4
 Author: Maarten Menten
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

if ( ! class_exists( 'MM_General' ) )
{
	class MM_General
	{
		public function __construct()
		{
			add_filter( 'motionmill_helpers', array(&$this, 'on_helpers') );
			add_filter( 'motionmill_settings_pages', array(&$this, 'on_settings_pages') );
			add_filter( 'motionmill_settings_sections', array(&$this, 'on_settings_sections') );
			add_filter( 'motionmill_settings_fields', array(&$this, 'on_settings_fields') );
			add_filter( 'motionmill_javascript_vars', array(&$this, 'on_javascript_vars') );
			add_action( 'motionmill_init', array( &$this, 'initialize' ) );
		}

		public function initialize()
		{
			add_action( 'wp_head', array( &$this, 'on_wp_head' ) );

			add_filter( 'body_class', array( &$this, 'on_body_class' ) );
			add_filter( 'mce_css', array( &$this, 'on_mce_css' ) );

			if ( $this->get_option( 'post_excerpt_length' ) )
			{
				add_filter( 'excerpt_length', array( &$this, 'on_excerpt_length' ) );
			}

			if ( $this->get_option( 'post_excerpt_more' ) )
			{
				add_filter( 'excerpt_more', array( &$this, 'on_excerpt_more' ) );
			}

			if ( $this->get_option( 'enable_widget_shortcodes' ) )
			{
				add_filter('widget_text', 'do_shortcode');
			}

			if ( $this->get_option( 'wpautop_enable' ) )
			{
				add_action( 'add_meta_boxes', array( &$this, 'on_add_meta_boxes' ) );
				add_action( 'save_post', array( $this, 'on_save_post' ) );
				add_action( 'the_post', array( $this, 'on_the_post' ) );
				add_action( 'loop_end', array( $this, 'on_loop_end' ) );
			}
		}

		public function on_add_meta_boxes()
		{
			add_meta_box( 'motionmill-wpautop', __( 'Motionmill Auto Paragraphs', Motionmill::TEXTDOMAIN ), array( &$this, 'on_print_metabox_autop' ), null, 'side', 'default' );
		}

		public function on_print_metabox_autop( $post )
		{
			wp_nonce_field( 'motionmill_wpautop_save_post', Motionmill::NONCE_NAME );

			$wpautop = get_post_meta( $post->ID, '_motionmill_wpautop', true );

			if ( $wpautop === '' )
			{
				$wpautop = true;
			}

			?>

			<p class="description"><?php _e( 'Changes double line-breaks into HTML paragraphs for content and excerpt fields.', Motionmill::TEXTDOMAIN ); ?></p>

			<p>
				<label><input type="checkbox" name="motionmill_wpautop" value="1"<?php checked( $wpautop, true ); ?>><?php _e( 'Enable', Motionmill::TEXTDOMAIN ); ?></label>
			</p>

			<?php
		}

		public function on_save_post( $post_id )
		{
			if ( empty( $_POST[ Motionmill::NONCE_NAME ] ) || ! wp_verify_nonce( $_POST[ Motionmill::NONCE_NAME ], 'motionmill_wpautop_save_post' ) )
			{
				return $post_id;
			}

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			{
				return $post_id;
			}

			if ( 'page' == $_POST['post_type'] )
			{
				if ( ! current_user_can( 'edit_page', $post_id ) )
				{
					return $post_id;
				}
			}

			else
			{
				if ( ! current_user_can( 'edit_post', $post_id ) )
				{
					return $post_id;
				}
					
			}

			update_post_meta( $post_id, '_motionmill_wpautop', ! empty( $_POST[ 'motionmill_wpautop' ] ) );
		}

		public function the_post( $post )
		{
			$wpautop = (boolean) get_post_meta( $post->ID, '_motionmill_wpautop', true );

			if ( $wpautop )
			{
				remove_filter( 'the_content', 'wpautop' );
				remove_filter( 'the_excerpt', 'wpautop' );
			}

			else
			{
				if ( ! has_filter( 'the_content', 'wpautop' ) )
				{
					add_filter( 'the_content', 'wpautop' );
				}

				if ( ! has_filter( 'the_excerpt', 'wpautop' ) )
				{
					add_filter( 'the_excerpt', 'wpautop' );
				}
			}
		}

		/**
		 * loop_end function.
		 * After we run our loop, everything should be set back to normal
		 *
		 * @access public
		 * @return void
		 */
		public function on_loop_end()
		{
			if ( ! has_filter( 'the_content', 'wpautop' ) )
			{
				add_filter( 'the_content', 'wpautop' );
			}

			if ( ! has_filter( 'the_excerpt', 'wpautop' ) )
			{
				add_filter( 'the_excerpt', 'wpautop' );
			}
		}

		public function get_option( $key = null, $default = '' )
		{
			return MM('Settings')->get_option( 'motionmill_general', $key, $default );
		}

		public function on_settings_pages($pages)
		{
			$pages[] = array
			(
				'id'          => 'motionmill_general',
				'title'       => __('General', Motionmill::TEXTDOMAIN),
				'description' => __('', Motionmill::TEXTDOMAIN),
				'priority'    => 5
			);

			return $pages;
		}

		public function on_settings_sections($sections)
		{
			// favicon

			$sections[] = array
			(
				'id' 		  => 'motionmill_general_general',
				'title' 	  => __( '', Motionmill::TEXTDOMAIN ),
				'description' => __( '', Motionmill::TEXTDOMAIN ),
				'page'        => 'motionmill_general'
			);

			// posts

			$sections[] = array
			(
				'id' 		  => 'motionmill_general_post',
				'title' 	  => __( 'Posts', Motionmill::TEXTDOMAIN ),
				'description' => __( '', Motionmill::TEXTDOMAIN ),
				'page'        => 'motionmill_general'
			);

			// body class

			$sections[] = array
			(
				'id' 		  => 'motionmill_general_body_class',
				'title' 	  => __( 'Body classes', Motionmill::TEXTDOMAIN ),
				'description' => __( 'Add css classes that are assigned to the body HTML element on the current page.', Motionmill::TEXTDOMAIN ),
				'page'        => 'motionmill_general'
			);

			return $sections;
		}

		public function on_settings_fields($fields)
		{
			// favicon

			$fields[] = array
			(
				'id'           => 'favicon',
				'title'        => __( 'Favicon', Motionmill::TEXTDOMAIN ),
				'description'  => __( "Also known as a Web site icon or bookmark icon. Browsers that provide favicon support typically display a favicon in the browser's address bar. Most commonly 16×16 pixels", Motionmill::TEXTDOMAIN ),
				'type'         => 'media',
				'value'        => '',
				'page'         => 'motionmill_general',
				'section'      => 'motionmill_general_general',
				'translatable' => false
			);

			// post

			$fields[] = array
			(
				'id'           => 'post_excerpt_length',
				'title'        => __( 'Excerpt Length', Motionmill::TEXTDOMAIN ),
				'description'  => __( 'The maximum number of words for a single post <a href="http://codex.wordpress.org/Excerpt" target="_blank">excerpt</a>. Leave empty to use the default value.', Motionmill::TEXTDOMAIN ),
				'type'         => 'textfield',
				'value'        => '',
				'page'         => 'motionmill_general',
				'section'      => 'motionmill_general_post',
				'translatable' => true
			);

			$fields[] = array
			(
				'id' 		  => 'post_excerpt_more',
				'title' 	  => __( 'Excerpt More', Motionmill::TEXTDOMAIN ),
				'description' => __( 'The text after the cut off excerpt. Use <code>[permalink]</code> to refer to the url. (Leave empty to use the default value)', Motionmill::TEXTDOMAIN ),
				'type'		  => 'textfield',
				'value'       => '',
				'page'		  => 'motionmill_general',
				'section'     => 'motionmill_general_post',
				'translatable' => true
			);

			$fields[] = array
			(
				'id' 		  => 'post_editor_css',
				'title' 	  => __( 'Editor CSS', Motionmill::TEXTDOMAIN ),
				'description' => __( 'Additional CSS file URLs for the rich text editor. (One entry per line)', Motionmill::TEXTDOMAIN ),
				'type'		  => 'textarea',
				'value'       => '',
				'page'		  => 'motionmill_general',
				'section'     => 'motionmill_general_post',
				'translatable' => false
			);

			$fields[] = array
			(
				'id'           => 'enable_widget_shortcodes',
				'title'        => __( 'Widget Shortcode Support', Motionmill::TEXTDOMAIN ),
				'description'  => __( 'Enables/disables <a href="http://codex.wordpress.org/Shortcode_API" target="_blank">shortcodes</a> for widgets', Motionmill::TEXTDOMAIN ),
				'type'         => 'checkbox',
				'value'        => '',
				'page'         => 'motionmill_general',
				'section'      => 'motionmill_general_general',
				'translatable' => false
			);

			$fields[] = array
			(
				'id' 		  => 'body_class_language',
				'title' 	  => __( 'language', Motionmill::TEXTDOMAIN ),
				'description' => __( 'Example: lang-en', Motionmill::TEXTDOMAIN ),
				'type'		  => 'checkbox',
				'value'       => '',
				'page'		  => 'motionmill_general',
				'section'     => 'motionmill_general_body_class',
				'translatable' => false
			);

			$fields[] = array
			(
				'id'           => 'wpautop_enable',
				'title'        => __( 'Toggle Auto Paragraphs', Motionmill::TEXTDOMAIN ),
				'description'  => __( 'Provides the ability to enable/disable <a href="http://codex.wordpress.org/Function_Reference/wpautop" title="wpautop" target="_blank">wpautop</a>. (see post edit screen).', Motionmill::TEXTDOMAIN ),
				'type'         => 'checkbox',
				'value'        => '',
				'page'         => 'motionmill_general',
				'section'      => 'motionmill_general_general',
				'translatable' => false
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
			if ( $this->get_option( 'body_class_language' ) )
			{
				$classes[] = sprintf( 'lang-%s', MM_WordPress::get_language_code() );
			}

			return $classes;
		}

		public function on_excerpt_length( $default )
		{
			return $this->get_option( 'post_excerpt_length', $default );
		}

		public function on_excerpt_more( $excerpt )
		{
			$vars = array
			(
				'permalink' => get_permalink()
			);

			$str = $this->get_option( 'post_excerpt_more' );

			foreach ( $vars as $key => $value )
			{
				$tag = sprintf( '[%s]', $key );

				$str = str_replace( $tag, $value, $str );
			}

			return $str;
		}

		public function on_mce_css( $mce_css )
		{
			$my_css = MM_Array::explode( "\n", $this->get_option( 'post_editor_css' ) );

			if ( count( $my_css ) > 0 )
			{
				$mce_css = rtrim( $mce_css, ',' ) . ',' . implode( ',', $my_css );
			}

			return $mce_css;
		}
		
		public function on_helpers( $helpers )
		{
			array_push( $helpers , 'MM_Array', 'MM_WordPress' );

			return $helpers;
		}

		public function on_javascript_vars( $vars )
		{
			return array_merge(array
			(
				'lang' => MM_WordPress::get_language_code()
			), $vars);
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
