<?php if ( ! defined('ABSPATH') ) exit; // Exits when accessed directly

if ( ! class_exists('MM_Wordpress') )
{
	class MM_Wordpress
	{
		/* ---------------------------------------------------------------------------------------------------------- */

		/**
		 * Get User Role
		 *
		 * Returns The role of a user.
		 * When no ID is supplied the role of the current user will be returned.
		 *
		 * @author Maarten Menten
		 * @version 1.0.0
		 * @category wordpress
		 * @param user_id integer The id of the user (optional).
		 * @return mixed The role of the user or false if the user could not be found.
		 */

		static public function get_user_role( $user_id = 0 )
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

		/* ---------------------------------------------------------------------------------------------------------- */

		/**
		 * Is User Blog
		 *
		 * Return true when a user is registered for a certain blog.
		 *
		 * @author Maarten Menten
		 * @version 1.0.0
		 * @category wordpress
		 * @note This method is only supported in multisites.
		 * @param user_id The id of the user (optional). Default: the logged in user
		 * @param blog_id The id of the blog (optional). Default: the current blog
		 * @return Boolean
		 */

		static public function is_user_blog( $user_id = 0, $blog_id = 0 )
		{
		    if ( ! $user_id && is_user_logged_in() )
		    {
		        $user_id = get_current_user_id();
		    }
		 
		    if ( ! $blog_id )
		    {
		        $blog_id = get_current_blog_id();
		    }
		 
		    $blogs = get_blogs_of_user( $user_id );
		 
		    if ( is_array($blogs) )
		    {
		        foreach ( $blogs as $blog )
		        {
		            if ( $blog->userblog_id == $blog_id )
		            {
		                return true;
		            }
		        }
		    }
		 
		    return false;
		}
	}
}

?>