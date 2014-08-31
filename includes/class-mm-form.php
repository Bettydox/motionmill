<?php if ( ! defined('ABSPATH') ) exit; // Exits when accessed directly

if ( ! class_exists('MM_Form') )
{
	class MM_Form
	{
		/* ---------------------------------------------------------------------------------------------------------- */

		/**
		 * Get Post
		 *
		 * Returns the posted data of a form element. (after the form has been submitted)
		 *
		 * @author Maarten Menten
		 * @version 1.0.0
		 * @category Form
		 * @param key String The name of the form element.
		 * @param default mixed The value to be returned when te element could not be found. (optional)
		 * @return mixed The value of the post or the default value.
		 */
		
		static public function get_post($key, $default = '')
		{
			if ( isset($_POST[$key]) ) 
			{
				return $_POST[$key];
			}

			return $default;
		}
		
		static public function parse_attributes( $attributes )
		{
			$str = '';

			foreach ( $attributes as $key => $value )
			{
				$str .= sprintf( ' %s="%s"', esc_html( $key ), esc_attr( $value ) );
			}

			return $str;
		}

	}
}

?>