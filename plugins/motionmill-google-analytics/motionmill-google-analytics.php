<?php
/**
* Plugin Name: Motionmill Google Analytics
* Plugin URI:
* Description: Connects your blog with Google Analytics
* Version: 1.0.0
* Author: Maarten Menten
* Author URI: http://motionmill.com
* License: GPL2
*/

if ( ! class_exists('MM_Google_Analytics') )
{
	class MM_Google_Analytics
	{
		protected $motionmill = null;

		public function __construct()
		{
			add_action( 'motionmill_init', array( &$this, 'initialize' ) );
		}
		
		public function initialize()
		{
			$this->motionmill = Motionmill::get_instance();
			
			add_filter( 'motionmill_settings_pages', array(&$this, 'on_settings_pages') );
			add_filter( 'motionmill_settings_sections', array(&$this, 'on_settings_sections') );
			add_filter( 'motionmill_settings_fields', array(&$this, 'on_settings_fields') );

			add_action( 'wp_head', array(&$this, 'on_head'), 1000 );
		}

		public function on_settings_pages($pages)
		{
			$pages[] = array
			(
				'id' 		   => 'motionmill_google_analytics',
				'title' 	   => __('Google Analytics', Motionmill::TEXTDOMAIN),
				'description'  => __('Connects your blog with <a href="http://www.google.com/analytics/" target="_blank">Google Analytics</a>.', Motionmill::TEXTDOMAIN)
			);

			return $pages;
		}

		public function on_settings_sections($sections)
		{
			$sections[] = array
			(
				'id' 		  => 'motionmill_google_analytics_general',
				'title' 	  => __('Tracking', Motionmill::TEXTDOMAIN),
				'description' => __('', Motionmill::TEXTDOMAIN),
				'page'		  => 'motionmill_google_analytics'
			);

			return $sections;
		}

		public function on_settings_fields($fields)
		{
			$fields[] = array
			(
				'id' 		  => 'tracking_code',
				'title' 	  => __('Code', Motionmill::TEXTDOMAIN),
				'description' => __('Leave empty to disable.', Motionmill::TEXTDOMAIN),
				'type'		  => 'textfield',
				'value'       => '',
				'page'		  => 'motionmill_google_analytics',
				'section'     => 'motionmill_google_analytics_general'
			);

			return $fields;
		}

		public function get_option( $key = null, $value = '' )
		{
			return $this->motionmill->get_plugin('MM_Settings')->get_option( 'motionmill_google_analytics', $key, $value );
		}

		public function on_head()
		{
			$tracking_code = trim( $this->get_option( 'tracking_code' ) );

			if ( $tracking_code == '' )
				return;
			?>

			<script type="text/javascript">

			  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

			  ga('create', '<?php echo esc_html( $tracking_code ); ?>', 'auto');
			  ga('send', 'pageview');

			</script>

			<?php
		}
	}

	function motionmill_plugins_add_google_analytics($plugins)
	{
		$plugins[] = 'MM_Google_Analytics';

		return $plugins;
	}

	add_action( 'motionmill_plugins', 'motionmill_plugins_add_google_analytics' );
}

?>