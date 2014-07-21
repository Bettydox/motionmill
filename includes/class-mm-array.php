<?php if ( ! defined('ABSPATH') ) exit; // Exits when accessed directly

/**
 * Motionmill Array Helpers
 *
 * Some description.
 *
 * @author
 * @version 1.0.0
 * @package Motionmill
 * @subpackage Helpers
 * @filesource
 */

if ( ! class_exists('MM_Array') )
{
	class MM_Array
	{
		/* ---------------------------------------------------------------------------------------------------------- */

		/**
		 * Get Element By
		 *
		 * Searches elements in a multidimensional array that matches the search criteria.
		 *
		 * @package Motionmill
 		 * @subpackage array_helpers
		 * @author Maarten Menten
		 * @version 1.0.0
		 * @param search mixed The search parameters (String or Array).
		 * @param container mixed The container to search into (Array or Object).
		 * @return mixed The first element that matches the search criteria.
		 */

		static public function get_element_by($search, $container)
		{
			$elements = self::get_elements_by($search, $container, 0, 1);

			return count($elements) > 0 ? $elements[0] : null;
		}

		/* ---------------------------------------------------------------------------------------------------------- */

		/**
		 * Get Elements By
		 *
		 * Searches elements in a multidimensional array that matches the search criteria.
		 *
		 * @package Motionmill
 		 * @subpackage array_helpers
		 * @author Maarten Menten
		 * @version 1.0.0
		 * @param search mixed The search parameters (String or Array)
		 * @param container mixed The container to search into (Array or Object)
		 * @param offset integer The index to start searching
		 * @param limit integer The amount of elements to return.
		 * @return Array An array of elements the matches the search criteria.
		 */

		static public function get_elements_by($search, $container, $offset = 0, $limit = -1)
		{
			// makes sure search is array
			if ( ! is_array($search) )
			{
				parse_str($search, $search);
			}

			if ( empty($search) )
			{
				return $container;
			}

			// checks offset bounds
			if ( $offset < 0 )
			{
				$offset = 0;
			}

			// searches
			$elements = array();
			
			for ( $i = $offset; $i < count($container); $i++ )
			{ 
				if ( $limit != -1 && count($elements) == $limit )
				{
					break;
				}
				
				if ( ! isset($container[$i]) )
				{
					continue;
				}

				$element = $container[$i];
				
				// makes sure element is array
				$element_array = is_object( $element ) ? get_object_vars($element) : $element;

				$include = true;

				foreach ( $search as $key => $value )
				{
					if ( ! isset($element_array[$key]) || $element_array[$key] != $value )
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

			return $elements;
		}

		/* ---------------------------------------------------------------------------------------------------------- */

		/**
		 * Value
		 *
		 * Returns the value at specific key in an array.
		 *
		 * @package Motionmill
 		 * @subpackage array_helpers
		 * @author Maarten Menten
		 * @version 1.0.0
		 * @param key The key.
		 * @param default The default value to be returned when the key does not exists.
		 * @param container Array.
		 * @return mixed The value or default value.
		 */

		static public function value($key, $default, $container)
		{
			$array = is_array( $container ) ? $container : get_object_vars( $container );

			if ( isset( $array[ $key ] ) )
			{
				return $array[ $key ];
			}

			return $default;
		}

		/* ---------------------------------------------------------------------------------------------------------- */

		/**
		 * Explode
		 *
		 * Returns an array of strings, each of which is a substring of string 
		 * formed by splitting it on boundaries formed by the string delimiter.
		 * 
		 * Makes sure empty values are left out.
		 *
		 * @package Motionmill
 		 * @subpackage array_helpers
		 * @author Maarten Menten
		 * @version 1.0.0
		 * @param delimiter String The boundary string.
		 * @param string String The input string.
		 * @return Array An array of strings.
		 */

		static public function explode($delimiter, $string)
		{
			$array = explode( $delimiter, $string );
			$array = array_map( 'trim', $array ); // removes spaces
			$array = array_filter( $array ); // removes empty values
			$array = array_values( $array ); // clean indexes

			return $array;
		}
	}
}

?>