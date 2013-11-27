<?php

if ( ! function_exists('mm_get_user_role'))
{
	function mm_get_user_role($user_id, $key = null)
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
}

if ( ! function_exists('mm_error_message'))
{
	function mm_error_message($message, $type = 'error')
	{
		$types = array('error', 'updated');

		if ( ! in_array($type, $types) )
			$type = $types[0];

		return sprintf( '<div class="%s"><p><strong>%s</strong></p></div>', $type, $message );
	}
}

?>