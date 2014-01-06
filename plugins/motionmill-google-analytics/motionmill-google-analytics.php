<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Google Analytics
 Plugin URI: http://motionmill.com
 Description: Connects your blog with Google Analytics.
 Version: 1.0.0
 Author: Motionmill
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

// loads plugin when motionmill is loaded 
add_action( 'motionmill_loaded', function(){

if ( ! class_exists('MM_Google_Analytics') )
{
	class MM_Google_Analytics extends MM_Plugin
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

			add_action( 'wp_head', array(&$this, 'on_head'), 10000 );
		}

		public function on_settings_pages($pages)
		{
			$pages[] = array
			(
				'id' 		  => 'motionmill_google_analytics',
				'title' 	  => __('Google Analytics', MM_TEXTDOMAIN),
				'description' => __('<p>Connects your blog with Google Analytics.</p>', MM_TEXTDOMAIN)
			);

			return $pages;
		}

		public function on_settings_sections($sections)
		{
			$sections[] = array
			(
				'id' 		  => 'general',
				'title' 	  => __('General', MM_TEXTDOMAIN),
				'description' => __('', MM_TEXTDOMAIN),
				'page'		  => 'motionmill_google_analytics'
			);

			return $sections;
		}

		public function on_settings_fields($fields)
		{
			$fields[] = array
			(
				'id' 		  => 'tracking_code',
				'title' 	  => __('Tracking Code', MM_TEXTDOMAIN),
				'description' => __('', MM_TEXTDOMAIN),
				'type'		  => 'textfield',
				'class'       => 'regular-text',
				'value'       => __('', MM_TEXTDOMAIN),
				'page'		  => 'motionmill_google_analytics',
				'section'     => 'general'
			);

			$fields[] = array
			(
				'id' 		  => 'enabled',
				'title' 	  => __('Enable', MM_TEXTDOMAIN),
				'description' => __('', MM_TEXTDOMAIN),
				'type'		  => 'checkbox',
				'value'       => 0,
				'page'		  => 'motionmill_google_analytics',
				'section'     => 'general'
			);

			return $fields;
		}

		public function on_head()
		{
			$options = $this->_('MM_Settings')->get_option('motionmill_google_analytics');

			if ( empty($options['enabled']) )
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