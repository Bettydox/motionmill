<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists('MM_Plugin') )
{
	class MM_Plugin
	{
		protected $mm = null;
		protected $helpers = array();

		public function __construct($args = array())
		{
			$options = array_merge(array
			(
				'helpers' => array()
			), (array) $args );

			$this->mm = &mm_get_instance();
			$this->helpers = $options['helpers'];

			add_filter( 'motionmill_helpers', array(&$this, 'helpers') );
			add_action( 'motionmill_init', array(&$this, 'initialize') );
		}

		public function initialize()
		{

		}
		
		public function _($plugin)
		{
			return $this->mm->get_plugin($plugin);
		}

		public function helpers($helpers)
		{
			return array_merge($this->helpers, $helpers);
		}
	}
}
?>