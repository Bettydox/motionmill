<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists('MM_Error') )
{
	class MM_Error extends WP_Error
	{
		public function __construct($code = '', $message = '', $data = '')
		{
			parent::__construct($code, $message, $data);
		}

		public function get_error_messages_string($options = array())
		{
			$options = array_merge(array
			(
				'wrapper_format' => '<div class="errors">%s</div>',
				'message_format' => '%s<br />'
	 		), $options);

			$codes = $this->get_error_codes();

			$str = '';

			if ( count($codes) > 0 )
			{
				foreach ( $codes as $code )
				{
					$str .= $this->get_error_message_string( $code, $options['message_format'] );
				}

				$str .= sprintf( $options['wrapper_format'], $str );
			}

			return $str;
		}

		public function get_error_message_string($code = '', $format = '<div class="error">%s</div>')
		{
			$message = $this->get_error_message($code);

			if ( empty($message) )
				return '';

			return sprintf( $format, $message );
		}
	}
}
?>