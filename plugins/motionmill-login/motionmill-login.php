<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Login
 Plugin URI: https://github.com/addwittz/motionmill/tree/master/plugins/motionmill-login
 Description: Customizes the login page.
 Version: 1.0.5
 Author: Maarten Menten
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

if ( ! class_exists('MM_Login') )
{
	class MM_Login
	{
		const FILE = __FILE__;

		public function __construct()
		{
			//require_once( plugin_dir_path( self::FILE ) . 'includes/class-mm-login-form.php' );

			add_filter( 'motionmill_helpers', array( &$this, 'on_helpers' ) );
			add_filter( 'motionmill_settings_pages', array(&$this, 'on_settings_pages') );
			add_filter( 'motionmill_settings_sections', array(&$this, 'on_settings_sections') );
			add_filter( 'motionmill_settings_fields', array(&$this, 'on_settings_fields') );
			add_action( 'motionmill_init', array( &$this, 'initialize' ) );
		}
		
		public function initialize()
		{	
			if ( ! $this->get_option( 'enable' ) )
			{
				return;
			}

			// admin
			add_action( 'login_head', array(&$this, 'on_login_head') );
			add_action( 'login_footer', array(&$this, 'on_login_footer') );
			add_filter( 'login_headerurl', array(&$this, 'on_login_headerurl') );
			add_filter( 'login_headertitle', array(&$this, 'on_login_headertitle') );
			add_filter( 'login_message', array(&$this, 'on_login_message') );
			add_filter( 'login_redirect', array(&$this, 'on_login_redirect'), 10, 3 );

			if ( $this->get_option('auth_type') )
			{
				add_filter( 'authenticate', array( &$this, 'on_authenticate' ), 30, 3 );
				add_filter( 'gettext', array( &$this, 'on_gettext' ), 20, 3 );
			}
		}

		public function get_option( $key = null, $default = '' )
		{
			return MM('Settings')->get_option( 'motionmill_login', $key, $default );
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
			$image = $this->get_option( 'header_image' );

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

					h1 a
					{
						width		     : <?php echo esc_html( $image_sizes['width'] ); ?>px !important;
						height			 : <?php echo esc_html( $image_sizes['height'] ); ?>px !important;
						background-image : url("<?php echo esc_html( $image ); ?>") !important;
						background-size  : <?php echo esc_html( $image_sizes['width'] ); ?>px <?php echo esc_html( $image_sizes['height'] ); ?>px !important;
						margin-left      : -40px;
         			}

				</style>

			<?php
		}

		public function on_login_message( $default )
		{
			return $this->get_option( 'message' );
		}

		public function on_login_footer()
		{
			echo $this->get_option( 'footer' );
		}

		public function on_login_redirect( $default, $request, $user )
		{
			$post_id = $this->get_option( 'redirect' );

			if ( $post_id )
			{
				return get_permalink( $post_id );
			}

			return $default;
		}

		public function on_authenticate( $user, $login, $password )
		{
			$errors = apply_filters( 'motionmill_login_authentication_errors', array
			(
				''               => __( 'Invalid login.', Motionmill::TEXTDOMAIN ),
				'username'       => __( 'Username is not registered.', Motionmill::TEXTDOMAIN ),
				'email'          => __( 'Email is not registered.', Motionmill::TEXTDOMAIN ),
				'username_email' => __( 'Username or email is not registered.', Motionmill::TEXTDOMAIN )
			));

			$auth_type = $this->get_option( 'auth_type' );

			switch ( $auth_type )
			{
				case 'login':
					
					$user = get_user_by( 'login', $login );

					break;

				case 'email':
					
					$user = get_user_by( 'email', $login );

					break;

				case 'login_email':
					
					$user = get_user_by( 'login', $login );

					if ( ! $user )
					{
						$user = get_user_by( 'email', $login );
					}

					break;
			}

			if ( ! $user )
			{
				return new WP_Error( sprintf( 'invalid_login_%s', $auth_type ), $errors[ $auth_type ] );
			}

			if ( ! wp_check_password( $password, $user->user_pass, $user->ID ) )
			{
				return new WP_Error( 'invalid_password', __( 'Password is incorrect.', Motionmill::TEXTDOMAIN ) );
			}

			return $user;
		}

		public function on_gettext( $translated_text, $text, $domain )
		{
			global $pagenow;

			if ( $pagenow == 'wp-login.php' )
			{
				$auth_type = $this->get_option('auth_type');

				$labels = apply_filters( 'motionmill_login_labels', array
				(
					'Username' => array
					(
						'username'       => __( 'Username', Motionmill::TEXTDOMAIN ),
						'email'          => __( 'Email', Motionmill::TEXTDOMAIN ),
						'username_email' => __( 'Username or Email', Motionmill::TEXTDOMAIN )
					)
				));

				if ( isset( $labels[ $text ] ) )
				{
					$messages = $labels[ $text ];

					if ( isset( $messages[ $auth_type ] ) )
					{
						$message = $messages[ $auth_type ];

						return $message;
					}
				}
			}

			return $translated_text;
		}

		public function on_helpers( $helpers )
		{
			array_push( $helpers , 'MM_Image', 'MM_Form' );

			return $helpers;
		}

		public function on_settings_pages($pages)
		{
			$pages[] = array
			(
				'id' 		  => 'motionmill_login',
				'title' 	  => __('Login', Motionmill::TEXTDOMAIN),
				'description' => __('', Motionmill::TEXTDOMAIN)
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

			$sections[] = array
			(
				'id' 		  => 'motionmill_login_activation',
				'title' 	  => __( 'Activation', Motionmill::TEXTDOMAIN ),
				'description' => __( '', Motionmill::TEXTDOMAIN ),
				'page'        => 'motionmill_login'
			);

			return $sections;
		}

		public function on_settings_fields($fields)
		{
			$fields[] = array
			(
				'id'           => 'header_image',
				'title'        => __( 'Header image', Motionmill::TEXTDOMAIN ),
				'description'  => __( '', Motionmill::TEXTDOMAIN ),
				'type'         => 'media',
				'value'        => __( plugins_url('images/logo-motionmill.png', Motionmill::FILE), Motionmill::TEXTDOMAIN ),
				'page'         => 'motionmill_login',
				'section'      => 'motionmill_login_layout',
				'translatable' => true
			);

			$fields[] = array
			(
				'id'           => 'header_title',
				'title'        => __( 'Header title', Motionmill::TEXTDOMAIN ),
				'description'  => __( 'The text that appears when hovering the header.', Motionmill::TEXTDOMAIN ),
				'type'         => 'textfield',
				'value'        => __( 'Powered by Motionmill', Motionmill::TEXTDOMAIN ),
				'page'         => 'motionmill_login',
				'section'      => 'motionmill_login_layout',
				'translatable' => true
			);

			$fields[] = array
			(
				'id'           => 'header_url',
				'title'        => __( 'Header URL', Motionmill::TEXTDOMAIN ),
				'description'  => __( 'The url to visit when clicking the header.', Motionmill::TEXTDOMAIN ),
				'type'         => 'textfield',
				'value'        => __( 'http://motionmill.com', Motionmill::TEXTDOMAIN ),
				'page'         => 'motionmill_login',
				'section'      => 'motionmill_login_layout',
				'translatable' => true
			);

			$fields[] = array
			(
				'id'           => 'message',
				'title'        => __( 'Message', Motionmill::TEXTDOMAIN ),
				'description'  => __( '', Motionmill::TEXTDOMAIN ),
				'type'         => 'editor',
				'value'        => '',
				'page'         => 'motionmill_login',
				'section'      => 'motionmill_login_layout',
				'translatable' => true
			);

			$fields[] = array
			(
				'id'           => 'footer',
				'title'        => __( 'Footer', Motionmill::TEXTDOMAIN ),
				'description'  => __( '', Motionmill::TEXTDOMAIN ),
				'type'         => 'editor',
				'value'        => '',
				'page'         => 'motionmill_login',
				'section'      => 'motionmill_login_layout',
				'translatable' => true
			);

			$fields[] = array
			(
				'id'          => 'auth_type',
				'title'       => __( 'Type', Motionmill::TEXTDOMAIN ),
				'description' => __( '', Motionmill::TEXTDOMAIN ),
				'type'		  => 'dropdown',
				'type_args'   => array
				(
					'options' => array
					(
						''            => __( "- Don't change -", Motionmill::TEXTDOMAIN ),
						'login'       => __( 'Username', Motionmill::TEXTDOMAIN ),
						'email'       => __( 'Email', Motionmill::TEXTDOMAIN ),
						'login_email' => __( 'Username or email', Motionmill::TEXTDOMAIN ),
					)
				),
				'value'        => '',
				'page'         => 'motionmill_login',
				'section'      => 'motionmill_login_authentication',
				'translatable' => false
			);
			
			$options = array
			(
				'' => __( "- Don't change -", Motionmill::TEXTDOMAIN )
			);

			$options = $options + MM_Form::get_post_dropdown_options( 'page' );

			$fields[] = array
			(
				'id'           => 'redirect',
				'title'        => __( 'Redirect', Motionmill::TEXTDOMAIN ),
				'description'  => __( 'The page to go to when user is authenticated.', Motionmill::TEXTDOMAIN ),
				'type'         => 'dropdown',
				'type_args'    => array( 'options' => $options ),
				'value'        => '',
				'page'         => 'motionmill_login',
				'section'      => 'motionmill_login_authentication',
				'translatable' => true
			);

			$fields[] = array
			(
				'id'           => 'enable',
				'title'        => __( 'Enable', Motionmill::TEXTDOMAIN ),
				'description'  => __( 'Check/uncheck to enable/disable.', Motionmill::TEXTDOMAIN ),
				'type'         => 'checkbox',
				'value'        => 1,
				'page'         => 'motionmill_login',
				'section'      => 'motionmill_login_activation',
				'translatable' => false
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
