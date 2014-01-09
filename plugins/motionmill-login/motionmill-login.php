<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Login
 Plugin URI: http://motionmill.com
 Description: Customizes the WordPress login page.
 Version: 1.0.0
 Author: Motionmill
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

add_action( 'motionmill_loaded', function(){

if ( ! class_exists('MM_Login') )
{
	class MM_Login extends MM_Plugin
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

			add_action( 'login_head', array(&$this, 'on_login_head') );
			add_filter( 'login_headerurl', array(&$this, 'on_login_headerurl') );
			add_filter( 'login_headertitle', array(&$this, 'on_login_headertitle') );
			add_filter( 'login_message', array(&$this, 'on_login_message') );
		}

		public function on_settings_pages($pages)
		{
			$pages[] = array
			(
				'id' 		  => 'motionmill_login',
				'title' 	  => __('Login', MM_TEXTDOMAIN),
				'description' => __('<p>Customizes the WordPress login page.</p>', MM_TEXTDOMAIN)
			);

			return $pages;
		}

		public function on_settings_sections($sections)
		{
			$sections[] = array
			(
				'id' 		  => 'general',
				'title' 	  => __('General', MM_TEXTDOMAIN),
				'description' => __('', MM_TEXTDOMAIN),
				'page'        => 'motionmill_login'
			);

			return $sections;
		}

		public function on_settings_fields($fields)
		{
			$fields[] = array
			(
				'id'          => 'header_image',
				'title'       => __( 'Header Image', MM_TEXTDOMAIN ),
				'description' => __( '', MM_TEXTDOMAIN ),
				'type'        => 'textfield',
				'class'       => 'regular-text',
				'value'       => __( 'http://motionmill.com/motionmill-plugin/images/motionmill-logo.png', MM_TEXTDOMAIN ),
				'page'		  => 'motionmill_login',
				'section'     => 'general'
			);

			$fields[] = array
			(
				'id'          => 'header_title',
				'title'       => __( 'Header Title', MM_TEXTDOMAIN ),
				'description' => __( 'The text that appears when hovering the header.', MM_TEXTDOMAIN ),
				'type'        => 'textfield',
				'class'       => 'regular-text',
				'value'       => __( 'Powered by Motionmill', MM_TEXTDOMAIN ),
				'page'		  => 'motionmill_login',
				'section'     => 'general'
			);

			$fields[] = array
			(
				'id'        => 'header_url',
				'title'       => __( 'Header URL', MM_TEXTDOMAIN ),
				'description' => __( 'The url to visit when clicking the header.', MM_TEXTDOMAIN ),
				'type'		  => 'textfield',
				'class'       => 'regular-text',
				'value'       => __( 'http://motionmill.com', MM_TEXTDOMAIN ),
				'page'		  => 'motionmill_login',
				'section'     => 'general'
			);

			$fields[] = array
			(
				'id'          => 'message',
				'title'       => __( 'Message', MM_TEXTDOMAIN ),
				'description' => __( '', MM_TEXTDOMAIN ),
				'type'		  => 'textarea',
				'rows'        => '3',
				'class'       => 'regular-text',
				'value'       => __( '', MM_TEXTDOMAIN ),
				'page'		  => 'motionmill_login',
				'section'     => 'general'
			);

			$fields[] = array
			(
				'id' 		  => 'enabled',
				'title' 	  => __( 'Enable', MM_TEXTDOMAIN ),
				'description' => __( '', MM_TEXTDOMAIN ),
				'type'		  => 'checkbox',
				'value'       => '1',
				'page'		  => 'motionmill_login',
				'section'     => 'general'
			);

			return $fields;
		}

		public function on_login_headerurl($default)
		{
			$options = $this->_('MM_Settings')->get_option('motionmill_login');

			return ! empty( $options['enabled'] ) ? $options['header_url'] : $default;
		}

		public function on_login_headertitle($default)
		{
			$options = $this->_('MM_Settings')->get_option('motionmill_login');

			return ! empty( $options['enabled'] ) ? $options['header_title'] : $default;
		}

		public function on_login_head()
		{
			$options = $this->_('MM_Settings')->get_option('motionmill_login');

			if ( empty( $options['enabled'] ) || $options['header_image'] == '' )
				return;

			$header_image_sizes =  MM_Helper::get_image_size( $options['header_image'] );

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
			$options = $this->_('MM_Settings')->get_option('motionmill_login');

			return ! empty( $options['enabled'] ) ? $options['message'] : $default;
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

});

?>