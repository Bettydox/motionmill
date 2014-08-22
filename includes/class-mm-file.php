<?php if ( ! defined('ABSPATH') ) exit; // Exits when accessed directly

if ( ! class_exists('MM_File') )
{
	class MM_File
	{
		public static function get_content( $file, $offset = 0, $limit = -1, $line_length = 1024 )
		{
			if ( $handle = fopen( $file, 'r' ) )
			{
				fseek( $handle, $offset );

				$contents = '';

				$n = 0;

				while ( ( $line = fgets($handle, $line_length) ) !== false )
				{
					$n++;

					$contents .= $line . "\n";
				
					if ( $n == $limit && $limit != -1 )
					{
						break;
					}
				}

				fseek( $handle, 0 );

				return $contents;
			}

			return new WP_Error( 'cannot_open_file', __( 'Cannot open file.' ), $file );
		}

		public function is_404($url)
		{
			$file_headers = @get_headers( $url );

			return $file_headers[0] == 'HTTP/1.1 404 Not Found';
		}

		public static function get_entries( $path, $dir = true )
		{
			$path = realpath( $path );

			$entries = array();

			if ( is_dir( $path ) )
			{
				$objects = new RecursiveIteratorIterator( new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST );

				foreach ( $objects as $object )
				{
					if ( $dir == false && $object->isDir() )
					{
						continue;
					}

					$entries[] = $object->getRealPath();
				}
			}

			return $entries;
		}
	}
}

?>