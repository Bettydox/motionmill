<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Login
 Plugin URI: https://github.com/addwittz/motionmill/tree/master/plugins/motionmill-login
 Description: Customizes the login page.
 Version: 1.0.2
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
		protected $auth_types = array();

		public function __construct()
		{
			$this->auth_types = array
			(
				'login' => array
				(
					'title' => __( 'Username', Motionmill::TEXTDOMAIN ),
					'error' => __( 'Invalid username.', Motionmill::TEXTDOMAIN ),
				),

				'email' => array
				(
					'title' => __( 'Email', Motionmill::TEXTDOMAIN ),
					'error' => __( 'Invalid email.', Motionmill::TEXTDOMAIN )
				),

				'login|email' => array
				(
					'title' => __( 'Username or Email', Motionmill::TEXTDOMAIN ),
					'error' => __( 'Invalid username or email.', Motionmill::TEXTDOMAIN )
				)
			);

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
			add_filter( 'login_footer', array(&$this, 'on_login_footer') );
			add_filter( 'authenticate', array( &$this, 'on_authenticate' ), 30, 3 );

			add_filter( 'gettext', array( &$this, 'on_gettext' ), 20, 3 );
		}

		public function get_option( $key = null, $default = '' )
		{
			return $this->motionmill->get_plugin( 'MM_Settings' )->get_option( 'motionmill_login', $key, $default );
		}

		public function on_login_headerurl($default)
		{
			return $this->get_option( 'header_url' );
		}

		public function on_login_headertitle($default)
		{
			return $this->get_option( 'header_title' );
		}

		public function on_login_head()
		{
			$image = trim( $this->get_option( 'header_image' ) );

			if ( $image == '' )
			{
				return;
			}

			$image_sizes = MM_Image::get_size( $image );

			if ( ! is_array( $image_sizes )  )
			{
				return;
			}

			?>
				<style type="text/css">

					.login h1 a
					{ 
						width            : 100%;
						height           : <?php echo esc_html( $image_sizes['height'] ); ?>px;
						background-size  : <?php echo esc_html( $image_sizes['width'] ); ?>px <?php echo esc_html( $image_sizes['height'] ); ?>px;
						background-image : url("<?php echo esc_html( $image ); ?>");
					}

				</style>

			<?php
		}

		public function on_login_message( $default )
		{
			return $this->get_option( 'message' );
		}

		public function on_login_footer( $default )
		{
			return $this->get_option( 'footer' );
		}

		public function on_authenticate( $user, $username, $password )
		{
			if ( empty( $username ) )
			{
				return $user;
			}

			$vars = MM_Array::explode( '|', $this->get_option( 'auth_type' ) );

			$type = $this->get_option( 'auth_type' );

			if ( count( $vars ) > 0 && isset( $this->auth_types[$type] ) )
			{
				$messages = array
				(
					'login'       => __( '<strong>ERROR:</strong> Invalid username.', Motionmill::TEXTDOMAIN ),
					'email'       => __( '<strong>ERROR:</strong> Invalid email.', Motionmill::TEXTDOMAIN ),
					'login|email' => __( '<strong>ERROR:</strong> Invalid username or email.', Motionmill::TEXTDOMAIN )
				);

				foreach ( $vars as $var )
				{
					$user = get_user_by( $var, $username );

					if ( $user )
					{
						break;
					}
				}

				if ( ! $user )
				{
					return new WP_Error( 'invalid_login', $this->auth_types[$type]['error'] );
				}

				if ( ! wp_check_password( $password, $user->user_pass, $user->ID ) )
				{
					return new WP_Error( 'invalid_password', __( 'The password you entered is incorrect.', Motionmill::TEXTDOMAIN ) );
				}
			}

			return $user;
		}

		public function on_gettext( $translated_text, $text, $domain )
		{
			global $pagenow;

			if ( $pagenow != 'wp-login.php' )
			{ 
				return $translated_text;
			}

			$type = $this->get_option( 'auth_type' );

			if ( $text == 'Username' && isset( $this->auth_types[ $type ] ) )
			{
				return $this->auth_types[ $type ]['title'];
			}

			return $translated_text;
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
				'id' 		  => 'motionmill_login_layout',
				'title' 	  => __( 'Layout', Motionmill::TEXTDOMAIN ),
				'description' => __( '', Motionmill::TEXTDOMAIN ),
				'page'        => 'motionmill_login'
			);

			$sections[] = array
			(
				'id' 		  => 'motionmill_login_authentication',
				'title' 	  => __( 'Authentication', Motionmill::TEXTDOMAIN ),
				'description' => __( '', Motionmill::TEXTDOMAIN ),
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
				'section'     => 'motionmill_login_layout'
			);

			$fields[] = array
			(
				'id'          => 'header_title',
				'title'       => __( 'Header title', Motionmill::TEXTDOMAIN ),
				'description' => __( 'The text that appears when hovering the header.', Motionmill::TEXTDOMAIN ),
				'type'        => 'textfield',
				'value'       => __( 'Powered by Motionmill', Motionmill::TEXTDOMAIN ),
				'page'		  => 'motionmill_login',
				'section'     => 'motionmill_login_layout'
			);

			$fields[] = array
			(
				'id'        => 'header_url',
				'title'       => __( 'Header URL', Motionmill::TEXTDOMAIN ),
				'description' => __( 'The url to visit when clicking the header.', Motionmill::TEXTDOMAIN ),
				'type'		  => 'textfield',
				'value'       => __( 'http://motionmill.com', Motionmill::TEXTDOMAIN ),
				'page'		  => 'motionmill_login',
				'section'     => 'motionmill_login_layout'
			);

			$fields[] = array
			(
				'id'          => 'message',
				'title'       => __( 'Message', Motionmill::TEXTDOMAIN ),
				'description' => __( '', Motionmill::TEXTDOMAIN ),
				'type'		  => 'editor',
				'value'       => '',
				'page'		  => 'motionmill_login',
				'section'     => 'motionmill_login_layout'
			);

			$fields[] = array
			(
				'id'          => 'footer',
				'title'       => __( 'Footer', Motionmill::TEXTDOMAIN ),
				'description' => __( '', Motionmill::TEXTDOMAIN ),
				'type'		  => 'editor',
				'value'       => '',
				'page'		  => 'motionmill_login',
				'section'     => 'motionmill_login_layout'
			);

			$options = array();

			foreach ( $this->auth_types as $key => $data )
			{
				$options[ $key ] = $data['title'];
			}

			$fields[] = array
			(
				'id'          => 'auth_type',
				'title'       => __( 'Type', Motionmill::TEXTDOMAIN ),
				'description' => __( '', Motionmill::TEXTDOMAIN ),
				'type'		  => 'dropdown',
				'type_args'   => array( 'options' => $options ),
				'value'       => 'login',
				'page'		  => 'motionmill_login',
				'section'     => 'motionmill_login_authentication'
			);

			return $fields;
		}
	}

	// registers plugin
	function motionmill_plugins_add_login( $plugins )
	{
		array_push( $plugins, 'MM_Login' );

		return $plugins;
	}
	
	add_filter( 'motionmill_plugins', 'motionmill_plugins_add_login', 5 );
}

?>
