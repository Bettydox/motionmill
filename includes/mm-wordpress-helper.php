<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! function_exists('mm_get_user_role') )
{
	function mm_get_user_role($user_id = 0)
	{
		global $wp_roles;

		$user = null;

		if ( $user_id )
		{
			$user = new WP_User( $user_id );
		}
		
		else if ( is_user_logged_in() )
		{
			$user = wp_get_current_user();
		}

		if ( $user )
		{
			$role = $user->roles[0];

			// checks if role is registered
			if ( isset($wp_roles->role_names[$role]) )
			{
				return $wp_roles->role_names[$role];
			}
		}
		
		return false;
	}
}

if ( ! function_exists('mm_get_error_messages_string') )
{
	function mm_get_error_messages_string($error, $options = array())
	{
		$options = array_merge(array
		(
			'wrapper_format' => '<div class="errors">%s</div>',
			'message_format' => '%s<br />'
 		), $options);

		$codes = $error->get_error_codes();

		$str = '';

		if ( count($codes) > 0 )
		{
			foreach ( $codes as $code )
			{
				$str .= mm_get_error_message_string( $code, $options['message_format'] );
			}

			$str .= sprintf( $options['wrapper_format'], $str );
		}

		return $str;
	}
}

if ( ! function_exists('mm_get_error_message_string') )
{
	function mm_get_error_message_string($error, $code = '', $format = '<div class="error">%s</div>')
	{
		$message = $error->get_error_message($code);

		if ( empty($message) )
			return '';

		return sprintf( $format, $message );
	}
}

?>