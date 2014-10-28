<?php if ( ! defined('ABSPATH') ) exit; // Exits when accessed directly

if ( ! class_exists('MM_Wordpress') )
{
	class MM_Wordpress
	{
		static public function get_language_code()
		{
			if ( defined( 'ICL_LANGUAGE_CODE' ) )
			{
				return ICL_LANGUAGE_CODE;
			}

			return substr( get_bloginfo('language'), 0, 2 );
		}

		static public function get_language_codes()
		{
			return array_keys( self::get_languages() );
		}

		static public function is_active_language( $lang )
		{
			$codes = self::get_language_codes();

			return isset( $codes[ $lang ] );
		}
		
		static public function is_multilingual()
		{
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

			return is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' );
		}

		static public function get_languages()
		{
			$languages = array();

			if ( self::is_multilingual() )
			{
				foreach ( icl_get_languages('skip_missing=N&orderby=KEY&order=DIR&link_empty_to=str') as $code => $lang )
				{
				 	$languages[ $code ] = $lang['native_name'];
				} 
			}

			else
			{
				$languages[ self::get_language_code() ] = __( 'English', Motionmill::TEXTDOMAIN ); 
			}

			return $languages;
		}

		static public function get_terms_by_post_type( $taxonomy, $post_type, $args = array() )
		{
			extract( array_merge( array
			(
				'hide_empty' => true
			), $args ) );

		    global $wpdb;

		    $query = $wpdb->prepare( "SELECT term.*, COUNT(*) from $wpdb->terms AS term 
		    	INNER JOIN $wpdb->term_taxonomy AS tax ON term.term_id = tax.term_id 
		    	INNER JOIN $wpdb->term_relationships AS relation ON relation.term_taxonomy_id = tax.term_taxonomy_id 
		    	INNER JOIN $wpdb->posts AS post ON post.ID = relation.object_id WHERE post.post_type = %s AND tax.taxonomy = %s GROUP BY term.term_id", $post_type, $taxonomy );

		    $rows = $wpdb->get_results( $query );

		    if ( ! is_array( $rows ) )
		    {
		    	return null;
		    }

		    $terms = array();

		    foreach ( $rows as $row )
		    {
		    	if ( $hide_empty && $row->{'COUNT(*)'} == 0 )
		    	{
		    		continue;
		    	}

		    	$term = get_term( $row->term_id, $taxonomy );

		    	if ( ! $term || is_wp_error( $term ) )
		    	{
		    		continue;
		    	}

		    	$terms[] = $term;
		    }

		    return $terms;
		}

		/* ---------------------------------------------------------------------------------------------------------- */

		/**
		 * Get Error Messages
		 *
		 * Gets error messages from an array
		 *
		 * @return Array The translate array
		 */

		static public function get_error_messages( $errors, $args = array() )
		{
			$options = array_merge( array
			(
				'no_duplicates' => true,
				'only_errors'   => true

			), (array) $args );

			$translations = array();

			if ( is_array( $errors ) )
			{
				foreach ( $errors as $key => $value )
				{
					if ( is_wp_error( $value ) )
					{
						$translation = $value->get_error_message();
					}

					else if ( $options['only_errors'] == false )
					{
						$translation = $value;
					}

					if ( $options['no_duplicates'] && in_array( $translation, $translations ) )
					{
						continue;
					}

					if ( ! isset( $translation ) )
					{
						continue;
					}

					$translations[ $key ] = $translation;
				}
			}

			return $translations;
		}

		/* ---------------------------------------------------------------------------------------------------------- */

		/**
		 * Get Post Vars
		 *
		 * Returns post vars
		 *
		 * @return Array
		 */

		static public function get_post_vars()
		{
			return get_object_vars( new WP_Post() );
		}

		/* ---------------------------------------------------------------------------------------------------------- */
		
		/**
		 * Filter Post Data
		 *
		 * removes non post data from an associative array or object and returns the filtered results.
		 *
		 * @return Array
		 */

		static public function filter_post_vars( $data )
		{
			return array_intersect_key( get_object_vars( $data ), self::get_post_vars() );
		}

		/* ---------------------------------------------------------------------------------------------------------- */
		
		/**
		 * Filter Post Meta Data
		 *
		 * removes post data from an associative array and returns the filtered results.
		 *
		 * @return Array
		 */

		static public function filter_post_meta_vars( $data )
		{
			return array_diff_key( get_object_vars( $data ), self::get_post_vars() );
		}

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