<?php

if ( ! function_exists('mm_get_instance') )
{
	function mm_get_instance()
	{
		static $instance = null;

		if ( $instance == null )
		{
			$instance = new Motionmill();
		}

		return $instance;
	}
}

?>