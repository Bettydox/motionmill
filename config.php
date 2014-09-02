<?php if ( ! defined('ABSPATH') ) exit; // Exits when accessed directly

function motionmill_github_options( $options )
{
	return array_merge( $options, array
	(
		'account'       => 'addwittz',
		'client_login'  => 'mmaarten',
		'client_secret' => 'e280054b8a4afd585b21f43774b34aa4fb3a0c28',
		'auth_type'     => 'http_token', // http_password | http_token | url_token
	));
}

add_filter( 'motionmill_github_options', 'motionmill_github_options', 5 );

?>