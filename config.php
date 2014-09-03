<?php if ( ! defined('ABSPATH') ) exit; // Exits when accessed directly

function motionmill_settings_options( $options )
{
	return array_merge( $options, array
	(
		'page_capability'    => 'manage_options',
		'page_parent_slug'   => 'motionmill',
		'page_priority'      => 10,
		'page_submit_button' => true,
		'page_admin_bar'     => true,
		'field_rules'        => array( 'trim' ),
		'field_type'         => 'textfield'
	));
}

add_filter( 'motionmill_settings_options', 'motionmill_settings_options', 5 );

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