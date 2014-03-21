<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! function_exists('mm_parse_template') )
{
	function mm_parse_template($template, $vars = array(), $options = array())
	{
		$options = array_merge(array
		(
			'tag_l' => '[',
			'tag_r' => ']',
			'html'  => false
		), (array) $options);

		$offset = 0;

		while ( ( $start = strpos($template, $options['tag_l'], $offset) ) !== false && ( $end = strpos($template, $options['tag_r'], $offset + strlen($options['tag_l']) ) ) !== false )
		{				
			$tag  	  = substr( $template , $start, $end + strlen($options['tag_r']) - $start );
			$tag_name = substr( $tag , strlen($options['tag_l']), - strlen($options['tag_r']) );

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

			$template = substr_replace( $template, $replacement, $start, strlen($tag) );

			if ( $offset < strlen($template) )
			{
				$offset++;
			}
		}

		return $template;
	}
}

if ( ! function_exists('mm_load_template') )
{
	function mm_load_template( $file, $vars = array(), $return = false )
	{
		extract($vars);

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

?>