<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

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
			add_action( 'motionmill_settings_pages', array(&$this, 'on_settings_pages') );
			add_action( 'motionmill_settings_sections', array(&$this, 'on_settings_sections') );
			add_action( 'motionmill_settings_fields', array(&$this, 'on_settings_fields') );
			
			add_action( 'login_head', array(&$this, 'on_login_head') );
			add_filter( 'login_headerurl', array(&$this, 'on_login_header_url') );
			add_filter( 'login_headertitle', array(&$this, 'on_login_header_title') );
		}

		public function on_settings_pages($pages)
		{
			$pages[] = array
			(
				'id'          => 'motionmill_login',
				'title'       => __('Login', MM_TEXTDOMAIN),
				'description' => __('', MM_TEXTDOMAIN)
			);

			return $pages;
		}

		public function on_settings_sections($sections)
		{
			$sections[] = array
			(
				'id' 		  => 'general',
				'title' 	  => __('', MM_TEXTDOMAIN),
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
				'title'       => __('Header image', MM_TEXTDOMAIN),
				'description' => '',
				'type'        => 'image',
				'value'       => __('http://motionmill.com/wp-content/themes/motionmill2013/images/logo.png', MM_TEXTDOMAIN),
				'page'        => 'motionmill_login',
				'section'     => 'general'
			);

			$fields[] = array
			(
				'id'          => 'header_title',
				'title'       => __('Header title', MM_TEXTDOMAIN),
				'description' => __('This text will appear when you hover on the header image.', MM_TEXTDOMAIN),
				'type'        => 'textfield',
				'value'       => __('Powered by Motionmill', MM_TEXTDOMAIN),
				'page'        => 'motionmill_login',
				'section'     => 'general'
			);

			$fields[] = array
			(
				'id'        => 'header_url',
				'title'       => __('Header url', MM_TEXTDOMAIN),
				'description' => __('The url to visit when you click on the header image.', MM_TEXTDOMAIN),
				'type'		  => 'textfield',
				'value'       => __('http://motionmill.com', MM_TEXTDOMAIN),
				'page'        => 'motionmill_login',
				'section'     => 'general'
			);

			$fields[] = array
			(
				'id' 		  => 'enabled',
				'title' 	  => __('Enable', MM_TEXTDOMAIN),
				'description' => __('Check/uncheck to enable/disable.', MM_TEXTDOMAIN),
				'type'		  => 'checkbox',
				'value'       => 0,
				'page'        => 'motionmill_login',
				'section'     => 'general'
			);

			return $fields;
		}

		public function on_login_header_url($default)
		{
			$options = $this->_('MM_Settings')->get_option('motionmill_login');

			return ! empty($options['enabled']) ? $options['header_url'] : $default;
		}

		public function on_login_header_title($default)
		{
			$options = $this->_('MM_Settings')->get_option('motionmill_login');

			return ! empty($options['enabled']) ? $options['header_title'] : $default;
		}

		public function on_login_head()
		{
			$options = $this->_('MM_Settings')->get_option('motionmill_login');

			if ( empty($options['enabled']) )
				return;

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