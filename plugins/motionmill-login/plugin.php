<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists('MM_Login') )
{
	class MM_Login extends MM_Plugin
	{
		public function __construct()
		{
			parent::__construct(array
            (
                'helpers' => array()
            ));
		}

		public function initialize()
		{
			add_action( 'motionmill_settings_sections', array(&$this, 'settings_sections') );
			add_action( 'motionmill_settings_fields', array(&$this, 'settings_fields') );
			
			if ( $this->_('MM_Settings')->get_option('login', 'enabled') == 1 )
			{
				add_action( 'login_head', array(&$this, 'on_head') );
				add_filter( 'login_headerurl', array(&$this, 'on_header_url') );
				add_filter( 'login_headertitle', array(&$this, 'on_header_title') );
			}
		}

		public function settings_sections($sections)
		{
			$sections[] = array
			(
				'name'        => 'login',
				'title'       => __('Login page', MM_TEXTDOMAIN),
				'description' => '',
				'sanitize_cb' => array( &$this, 'on_sanitize_options' )
			);

			return $sections;
		}

		public function settings_fields($fields)
		{
			$fields[] = array
			(
				'name'        => 'header_image',
				'title'       => __('Header image', MM_TEXTDOMAIN),
				'description' => '',
				'type'		  => 'image',
				'value'       => __('http://motionmill.com/wp-content/themes/motionmill2013/images/logo.png', MM_TEXTDOMAIN),
				'section'     => 'login'
			);

			$fields[] = array
			(
				'name'        => 'header_title',
				'title'       => __('Header title', MM_TEXTDOMAIN),
				'description' => __('This text will appear when you hover on the header image.', MM_TEXTDOMAIN),
				'type'		  => 'textfield',
				'value'       => __('Powered by Motionmill', MM_TEXTDOMAIN),
				'section'     => 'login'
			);

			$fields[] = array
			(
				'name'        => 'header_url',
				'title'       => __('Header url', MM_TEXTDOMAIN),
				'description' => __('The url to visit when you click on the header image.', MM_TEXTDOMAIN),
				'type'		  => 'textfield',
				'value'       => __('http://motionmill.com', MM_TEXTDOMAIN),
				'section'     => 'login'
			);

			$fields[] = array
			(
				'name' 		  => 'enabled',
				'title' 	  => __('Enable', MM_TEXTDOMAIN),
				'description' => __('Check/uncheck to enable/disable.', MM_TEXTDOMAIN),
				'type'		  => 'checkbox',
				'value'       => 0,
				'section'     => 'login'
			);

			return $fields;
		}

		public function on_header_url($default)
		{
			$options = $this->_('MM_Settings')->get_option('login');

			return ! empty($options['activate']) ? $options['header_url'] : $default;
		}

		public function on_header_title($default)
		{
			$options = $this->_('MM_Settings')->get_option('login');

			return ! empty($options['activate']) ? $options['header_title'] : $default;
		}

		public function on_head()
		{
			$options = $this->_('MM_Settings')->get_option('login');

			if ( trim( $options['header_image'] ) == '' )
				return;

			$size = @getimagesize( $options['header_image'] );

			if ( ! is_array($size)  )
				return;

			?>
				<style type="text/css">

					.login h1 a
					{ 
						width            : 100%;
						height           : <?php echo esc_html( $size[1] ); ?>px;
						background-size  : <?php echo esc_html( $size[0] ); ?>px <?php echo esc_html( $size[1] ); ?>px;
						background-image : url("<?php echo esc_html( $options['header_image'] ); ?>");
					}

				</style>

			<?php
		}

		public function on_sanitize_options($input)
		{
			return $input;
		}
	}

	// registers plugin
	function motionmill_login_register($plugins)
	{
		array_push($plugins, 'MM_Login');

		return $plugins;
	}
	
	add_filter( 'motionmill_plugins', 'motionmill_login_register', 5 );
}

?>