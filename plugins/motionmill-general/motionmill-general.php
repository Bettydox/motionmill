<?php
/**
* Plugin Name: Motionmill General
* Plugin URI:
* Description: Handles general WordPress settings.
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
			add_filter( 'motionmill_settings_pages', array(&$this, 'on_settings_pages') );
			add_filter( 'motionmill_settings_sections', array(&$this, 'on_settings_sections') );
			add_filter( 'motionmill_settings_fields', array(&$this, 'on_settings_fields') );
			add_action( 'motionmill_init', array( &$this, 'initialize' ) );
		}

		public function initialize()
		{
			$this->motionmill = Motionmill::get_instance();
			
			add_action( 'wp_head', array( &$this, 'on_wp_head' ) );
			add_action( 'wp_enqueue_scripts', array( &$this, 'on_enqueue_scripts' ) );

			add_filter( 'body_class', array( &$this, 'on_body_class' ) );
			add_filter( 'mce_css', array( &$this, 'on_mce_css' ) );
			add_filter( 'excerpt_length', array( &$this, 'on_excerpt_length' ) );

			if ( $this->get_option( 'mail_enable' ) )
			{
				add_action( 'wp_mail_from', array(&$this, 'on_mail_from') );
				add_filter( 'wp_mail_from_name', array(&$this, 'on_mail_from_name') );
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

			// body class

			$sections[] = array
			(
				'id' 		  => 'motionmill_general_body_class',
				'title' 	  => __( 'Body class', Motionmill::TEXTDOMAIN ),
				'description' => __( 'Adds extra information to the HTML &lt;body&gt; tag. Most commonly used by developers for styling or scripting purposes.', Motionmill::TEXTDOMAIN ),
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

			// tracking

			$sections[] = array
			(
				'id' 		  => 'motionmill_general_tracking',
				'title' 	  => __('Tracking', Motionmill::TEXTDOMAIN),
				'description' => __('', Motionmill::TEXTDOMAIN),
				'page'		  => 'motionmill_general'
			);

			// mail

			$sections[] = array
			(
				'id' 		  => 'motionmill_general_mail',
				'title' 	  => __('Mail', Motionmill::TEXTDOMAIN),
				'description' => __('', Motionmill::TEXTDOMAIN),
				'page'		  => 'motionmill_general'
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

			// post

			$fields[] = array
			(
				'id' 		  => 'post_excerpt_length',
				'title' 	  => __( 'Excerpt Length', Motionmill::TEXTDOMAIN ),
				'description' => __( 'The length of a single-post <a href="http://codex.wordpress.org/Excerpt" target="_blank">excerpt</a>.', Motionmill::TEXTDOMAIN ),
				'type'		  => 'textfield',
				'value'       => 20,
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

			// tracking

			$fields[] = array
			(
				'id' 		  => 'tracking_code',
				'title' 	  => __( 'Code', Motionmill::TEXTDOMAIN ),
				'description' => __( 'The <a href="http://google.com/analytics" target="_blank">Google Analytics</a> tracking code.', Motionmill::TEXTDOMAIN ),
				'type'		  => 'textfield',
				'value'       => '',
				'page'		  => 'motionmill_general',
				'section'     => 'motionmill_general_tracking'
			);

			$fields[] = array
			(
				'id' 		  => 'tracking_enable',
				'title' 	  => __( 'Enable', Motionmill::TEXTDOMAIN ),
				'description' => __( 'Check/uncheck to enable/disable.', Motionmill::TEXTDOMAIN ),
				'type'		  => 'checkbox',
				'value'       => 0,
				'page'		  => 'motionmill_general',
				'section'     => 'motionmill_general_tracking'
			);

			// mail
			$fields[] = array
			(
				'id'          => 'mail_from_name',
				'title'       => __( "Sender's name", Motionmill::TEXTDOMAIN ),
				'description' => __( '', Motionmill::TEXTDOMAIN ),
				'type'        => 'textfield',
				'class'       => 'regular-text',
				'value'       => get_bloginfo( 'name' ),
				'page'        => 'motionmill_general',
				'section'     => 'motionmill_general_mail'
			);

			$fields[] = array
			(
				'id'          => 'mail_from',
				'title'       => __( "Sender's email", Motionmill::TEXTDOMAIN ),
				'description' => __( '', Motionmill::TEXTDOMAIN ),
				'type'        => 'textfield',
				'class'       => 'regular-text',
				'value'       => get_option( 'admin_email' ),
				'page'        => 'motionmill_general',
				'section'     => 'motionmill_general_mail'
			);

			$fields[] = array
			(
				'id' 		  => 'mail_enable',
				'title' 	  => __( 'Enable', Motionmill::TEXTDOMAIN ),
				'description' => __( 'Check/uncheck to enable/disable.', Motionmill::TEXTDOMAIN ),
				'type'		  => 'checkbox',
				'value'       => 1,
				'page'		  => 'motionmill_general',
				'section'     => 'motionmill_general_mail'
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
				$language = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : substr( get_bloginfo('language') , 0, 2 );

				$classes[] = sprintf( 'lang-%s', strtolower($language) );
			}

			// post count
			if ( $this->get_option( 'body_class_post_count' ) )
			{
				global $wp_query;

				$classes[] = sprintf( 'post-count-%s' , $wp_query->post_count );
			}

			return $classes;
		}

		public function on_excerpt_length( $length )
		{
			return $this->get_option( 'post_excerpt_length', $length );
		}

		public function on_mce_css( $mce_css )
		{
			$my_css = $this->get_option( 'post_editor_css' );
			$my_css = explode( "\n", $my_css );
			$my_css = array_filter( $my_css );
			$my_css = array_values( $my_css );

			if ( ! empty( $my_css ) )
			{
				$mce_css = rtrim( $mce_css, ',' ) . ',' . implode( ',', $my_css );
			}

			return $mce_css;
		}
		
		public function on_mail_from_name($default)
		{
			return $this->get_option( 'mail_from_name' );
		}

		public function on_mail_from( $default )
		{
			return $this->get_option( 'mail_from' );
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

		public function on_enqueue_scripts()
		{
			wp_enqueue_script( 'motionmill-general', plugins_url( 'js/scripts.js', __FILE__ ), array( 'jquery' ), '1.0.0', false );
		
			wp_localize_script( 'motionmill-general', 'mm_general_options', $this->get_option() );
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