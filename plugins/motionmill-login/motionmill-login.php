<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Login
 Plugin URI:
 Description: Customizes the login page.
 Version: 1.0.0
 Author: Maarten Menten
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

if ( ! class_exists('MM_Login') )
{
	class MM_Login
	{
		protected $motionmill = null;

		public function __construct()
		{
			add_filter( 'motionmill_helpers', array( &$this, 'on_helpers' ) );
			add_filter( 'motionmill_settings_pages', array(&$this, 'on_settings_pages') );
			add_filter( 'motionmill_settings_sections', array(&$this, 'on_settings_sections') );
			add_filter( 'motionmill_settings_fields', array(&$this, 'on_settings_fields') );
			add_action( 'motionmill_init', array( &$this, 'initialize' ) );
		}
		
		public function initialize()
		{	
			$this->motionmill = Motionmill::get_instance();
			
			add_action( 'login_head', array(&$this, 'on_login_head') );
			add_filter( 'login_headerurl', array(&$this, 'on_login_headerurl') );
			add_filter( 'login_headertitle', array(&$this, 'on_login_headertitle') );
			add_filter( 'login_message', array(&$this, 'on_login_message') );
		}

		public function on_helpers($helpers)
		{
			$helpers[] = 'MM_Image';

			return $helpers;
		}

		public function on_settings_pages($pages)
		{
			$pages[] = array
			(
				'id' 		  => 'motionmill_login',
				'title' 	  => __('Login', Motionmill::TEXTDOMAIN),
				'description' => __('Customizes the WordPress login page.', Motionmill::TEXTDOMAIN)
			);

			return $pages;
		}

		public function on_settings_sections($sections)
		{
			$sections[] = array
			(
				'id' 		  => 'motionmill_login_general',
				'title' 	  => __('', Motionmill::TEXTDOMAIN),
				'description' => __('', Motionmill::TEXTDOMAIN),
				'page'        => 'motionmill_login'
			);

			return $sections;
		}

		public function on_settings_fields($fields)
		{
			$fields[] = array
			(
				'id'          => 'header_image',
				'title'       => __( 'Header image', Motionmill::TEXTDOMAIN ),
				'description' => __( '', Motionmill::TEXTDOMAIN ),
				'type'        => 'media',
				'value'       => __( plugins_url('images/logo-motionmill.png', Motionmill::FILE), Motionmill::TEXTDOMAIN ),
				'page'		  => 'motionmill_login',
				'section'     => 'motionmill_login_general'
			);

			$fields[] = array
			(
				'id'          => 'header_title',
				'title'       => __( 'Header title', Motionmill::TEXTDOMAIN ),
				'description' => __( 'The text that appears when hovering the header.', Motionmill::TEXTDOMAIN ),
				'type'        => 'textfield',
				'value'       => __( 'Powered by Motionmill', Motionmill::TEXTDOMAIN ),
				'page'		  => 'motionmill_login',
				'section'     => 'motionmill_login_general'
			);

			$fields[] = array
			(
				'id'        => 'header_url',
				'title'       => __( 'Header URL', Motionmill::TEXTDOMAIN ),
				'description' => __( 'The url to visit when clicking the header.', Motionmill::TEXTDOMAIN ),
				'type'		  => 'textfield',
				'value'       => __( 'http://motionmill.com', Motionmill::TEXTDOMAIN ),
				'page'		  => 'motionmill_login',
				'section'     => 'motionmill_login_general'
			);

			$fields[] = array
			(
				'id'          => 'message',
				'title'       => __( 'Message', Motionmill::TEXTDOMAIN ),
				'description' => __( '', Motionmill::TEXTDOMAIN ),
				'type'		  => 'editor',
				'value'       => __( '', Motionmill::TEXTDOMAIN ),
				'page'		  => 'motionmill_login',
				'section'     => 'motionmill_login_general'
			);

			return $fields;
		}

		public function on_login_headerurl($default)
		{
			$options = $this->motionmill->get_plugin('MM_Settings')->get_option('motionmill_login');

			return $options['header_url'];
		}

		public function on_login_headertitle($default)
		{
			$options = $this->motionmill->get_plugin('MM_Settings')->get_option('motionmill_login');

			return $options['header_title'];
		}

		public function on_login_head()
		{
			$options = $this->motionmill->get_plugin('MM_Settings')->get_option('motionmill_login');

			if ( $options['header_image'] == '' )
				return;

			$header_image_sizes = MM_Image::get_size( $options['header_image'] );

			if ( ! is_array($header_image_sizes)  )
				return;

			?>
				<style type="text/css">

					.login h1 a
					{ 
						width            : 100%;
						height           : <?php echo esc_html( $header_image_sizes['height'] ); ?>px;
						background-size  : <?php echo esc_html( $header_image_sizes['width'] ); ?>px <?php echo esc_html( $header_image_sizes['height'] ); ?>px;
						background-image : url("<?php echo esc_html( $options['header_image'] ); ?>");
					}

				</style>

			<?php
		}

		public function on_login_message($default)
		{
			$options = $this->motionmill->get_plugin('MM_Settings')->get_option('motionmill_login');

			return $options['message'];
		}
	}

	// registers plugin
	function motionmill_plugins_add_login($plugins)
	{
		array_push($plugins, 'MM_Login');

		return $plugins;
	}
	
	add_filter( 'motionmill_plugins', 'motionmill_plugins_add_login', 5 );
}

?>