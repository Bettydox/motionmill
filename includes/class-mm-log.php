<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists('MM_log') )
{
	class MM_log
	{
		protected $file = null;
		protected $author = null;

		public function __construct()
		{	
			$traces = debug_backtrace();
			$this->author = $traces[1];

			$this->file = ini_get( 'error_log' );
		}

		public function add( $value )
		{
			$args = func_get_args();

			if ( count( $args ) > 0 )
			{
				$value = call_user_func_array( 'sprintf' , $args );
			}

			$traces = debug_backtrace();

			$author = $traces[0];

			$message = array
			(
				'author'   => $this->author['class'],
				'content'  => $value
			);

			return $this->save( $message );
		}

		public function save( $message )
		{
			$str = '[motionmill] ';

			foreach ( $message as $key => $value )
			{
				if ( $key == 'content' )
				{
					continue;
				}

				$str .= sprintf( '[%s:%s] ', $key, $value );
			}

			$str .= $message['content'];

			error_log( $str );
		}

		public function get_messages()
		{
			$messages = array();

			$handle = @fopen( $this->file , 'r' );

			if ( $handle )
			{
				// make sure there are no errors from here. otherwise infinite loop.

				while ( ( $line = @fgets( $handle, 4096 ) ) !== false )
				{
					// [20-Jul-2014 18:21:54 UTC] [motionmill] [author:MM_log] [file:wp-content/plugins/motionmill/motionmill.php] Hello World!
					@preg_match( '/^.*?\[motionmill\]\[author:(.*)\]\s*(.*)\s*/', $line, $matches );

					// 20-Jul-2014 17:31:56 UTC
					if ( ! is_array( $matches ) || empty($matches) )
					{
						continue;
					}

					@list( $raw, $time, $category, $author, $file, $message ) = $matches;

					$messages[] = array
					(
						'author'   => $author,
						'file'     => $file,
						'content'  => $message
					);
				}

				@fclose( $handle );
			}

			return $messages;
		}
	}
}

?>