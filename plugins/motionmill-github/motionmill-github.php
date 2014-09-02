<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill GitHub
 Plugin URI: https://github.com/addwittz/motionmill/tree/master/plugins/motionmill-github
 Description: Fetches data from GitHub
 Version: 1.0.3
 Author: Maarten Menten
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

if ( ! class_exists( 'MM_GitHub' ) )
{
	class MM_GitHub
	{
		protected $options    = array();
		protected $http_codes = array();

		public function __construct()
		{	
			add_action( 'motionmill_init', array( &$this, 'initialize' ) );
		}
		
		public function initialize()
		{
			$this->options = apply_filters( 'motionmill_github_options', array
			(
				'account'       => '',
				'client_login'  => '',
				'client_secret' => '',
				'auth_type'     => 'url_token', // http_password | http_token | url_token
				'http_port'     => 0,
				'user_agent'    => $_SERVER[ 'HTTP_USER_AGENT' ],
				'timeout'       => 0,
				'base_url'      => 'https://api.github.com'
			));

			$this->http_codes = array
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
			    510 => __( 'Not Extended', Motionmill::TEXTDOMAIN )
			);
		}

		public function get_tags( $repo )
		{
			$data = $this->do_request( sprintf( 'repos/%s/%s/git/refs/tags', $this->options['account'], $repo ) );

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

		public function get_versions( $repo )
		{
			$tags = $this->get_tags( $repo );

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

		// https://github.com/ornicar/php-github-api/blob/master/lib/Github/HttpClient/Curl.php
		public function do_request( $url, $parameters = array(), $http_method = 'GET', $options = array() )
		{
			$options = array_merge( $this->options, $options );
			
			$url = trailingslashit( $options['base_url'] ) . ltrim( $url, '/' );

			$curl_options = array();

			if ( $options['client_login'] )
			{
				switch ( $options['auth_type'] )
				{
					case 'http_password' :
						
						$curl_options[ CURLOPT_USERPWD ] = $options['client_login'] . ':' . $options['client_secret'];

						break;

					case 'http_token' :
						
						$curl_options[ CURLOPT_USERPWD ] =  sprintf( '%s:%s', $options['client_login'], $options['client_secret'] );

						break;
					
					case 'url_token' : default:

						$parameters = array_merge(array
						(
							'login' => $options['client_login'],
							'token' => $options['client_secret']
						), $parameters);
				}
			}

			if ( ! empty( $parameters ) )
			{
				$query_string = utf8_encode( http_build_query( $parameters, '', '&') );

				if ( $http_method == 'GET' )
				{
					$url .= '?' . $query_string;
				}
				else
				{
					$curl_options[ CURLOPT_POST ] = true;
					$curl_options[ CURLOPT_POSTFIELDS ] = $query_string;
				}
        	}

			$curl_options[ CURLOPT_URL ]            = $url;
			$curl_options[ CURLOPT_PORT ]           = $options['http_port'];
			$curl_options[ CURLOPT_USERAGENT ]      = $options['user_agent'];
			$curl_options[ CURLOPT_FOLLOWLOCATION ] = true;
			$curl_options[ CURLOPT_RETURNTRANSFER ] = true;
			$curl_options[ CURLOPT_SSL_VERIFYPEER ] = false;
			$curl_options[ CURLOPT_TIMEOUT ]        = $options['timeout'];
       		
			$response = $this->do_curl( $curl_options );
			
			$http_code = $response['headers']['http_code'];

			if ( ! in_array( $http_code, array( 0, 200, 201 ) ) )
			{
				if ( isset( $this->http_codes[ $http_code ] ) )
				{
					$message = $this->http_codes[ $http_code ];
				}

				else
				{
					$message = __( 'An unknown HTTP error occured.', Motionmill::TEXTDOMAIN );
				}

				return new WP_Error( 'github_api', sprintf( '%s - %s', $http_code, $message ) );
	        }

	        if ( $response['error_number'] != '' )
	        {
	        	return new WP_Error( 'github_api', sprintf( '%s - %s', $response['error_number'], $response['error_message'] ) );
	        }

	        return json_decode( $response['response'] );
		}

		protected function do_curl( $options )
		{
			$ch = curl_init();

			curl_setopt_array( $ch, $options );

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

// registers plugin
if ( ! function_exists('motionmill_plugins_add_github') )
{
	function motionmill_plugins_add_github( $plugins )
	{
		$plugins[] = 'MM_GitHub';

		return $plugins;
	}

	add_filter( 'motionmill_plugins', 'motionmill_plugins_add_github' );
}

?>
