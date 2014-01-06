<?php

class MM_Helper
{
	public static function form_post($name, $default = '')
	{
		if ( isset($_POST) && isset($_POST[$name]) )
		{
			return $_POST[$name];
		}

		return $default;
	}

	public static function form_value($name, $default = '')
	{
		return esc_html( self::form_post($name, $default) );
	}

	public static function clean_path($path)
	{
		return trim( preg_replace( '/([\/ \t]*\/[\/ \t]*)+/', '/', $path) , '/');
	}

	public static function get_user_role($user_id, $key = null)
	{
		$user = new WP_User($user_id);
		$role_name = $user->roles[0];

		if ( ! $key )
		{
			return $role_name;
		}

		$roles = get_option('wp_user_roles', array());

		if ( isset($roles[$role_name][$key]) )
		{
			return $roles[$role_name][$key];
		}
		
		return '';
	}

	public static function error_message($message, $type = 'error')
	{
		$types = array('error', 'updated');

		if ( ! in_array($type, $types) )
			$type = $types[0];

		return sprintf( '<div class="%s"><p><strong>%s</strong></p></div>', $type, $message );
	}

	public static function get_element_by($prop, $value, $container = array())
	{
		$elements = self::get_elements_by( $prop, $value, $container );

		return count($elements) > 0 ? $elements[0] : null;
	}

	public static function get_elements_by($search, $container)
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

	public static function get_element_values($key, $container)
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

	public static function clean_explode($delimiter, $string)
	{
		// removes spaces, newlines and delimiters around delimiter
		$string = preg_replace( sprintf( '/([ \t\n]*%s[ \t\n]*)+/', preg_quote($delimiter) ), $delimiter, $string);
		// removes first and last character if delimiter
		$string = trim($string, $delimiter);

		return strlen($string) > 0 ? explode($delimiter, $string) : array();
	}
}

?>