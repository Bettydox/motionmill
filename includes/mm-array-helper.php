<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! function_exists('mm_get_element_by') )
{
	function mm_get_element_by($prop, $value, $container = array())
	{
		$elements = mm_get_elements_by( $prop, $value, $container );

		return count($elements) > 0 ? $elements[0] : null;
	}
}

if ( ! function_exists('mm_get_elements_by') )
{
	function mm_get_elements_by($search, $container)
	{
		if ( ! is_array($search) )
		{
			parse_str($search, $search);
		}

		$elements = array();

		if ( is_array($container) )
		{
			foreach ( $container as $element )
			{
				$include = true;

				foreach ( $search as $key => $value )
				{
					if ( ! isset($element[$key]) || $element[$key] != $value )
					{
						$include = false;

						break;
					}
				}

				if ( $include )
				{
					$elements[] = $element;
				}
			}
		}

		return $elements;
	}
}

if ( ! function_exists('mm_get_element_values') )
{
	function mm_get_element_values($key, $container)
	{
		$a = array();

		foreach ( $container as $element )
		{
			$element_array = ! is_array($element) ? get_object_vars($element) : $element;

			if ( ! isset($element_array[$key]) )
				break;

			if ( in_array($element_array[$key], $a) )
				continue;

			$a[] = $element_array[$key];
		}

		return $a;
	}
}

if ( ! function_exists('mm_clean_explode') )
{
	function mm_clean_explode($delimiter, $string)
	{
		// removes spaces, newlines and delimiters around delimiter
		$string = preg_replace( sprintf( '/([ \t\n]*%s[ \t\n]*)+/', preg_quote($delimiter) ), $delimiter, $string);
		// removes first and last character if delimiter
		$string = trim($string, $delimiter);

		return strlen($string) > 0 ? explode($delimiter, $string) : array();
	}
}

?>