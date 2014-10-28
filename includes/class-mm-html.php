<?php if ( ! defined('ABSPATH') ) exit; // Exits when accessed directly

if ( ! class_exists('MM_HTML') )
{
	class MM_HTML
	{
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