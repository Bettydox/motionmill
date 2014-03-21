<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists('MM_Plugin') )
{
	class MM_Plugin
	{
		protected $motionmill = null;

		public function __construct()
		{			
			$this->motionmill = Motionmill::get_instance();			

			add_action( 'motionmill_init', array(&$this, 'initialize') );
			add_filter( 'motionmill_helpers', array(&$this, 'on_helpers') );
		}

		public function initialize()
		{
			
		}

		public function on_helpers($helpers)
		{
			return $helpers;
		}
		
		public function _($plugin)
		{
			return $this->motionmill->get_plugin($plugin);
		}
	}
}
?>