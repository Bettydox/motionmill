<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! function_exists('mm_clean_path') )
{
	function mm_clean_path($path)
	{
		return trim( preg_replace( '/([\/ \t]*\/[\/ \t]*)+/', '/', $path) , '/');
	}
}

?>