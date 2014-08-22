<?php if ( ! defined('ABSPATH') ) exit; // Exits when accessed directly

if ( ! class_exists('MM_Template') )
{
	class MM_Template
	{
		static public function parse( $template, $vars = array(), $options = array() )
		{
			$options = array_merge( array
			(
				'tag'  => '[%s]',
				'html' => false

			), (array) $options );

			$tag = explode( '%s', $options['tag'] );

			$offset = 0;

			while ( ( $start = strpos($template, $tag[0], $offset) ) !== false && ( $end = strpos($template, $tag[1], $offset + strlen($tag[0]) ) ) !== false )
			{				
				$tag  	  = substr( $template , $start, $end + strlen( $tag[1] ) - $start );
				$tag_name = substr( $tag , strlen( $tag[0] ), - strlen( $tag[1] ) );

				if ( isset($vars[$tag_name]) )
				{
					$replacement = $vars[ $tag_name ];
				}
				else
				{
					$replacement = $tag;	
				}

				if ( $options['html'] )
				{
					$replacement = esc_html( $replacement );
				}

				$template = substr_replace( $template, $replacement, $start, strlen( $tag ) );

				if ( $offset < strlen( $template ) )
				{
					$offset++;
				}
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