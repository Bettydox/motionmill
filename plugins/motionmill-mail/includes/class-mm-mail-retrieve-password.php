<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class MM_Mail_Retrieve_Password
{	
	public function __construct()
	{
		MM( 'Loader' )->load_class( 'MM_Template' );

		add_filter( 'motionmill_mail_templates', array( &$this, 'on_mail_templates' ) );
		add_filter( 'motionmill_mail_tag_categories', array( &$this, 'on_mail_tag_categories' ) );
		add_filter( 'motionmill_mail_tags', array( &$this, 'on_mail_tags' ) );
		add_filter( 'retrieve_password_title', array( &$this, 'on_retrieve_password_title' ) );
		add_filter( 'retrieve_password_message', array( &$this, 'on_retrieve_password_message' ), 10, 2 );
	}

	public function on_mail_templates( $templates )
	{
		$templates[] = array
		(
			'id'           => 'retrieve_password_message',
			'title'        => __( 'Retrieve Password message', Motionmill::TEXTDOMAIN ),
			'description'  => __( 'This mail is sent to the user that wants to change password.', Motionmill::TEXTDOMAIN ),
			'to'           => null,
			'subject'      => '[${blog:name}] Password Reset',
			'message'      => 'Someone requested that the password be reset for the following account:' . "\r\n\r\n"
								.  '${blog:url}' . "\r\n\r\n" .  'Username: ${user:user_login}' . "\r\n\r\n" 
								.  'If this was a mistake, just ignore this email and nothing will happen.' . "\r\n\r\n" 
								.  'To reset your password, visit the following address:' . "\r\n\r\n" 
								.  '${retrieve_password_message:change_password_url}' . "\r\n\r\n",
			'headers'      => null,
			'attachments'  => null,
			'enable'       => true,
			'tag_cats'     => array( 'blog', 'general', 'user', 'retrieve_password_message' ) 
		);

		return $templates;
	}

	public function on_mail_tag_categories( $categories )
	{
		$categories[] = array
		(
			'id'          => 'retrieve_password_message',
			'title'       => __( 'Retrieve password message', Motionmill::TEXTDOMAIN ),
			'description' => __( '', Motionmill::TEXTDOMAIN )
		);

		return $categories;
	}

	public function on_mail_tags( $tags )
	{
		$tags[] = array
		(
			'name'        => 'change_password_url',
			'title'       => __( 'Retrieve password URL', Motionmill::TEXTDOMAIN ),
			'description' => __( 'The page where the user can reset password', Motionmill::TEXTDOMAIN ),
			'category'    => 'retrieve_password_message'
		);

		return $tags;
	}

	public function on_retrieve_password_title( $subject )
	{
		if ( MM( 'Mail' )->get_template_option( 'retrieve_password_message', 'enable' ) )
		{
			$my_subject = MM( 'Mail' )->get_template_option( 'retrieve_password_message', 'subject' );
		
			return MM_Template::parse_tags( $my_subject, null, array( MM( 'Mail' ), 'parse_tag' ) );
		}

		return $subject;
	}

	public function on_retrieve_password_message( $message, $key )
	{
		if ( MM( 'Mail' )->get_template_option( 'retrieve_password_message', 'enable' ) )
		{
			// gets user from message
			preg_match( '/&login=(.*?)>/', $message, $matches );

			if ( is_array( $matches ) && count( $matches ) > 0 )
			{
				$user_login = $matches[1];
			}

			else
			{
				$user_login = '';
			}

			if ( $user_login )
			{
				$user = get_user_by( 'login', $user_login );
			}

			MM( 'Mail' )->set_tag_category_var( 'user', array( 'user_id' => $user->ID ) );

			MM( 'Mail' )->set_tag_values( 'retrieve_password_message', array
			(
				'change_password_url' => network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login' )
			));

			$message = MM_Template::parse_tags( MM( 'Mail' )->get_template_option( 'retrieve_password_message', 'message' ), null, array( MM( 'Mail' ), 'parse_tag' ) );

			return $message;
		}

		return $message;
	}
}

$mm_mail_retrieve_password = new MM_Mail_Retrieve_Password();

?>
