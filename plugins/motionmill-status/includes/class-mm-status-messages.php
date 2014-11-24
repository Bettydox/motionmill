<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class MM_Status_Messages
{
	public function __construct()
	{
		add_filter( 'motionmill_status_message_types', array( &$this, 'on_message_types' ), 5 );
		add_filter( 'motionmill_status_messages', array( &$this, 'on_messages' ), 5 );
		add_filter( 'motionmill_status_default_message_type', array( &$this, 'on_default_message_type' ), 5 );
	}

	public function on_default_message_type( $message_type )
	{
		return 'notice';
	}

	public function on_message_types( $types )
	{
		$types[] = array
		(
			'id'          => 'notice',
			'title'       => __( 'Notice', Motionmill::TEXTDOMAIN ),
			'description' => __( '', Motionmill::TEXTDOMAIN ),
			'icon'        => 'info-circle'
		);

		$types[] = array
		(
			'id'          => 'warning',
			'title'       => __( 'Warning', Motionmill::TEXTDOMAIN ),
			'description' => __( '', Motionmill::TEXTDOMAIN ),
			'icon'        => 'warning'
		);

		$types[] = array
		(
			'id'          => 'error',
			'title'       => __( 'Error', Motionmill::TEXTDOMAIN ),
			'description' => __( '', Motionmill::TEXTDOMAIN ),
			'icon'        => 'warning'
		);

		$types[] = array
		(
			'id'          => 'success',
			'title'       => __( 'Success', Motionmill::TEXTDOMAIN ),
			'description' => __( '', Motionmill::TEXTDOMAIN ),
			'icon'        => 'check-circle'
		);

		return $types;
	}

	public function on_messages( $messages )
	{	
		// htaccess
		$htaccess_file = ABSPATH . '.htaccess';

		if ( file_exists( $htaccess_file ) && is_readable( $htaccess_file ) && is_writable( $htaccess_file ) )
		{
			$messages[] = array
			(
				'id'     => 'motionmill_status_htaccess',
				'text'   => __( 'htaccess file exists and is readable and writeable.', Motionmill::TEXTDOMAIN ),
				'type'   => 'success',
				'author' => 'MM_Status'
			);
		}

		else
		{
			$messages[] = array
			(
				'id'     => 'motionmill_status_htaccess',
				'text'   => sprintf( __( 'htaccess file cannot be accessed. does it exist and is it readable and writable?', Motionmill::TEXTDOMAIN ) ),
				'type'   => 'warning',
				'author' => 'MM_Status'
			);
		}

		// uploads dir
		$upload_dir = wp_upload_dir();
		$upload_dir = $upload_dir['basedir'];
		
		if ( file_exists( $upload_dir ) && is_readable( $upload_dir ) && is_writable( $upload_dir ) )
		{
			$messages[] = array
			(
				'id'     => 'motionmill_status_uploads',
				'text'   => __( 'The uploads directory exists and is readable and writeable.', Motionmill::TEXTDOMAIN ),
				'type'   => 'success',
				'author' => 'MM_Status'
			);
		}

		else
		{
			$messages[] = array
			(
				'id'     => 'motionmill_status_uploads',
				'text'   => sprintf( __( 'The uploads directory cannot be accessed. does it exist and is it readable and writable?', Motionmill::TEXTDOMAIN ) ),
				'type'   => 'warning',
				'author' => 'MM_Status'
			);
		}

		// blog accessible for search enqines
		if ( get_option( 'blog_public', 0 ) )
		{
			$messages[] = array
			(
				'id'     => 'motionmill_status_blog_public',
				'text'   => __( 'Blog is accessible for search robots.', Motionmill::TEXTDOMAIN ),
				'type'   => 'success',
				'author' => 'MM_Status'
			);
		}

		else
		{
			$messages[] = array
			(
				'id'     => 'motionmill_status_blog_public',
				'text'   => __( 'Blog is not accessible for search robots.', Motionmill::TEXTDOMAIN ),
				'type'   => 'error',
				'author' => 'MM_Status'
			);
		}

		// checks plugins
		$plugins_to_check = apply_filters( 'motionmill_status_check_plugins', array( 'better-wp-security/better-wp-security.php', 'iwp-client/init.php' ) );

		foreach ( $plugins_to_check as $file )
		{
			$message_id = sprintf( 'motionmill_status_plugin-%s', sanitize_title( $file ) );

			if ( ! file_exists( trailingslashit( WP_PLUGIN_DIR ) . $file ) )
			{
				$messages[] = array
				(
					'id'     => $message_id,
					'text'   => sprintf( __( 'Plugin <strong>%s</strong> is not installed.', Motionmill::TEXTDOMAIN ), dirname( $file ) ),
					'type'   => 'warning',
					'author' => 'MM_Status'
				);

				continue;
			}

			$plugin = get_plugin_data( trailingslashit( WP_PLUGIN_DIR ) . $file, false );

			if ( ! is_plugin_active( $file ) )
			{
				$messages[] = array
				(
					'id'     => $message_id,
					'text'   => sprintf( __( 'Plugin <strong>%s</strong> is not active.', Motionmill::TEXTDOMAIN ), $plugin['Name'] ),
					'type'   => 'warning',
					'author' => 'MM_Status'
				);

				continue;
			}

			$messages[] = array
			(
				'id'     => $message_id,
				'text'   => sprintf( __( 'Plugin <strong>%s</strong> is active.', Motionmill::TEXTDOMAIN ), $plugin['Name'] ),
				'type'   => 'success',
				'author' => 'MM_Status'
			);
		}

		// error log
		$file = ini_get( 'error_log' );

		if ( $file )
		{
			$size = filesize( $file );

			$messages[] = array
			(
				'id'     => 'motionmill_status_error_log',
				'text'   => sprintf( __( 'Log file <code>%s</code> size is %s bytes.', Motionmill::TEXTDOMAIN ), $file, $size ),
				'type'   => 'warning',
				'author' => 'MM_Status'
			);
		}

		return $messages;
	}
}

$mm_status_messages = new MM_Status_Messages();

?>