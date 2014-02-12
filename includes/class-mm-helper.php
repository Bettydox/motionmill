<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists('MM_Helper') )
{
	class MM_Helper
	{
		static public function form_post($name, $default = '')
		{
			if ( isset($_POST) && isset($_POST[$name]) )
			{
				return $_POST[$name];
			}

			return $default;
		}

		static public function form_value($name, $default = '')
		{
			return esc_html( self::form_post($name, $default) );
		}

		static public function clean_path($path)
		{
			return trim( preg_replace( '/([\/ \t]*\/[\/ \t]*)+/', '/', $path) , '/');
		}

		static public function get_user_role($user_id, $key = null)
		{
			$user = new WP_User($user_id);
			$role_name = $user->roles[0];

			if ( ! $key )
			{
				return $role_name;
			}

			$roles = get_option('wp_user_roles', array());

			if ( isset($roles[$role_name][$key]) )
			{
				return $roles[$role_name][$key];
			}
			
			return '';
		}

		static public function error_message($message, $type = 'error')
		{
			$types = array('error', 'updated');

			if ( ! in_array($type, $types) )
				$type = $types[0];

			return sprintf( '<div class="%s"><p><strong>%s</strong></p></div>', $type, $message );
		}

		static public function get_element_by($prop, $value, $container = array())
		{
			$elements = self::get_elements_by( $prop, $value, $container );

			return count($elements) > 0 ? $elements[0] : null;
		}

		static public function get_elements_by($search, $container)
		{
			if ( ! is_array($search) )
			{
				parse_str($search, $search);
			}

			$elements = array();

			if ( is_array($container) )
			{
				foreach ( $container as $element )
				{
					$include = true;

					foreach ( $search as $key => $value )
					{
						if ( ! isset($element[$key]) || $element[$key] != $value )
						{
							$include = false;

							break;
						}
					}

					if ( $include )
					{
						$elements[] = $element;
					}
				}
			}

			return $elements;
		}

		static public function get_element_values($key, $container)
		{
			$a = array();

			foreach ( $container as $element )
			{
				$element_array = ! is_array($element) ? get_object_vars($element) : $element;

				if ( ! isset($element_array[$key]) )
					break;

				if ( in_array($element_array[$key], $a) )
					continue;

				$a[] = $element_array[$key];
			}

			return $a;
		}

		static public function clean_explode($delimiter, $string)
		{
			// removes spaces, newlines and delimiters around delimiter
			$string = preg_replace( sprintf( '/([ \t\n]*%s[ \t\n]*)+/', preg_quote($delimiter) ), $delimiter, $string);
			// removes first and last character if delimiter
			$string = trim($string, $delimiter);

			return strlen($string) > 0 ? explode($delimiter, $string) : array();
		}

		static public function get_image_size($url)
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

		static public function parse_template($template, $vars = array(), $options = array())
		{
			$options = array_merge(array
			(
				'tag_l' => '[',
				'tag_r' => ']',
				'html'  => false
			), (array) $options);

			$offset = 0;

			while ( ( $start = strpos($template, $options['tag_l'], $offset) ) !== false && ( $end = strpos($template, $options['tag_r'], $offset + strlen($options['tag_l']) ) ) !== false )
			{				
				$tag  	  = substr( $template , $start, $end + strlen($options['tag_r']) - $start );
				$tag_name = substr( $tag , strlen($options['tag_l']), - strlen($options['tag_r']) );

				if ( isset($vars[$tag_name]) )
				{
					$replacement = $vars[ $tag_name ];
				}
				else
				{
					$replacement = $tag;	
				}

				if ( $options['html'] )
				{
					$replacement = esc_html( $replacement );
				}

				$template = substr_replace( $template, $replacement, $start, strlen($tag) );

				if ( $offset < strlen($template) )
				{
					$offset++;
				}
			}

			return $template;
		}

		static public function load_template( $file, $vars = array(), $return = false )
		{
			extract($vars);

			if ( $return )
			{
				ob_start();
			}

			include( $file );

			if ( $return )
	        {
	            return @ob_get_clean();
	        }
		}

		static public function get_geo_info($address)
		{
		    $response = file_get_contents( sprintf('http://maps.google.com/maps/api/geocode/json?sensor=false&address=%s', urlencode($address) ) );
		 
		    if ( $response !== false )
		    {
		        $data = json_decode( $response );
		 
		        if ( $data->status == 'OK' )
		        {
		            return $data->results;
		        }
		    }
		 
		    return false;
		}

		static public function mm_get_cf7_data($form, $num = 0)
		{	
			require_once( ABSPATH . 'wp-content/plugins/contact-form-7-to-database-extension/CFDBFormIterator.php' );

			$exp = new CFDBFormIterator();
			$exp->export( $form, array() );

			$rows = array();

			while ( ( $row = $exp->nextRow() ) && count($rows) < $num )
			{
			    $rows[] = $row;
			}

			return $rows;
		}
	}
}
?>