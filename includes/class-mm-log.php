<?php if ( ! defined( 'ABSPATH' ) ) exit; // exits when accessed directly

class MM_Log
{
	public function __construct()
	{
		
	}

	public function add( $message )
	{
		$author = 'unknown';

		$traces = debug_backtrace();

		if ( count( $traces ) > 1 )
		{
			$trace = $traces[1];

			if ( ! empty( $trace['class'] ) && ! empty( $trace['function'] ) )
			{
				$author = sprintf( '%s::%s()', $trace['class'], $trace['function'] );
			}

			elseif ( ! empty( $trace['function'] ) )
			{
				$author = sprintf( '%s()', $trace['function'] );
			}

			elseif ( ! empty( $trace['file'] ) )
			{
				$author = substr( $trace['file'] , 0, strlen( ABSPATH ) );
			}
		}

		error_log( sprintf( '[motionmill] [%s] %s', $author, $message ) );
	}
}

?>