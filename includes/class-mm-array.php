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
		static public function key_value( $key_var, $value_var, $container )
		{
			$results = array();

			foreach ( $container as $element )
			{
				$element = get_object_vars( $element );

				if ( ! isset( $element[ $key_var ] ) )
				{
					continue;
				}

				$key = $element[ $key_var ];

				if ( isset( $element[ $value_var ] ) )
				{
					$value = $element[ $value_var ];
				}

				else
				{
					$value = null;
				}

				$results[ $key ] = $value;
			}

			return $results;
		}

		static public function keys_exists( $keys, $array )
		{
			foreach ( $keys as $key )
			{
				if ( ! array_key_exists( $key, $array ) )
				{
					return false;
				}
			}

			return true;
		}

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
		 * @uses MM_Array::get_elements_by
		 */

		static public function get_element_by( $search, $container )
		{
			$elements = self::get_elements_by( $search, $container, 0, 1 );

			return count( $elements ) > 0 ? $elements[0] : null;
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
		 * @return Array An array of elements the matches the search criteria.
		 */

		static public function get_elements_by( $search = '', $container, $offset = 0, $limit = -1 )
		{
			if ( ! is_array( $search ) )
			{
				parse_str( $search, $search );
			}

			if ( empty( $search ) )
			{
				return $container;
			}

			$elements = array();

			for ( $i = $offset; $i < count( $container ); $i++ )
			{ 
				$element = $container[ $i ];
			
				// makes sure element is array
				$element_array = ( ! is_array( $element ) ) ? get_object_vars( $element ) : $element;

				$include = true;

				foreach ( $search as $key => $value )
				{
					if ( ! isset( $element_array[$key] ) )
					{
						$include = false;

						break;
					}

					if ( ! is_array( $value ) )
					{
						$values = array( $value );	
					}

					else
					{
						$values = $value;
					}

					if ( ! in_array( $element_array[$key], $values ) )
					{
						$include = false;

						break;
					}
				}

				if ( ! $include )
				{
					continue;
				}

				$elements[] = $element;

				if ( count( $elements ) == $limit )
				{
					break;
				}
			}

			return $elements;
		}

		static public function get_unique_element_values( $key, $container )
		{
			$values = array();

			foreach ( $container as $element )
			{
				if ( ! is_array( $element ) )
				{
					$element = get_object_vars( $element );
				}

				if ( ! isset( $element[$key] ) )
				{
					continue;
				}

				$value = $element[$key];

				if ( in_array( $value , $values ) )
				{
					continue;
				}

				$values[] = $value;
			}

			return $values;
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