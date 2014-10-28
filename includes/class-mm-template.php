<?php if ( ! defined('ABSPATH') ) exit; // Exits when accessed directly

if ( ! class_exists('MM_Template') )
{
	class MM_Template
	{
		static public function parse_tags( $template, $vars = null, $callback = null, $args = array() )
		{
			extract( array_merge( array
			(
				'tag_left'      => '${',
				'tag_right'     => '}',
				'tag_meta_sep'  => ':',
				'tag_meta_vars' => array( 'category' => '', 'name' => '' )
			), $args) );

			$offset = 0;

			while ( ( $start = stripos( $template, $tag_left, $offset ) ) !== false && ( $end = stripos( $template, $tag_right, $offset + 1 ) ) !== false  )
			{	
				// tag
				$length = $end - $start + strlen( $tag_right );

				$tag       = substr( $template, $start, $length );
				$tag_inner = substr( $tag, strlen( $tag_left ), - strlen( $tag_right ) );

				// tag meta
				$tag_meta = array();

				if ( is_array( $tag_meta_vars ) && $tag_meta_sep )
				{
					if ( stripos( $tag_inner, $tag_meta_sep ) )
					{
						$meta = explode( $tag_meta_sep, $tag_inner );
					}

					else
					{
						$meta = array( $tag_inner );
					}

					$i = 0;

					foreach ( $tag_meta_vars as $key => $default )
					{
						$value = isset( $meta[$i] ) ? $meta[$i] : $default;

						$tag_meta[ $key ] = $value;

						$i++;
					}
				}

				// replacement
				if ( is_array( $vars ) && isset( $vars[ $tag_inner ] ) )
				{
					$replacement = $vars[ $tag_inner ];
				}

				else
				{
					$replacement = $tag;
				}

				if ( is_callable( $callback ) )
				{
					$replacement = call_user_func( $callback, $replacement, $tag, $tag_inner, $tag_meta );
				}

				//error_log( sprintf( '%s() - $offset: %s, $start: %s, $end: %s, $length: %s, $tag: %s, $replacement: %s', __FUNCTION__, $offset, $start, $end, $length, $tag, $replacement ) );

				$template = substr_replace( $template, $replacement, $start, $length );

				$offset = $start + strlen( $replacement );
			}

			return $template;
		}

		static public function load( $file, $vars = null, $return = false )
		{
			if ( is_array($vars) )
			{
				extract($vars);
			}

			if ( $return )
			{
				ob_start();
			}

			include( $file );

			if ( $return )
		    {
		        return @ob_get_clean();
		    }
		}
	}
}

?>