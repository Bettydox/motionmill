<?php

function mm_mail_templates_templates($templates)
{
	$templates['registration_confirmation'] = array
	(
		'title'       => __('Registration confirmation'),
		'description' => __(''),
		'supports'    => array( 'from', 'subject', 'headers', 'attachments', 'message', 'html' ),
		'tags'        => apply_filters('motionmill_mail_templates_registration_confirmation_tags', array
		(
			'blog_name'      => array( 'title' => 'Blog Name', 'description' => '' ),
			'user_login'     => array( 'title' => 'User Login', 'description' => '' ),
			'user_email'     => array( 'title' => 'User Email', 'description' => '' ),
			'user_firstname' => array( 'title' => 'User Firstname', 'description' => '' ),
			'user_lastname'  => array( 'title' => 'User Lastname', 'description' => '' ),
			'password'       => array( 'title' => 'User Plaintext Password', 'description' => '' ),
			'login_url'      => array( 'title' => 'Login URL', 'description' => '' )
		)),
		'defaults' => array
		(
			'subject' => '[blog_name] Your username and password',
			'message' => "Username: [user_login]\nPassword: [password]\n[login_url]"
		)
	);

	$templates['registration_notification'] = array
	(
		'title'       => __('Registration notification'),
		'description' => __(''),
		'supports'    => array( 'from', 'to', 'subject', 'headers', 'attachments', 'message', 'html' ),
		'tags'        => apply_filters('motionmill_mail_templates_registration_notification_vars', array
		(
			'blog_name'      => array( 'title' => 'Blog Name', 'description' => '' ),
			'user_login'     => array( 'title' => 'User Login', 'description' => '' ),
			'user_email'     => array( 'title' => 'User Email', 'description' => '' ),
			'user_firstname' => array( 'title' => 'User Firstname', 'description' => '' ),
			'user_lastname'  => array( 'title' => 'User Lastname', 'description' => '' ),
			'password'       => array( 'title' => 'User Plaintext Password', 'description' => '' )
		)),
		'defaults' => array
		(
			'to'      => get_option('admin_email'),
			'subject' => '[blog_name] New User Registration',
			'message' => "New user registration on your site [blog_name]:\nUsername: [user_login]\nE-mail: [user_email]"
		)
	);

	$templates['change_password_notification'] = array
	(
		'title'       => __('Change password notification'),
		'description' => __(''),
		'supports'    => array( 'from', 'to', 'subject', 'headers', 'attachments', 'message', 'html' ),
		'tags'        => apply_filters('motionmill_mail_templates_change_password_notification_tags', array
		(
			'blog_name'      => array( 'title' => 'Blog Name', 'description' => '' ),
			'user_login'     => array( 'title' => 'User Login', 'description' => '' ),
			'user_email'     => array( 'title' => 'User Email', 'description' => '' ),
			'user_firstname' => array( 'title' => 'User Firstname', 'description' => '' ),
			'user_lastname'  => array( 'title' => 'User Lastname', 'description' => '' ),
			'password'       => array( 'title' => 'User Plaintext Password', 'description' => '' )
		)),

		'defaults' => array
		(
			'to'      => get_option('admin_email'),
			'subject' => '[blog_name] Password Lost/Changed',
			'message' => 'Password Lost and Changed for user: [user_login]'
		)
	);

	return $templates;
}

add_filter('motionmill_mail_templates_templates', 'mm_mail_templates_templates', 1, 5 );

if ( ! function_exists('wp_new_user_notification') )
{
	function wp_new_user_notification($user_id, $plaintext_pass = '')
	{
		$mm =&mm_get_instance();


		$user = get_userdata( $user_id );

		$vars = apply_filters('motionmill_mail_templates_registration_notification_vars', array
		(
			'blog_name'      => wp_specialchars_decode(get_option('blogname'), ENT_QUOTES),
			'user_login'     => $user->user_login,
			'user_email'     => $user->user_email,
			'user_firstname' => $user->user_firstname,
			'user_lastname'  => $user->user_lastname
		));

		$mm->get_plugin('MM_Mail_Templates')->mail_template('registration_notification', null, $vars);

		if ( empty($plaintext_pass) )
			return;

		$vars = apply_filters('motionmill_mail_templates_registration_confirmation_vars', array
		(
			'blog_name'      => wp_specialchars_decode(get_option('blogname'), ENT_QUOTES),
			'user_login'     => $user->user_login,
			'user_email'     => $user->user_email,
			'user_firstname' => $user->user_firstname,
			'user_lastname'  => $user->user_lastname,
			'password'       => $plaintext_pass,
			'login_url'      => wp_login_url()
		));

		$mm->get_plugin('MM_Mail_Templates')->mail_template('registration_confirmation', $user->user_email, $vars);
	}
}

if ( ! function_exists('wp_password_change_notification') )
{
	function wp_password_change_notification(&$user)
	{
		$mm =&mm_get_instance();

		$vars = apply_filters('motionmill_mail_templates_change_password_notification_vars', array
		(
			'blog_name'      => wp_specialchars_decode(get_option('blogname'), ENT_QUOTES),
			'user_login'     => $user->user_login,
			'user_email'     => $user->user_email,
			'user_firstname' => $user->user_firstname,
			'user_lastname'  => $user->user_lastname
		));

		$mm->get_plugin('MM_Mail_Templates')->mail_template('change_password_notification', null, $vars);
	}
}

?>