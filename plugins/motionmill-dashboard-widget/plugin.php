<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists('MM_Dashboard_Widget') )
{
	class MM_Dashboard_Widget extends MM_Plugin
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
			add_filter( 'motionmill_settings_sections', array(&$this, 'on_settings_sections') );
			add_filter( 'motionmill_settings_fields', array(&$this, 'on_settings_fields') );

			if ( $this->_('MM_Settings')->get_option('dashboard_widget', 'enabled') == 1 )
			{
				add_action( 'wp_dashboard_setup', array(&$this, 'on_dashboard_setup') );
				add_action( 'admin_head', array(&$this, 'on_admin_head') );
			}
		}

		public function on_settings_sections($sections)
		{
			$sections[] = array
			(
				'name' 		  => 'dashboard_widget',
				'title' 	  => __('Dashboard widget', MM_TEXTDOMAIN),
				'description' => __('Creates an editable widget on the dashboard.', MM_TEXTDOMAIN),
				'parent'      => ''
			);

			return $sections;
		}

		public function on_settings_fields($fields)
		{
			$fields[] = array
			(
				'name' 		  => 'title',
				'title' 	  => __('Title', MM_TEXTDOMAIN),
				'description' => __('', MM_TEXTDOMAIN),
				'type'		  => 'textfield',
				'value'       => __('My Dashboard Widget', MM_TEXTDOMAIN),
				'section'     => 'dashboard_widget'
			);

			$fields[] = array
			(
				'name' 		  => 'content',
				'title' 	  => __('Content', MM_TEXTDOMAIN),
				'description' => __('', MM_TEXTDOMAIN),
				'type'		  => 'editor',
				'value'       => '',
				'section'     => 'dashboard_widget'
			);

			$fields[] = array
			(
				'name' 		  => 'textcolor',
				'title' 	  => __('Header text color', MM_TEXTDOMAIN),
				'description' => __('Leave empty to use defaults.', MM_TEXTDOMAIN),
				'type'		  => 'colorpicker',
				'value'       => '#FFFFFF',
				'section'     => 'dashboard_widget'
			);

			$fields[] = array
			(
				'name' 		  => 'bgcolor',
				'title' 	  => __('Header background color', MM_TEXTDOMAIN),
				'description' => __('Leave empty to use defaults.', MM_TEXTDOMAIN),
				'type'		  => 'colorpicker',
				'value'       => '#2787B2',
				'section'     => 'dashboard_widget'
			);

			$fields[] = array
			(
				'name' 		  => 'enabled',
				'title' 	  => __('Enable', MM_TEXTDOMAIN),
				'description' => __('Check/uncheck to enable/disable.', MM_TEXTDOMAIN),
				'type'		  => 'checkbox',
				'value'       => 0,
				'section'     => 'dashboard_widget'
			);

			return $fields;
		}

		public function on_dashboard_setup()
		{
			$options = $this->_('MM_Settings')->get_option('dashboard_widget');

			wp_add_dashboard_widget( 'mm_dashboard_widget', $options['title'], array(&$this, 'print_dashboard_widget') );
		}

		public function print_dashboard_widget()
		{
			echo $this->_('MM_Settings')->get_option('dashboard_widget', 'content');
		}

		public function on_admin_head()
		{
			$screen = get_current_screen();

			if ( $screen->id != 'dashboard' )
				return;

			$options = $this->_('MM_Settings')->get_option('dashboard_widget');

			?>

			<style type="text/css">
		
				#mm_dashboard_widget h3.hndle
				{
					<?php if ( $this->_('MM_Settings')->get_option('dashboard_widget', 'textcolor') != '' ) : ?>
					text-shadow: none;
					color: <?php echo esc_html( $this->_('MM_Settings')->get_option('dashboard_widget', 'textcolor') ); ?>;
					<?php endif; ?>

					<?php if ( $this->_('MM_Settings')->get_option('dashboard_widget', 'bgcolor') != '' ) : ?>
					background: <?php echo esc_html( $this->_('MM_Settings')->get_option('dashboard_widget', 'bgcolor') ); ?>;
					<?php endif; ?>
				}

			</style>

			<?php
		}
	}

	function mm_dashboard_widget_register($plugins)
	{
		$plugins[] = 'MM_Dashboard_Widget';

		return $plugins;
	}

	add_action( 'motionmill_plugins', 'mm_dashboard_widget_register', 5 );
}


?>