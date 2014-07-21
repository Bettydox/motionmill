<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Dashboard Widget
 Plugin URI: http://motionmill.com
 Description: Creates an editable widget on the dashboard.
 Version: 1.0.1
 Author: Motionmill
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

// checks if motionmill plugin is loaded
add_action( 'motionmill_loaded', function(){

if ( ! class_exists('MM_Dashboard_Widget') )
{
	class MM_Dashboard_Widget extends MM_Plugin
	{
		public function initialize()
		{	
			add_action( 'wp_dashboard_setup', array(&$this, 'on_dashboard_setup') );
			add_action( 'admin_head', array(&$this, 'on_admin_head') );
		}

		public function get_option( $key = null, $default = '' )
		{
			return $this->motionmill->get_plugin( 'MM_Settings' )->get_option( 'motionmill_dashboard_widget', $key, $default );
		}

		public function on_settings_pages($pages)
		{
			$pages[] = array
			(
				'id' 		  => 'motionmill_dashboard_widget',
				'title' 	  => __('Dashboard Widget', Motionmill::TEXT_DOMAIN),
				'description' => __('<p>Creates an editable widget on the dashboard.</p>', Motionmill::TEXT_DOMAIN),
				'option_name' => 'motionmill_dashboard_widget'
			);

			return $pages;
		}

		public function on_settings_sections($sections)
		{
			$sections[] = array
			(
				'id' 		  => 'motionmill_dashboard_widget_content',
				'title' 	  => __('Content', Motionmill::TEXT_DOMAIN),
				'description' => __('', Motionmill::TEXT_DOMAIN),
				'page'        => 'motionmill_dashboard_widget'
			);

			$sections[] = array
			(
				'id' 		  => 'motionmill_dashboard_widget_styling',
				'title' 	  => __('Styling', Motionmill::TEXT_DOMAIN),
				'description' => __('', Motionmill::TEXT_DOMAIN),
				'page'        => 'motionmill_dashboard_widget'
			);

			return $sections;
		}

		public function on_settings_fields($fields)
		{
			$fields[] = array
			(
				'id' 		  => 'title',
				'title' 	  => __('Title', Motionmill::TEXT_DOMAIN),
				'description' => __('', Motionmill::TEXT_DOMAIN),
				'type'		  => 'textfield',
				'value'       => 'Motionmill',
				'page'        => 'motionmill_dashboard_widget',
				'section'     => 'motionmill_dashboard_widget_content'
			);

			$fields[] = array
			(
				'id' 		  => 'content',
				'title' 	  => __('Content', Motionmill::TEXT_DOMAIN),
				'description' => __('', Motionmill::TEXT_DOMAIN),
				'type'		  => 'editor',
				'wpautop'     => false,
				'value'       => __('Enjoy your site!', Motionmill::TEXT_DOMAIN),
				'page'        => 'motionmill_dashboard_widget',
				'section'     => 'motionmill_dashboard_widget_content'
			);

			$fields[] = array
			(
				'id' 		  => 'header_color',
				'title' 	  => __('Header Text Color', Motionmill::TEXT_DOMAIN),
				'description' => __('Leave empty to use defaults.', Motionmill::TEXT_DOMAIN),
				'type'		  => 'colorpicker',
				'value'       => '#FFFFFF',
				'page'        => 'motionmill_dashboard_widget',
				'section'     => 'motionmill_dashboard_widget_styling'
			);

			$fields[] = array
			(
				'id' 		  => 'header_background_color',
				'title' 	  => __('Header Background Color', Motionmill::TEXT_DOMAIN),
				'description' => __('Leave empty to use defaults.', Motionmill::TEXT_DOMAIN),
				'type'		  => 'colorpicker',
				'value'       => '#ed1e26',
				'page'        => 'motionmill_dashboard_widget',
				'section'     => 'motionmill_dashboard_widget_styling'
			);

			$fields[] = array
			(
				'id' 		  => 'border_color',
				'title' 	  => __('Border Color', Motionmill::TEXT_DOMAIN),
				'description' => __('Leave empty to use defaults.', Motionmill::TEXT_DOMAIN),
				'type'		  => 'colorpicker',
				'value'       => '#ed1e26',
				'page'        => 'motionmill_dashboard_widget',
				'section'     => 'motionmill_dashboard_widget_styling'
			);

			return $fields;
		}

		public function on_dashboard_setup()
		{
			wp_add_dashboard_widget( 'mm_dashboard_widget', $this->get_option( 'title' ), array(&$this, 'on_print_dashboard_widget') );
		}

		public function on_print_dashboard_widget()
		{
			echo $this->get_option( 'content' );
		}

		public function on_admin_head()
		{
			$screen = get_current_screen();

			if ( $screen->id != 'dashboard' )
				return;

			?>

			<style type="text/css">
		
				#mm_dashboard_widget
				{
					<?php if ( $this->get_option( 'border_color' ) != '' ) : ?>
					border-color: <?php echo $this->get_option( 'border_color' ); ?>;
					<?php endif; ?>
				}

				#mm_dashboard_widget .hndle
				{
					<?php if ( $this->get_option( 'header_color' ) != '' ) : ?>
					color: <?php echo $this->get_option( 'header_color' ); ?>;
					<?php endif; ?>

					<?php if ( $this->get_option( 'header_background_color' ) != '' ) : ?>
					background-color: <?php echo $this->get_option( 'header_background_color' ); ?>;
					<?php endif; ?>
				}

			</style>

			<?php
		}
	}

	// registers plugin
	function motionmill_plugin_add_dashboard_widget($plugins)
	{
		array_push($plugins, 'MM_Dashboard_Widget');

		return $plugins;
	}
	
	add_filter( 'motionmill_plugins', 'motionmill_plugin_add_dashboard_widget', 5 );
}

});

?>