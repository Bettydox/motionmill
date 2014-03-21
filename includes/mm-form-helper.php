<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! function_exists('mm_form_post') )
{
	function mm_form_post($name, $default = '')
	{
		if ( isset($_POST) && isset($_POST[$name]) )
		{
			return $_POST[$name];
		}

		return $default;
	}
}

if ( ! function_exists('mm_form_value') )
{
	function mm_form_value($name, $default = '')
	{
		return esc_html( mm_form_post($name, $default) );
	}
}

?>