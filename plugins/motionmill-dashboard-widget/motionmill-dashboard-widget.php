<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Dashboard Widget
 Plugin URI: http://motionmill.com
 Description: Creates an editable widget on the dashboard.
 Version: 1.0.0
 Author: Motionmill
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

add_action( 'motionmill_loaded', function(){

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
				'title' 	  => __('Dashboard Widget', MM_TEXTDOMAIN),
				'description' => __('<p>Creates an editable widget on the dashboard.</p>', MM_TEXTDOMAIN)
			);

			return $pages;
		}

		public function on_settings_sections($sections)
		{
			$sections[] = array
			(
				'id' 		  => 'content',
				'title' 	  => __('Content', MM_TEXTDOMAIN),
				'description' => __('', MM_TEXTDOMAIN),
				'page'        => 'motionmill_dashboard_widget'
			);

			$sections[] = array
			(
				'id' 		  => 'styling',
				'title' 	  => __('Styling', MM_TEXTDOMAIN),
				'description' => __('', MM_TEXTDOMAIN),
				'page'        => 'motionmill_dashboard_widget'
			);

			$sections[] = array
			(
				'id' 		  => 'activation',
				'title' 	  => __('Activation', MM_TEXTDOMAIN),
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
				'value'       => 'Motionmill',
				'page'        => 'motionmill_dashboard_widget',
				'section'     => 'content'
			);

			$fields[] = array
			(
				'id' 		  => 'content',
				'title' 	  => __('Content', MM_TEXTDOMAIN),
				'description' => __('', MM_TEXTDOMAIN),
				'type'		  => 'editor',
				'wpautop'     => false,
				'value'       => __('Enjoy your site!', MM_TEXTDOMAIN),
				'page'        => 'motionmill_dashboard_widget',
				'section'     => 'content'
			);

			$fields[] = array
			(
				'id' 		  => 'header_color',
				'title' 	  => __('Header Text Color', MM_TEXTDOMAIN),
				'description' => __('Leave empty to use defaults.', MM_TEXTDOMAIN),
				'type'		  => 'colorpicker',
				'value'       => '#FFFFFF',
				'page'        => 'motionmill_dashboard_widget',
				'section'     => 'styling'
			);

			$fields[] = array
			(
				'id' 		  => 'header_background_color',
				'title' 	  => __('Header Background Color', MM_TEXTDOMAIN),
				'description' => __('Leave empty to use defaults.', MM_TEXTDOMAIN),
				'type'		  => 'colorpicker',
				'value'       => '#ed1e26',
				'page'        => 'motionmill_dashboard_widget',
				'section'     => 'styling'
			);

			$fields[] = array
			(
				'id' 		  => 'border_color',
				'title' 	  => __('Border Color', MM_TEXTDOMAIN),
				'description' => __('Leave empty to use defaults.', MM_TEXTDOMAIN),
				'type'		  => 'colorpicker',
				'value'       => '#ed1e26',
				'page'        => 'motionmill_dashboard_widget',
				'section'     => 'styling'
			);

			$fields[] = array
			(
				'id' 		  => 'enabled',
				'title' 	  => __('Enable', MM_TEXTDOMAIN),
				'description' => __('Check/uncheck to enable/disable.', MM_TEXTDOMAIN),
				'type'		  => 'checkbox',
				'value'       => '1',
				'page'        => 'motionmill_dashboard_widget',
				'section'     => 'activation'
			);

			return $fields;
		}

		public function on_dashboard_setup()
		{
			$options = $this->_('MM_Settings')->get_option('motionmill_dashboard_widget');

			if ( empty($options['enabled']) )
				return;

			wp_add_dashboard_widget( 'mm_dashboard_widget', $options['title'], array(&$this, 'on_print_dashboard_widget') );
		}

		public function on_print_dashboard_widget()
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
		
				#mm_dashboard_widget
				{
					<?php if ( $options['border_color'] != '' ) : ?>
					border-color: <?php echo $options['border_color']; ?>;
					<?php endif; ?>
				}

				#mm_dashboard_widget .hndle
				{
					<?php if ( $options['header_color'] != '' ) : ?>
					color: <?php echo $options['header_color']; ?>;
					<?php endif; ?>

					<?php if ( $options['header_background_color'] != '' ) : ?>
					background-color: <?php echo $options['header_background_color']; ?>;
					<?php endif; ?>
				}

				#mm_dashboard_widget .inside
				{
					
				}

			</style>

			<?php
		}
	}

	// registers plugin
	function motionmill_plugins_add_dashboard_widget($plugins)
	{
		array_push($plugins, 'MM_Dashboard_Widget');

		return $plugins;
	}
	
	add_filter( 'motionmill_plugins', 'motionmill_plugins_add_dashboard_widget', 5 );
}

});

?>