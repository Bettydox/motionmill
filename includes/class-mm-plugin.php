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
		}

		public function initialize()
		{
			
		}
		
		public function _($plugin)
		{
			return $this->motionmill->get_plugin($plugin);
		}
	}
}
?>