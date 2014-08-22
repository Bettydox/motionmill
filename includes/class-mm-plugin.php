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
			$this->id = get_class($this);

			$ref = new ReflectionClass($this);
			$this->file = $ref->getFileName();

			$this->motionmill = Motionmill::get_instance();	

			add_action( 'motionmill_init', array(&$this, 'initialize') );

			error_log( sprintf( '%s constructed', get_class($this) ) );
		}

		public function initialize()
		{
			error_log( sprintf( '%s initialized', get_class($this) ) );
		}

		public function get_id()
		{
			return $this->id;
		}

		public function get_file()
		{
			return $this->file;
		}

		
	}
}
?>