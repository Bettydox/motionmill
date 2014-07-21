<?php

function motionmill_mail_templates_add_templates($templates)
{
	$motionmill = Motionmill::get_instance();
	
	$templates['user_notification'] = array
	(
		'title'       => __( 'User notification', $motionmill::TEXT_DOMAIN ),
		'description' => __( 'Notify the blog admin of a new user.', $motionmill::TEXT_DOMAIN ),
		'fields'      => array
		(
			'from' 	      => null,
			'to' 		  => get_option('admin_email'),
			'subject' 	  => '[blog:name] New User Registration',
			'message' 	  => "New user registration on your site [blog:name]:\r\n\r\nUsername: [user:user_login]\r\nE-mail: [user:user_email]\r\n",
			'headers' 	  => '',
			'attachments' => '',
			'html'        => false
		)
	);

	$templates['user_confirmation'] = array
	(
		'title'       => __( 'User Confirmation', $motionmill::TEXT_DOMAIN ),
		'description' => __( 'Send an email with login/password to the new user.', $motionmill::TEXT_DOMAIN ),
		'fields'      => array
		(
			'from' 	      => null,
			'to' 		  => '[user:user_email]',
			'subject' 	  => '[blog:name] Your username and password',
			'message' 	  => "Username: [user:user_login]\r\nPassword: [password]\r\n[login_url]\r\n",
			'headers' 	  => '',
			'attachments' => '',
			'html'        => false
		)
	);

	$templates['change_password_notification'] = array
	(
		'title'       => __( 'Change Password Notification', $motionmill::TEXT_DOMAIN ),
		'description' => __( 'Send a copy of password change notification to the admin.', $motionmill::TEXT_DOMAIN ),
		'fields'      => array
		(
			'from' 	      => null,
			'to' 		  => get_option('admin_email'),
			'subject' 	  => '[blog:name] Password Lost/Changed',
			'message' 	  => 'Password lost and changed for user: [user:user_login]',
			'headers' 	  => '',
			'attachments' => '',
			'html'        => false
		)
	);

	$templates['retrieve_password'] = array
	(
		'title'       => __( 'Retrieve Password', $motionmill::TEXT_DOMAIN ),
		'description' => __( 'This mail will be send to the user who wants to reset his/her password.', $motionmill::TEXT_DOMAIN ),
		'fields'      => array
		(
			'from' 	      => null,
			'to' 		  => null,
			'subject' 	  => '[blog:name] Password Reset',
			'message' 	  => "Someone requested that the password be reset for the following account: \r\n\r\n[network_home_url]\r\n\r\nUsername: [user:user_login]\r\n\r\nIf this was a mistake, just ignore this email and nothing will happen.\r\n\r\nTo reset your password, visit the following address:\r\n[recover_link]",
			'headers' 	  => null,
			'attachments' => null,
			'html'        => null
		)
	);

	return $templates;
}

add_filter( 'motionmill_mail_templates', 'motionmill_mail_templates_add_templates' );

if ( ! function_exists('wp_new_user_notification') )
{
	function wp_new_user_notification( $user_id, $plaintext_pass = '' )
	{
		$user = new WP_User( $user_id );
		$motionmill =& Motionmill::get_instance();

		$motionmill->get_plugin('MM_Mail')->mail_template('user_notification', array
		(
			'user_id' => $user->ID
		));
		
		if ( empty( $plaintext_pass ) )
			return;

		return $motionmill->get_plugin('MM_Mail')->mail_template('user_confirmation', array
		(
			'user_id'  => $user->ID,
			'password' => $plaintext_pass
		));
	}
}

if ( ! function_exists('wp_new_user_notification') )
{
	function wp_password_change_notification(&$user)
	{
		$motionmill =& Motionmill::get_instance();

		$options = $motionmill->get_plugin('MM_Settings')->get_option('motionmill_mail');

		if ( strcasecmp( $user->user_email, $options['change_password_notification_to'] ) !== 0 )
			return false;

		return $motionmill->get_plugin('MM_Mail')->mail_template('change_password_notification', array
		(
			'user_id' => $user->ID
		));
	}
}

function motionmill_mail_retrieve_password_title($default)
{
	if ( strpos( $_POST['user_login'], '@' ) )
	{
		$user = get_user_by( 'email', trim( $_POST['user_login'] ) );
	}
	else
	{
		$user = get_user_by('login', trim( $_POST['user_login'] ) );
	}

	$motionmill =& Motionmill::get_instance();

	$options = $motionmill->get_plugin('MM_Settings')->get_option('motionmill_mail');

	if ( ! empty($options['retrieve_password_subject']) )
	{
		return $motionmill->get_plugin('MM_Mail')->parse_template( $options['retrieve_password_subject'], array
		(
			'user_id' => $user->ID
		));
	}

	return $default;
}

add_filter( 'retrieve_password_title', 'motionmill_mail_retrieve_password_title' );

function motionmill_mail_retrieve_password_message($default, $key)
{
	if ( strpos( $_POST['user_login'], '@' ) )
	{
		$user = get_user_by( 'email', trim( $_POST['user_login'] ) );
	}
	else
	{
		$user = get_user_by('login', trim( $_POST['user_login'] ) );
	}

	$motionmill =& Motionmill::get_instance();

	$options = $motionmill->get_plugin('MM_Settings')->get_option('motionmill_mail');

	if ( ! empty($options['retrieve_password_message']) )
	{
		return $motionmill->get_plugin('MM_Mail')->parse_template( $options['retrieve_password_message'], array
		(
			'user_id'      => $user->ID,
			'recover_link' => network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login')
		));
	}

	return $default;
}

add_filter( 'retrieve_password_message', 'motionmill_mail_retrieve_password_message', 10, 2 );

?>