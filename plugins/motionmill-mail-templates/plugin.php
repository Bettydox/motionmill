<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists('MM_Mail_Templates') )
{
	class MM_Mail_Templates extends MM_Plugin
	{
		public function __construct()
		{
			parent::__construct();
		}

		public function initialize()
		{
			add_action( 'motionmill_settings_pages', array(&$this, 'on_settings_pages') );
			add_action( 'motionmill_settings_sections', array(&$this, 'on_settings_sections') );
			add_action( 'motionmill_settings_fields', array(&$this, 'on_settings_fields') );
			
			add_filter( 'wp_mail', array(&$this, 'on_wp_mail') );
		}

		public function on_settings_pages($pages)
		{
			$pages[] = array
			(
				'id'          => 'motionmill_mail_templates',
				'title'       => __('Mail Templates', MM_TEXTDOMAIN),
				'description' => __('', MM_TEXTDOMAIN)
			);

			return $pages;
		}

		public function on_settings_sections($sections)
		{
			$sections[] = array
			(
				'id' 		  => 'general',
				'title' 	  => __('General', MM_TEXTDOMAIN),
				'description' => __('', MM_TEXTDOMAIN),
				'page'        => 'motionmill_mail_templates'
			);

			return $sections;
		}

		public function on_settings_fields($fields)
		{
			$fields[] = array
			(
				'id'          => 'sender',
				'title'       => __('Sender', MM_TEXTDOMAIN),
				'description' => __("The sender. Format: name &lt;email&gt;", MM_TEXTDOMAIN),
				'type'        => 'textfield',
				'value'       => '',
				'page'        => 'motionmill_mail_templates',
				'section'     => 'general'
			);

			$fields[] = array
			(
				'id'          => 'headers',
				'title'       => __('Additional headers', MM_TEXTDOMAIN),
				'description' => __('Seperate each entry with a newline.', MM_TEXTDOMAIN),
				'type'        => 'textarea',
				'rows'        => '5',
				'value'       => '',
				'page'        => 'motionmill_mail_templates',
				'section'     => 'general'
			);

			$fields[] = array
			(
				'id'          => 'attachments',
				'title'       => __('Attachments', MM_TEXTDOMAIN),
				'description' => __('Seperate each entry with a newline.', MM_TEXTDOMAIN),
				'type'        => 'textarea',
				'rows'        => '5',
				'value'       => '',
				'page'        => 'motionmill_mail_templates',
				'section'     => 'general'
			);

			$fields[] = array
			(
				'id' 		  => 'html',
				'title' 	  => __('Content-type HTML', MM_TEXTDOMAIN),
				'description' => __('Check to send emails as HTML', MM_TEXTDOMAIN),
				'type'		  => 'checkbox',
				'value'       => 0,
				'page'        => 'motionmill_mail_templates',
				'section'     => 'general'
			);

			$fields[] = array
			(
				'id' 		  => 'enabled',
				'title' 	  => __('Enable', MM_TEXTDOMAIN),
				'description' => __('Check/uncheck to enable/disable.', MM_TEXTDOMAIN),
				'type'		  => 'checkbox',
				'value'       => 0,
				'page'        => 'motionmill_mail_templates',
				'section'     => 'general'
			);

			return $fields;
		}

		public function on_wp_mail($defaults)
		{
			$options = $this->_('MM_Settings')->get_option('motionmill_mail_templates');

			if ( empty($options['enabled']) )
				return $defaults;

			// headers
			$headers = '';
			$headers .= 'From: ' . $options['sender'] . "\n";
			$headers .= 'Content-type: ' . ( ! empty($options['html']) ? 'text/html' : 'text/plain' ) . "\n";
			$headers .= trim($options['headers'], " \r\n");

			return array
			(
				'to'          => $defaults['to'],
				'subject'     => $defaults['subject'],
				'message'     => $defaults['message'],
				'headers'     => $headers,
				'attachments' => $options['attachments'],
			);
		}
	}

	// registers plugin
	function motionmill_mail_templates_register($plugins)
	{
		array_push($plugins, 'MM_Mail_Templates');

		return $plugins;
	}
	
	add_filter( 'motionmill_plugins', 'motionmill_mail_templates_register', 5 );
}

?>