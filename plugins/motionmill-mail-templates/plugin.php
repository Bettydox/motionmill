<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once( plugin_dir_path(__FILE__) . 'includes/templates.php' );

if ( ! class_exists('MM_Mail_Templates') )
{
	class MM_Mail_Templates extends MM_Plugin
	{
		public $template_data = array();
		private $pages        = array();
		private $fields       = array();

		public function __construct()
		{
			parent::__construct(array
            (
                'helpers' => array()
            ));
		}

		public function initialize()
		{
			add_action( 'init', array(&$this, 'on_init'), 5 );
			add_filter( 'motionmill_settings_sections', array(&$this, 'on_settings_sections') );
			add_filter( 'motionmill_settings_fields', array(&$this, 'on_settings_fields') );
			add_action( 'motionmill_settings_head', array(&$this, 'on_settings_head'), 5 );
			add_action(	'wp_ajax_mm_mail_templates_print_tag_window', array(&$this, 'on_print_tag_window') );
		}

		public function on_init()
		{
			$templates = apply_filters( 'motionmill_mail_templates_templates', array() );

			foreach ( $templates as $template_id => $data )
			{
				$this->template_data[$template_id] = array_merge(array
				(
					'id' 		  => $template_id,
					'title' 	  => $template_id,
					'description' => '',
					'supports'    => array( 'from', 'to', 'subject', 'headers', 'attachments', 'message', 'html' ),
					'tags' 		  => array(),
					'defaults'    => array()
				), $data);

				// tags
				$this->template_data[$template_id]['tags'] = array();

				if ( is_array($data['tags']) )
				{
					foreach ( $data['tags'] as $tag_id => $tag )
					{
						$this->template_data[$template_id]['tags'][$tag_id] = array_merge(array
						(
							'id' 		  => $tag_id,
							'title' 	  => $tag_id,
							'description' => ''
						), (array) $tag);
					}
				}

				// defaults
				$this->template_data[$template_id]['defaults'] = array_merge(array
				(
					'from'        => '',
					'to'          => '',
					'subject'     => '',
					'message'     => '',
					'headers'     => '',
					'attachments' => '',
					'html'        => false
				), isset($data['defaults']) && is_array($data['defaults']) ? $data['defaults'] : array() );
			}
		}

		public function on_settings_sections($sections)
		{
			$first_template = count( $this->template_data ) > 0 ? array_shift(array_values($this->template_data)) : null;

			$sections[] = array
			(
				'name' 		  => 'mail_templates',
				'title' 	  => __('Mail templates', MM_TEXTDOMAIN),
				'description' => __('', MM_TEXTDOMAIN),
				'parent'      => '',
				'link'        => $first_template ? '' . 'mail_templates/' . $first_template['id'] : ''
			);

			foreach ( $this->template_data as $data )
			{
				$sections[] = array
				(
					'name' 		  => $data['id'],
					'title' 	  => $data['title'],
					'description' => $data['description'],
					'parent'	  => 'mail_templates',
					'link'        => 'mail_templates/' . $data['id']
				);
			}

			return $sections;
		}

		public function on_settings_fields($fields)
		{
			$this->fields = array();

			foreach ( $this->template_data as $data )
			{
				if ( in_array('from', $data['supports']) )
				{
					$this->fields[] = array
					(
						'name'        => 'from',
						'title'       => __('From', MM_TEXTDOMAIN),
						'type'        => 'textfield',
						'value'       => $data['defaults']['from'],
						'section'     => sprintf('mail_templates/%s', $data['id'] ),
						'description' => __('Your Name &lt;yourname@example.com&gt;', MM_TEXTDOMAIN),
						'_template'   => $data['id']
					);
				}

				if ( in_array('to', $data['supports']) )
				{
					$this->fields[] = array
					(
						'name'        => 'to',
						'title'       => __('To', MM_TEXTDOMAIN),
						'type'        => 'textfield',
						'value'       => $data['defaults']['to'],
						'section'     => sprintf('mail_templates/%s', $data['id'] ),
						'description' => __('Your Name &lt;yourname@example.com&gt;', MM_TEXTDOMAIN),
						'_template'   => $data['id']
					);
				}

				if ( in_array('subject', $data['supports']) )
				{
					$this->fields[] = array
					(
						'name'      => 'subject',
						'title'     => __('Subject', MM_TEXTDOMAIN),
						'type'      => 'textfield',
						'value'     => $data['defaults']['subject'],
						'section'     => sprintf('mail_templates/%s', $data['id'] ),
						'tags'      => true,
						'_template' => $data['id']
					);
				}

				if ( in_array('headers', $data['supports']) )
				{
					$this->fields[] = array
					(
						'name'        => 'headers',
						'title'       => __('Additional headers', MM_TEXTDOMAIN),
						'type'        => 'textarea',
						'value'       => $data['defaults']['headers'],
						'section'     => sprintf('mail_templates/%s', $data['id'] ),
						'rows'        => 3,
						'description' => __('From: Your Name &lt;yourname@example.com&gt;<br />Cc: Your Name &lt;yourname@example.com&gt;<br />Bcc: YourName&lt;yourname@example.com&gt;<br />(One line for each entry)', MM_TEXTDOMAIN),
						'_template'   => $data['id']
					);
				}

				if ( in_array('attachments', $data['supports']) )
				{
					$this->fields[] = array
					(
						'name'        => 'attachments',
						'title'       => __('Attachments', MM_TEXTDOMAIN),
						'type'        => 'textarea',
						'value'       => $data['defaults']['attachments'],
						'section'     => sprintf('mail_templates/%s', $data['id'] ),
						'rows'        => 3,
						'description' => __('http://yoursite.com/file-1.jpg<br />http://yoursite.com/file-2.jpg<br />(One line for each entry)', MM_TEXTDOMAIN),
						'_template'   => $data['id']
					);
				}

				if ( in_array('message', $data['supports']) )
				{
					$this->fields[] = array
					(
						'name'        => 'message',
						'title' 	  => __('Message', MM_TEXTDOMAIN),
						'type' 	      => 'textarea',
						'value' 	  => $data['defaults']['message'],
						'section'     => sprintf('mail_templates/%s', $data['id'] ),
						'tags'		  => true,
						'_template'   => $data['id']
					);
				}

				if ( in_array('html', $data['supports']) )
				{
					$this->fields[] = array
					(
						'name' 		  => 'html',
						'title' 	  => __('Use HTML Content-type', MM_TEXTDOMAIN),
						'type' 	      => 'checkbox',
						'value' 	  => ! empty($data['defaults']['html']),
						'section'     => sprintf('mail_templates/%s', $data['id'] ),
						'description' => __('Check to send as html.', MM_TEXTDOMAIN),
						'_template'   => $data['id']
					);
				}
				
			}

			return array_merge($fields, $this->fields);
		}

		public function on_settings_head($section)
		{
			if ( stripos( $section, 'mail_templates' ) !== 0 )
				return;
			
			add_thickbox();

			$tag_fields = array();

			foreach ( $this->fields as $field )
			{
				if ( empty($field['tags']) )
					continue;
				
				if ( $field['section'] != $section )
					continue;

				$tag_fields[] = array
				(
					'id'       => 'motionmill_settings-' . $field['name'],
					'template' => $field['_template']
				);
			}
			?>

			<script type="text/javascript">

				jQuery(document).ready(function($)
				{
					// inserts tag buttons
					var fields = <?php echo json_encode($tag_fields); ?>;

					for ( var i = 0; i < fields.length; i++ )
					{
						var field = fields[i];

						var button = $('<a href="#" class="button insert-tag-button thickbox"></a>')
							.text("<?php _e('Insert tag', MM_TEXTDOMAIN); ?>")
							.attr('title', "<?php _e('Insert tag', MM_TEXTDOMAIN); ?>")
							.attr( 'href', '/wp-admin/admin-ajax.php?action=mm_mail_templates_print_tag_window&width=800&height=600&template_id=' + field.template + '&field_id=' + field.id )

						$( '#' + field.id ).parent().append(button);
					};
				});

			</script>

			<?php
		}

		public function on_print_tag_window()
		{
			$template = $this->template_data[ $_GET['template_id'] ];
			$field = $_GET['field_id'];

			?>

			<script type="text/javascript">

				function insertAtCaret(element, value)
				{
					var elem = document.getElementById(element);

					// IE
					if (document.selection)
					{
	    				elem.focus();
	    				var selection = document.selection.createRange();
	    				selection.text = value;
					}

					// other browsers
					else if ( elem.selectionStart || elem.selectionStart == '0' )
					{
						var startPos = elem.selectionStart;
	        			var endPos = elem.selectionEnd;

	        			elem.value = elem.value.substring(0, startPos) + value + elem.value.substring(endPos, elem.value.length);
	            	}

	            	else
	            	{
	            		elem.value += value;
	            	}
				}

				jQuery(document).ready(function($)
				{
					var field = $('#<?php echo $field; ?>');

					$('.mm-mail-templates-tags .tag-button').click(function(e)
					{
						e.preventDefault();

						insertAtCaret('<?php echo $field; ?>', $(this).attr('data-tag') );

						tb_remove();
					});
				});

			</script>

			<h1><?php _e('Available tags'); ?></h1>

			<?php if ( count($template['tags']) == 0 ) : ?>
			<p><?php _e('No tags available.'); ?></p>
			
			<?php else : ?>

			<p><?php _e('Click on a tag to insert.'); ?></p>

			<div class="mm-mail-templates-tags">
			<?php foreach ($template['tags'] as $data ) : ?>
			<a href="#" class="button tag-button" title="<?php echo esc_attr($data['description']); ?>" data-tag="[<?php echo esc_attr($data['id']); ?>]"><?php echo esc_html($data['title']); ?></a>
			<?php endforeach; ?>
			</div>

			<?php endif; ?>

			<?php

			die();
		}

		public function parse_template($template, $vars, $html = true)
		{
			$parsed = $template;

			foreach ( $vars as $key => $value )
			{
				$parsed = str_replace( '[' . $key . ']', $html ? esc_html($value) : $value, $parsed );
			}

			return $parsed;
		}

		public function mail_template($template_id, $to = null, $vars = array() )
		{
			$options = $this->_('MM_Settings')->get_option( 'mail_templates/' . $template_id );

			if ( ! $options || ! is_array($options) )
				return false;

			if ( ! $to && empty($options['to']) )
				return false;

			$html    = ! empty($options['html']);
			$to      = $to ? $to : $options['to'];
			$subject = ! empty($options['subject']) ? $this->parse_template( $options['subject'], $vars, $html ) : '';
			$message = ! empty($options['message']) ? $this->parse_template( $options['message'], $vars, $html ) : '';
			
			$headers = explode( "\n", $options['headers'] );

			if ( $options['from'] != '' )
			{
				$headers[] = 'From: ' . $options['from'];
			}

			$headers[] = 'Content-type: ' . ( $html ? 'text/html' : 'text/plain' );

			return wp_mail( $to, $subject, $message, $headers, $options['attachments'] );
		}
	}

	function mm_mm_mail_templates_register($plugins)
	{
		$plugins[] = 'MM_Mail_Templates';

		return $plugins;
	}

	add_action( 'motionmill_plugins', 'mm_mm_mail_templates_register', 5 );
}
?>