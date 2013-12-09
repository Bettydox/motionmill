<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists('MM_Dashboard_Widget') )
{
	class MM_Dashboard_Widget extends MM_Plugin
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

			add_action( 'wp_dashboard_setup', array(&$this, 'on_dashboard_setup') );
			add_action( 'admin_head', array(&$this, 'on_admin_head') );
		}

		public function on_settings_pages($pages)
		{
			$pages[] = array
			(
				'id' 		  => 'motionmill_dashboard_widget',
				'title' 	  => __('Dashboard widget', MM_TEXTDOMAIN),
				'description' => __('Creates an editable widget on the dashboard.', MM_TEXTDOMAIN),
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
				'page'        => 'motionmill_dashboard_widget'
			);

			return $sections;
		}

		public function on_settings_fields($fields)
		{
			$fields[] = array
			(
				'id' 		  => 'title',
				'title' 	  => __('Title', MM_TEXTDOMAIN),
				'description' => __('', MM_TEXTDOMAIN),
				'type'		  => 'textfield',
				'value'       => __('My Dashboard Widget', MM_TEXTDOMAIN),
				'page'        => 'motionmill_dashboard_widget',
				'section'     => 'general'
			);

			$fields[] = array
			(
				'id' 		  => 'content',
				'title' 	  => __('Content', MM_TEXTDOMAIN),
				'description' => __('', MM_TEXTDOMAIN),
				'type'		  => 'editor',
				'value'       => '',
				'page'        => 'motionmill_dashboard_widget',
				'section'     => 'general'
			);

			$fields[] = array
			(
				'id' 		  => 'textcolor',
				'title' 	  => __('Header text color', MM_TEXTDOMAIN),
				'description' => __('Leave empty to use defaults.', MM_TEXTDOMAIN),
				'type'		  => 'colorpicker',
				'value'       => '#FFFFFF',
				'page'        => 'motionmill_dashboard_widget',
				'section'     => 'general'
			);

			$fields[] = array
			(
				'id' 		  => 'bgcolor',
				'title' 	  => __('Header background color', MM_TEXTDOMAIN),
				'description' => __('Leave empty to use defaults.', MM_TEXTDOMAIN),
				'type'		  => 'colorpicker',
				'value'       => '#2787B2',
				'page'        => 'motionmill_dashboard_widget',
				'section'     => 'general'
			);

			$fields[] = array
			(
				'id' 		  => 'enabled',
				'title' 	  => __('Enable', MM_TEXTDOMAIN),
				'description' => __('Check/uncheck to enable/disable.', MM_TEXTDOMAIN),
				'type'		  => 'checkbox',
				'value'       => 0,
				'page'        => 'motionmill_dashboard_widget',
				'section'     => 'general'
			);

			return $fields;
		}

		public function on_dashboard_setup()
		{
			$options = $this->_('MM_Settings')->get_option('motionmill_dashboard_widget');

			if ( empty($options['enabled']) )
				return;

			wp_add_dashboard_widget( 'mm_dashboard_widget', $options['title'], array(&$this, 'print_dashboard_widget') );
		}

		public function print_dashboard_widget()
		{
			$options = $this->_('MM_Settings')->get_option('motionmill_dashboard_widget');

			if ( empty($options['enabled']) )
				return;

			echo $options['content'];
		}

		public function on_admin_head()
		{
			$screen = get_current_screen();

			if ( $screen->id != 'dashboard' )
				return;

			$options = $this->_('MM_Settings')->get_option('motionmill_dashboard_widget');

			if ( empty($options['enabled']) )
				return;

			?>

			<style type="text/css">
		
				#mm_dashboard_widget h3.hndle
				{
					<?php if ( $options['textcolor'] != '' ) : ?>
					text-shadow: none;
					color: <?php echo esc_html( $options['textcolor'] ); ?>;
					<?php endif; ?>

					<?php if ( $options['bgcolor'] != '' ) : ?>
					background: <?php echo esc_html( $options['bgcolor'] ); ?>;
					<?php endif; ?>
				}

			</style>

			<?php
		}
	}

	function motionmill_dashboard_widget_register($plugins)
	{
		$plugins[] = 'MM_Dashboard_Widget';

		return $plugins;
	}

	add_action( 'motionmill_plugins', 'motionmill_dashboard_widget_register', 5 );
}


?>