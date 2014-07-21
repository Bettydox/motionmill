<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Login
 Plugin URI: http://motionmill.com
 Description: Customizes the WordPress login page.
 Version: 1.0.1
 Author: Motionmill
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

// checks if motionmill plugin is loaded
add_action( 'motionmill_loaded', function(){

if ( ! class_exists('MM_Login') )
{
	class MM_Login extends MM_Plugin
	{
		public function initialize()
		{	
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
				'title' 	  => __('Login', Motionmill::TEXT_DOMAIN),
				'description' => __('Customizes the WordPress login page.', Motionmill::TEXT_DOMAIN)
			);

			return $pages;
		}

		public function on_settings_sections($sections)
		{
			$sections[] = array
			(
				'id' 		  => 'motionmill_login_general',
				'title' 	  => __('', Motionmill::TEXT_DOMAIN),
				'description' => __('', Motionmill::TEXT_DOMAIN),
				'page'        => 'motionmill_login'
			);

			return $sections;
		}

		public function on_settings_fields($fields)
		{
			$fields[] = array
			(
				'id'          => 'header_image',
				'title'       => __( 'Header image', Motionmill::TEXT_DOMAIN ),
				'description' => __( '', Motionmill::TEXT_DOMAIN ),
				'type'        => 'media',
				'class'       => 'regular-text',
				'value'       => __( plugins_url('images/logo-motionmill.png', Motionmill::FILE), Motionmill::TEXT_DOMAIN ),
				'page'		  => 'motionmill_login',
				'section'     => 'motionmill_login_general'
			);

			$fields[] = array
			(
				'id'          => 'header_title',
				'title'       => __( 'Header title', Motionmill::TEXT_DOMAIN ),
				'description' => __( 'The text that appears when hovering the header.', Motionmill::TEXT_DOMAIN ),
				'type'        => 'textfield',
				'class'       => 'regular-text',
				'value'       => __( 'Powered by Motionmill', Motionmill::TEXT_DOMAIN ),
				'page'		  => 'motionmill_login',
				'section'     => 'motionmill_login_general'
			);

			$fields[] = array
			(
				'id'        => 'header_url',
				'title'       => __( 'Header URL', Motionmill::TEXT_DOMAIN ),
				'description' => __( 'The url to visit when clicking the header.', Motionmill::TEXT_DOMAIN ),
				'type'		  => 'textfield',
				'class'       => 'regular-text',
				'value'       => __( 'http://motionmill.com', Motionmill::TEXT_DOMAIN ),
				'page'		  => 'motionmill_login',
				'section'     => 'motionmill_login_general'
			);

			$fields[] = array
			(
				'id'          => 'message',
				'title'       => __( 'Message', Motionmill::TEXT_DOMAIN ),
				'description' => __( '', Motionmill::TEXT_DOMAIN ),
				'type'		  => 'editor',
				'class'       => 'large-text',
				'value'       => __( '', Motionmill::TEXT_DOMAIN ),
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

});

?>