<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill General
 Plugin URI: https://github.com/addwittz/motionmill/tree/master/plugins/motionmill-general
 Description: Handles general WordPress settings.
 Version: 1.0.1
 Author: Maarten Menten
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

if ( ! class_exists( 'MM_General' ) )
{
	class MM_General
	{
		protected $motionmill = null;

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
			$this->motionmill = Motionmill::get_instance();
			
			add_action( 'wp_head', array( &$this, 'on_wp_head' ) );

			add_filter( 'body_class', array( &$this, 'on_body_class' ) );
			add_filter( 'mce_css', array( &$this, 'on_mce_css' ) );

			if ( $this->get_option( 'post_excerpt_length' ) != '' )
			{
				add_filter( 'excerpt_length', array( &$this, 'on_excerpt_length' ) );
			}

			if ( $this->get_option( 'post_excerpt_more' ) != '' )
			{
				add_filter( 'excerpt_more', array( &$this, 'on_excerpt_more' ) );
			}
			
			if ( $this->get_option( 'tracking_enable' ) )
			{
				add_action( 'wp_head', array(&$this, 'print_tracking_code'), 999 );
			}

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
				'description' => __('', Motionmill::TEXTDOMAIN),
				'priority'    => 10
			);

			return $pages;
		}

		public function on_settings_sections($sections)
		{
			// favicon

			$sections[] = array
			(
				'id' 		  => 'motionmill_general_general',
				'title' 	  => __( 'Miscellanious', Motionmill::TEXTDOMAIN ),
				'description' => __( '', Motionmill::TEXTDOMAIN ),
				'page'        => 'motionmill_general'
			);

			// posts

			$sections[] = array
			(
				'id' 		  => 'motionmill_general_post',
				'title' 	  => __( 'Post', Motionmill::TEXTDOMAIN ),
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
				'title' 	  => __( 'Favicon', Motionmill::TEXTDOMAIN ),
				'description' => __( "Also known as a Web site icon or bookmark icon. Browsers that provide favicon support typically display a favicon in the browser's address bar. Most commonly 16×16 pixels", Motionmill::TEXTDOMAIN ),
				'type'		  => 'media',
				'value'       => '',
				'page'		  => 'motionmill_general',
				'section'     => 'motionmill_general_general'
			);

			// post

			$fields[] = array
			(
				'id' 		  => 'post_excerpt_length',
				'title' 	  => __( 'Excerpt Length', Motionmill::TEXTDOMAIN ),
				'description' => __( 'The maximum number of words for a single post <a href="http://codex.wordpress.org/Excerpt" target="_blank">excerpt</a>. Leave empty to use the default value.', Motionmill::TEXTDOMAIN ),
				'type'		  => 'textfield',
				'value'       => '',
				'page'		  => 'motionmill_general',
				'section'     => 'motionmill_general_post'
			);

			$fields[] = array
			(
				'id' 		  => 'post_excerpt_more',
				'title' 	  => __( 'Excerpt More', Motionmill::TEXTDOMAIN ),
				'description' => __( 'The text after the cut off excerpt. Use <code>[permalink]</code> to refer to the url. (Leave empty to use the default value)', Motionmill::TEXTDOMAIN ),
				'type'		  => 'textfield',
				'value'       => '',
				'page'		  => 'motionmill_general',
				'section'     => 'motionmill_general_post'
			);

			$fields[] = array
			(
				'id' 		  => 'post_editor_css',
				'title' 	  => __( 'Editor CSS', Motionmill::TEXTDOMAIN ),
				'description' => __( 'Additional CSS file URLs for the rich text editor. (One entry per line)', Motionmill::TEXTDOMAIN ),
				'type'		  => 'textarea',
				'value'       => '',
				'page'		  => 'motionmill_general',
				'section'     => 'motionmill_general_post'
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
			$classes[] = sprintf( 'lang-%s', MM_Wordpress::get_language_code() );

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
		
		public function print_tracking_code()
		{
			?>

			<script type="text/javascript">

			  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

			  ga('create', '<?php echo esc_html( $this->get_option( "tracking_code" ) ); ?>', 'auto');
			  ga('send', 'pageview');

			</script>

			<?php
		}

		public function on_helpers( $helpers )
		{
			array_push( $helpers , 'MM_Array', 'MM_Wordpress' );

			return $helpers;
		}

		public function on_javascript_vars( $vars )
		{
			return array_merge(array
			(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'lang' 	  => MM_Wordpress::get_language_code()
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
