<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class MM_Mail_Change_Password_Notification
{	
	protected $wp_password_change_notification = false;

	public function __construct()
	{
		$this->wp_password_change_notification = function_exists( 'wp_password_change_notification' );

		add_filter( 'motionmill_mail_templates', array( &$this, 'on_mail_templates' ) );
		add_filter( 'motionmill_status_messages', array( &$this, 'on_status_messages' ) );
	}

	public function on_status_messages( $messages )
	{
		if ( $this->wp_password_change_notification )
		{
			$messages[] = array
			(
				'id'     => 'wp_password_change_notification_function',
				'text'   => __( 'wp_password_change_notification function already exists.', Motionmill::TEXTDOMAIN ),
				'type'   => 'warning',
				'author' => 'Motionmill Mail'
			);
		}

		else
		{
			$messages[] = array
			(
				'id'     => 'motionmill_mail_wp_new_user_notification_function',
				'text'   => __( 'wp_password_change_notification function does not exist.', Motionmill::TEXTDOMAIN ),
				'type'   => 'success',
				'author' => 'Motionmill Mail'
			);
		}
		
		return $messages;
	}

	public function on_mail_templates( $templates )
	{
		$templates[] = array
		(
			'id'           => 'change_password_notification',
			'title'        => __( 'Change password notification', Motionmill::TEXTDOMAIN ),
			'description'  => __( 'This mail is sent when a user resets a lost password.', Motionmill::TEXTDOMAIN ),
			'to'           => '${blog:admin_email}',
			'subject'      => '[${blog:name}] Password Lost/Changed',
			'message'      => 'Password Lost and Changed for user: ${user:user_login}',
			'headers'      => '',
			'attachments'  => '',
			'enable'       => false,
			'tag_cats'     => array( 'blog', 'general', 'user' ) 
		);

		return $templates;
	}
}

$mm_mail_change_password_notification = new MM_Mail_Change_Password_Notification();

if ( ! function_exists( 'wp_password_change_notification' ) )
{
	function wp_password_change_notification( &$user )
	{
		MM('Mail')->set_tag_category_var( 'user', array( 'user_id' => $user->ID ) );

		MM('Mail')->mail_template( 'change_password_notification' );
	}
}

?>
