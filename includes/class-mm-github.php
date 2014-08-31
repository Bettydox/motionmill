<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists('MM_GitHub') )
{
	class MM_GitHub
	{
		static public function plugin_to_repo( $file )
		{
			if ( stripos( $file, '.php' ) === false )
			{
				return $file;
			}

			return dirname( trim( $file, '/' ) );
		}

		static public function get_tags( $repo )
		{
			$data = self::do_request( $repo . '/git/refs/tags' );

			if ( is_wp_error( $data ) )
			{
				return $data;
			}

			$tags = array();

			foreach ( $data as $tag )
			{	
				$tags[] = basename( $tag->url );
			}

			return $tags;
		}

		static public function get_versions( $repo )
		{
			$tags = self::get_tags( $repo );

			if ( is_wp_error( $tags ) )
			{
				return $tags;
			}

			$versions = array();

			foreach ( $tags as $tag )
			{	
				if ( stripos( $tag, 'v') !== 0 )
				{
					return;
				}

				$versions[] = substr( $tag, 1 );
			}

			usort( $versions , function( $a, $b )
			{
				return version_compare( $a, $b );
			});

			return $versions;
		}

		static public function do_request( $extra )
		{
			$url = sprintf( 'https://api.github.com/repos/addwittz/%s', ltrim( $extra, '/' ) );

			$response = self::do_curl( $url, array
			(
				'username' => 'mmaarten',
				'password' => 'e280054b8a4afd585b21f43774b34aa4fb3a0c28'
			));

			$http_codes = array
	        (
			    100 => __( 'Continue', Motionmill::TEXTDOMAIN ),
			    101 => __( 'Switching Protocols', Motionmill::TEXTDOMAIN ),
			    102 => __( 'Processing', Motionmill::TEXTDOMAIN ),
			    200 => __( 'OK', Motionmill::TEXTDOMAIN ),
			    201 => __( 'Created', Motionmill::TEXTDOMAIN ),
			    202 => __( 'Accepted', Motionmill::TEXTDOMAIN ),
			    203 => __( 'Non-Authoritative Information', Motionmill::TEXTDOMAIN ),
			    204 => __( 'No Content', Motionmill::TEXTDOMAIN ),
			    205 => __( 'Reset Content', Motionmill::TEXTDOMAIN ),
			    206 => __( 'Partial Content', Motionmill::TEXTDOMAIN ),
			    207 => __( 'Multi-Status', Motionmill::TEXTDOMAIN ),
			    300 => __( 'Multiple Choices', Motionmill::TEXTDOMAIN ),
			    301 => __( 'Moved Permanently', Motionmill::TEXTDOMAIN ),
			    302 => __( 'Found', Motionmill::TEXTDOMAIN ),
			    303 => __( 'See Other', Motionmill::TEXTDOMAIN ),
			    304 => __( 'Not Modified', Motionmill::TEXTDOMAIN ),
			    305 => __( 'Use Proxy', Motionmill::TEXTDOMAIN ),
			    306 => __( 'Switch Proxy', Motionmill::TEXTDOMAIN ),
			    307 => __( 'Temporary Redirect', Motionmill::TEXTDOMAIN ),
			    400 => __( 'Bad Request', Motionmill::TEXTDOMAIN ),
			    401 => __( 'Unauthorized', Motionmill::TEXTDOMAIN ),
			    402 => __( 'Payment Required', Motionmill::TEXTDOMAIN ),
			    403 => __( 'Forbidden', Motionmill::TEXTDOMAIN ),
			    404 => __( 'Not Found', Motionmill::TEXTDOMAIN ),
			    405 => __( 'Method Not Allowed', Motionmill::TEXTDOMAIN ),
			    406 => __( 'Not Acceptable', Motionmill::TEXTDOMAIN ),
			    407 => __( 'Proxy Authentication Required', Motionmill::TEXTDOMAIN ),
			    408 => __( 'Request Timeout', Motionmill::TEXTDOMAIN ),
			    409 => __( 'Conflict', Motionmill::TEXTDOMAIN ),
			    410 => __( 'Gone', Motionmill::TEXTDOMAIN ),
			    411 => __( 'Length Required', Motionmill::TEXTDOMAIN ),
			    412 => __( 'Precondition Failed', Motionmill::TEXTDOMAIN ),
			    413 => __( 'Request Entity Too Large', Motionmill::TEXTDOMAIN ),
			    414 => __( 'Request-URI Too Long', Motionmill::TEXTDOMAIN ),
			    415 => __( 'Unsupported Media Type', Motionmill::TEXTDOMAIN ),
			    416 => __( 'Requested Range Not Satisfiable', Motionmill::TEXTDOMAIN ),
			    417 => __( 'Expectation Failed', Motionmill::TEXTDOMAIN ),
			    418 => __( 'I\'m a teapot', Motionmill::TEXTDOMAIN ),
			    422 => __( 'Unprocessable Entity', Motionmill::TEXTDOMAIN ),
			    423 => __( 'Locked', Motionmill::TEXTDOMAIN ),
			    424 => __( 'Failed Dependency', Motionmill::TEXTDOMAIN ),
			    425 => __( 'Unordered Collection', Motionmill::TEXTDOMAIN ),
			    426 => __( 'Upgrade Required', Motionmill::TEXTDOMAIN ),
			    449 => __( 'Retry With', Motionmill::TEXTDOMAIN ),
			    450 => __( 'Blocked by Windows Parental Controls', Motionmill::TEXTDOMAIN ),
			    500 => __( 'Internal Server Error', Motionmill::TEXTDOMAIN ),
			    501 => __( 'Not Implemented', Motionmill::TEXTDOMAIN ),
			    502 => __( 'Bad Gateway', Motionmill::TEXTDOMAIN ),
			    503 => __( 'Service Unavailable', Motionmill::TEXTDOMAIN ),
			    504 => __( 'Gateway Timeout', Motionmill::TEXTDOMAIN ),
			    505 => __( 'HTTP Version Not Supported', Motionmill::TEXTDOMAIN ),
			    506 => __( 'Variant Also Negotiates', Motionmill::TEXTDOMAIN ),
			    507 => __( 'Insufficient Storage', Motionmill::TEXTDOMAIN ),
			    509 => __( 'Bandwidth Limit Exceeded', Motionmill::TEXTDOMAIN ),
			    510 => __( 'Not Extended', Motionmill::TEXTDOMAIN ),
	        );
			
			$http_code = $response['headers']['http_code'];

			if ( ! in_array( $http_code, array( 0, 200, 201 ) ) )
			{
				if ( isset( $http_codes[ $http_code ] ) )
				{
					$message = $http_codes[ $http_code ];
				}

				else
				{
					$message = __( 'An unknown error occured.', Motionmill::TEXTDOMAIN );
				}

				return new WP_Error( 'github_api', sprintf( '%s - %s', $http_code, $message ) );
	        }

	        if ( $response['errorNumber'] != '' )
	        {
	        	return new WP_Error( 'github_api', $response['errorNumber'] );
	        }

	        return $response;
		}

		static public function do_curl( $url, $options = array() )
		{
			$options = array_merge( array
			(
				'username'  => '',
				'password'  => '',
				'useragent' => $_SERVER['HTTP_USER_AGENT'],
				'port'      => 0
			), (array) $options );

			$ch = curl_init();

			error_log( $url );

			curl_setopt_array( $ch, array
			(
				CURLOPT_URL            => $url,
				CURLOPT_USERPWD        => sprintf( '%s/token:%s', $options['username'], $options['password'] ),
				CURLOPT_USERAGENT      => $options['useragent'],
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_FOLLOWLOCATION => 1,
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_TIMEOUT        => 0,
				CURLOPT_PORT           => $options['port']
			));

			$response = array
			(
				'response'      => curl_exec( $ch ),
				'headers'       => curl_getinfo( $ch ),
				'error_number'  => curl_errno( $ch ),
				'error_message' => curl_error( $ch ),
			);

			curl_close( $ch );

			return $response;
		}
	}
}

?>