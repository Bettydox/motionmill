<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists('MM_Image_Helper') )
{
	class MM_Image
	{
		static public function get_size($url)
		{
			// alternative to getimagesize (uses fopen)

			$response = wp_remote_get($url);

			if ( is_wp_error( $response ) )
			{
				trigger_error( 'Unable to load image: ' . $response->get_message() );

				return null;
			}

			$img = imagecreatefromstring( $response['body'] );

			if ( ! $img )
			{
				trigger_error( 'Unable to get image sizes.' );

				return null;
			}

			return array
			(
				'width'  => imagesx($img),
				'height' => imagesy($img)
			);
		}
	}
}

?>