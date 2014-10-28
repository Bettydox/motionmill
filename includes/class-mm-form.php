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
		
		static public function get_post( $key, $default = '' )
		{
			if ( isset($_POST[$key]) ) 
			{
				return $_POST[$key];
			}

			return $default;
		}

		static public function get_value( $key, $default = '' )
		{
			return esc_html( self::get_post( $key, $default ) );
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

		static public function get_post_dropdown_options( $post_type = 'post', $parent = '' )
		{
			$posts = get_posts(array
			(
				'posts_per_page'   => -1,
				'offset'           => 0,
				'orderby'          => 'title',
				'order'            => 'ASC',
				'post_type'        => $post_type,
				'post_parent'      => $parent,
				'post_status'      => 'publish'
			));

			$options = array();

			foreach ( $posts as $post )
			{
				$depth = count( get_post_ancestors( $post->ID ) );

				$prefix = '';

				if ( $depth > 0 )
				{
					$prefix = str_repeat( '—', $depth ) . ' ';
				}

				$options[ $post->ID ] = $prefix . $post->post_title;

				foreach ( self::get_post_dropdown_options( $post_type, $post->ID ) as $key => $value )
				{
					$options[ $key ] = $value;
				}
			}

			return $options;
		}

	}
}

?>