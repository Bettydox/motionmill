<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

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
			add_filter( 'motionmill_settings_sections', array(&$this, 'on_settings_sections') );
			add_filter( 'motionmill_settings_fields', array(&$this, 'on_settings_fields') );

			if ( $this->_('MM_Settings')->get_option('google_analytics', 'enabled') == 1 )
			{
				add_action( 'wp_head', array(&$this, 'on_head'), 10000 );
			}
		}

		public function on_settings_sections($sections)
		{
			$sections[] = array
			(
				'name' 		  => 'google_analytics',
				'title' 	  => __('Google analytics', MM_TEXTDOMAIN),
				'description' => __('Connects your blog with Google analytics.', MM_TEXTDOMAIN),
				'parent'      => ''
			);

			return $sections;
		}

		public function on_settings_fields($fields)
		{
			$fields[] = array
			(
				'name' 		  => 'tracking_code',
				'title' 	  => __('Tracking code', MM_TEXTDOMAIN),
				'description' => __('', MM_TEXTDOMAIN),
				'type'		  => 'textfield',
				'value'       => __('', MM_TEXTDOMAIN),
				'section'     => 'google_analytics'
			);

			$fields[] = array
			(
				'name' 		  => 'enabled',
				'title' 	  => __('Enable', MM_TEXTDOMAIN),
				'description' => __('Check/uncheck to enable/disable.', MM_TEXTDOMAIN),
				'type'		  => 'checkbox',
				'value'       => 0,
				'section'     => 'google_analytics'
			);

			return $fields;
		}

		public function on_head()
		{
			$options = $this->_('MM_Settings')->get_option( 'google_analytics' );

			if ( empty($options['tracking_code']) )
				return;

			?>

			<script type="text/javascript">
	 
				var _gaq = _gaq || [];
				_gaq.push(['_setAccount', '<?php echo esc_html($options["tracking_code"]); ?>']);
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

	function mm_google_analytics_register($plugins)
	{
		$plugins[] = 'MM_Google_Analytics';

		return $plugins;
	}

	add_action( 'motionmill_plugins', 'mm_google_analytics_register', 5 );
}
?>