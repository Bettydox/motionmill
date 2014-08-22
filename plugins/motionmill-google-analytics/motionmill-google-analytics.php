<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Google Analytics
 Plugin URI: http://motionmill.com
 Description: Connects your blog with <a href="http://www.google.com/analytics/" target="_blank">Google Analytics</a>.
 Version: 1.0.1
 Author: Motionmill
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

// checks if motionmill plugin is loaded
add_action( 'motionmill_loaded', function(){

if ( ! class_exists('MM_Google_Analytics') )
{
	class MM_Google_Analytics extends MM_Plugin
	{
		public function initialize()
		{
			add_action( 'wp_head', array(&$this, 'on_head'), 1000 );
		}

		public function on_settings_pages($pages)
		{
			$pages[] = array
			(
				'id' 		   => 'motionmill_google_analytics',
				'title' 	   => __('Google Analytics', Motionmill::TEXT_DOMAIN),
				'description'  => __('Connects your blog with <a href="http://www.google.com/analytics/" target="_blank">Google Analytics</a>.', Motionmill::TEXT_DOMAIN)
			);

			return $pages;
		}

		public function on_settings_sections($sections)
		{
			$sections[] = array
			(
				'id' 		  => 'motionmill_google_analytics_general',
				'title' 	  => __('', Motionmill::TEXT_DOMAIN),
				'description' => __('', Motionmill::TEXT_DOMAIN),
				'page'		  => 'motionmill_google_analytics'
			);

			return $sections;
		}

		public function on_settings_fields($fields)
		{
			$fields[] = array
			(
				'id' 		  => 'tracking_code',
				'title' 	  => __('Tracking Code', Motionmill::TEXT_DOMAIN),
				'description' => __('Leave empty to disable.', Motionmill::TEXT_DOMAIN),
				'type'		  => 'textfield',
				'class'       => 'regular-text',
				'value'       => __('', Motionmill::TEXT_DOMAIN),
				'page'		  => 'motionmill_google_analytics',
				'section'     => 'motionmill_google_analytics_general'
			);

			return $fields;
		}

		public function on_head()
		{
			$options = $this->motionmill->get_plugin('MM_Settings')->get_option('motionmill_google_analytics');

			if ( trim($options['tracking_code']) == '' )
				return;
			?>

			<script type="text/javascript">
	 
				var _gaq = _gaq || [];
				_gaq.push(['_setAccount', '<?php echo esc_js( $options["tracking_code"] ); ?>']);
				_gaq.push(['_trackPageview']);

				(function()
				{
					var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
					ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
					var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
				})();

			</script>

			<?php
		}
	}

	function motionmill_plugins_add_google_analytics($plugins)
	{
		$plugins[] = 'MM_Google_Analytics';

		return $plugins;
	}

	add_action( 'motionmill_plugins', 'motionmill_plugins_add_google_analytics', 5 );
}
});

?>