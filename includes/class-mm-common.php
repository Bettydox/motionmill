<?php if ( ! defined('ABSPATH') ) exit; // Exits when accessed directly

if ( ! function_exists( 'MM' ) )
{
	function MM( $plugin = null )
	{
		$motionmill = Motionmill::get_instance();

		if ( ! $plugin )
		{
			return $motionmill;
		}

		$plugin_class_prefix = 'MM_';

		if ( stripos( $plugin, $plugin_class_prefix ) !== 0 )
		{
			$plugin = $plugin_class_prefix . $plugin;
		}
		
		return $motionmill->get_plugin( $plugin );
	}
}

if ( ! class_exists( 'MM_Common' ) )
{
	class MM_Common
	{
		static public function get_icon( $id, $content = '', $extra = array() )
		{	
			// see: http://fortawesome.github.io/Font-Awesome/icons/

			return sprintf( '<i class="fa fa-%s"%s>%s</i>', $id, MM_HTML::parse_attributes( $extra ), $content );
		}

		static public function url_exists( $url )
		{
			$headers = @get_headers( $url );

			if( empty( $headers ) || $headers[0] == 'HTTP/1.1 404 Not Found')
			{
	   			return false;
	   		}

	   		return true;
	   	}

	   	static public function is_serialized( $value )
	   	{
			return ( $value === 'b:0;' || @unserialize( $value ) !== false );
	   	}

	   	static public function get_admin_notice( $id, $subject, $message, $closeable = false )
		{
			if ( $closeable )
			{
				$notices = MM('Options')->get( 'admin_notices', array() );
				
				$user_id = get_current_user_id();

				if ( ! isset( $notices[ $user_id ] ) )
				{
					$notices[ $user_id ] = array();
				}

				$user_notices = &$notices[ $user_id ];

				if ( ! isset( $user_notices[ $id ] ) )
				{
					$user_notices[ $id ] = true;

					MM('Options')->set( 'admin_notices', $notices );
				}

				if ( isset( $_GET['notice'] ) && $_GET['notice'] == $id )
				{
					$user_notices[ $id ] = false;

					MM('Options')->set( 'admin_notices', $notices );
				}

				$notices = MM('Options')->get( 'admin_notices', array() );

				if ( $notices[ $user_id ][ $id ] == false )
				{
					return '';
				}
			}

			$css_classes = array( 'mm-admin-notice' );

			if ( is_wp_error( $message ) )
			{
				$error = true;

				$css_classes[] = 'error';

				$message = $message->get_error_message();
			}

			else
			{
				$error = false;

				$css_classes[] = 'updated';
			}

			$html = sprintf( '<strong>%s</strong><br>%s', $subject, $message );

			if ( $error )
			{
				$html .= ' ' . sprintf( __( 'You may contact the <a href="mailto:%s">administrator</a> regarding this issue.', Motionmill::TEXTDOMAIN ), 
					get_option( 'admin_email' ) );
			}

			if ( $closeable )
			{
				$css_classes[] = 'closeable';

				$html .= sprintf( ' <a href="?page=%s&notice=%s" title="%s" class="close-button"><i class="fa fa-close"></i></a>', 
					$_GET['page'], $id, __( 'Close', Motionmill::TEXTDOMAIN ) );		
			}

			return sprintf( '<div class="%s"><p>%s</p></div>', implode( ' ', $css_classes ), $html );
		}
	}
}

?>