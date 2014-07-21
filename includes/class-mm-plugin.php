<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists('MM_Plugin') )
{
	class MM_Plugin
	{
		protected $motionmill = null;
		
		public function __construct()
		{	
			$this->motionmill = Motionmill::get_instance();

			add_action( 'motionmill_helpers', array( &$this, 'on_helpers' ) );

			add_filter( 'motionmill_settings_pages', array(&$this, 'on_settings_pages') );
			add_filter( 'motionmill_settings_sections', array(&$this, 'on_settings_sections') );
			add_filter( 'motionmill_settings_fields', array(&$this, 'on_settings_fields') );

			add_action( 'motionmill_init', array( &$this, 'initialize' ) );
		}

		public function initialize()
		{
			
		}

		public function on_settings_pages($pages)
		{
			return $pages;
		}

		public function on_settings_sections($sections)
		{
			return $sections;
		}

		public function on_settings_fields($fields)
		{
			return $fields;
		}

		public function on_helpers( $helpers )
		{
			return $helpers;
		}
	}
}
?>