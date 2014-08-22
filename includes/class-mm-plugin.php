<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists('MM_Plugin') )
{
	class MM_Plugin
	{
		protected $id         = __CLASS__;
		protected $file       = __FILE__;
		protected $motionmill = null;
		
		public function __construct()
		{	
<<<<<<< HEAD
			$this->id = get_class($this);

			$ref = new ReflectionClass($this);
			$this->file = $ref->getFileName();

			$this->motionmill = Motionmill::get_instance();	

			add_action( 'motionmill_init', array(&$this, 'initialize') );

			error_log( sprintf( '%s constructed', get_class($this) ) );
=======
			$this->motionmill = Motionmill::get_instance();

			add_action( 'motionmill_helpers', array( &$this, 'on_helpers' ) );

			add_filter( 'motionmill_settings_pages', array(&$this, 'on_settings_pages') );
			add_filter( 'motionmill_settings_sections', array(&$this, 'on_settings_sections') );
			add_filter( 'motionmill_settings_fields', array(&$this, 'on_settings_fields') );

			add_action( 'motionmill_init', array( &$this, 'initialize' ) );
>>>>>>> FETCH_HEAD
		}

		public function initialize()
		{
			error_log( sprintf( '%s initialized', get_class($this) ) );
		}

<<<<<<< HEAD
		public function get_id()
		{
			return $this->id;
		}

		public function get_file()
		{
			return $this->file;
=======
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
>>>>>>> FETCH_HEAD
		}

		
	}
}
?>