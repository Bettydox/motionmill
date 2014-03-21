<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Login
 Plugin URI: http://motionmill.com
 Description: Customizes the WordPress email settings.
 Version: 1.0.0
 Author: Motionmill
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

add_action( 'motionmill_loaded', function(){

if ( ! class_exists('MM_Mail') )
{
	require_once( plugin_dir_path(__FILE__) . 'includes/templates.php' );

	class MM_Mail extends MM_Plugin
	{
		protected $templates = array();

		public function __construct()
		{
			parent::__construct();
		}

		public function initialize()
		{	
			add_filter( 'motionmill_settings_pages', array(&$this, 'on_settings_pages') );
			add_filter( 'motionmill_settings_sections', array(&$this, 'on_settings_sections') );
			add_filter( 'motionmill_settings_fields', array(&$this, 'on_settings_fields') );
			add_filter( 'motionmill_mail_parse_tag', array(&$this, 'on_parse_tag' ), 0, 3 );

			add_action( 'wp_mail_from', array(&$this, 'on_mail_from') );
			add_filter( 'wp_mail_from_name', array(&$this, 'on_mail_from_name') );

			add_action( 'init', array(&$this, 'on_init'), 10 );
		}

		public function on_init()
		{
			foreach ( apply_filters( 'motionmill_mail_templates', array() ) as $template_id => $data )
			{
				if ( ! is_array($data) )
					continue;

				$template = array_merge(array
				(
					'id'          => $template_id,
					'title'       => $template_id,
					'description' => '',
					'fields'      => array()
				), $data);

				$template['fields'] = array_merge(array
				(
					'from' 		  => '',
					'to' 		  => '',
					'subject'     => '',
					'message' 	  => '',
					'headers' 	  => '',
					'attachments' => '',
					'html' 	 	  => false
					
				), (array) $template['fields']);

				$this->templates[ $template_id ] = $template;
			}
		}

		public function on_settings_pages($pages)
		{
			$pages[] = array
			(
				'id' 		  => 'motionmill_mail',
				'title' 	  => __('Mail', MM_TEXTDOMAIN),
				'description' => __('<p>Customizes the WordPress mail settings.</p>', MM_TEXTDOMAIN)
			);

			return $pages;
		}

		public function on_settings_sections($sections)
		{
			$sections[] = array
			(
				'id' 		  => 'motionmill_mail_general',
				'title' 	  => __('General', MM_TEXTDOMAIN),
				'description' => __('', MM_TEXTDOMAIN),
				'page'        => 'motionmill_mail'
			);

			foreach ( $this->templates as $template_id => $data )
			{
				$sections[] = array
				(
					'id' 		  => 'motionmill_mail_' . $data['id'],
					'title' 	  => $data['title'],
					'description' => $data['description'],
					'page'        => 'motionmill_mail'
				);
			}

			return $sections;
		}

		public function on_settings_fields($fields)
		{
			$fields[] = array
			(
				'id'          => 'from_name',
				'title'       => __( "Sender's name", MM_TEXTDOMAIN ),
				'description' => __( 'Leave empty to disable.', MM_TEXTDOMAIN ),
				'type'        => 'textfield',
				'class'       => 'regular-text',
				'value'       => '',
				'page'        => 'motionmill_mail',
				'section'     => 'motionmill_mail_general'
			);

			$fields[] = array
			(
				'id'          => 'from',
				'title'       => __( "Sender's email", MM_TEXTDOMAIN ),
				'description' => __( 'Leave empty to disable.', MM_TEXTDOMAIN ),
				'type'        => 'textfield',
				'class'       => 'regular-text',
				'value'       => '',
				'page'        => 'motionmill_mail',
				'section'     => 'motionmill_mail_general'
			);

			foreach ( $this->templates as $template_id => $data )
			{
				if ( ! is_null( $data['fields']['from'] ) )
				{
					$fields[] = array
					(
						'id'          => $data['id'] . '_from',
						'title'       => __( 'From', MM_TEXTDOMAIN ),
						'description' => __( '', MM_TEXTDOMAIN ),
						'type'        => 'textfield',
						'class'       => 'regular-text',
						'value'       => $data['fields']['from'],
						'page'        => 'motionmill_mail',
						'section'     => 'motionmill_mail_' . $data['id']
					);
				}

				if ( ! is_null( $data['fields']['to'] ) )
				{
					$fields[] = array
					(
						'id'          => $data['id'] . '_to',
						'title'       => __( 'To', MM_TEXTDOMAIN ),
						'description' => __( '', MM_TEXTDOMAIN ),
						'type'        => 'textfield',
						'class'       => 'regular-text',
						'value'       => $data['fields']['to'],
						'page'        => 'motionmill_mail',
						'section'     => 'motionmill_mail_' . $data['id']
					);
				}

				if ( ! is_null( $data['fields']['subject'] ) )
				{
					$fields[] = array
					(
						'id'          => $data['id'] . '_subject',
						'title'       => __( 'Subject', MM_TEXTDOMAIN ),
						'description' => __( '', MM_TEXTDOMAIN ),
						'type'        => 'textfield',
						'class'       => 'regular-text',
						'value'       => $data['fields']['subject'],
						'page'        => 'motionmill_mail',
						'section'     => 'motionmill_mail_' . $data['id']
					);
				}

				if ( ! is_null( $data['fields']['message'] ) )
				{
					$fields[] = array
					(
						'id'          => $data['id'] . '_message',
						'title'       => __( 'Message', MM_TEXTDOMAIN ),
						'description' => __( '', MM_TEXTDOMAIN ),
						'type'        => 'textarea',
						'class'       => 'large-text',
						'value'       => $data['fields']['message'],
						'page'        => 'motionmill_mail',
						'section'     => 'motionmill_mail_' . $data['id']
					);
				}

				if ( ! is_null( $data['fields']['headers'] ) )
				{
					$fields[] = array
					(
						'id'          => $data['id'] . '_headers',
						'title'       => __( 'Additional Headers', MM_TEXTDOMAIN ),
						'description' => __( '', MM_TEXTDOMAIN ),
						'type'        => 'textarea',
						'class'       => 'large-text',
						'rows'        => '3',
						'value'       => $data['fields']['headers'],
						'page'        => 'motionmill_mail',
						'section'     => 'motionmill_mail_' . $data['id']
					);
				}

				if ( ! is_null( $data['fields']['attachments'] ) )
				{
					$fields[] = array
					(
						'id'          => $data['id'] . '_attachments',
						'title'       => __( 'Attachments', MM_TEXTDOMAIN ),
						'description' => __( '', MM_TEXTDOMAIN ),
						'type'        => 'textarea',
						'class'       => 'large-text',
						'rows'        => '3',
						'value'       => $data['fields']['attachments'],
						'page'        => 'motionmill_mail',
						'section'     => 'motionmill_mail_' . $data['id']
					);
				}

				if ( ! is_null( $data['fields']['html'] ) )
				{
					$fields[] = array
					(
						'id'          => $data['id'] . '_html',
						'title'       => __( 'HTML Content-type', MM_TEXTDOMAIN ),
						'description' => __( '', MM_TEXTDOMAIN ),
						'type'        => 'checkbox',
						'value'       => ! empty( $data['fields']['html'] ),
						'page'        => 'motionmill_mail',
						'section'     => 'motionmill_mail_' . $data['id']
					);
				}
			}

			return $fields;
		}

		public function on_parse_tag( $default, $name, $vars = array() )
		{
			if ( strpos($name, 'blog:') === 0 )
			{
				$var = substr( $name, strlen('blog:') );

				return get_bloginfo( $var );
			}

			if ( strpos($name, 'user:') === 0 && isset($vars['user_id']) )
			{
				$user = get_user_by( 'id', $vars['user_id'] );

				if ( $user )
				{
					$var = substr( $name, strlen('blog:') );

					if ( isset($user->$var) )
					{
						return $user->$var;
					}

					$user_meta = get_user_meta( $user->ID );

					if ( isset($user_meta[$var]) )
					{
						return get_user_meta( $user->ID, $var, true );
					}
				}
			}

			if ( $name == 'network_home_url' )
			{
				return network_home_url();
			}

			if ( $name == 'login_url' )
			{
				return wp_login_url();
			}

			return $default;
		}

		public function parse_template($template, $vars = array(), $html = false)
		{
			$offset = 0;

			while ( ( $start = strpos($template, '[', $offset) ) !== false && ( $end = strpos($template, ']', $offset + 1) ) !== false )
			{				
				$tag  	  = substr( $template , $start, $end + strlen(']') - $start );
				$tag_name = substr( $tag , strlen('['), - strlen(']') );

				if ( isset($vars[$tag_name]) )
				{
					$replacement = $vars[ $tag_name ];
				}
				else
				{
					$replacement = $tag;	
				}

				$replacement = apply_filters( 'motionmill_mail_parse_tag', $replacement, $tag_name, $vars );

				if ( $html )
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

		public function mail_template( $template_id, $vars = array() )
		{
			if ( ! isset($this->templates[$template_id]) )
				return false;

			$options = $this->_('MM_Settings')->get_option( 'motionmill_mail' );

			$html = ! empty( $options[ $template_id . '_html' ] );

			$to = $this->parse_template( $options[ $template_id . '_to' ], $vars );
			
			$subject = $this->parse_template( $options[ $template_id . '_subject' ], $vars );
			$message = $this->parse_template( $options[ $template_id . '_message' ], $vars, $html );

			// headers
			if ( ! empty($options[ $template_id . '_headers' ]) )
			{
				$headers  = trim( $options[ $template_id . '_headers' ] ) . "\n";
				$headers .= 'From: ' .  $options[ $template_id . '_from' ] . "\n";
				$headers .= 'Content-type: ' . $html ? 'text/html' : 'text/plain';
				$headers = $this->parse_template( $headers, $vars );
			}

			else
			{
				$headers = '';
			}
			
			// attachments
			if ( ! empty($options[ $template_id . '_attachments' ]) )
			{
				$attachments = $this->parse_template( $options[ $template_id . '_attachments' ], $vars );
			}

			else
			{
				$attachments = '';
			}

			return wp_mail( $to, $subject, $message, $headers, $attachments );
		}

		public function on_mail_from($default)
		{
			$options = $this->_('MM_Settings')->get_option('motionmill_mail');

			return $options['from'] != '' ? $options['from'] : $default;
		}

		public function on_mail_from_name($default)
		{
			$options = $this->_('MM_Settings')->get_option('motionmill_mail');

			return $options['from_name'] != '' ? $options['from_name'] : $default;
		}
	}

	// registers plugin
	function motionmill_plugins_add_mail($plugins)
	{
		array_push($plugins, 'MM_Mail');

		return $plugins;
	}
	
	add_filter( 'motionmill_plugins', 'motionmill_plugins_add_mail', 5 );
}

});

?>