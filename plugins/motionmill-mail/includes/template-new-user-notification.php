<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class MM_Mail_New_User_Notification
{	
	protected $wp_new_user_notification_exists = false;

	public function __construct()
	{
		$this->wp_new_user_notification_exists = function_exists( 'wp_new_user_notification' );

		add_filter( 'motionmill_mail_templates', array( &$this, 'on_mail_templates' ) );
		add_filter( 'motionmill_mail_tag_categories', array( &$this, 'on_mail_tag_categories' ) );
		add_filter( 'motionmill_mail_tags', array( &$this, 'on_mail_tags' ) );
		add_filter( 'motionmill_status_messages', array( &$this, 'on_status_messages' ) );
	}

	public function on_status_messages( $messages )
	{
		if ( $this->wp_new_user_notification_exists )
		{
			$messages[] = array
			(
				'id'     => 'motionmill_mail_wp_new_user_notification_function',
				'text'   => __( "wp_new_user_notification function already exists.", Motionmill::TEXTDOMAIN ),
				'type'   => 'warning',
				'author' => 'Motionmill Mail'
			);
		}

		else
		{
			$messages[] = array
			(
				'id'     => 'motionmill_mail_wp_new_user_notification_function',
				'text'   => __( "wp_new_user_notification function does not exist.", Motionmill::TEXTDOMAIN ),
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
			'id'           => 'user_registration',
			'title'        => __( 'User registration', Motionmill::TEXTDOMAIN ),
			'description'  => __( 'This email will be sent when a user registers. Most commenly used for sending login credentials.', Motionmill::TEXTDOMAIN ),
			'to'           => null,
			'subject'      => '[${blog:name}] Your username and password',
			'message'      => 'Username: ${user:user_login}' . "\r\n"
							. 'Password: ${user_registration:plaintextpass}' . "\r\n"
							. '${general:wp_login_url}' . "\r\n",
			'headers'      => '',
			'attachments'  => '',
			'enable'       => false,
			'tag_cats'     => array( 'blog', 'general', 'user', 'user_registration' ) 
		);

		$templates[] = array
		(
			'id'           => 'user_registration_notify',
			'title'        => __( 'User registration notification', Motionmill::TEXTDOMAIN ),
			'description'  => __( 'This email will be sent when a user registers.' ),
			'to'           => '${blog:admin_email}',
			'subject'      => '[${blog:name}] New user registration on your site',
			'message'      => 'Username: ${user:user_login}' . "\r\n\r\n" . 'E-mail: ${user:user_email}',
			'headers'      => '',
			'attachments'  => '',
			'enable'       => false,
			'tag_cats'     => array( 'blog', 'general', 'user' ) 
		);

		return $templates;
	}

	public function on_mail_tag_categories( $categories )
	{
		$categories[] = array
		(
			'id'          => 'user_registration',
			'title'       => __( 'User registration', Motionmill::TEXTDOMAIN ),
			'description' => __( '', Motionmill::TEXTDOMAIN )
		);

		return $categories;
	}

	public function on_mail_tags( $tags )
	{
		$tags[] = array
		(
			'name'        => 'plaintextpass',
			'title'       => __( 'Plain Text Pass', Motionmill::TEXTDOMAIN ),
			'description' => __( 'The unencrypted password', Motionmill::TEXTDOMAIN ),
			'category'    => 'user_registration'
		);

		return $tags;
	}
}

$mm_mail_new_user_notification = new MM_Mail_New_User_Notification();

if ( ! function_exists( 'wp_new_user_notification' ) )
{
	function wp_new_user_notification( $user_id, $plaintext_pass = '' )
	{
		$user = new WP_User( $user_id );

		MM('Mail')->set_tag_category_var( 'user', array( 'user_id' => $user_id ) );

		MM('Mail')->mail_template( 'user_registration_notify' );

		if ( empty( $plaintext_pass ) )
		{
			return;
		}

		MM('Mail')->set_tag_values( 'user_registration', array
		(
			'plaintextpass' => $plaintext_pass
		));

		MM('Mail')->mail_template( 'user_registration', array( 'to' => $user->user_email ) );
	}
}

?>
