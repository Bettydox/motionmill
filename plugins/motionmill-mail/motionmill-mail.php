<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Mail
 Plugin URI:
 Description:
 Version: 1.0.0
 Author: Maarten Menten
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

if ( ! class_exists( 'MM_Mail' ) )
{
	class MM_Mail
	{
		const FILE = __FILE__;

		protected $tag_categories = array();
		protected $tags       = array();
		protected $templates  = array();

		public function __construct( $config = array() )
		{	
			MM( 'Loader' )->load_class( 'MM_Array' );
			MM( 'Loader' )->load_class( 'MM_Template' );
			MM( 'Loader' )->load_class( 'MM_Mail_Tags', self::FILE );
			MM( 'Loader' )->load_class( 'MM_Mail_Retrieve_Password', self::FILE );
			MM( 'Loader' )->load_class( 'MM_Mail_New_User_Notification', self::FILE );
			MM( 'Loader' )->load_class( 'MM_Mail_Change_Password_Notification', self::FILE );
			
			add_filter( 'motionmill_settings_pages', array( &$this, 'on_settings_pages' ) );
			add_filter( 'motionmill_settings_sections', array( &$this, 'on_settings_sections' ) );
			add_filter( 'motionmill_settings_fields', array( &$this, 'on_settings_fields' ) );

			add_action( 'motionmill_init', array( &$this, 'initialize' ), 3 );
		}

		public function initialize()
		{
			foreach ( apply_filters( 'motionmill_mail_templates', array() ) as $data )
			{
				if ( empty( $data['id'] ) )
				{
					continue;
				}

				if ( isset( $data['tag_cats'] ) && ! is_array( $data['tag_cats'] ) )
				{
					continue;
				}

				$this->templates[] = array_merge( array
				(
					'id'           => $data['id'],
					'title'        => $data['id'],
					'description'  => '',
					'to'           => '',
					'subject'      => '',
					'message'      => '',
					'headers'      => '',
					'attachments'  => '',
					'tag_cats'     => array(),
					'enable'       => null
				), $data );
			}

			foreach ( apply_filters( 'motionmill_mail_tag_categories', array() ) as $data )
			{
				if ( empty( $data['id'] ) )
				{
					continue;
				}

				$this->tag_categories[] = array_merge( array
				(
					'id'          => $data['id'],
					'title'       => $data['id'],
					'description' => '',
					'_vars'       => array()
				), $data );
			}

			foreach ( apply_filters( 'motionmill_mail_tags', array() ) as $data )
			{
				if ( empty( $data['name'] ) || empty( $data['category'] ) )
				{
					continue;
				}

				$this->tags[] = array_merge( array
				(
					'id'          => sprintf( '%s-%s', $data['category'], $data['name'] ),
					'name'        => $data['name'],
					'title'       => $data['name'],
					'description' => '',
					'value'       => null,
					'category'    => $data['category']
				), $data );
			}

			add_filter( 'wp_mail_from_name', array( &$this, 'on_mail_from_name' ) );
			add_filter( 'wp_mail_from', array( &$this, 'on_mail_from' ) );
		}

		public function get_option( $key = null, $default = '' )
		{
			return MM('Settings')->get_option( 'motionmill_mail', $key, $default );
		}

		public function get_template( $search = '' )
		{
			return MM_Array::get_element_by( $search, $this->templates );
		}

		public function get_templates( $search = '' )
		{
			return MM_Array::get_elements_by( $search, $this->templates );
		}

		public function get_tag( $search = '' )
		{
			return MM_Array::get_element_by( $search, $this->tags );
		}

		public function get_tags( $search = '' )
		{
			return MM_Array::get_elements_by( $search, $this->tags );
		}

		public function get_tag_category( $search = '' )
		{
			return MM_Array::get_element_by( $search, $this->tag_categories );
		}

		public function get_tag_categories( $search = '' )
		{
			return MM_Array::get_elements_by( $search, $this->tag_categories );
		}

		public function get_tag_code( $tag )
		{
			return sprintf( '${%s:%s}', $tag['category'], $tag['name'] );
		}

		public function get_tag_by_code( $code )
		{	
			// removes tag delimiters
			$bounds = explode( '%s', '${%s}' );

			$inner = substr( $code, strlen( $bounds[0] ), - $bounds[1] ); 

			// gets tag meta
			list( $tag_name, $tag_cat ) = explode( ':', $inner );

			return $this->get_tag( array( 'name' => $tag_name, 'category' => $tag_cat ) );
		}

		public function get_template_page_id( $template_id = '' )
		{
			return sprintf( 'motionmill_mail-%s', $template_id );
		}

		public function set_tag_value( $tag_id, $value )
		{
			foreach ( $this->tags as &$tag )
			{
				if ( $tag['id'] == $tag_id )
				{
					$tag['value'] = $value;

					break;
				}
			}
		}

		public function set_tag_values( $category_id, $values )
		{
			$category = $this->get_tag_category( array( 'id' => $category_id ) );
		
			if ( $category )
			{
				foreach ( $values as $tag_name => $value )
				{
					$tag = $this->get_tag( array( 'name' => $tag_name, 'category' => $category['id'] ) );

					if ( ! $tag )
					{
						continue;
					}

					$this->set_tag_value( $tag['id'], $value );
				}
			}
		}

		public function get_tag_category_var( $category_id, $key = null, $default = '' )
		{
			$category = $this->get_tag_category( array( 'id' => $category_id ) );

			if ( $category )
			{
				$vars = $category['_vars'];

				if ( ! $key )
				{
					return $vars;
				}

				if ( isset( $vars[$key] ) )
				{
					return $vars[$key];
				}
			}

			return $default;
		}

		public function set_tag_category_var( $category_id, $key, $value = '' )
		{
			// recursive
			if ( is_array( $key ) )
			{
				foreach ( $key as $k => $v )
				{
					$this->set_tag_category_var( $category_id, $k, $v );
				}

				return;
			}

			foreach ( $this->tag_categories as &$category )
			{
				if ( $category['id'] != $category_id )
				{
					continue;
				}
				
				$category['_vars'][$key] = $value;
			}
		}

		public function get_template_option( $template_id, $key = null, $default = '' )
		{
			return MM('Settings')->get_option( $this->get_template_page_id( $template_id ), $key, $default );
		}

		public function mail_template( $template_id, $args = array() )
		{
			$template = $this->get_template( array( 'id' => $template_id ) );

			if ( ! $template )
			{
				return false;
			}

			$callback = array( &$this, 'parse_tag' );

			$template_args = array
			(
				'to'          => MM_Template::parse_tags( $this->get_template_option( $template['id'], 'to' ), null, $callback ),
				'subject'     => MM_Template::parse_tags( $this->get_template_option( $template['id'], 'subject' ), null, $callback ),
				'message'     => MM_Template::parse_tags( $this->get_template_option( $template['id'], 'message' ), null, $callback ),
				'headers'     => MM_Template::parse_tags( $this->get_template_option( $template['id'], 'headers' ), null, $callback ),
				'attachments' => MM_Template::parse_tags( $this->get_template_option( $template['id'], 'attachments' ), null, $callback ),
				'enable'      => (boolean) $this->get_template_option( $template['id'], 'enable', true )
			);

			extract( array_merge( $template_args, $args ) );

			if ( ! $enable )
			{
				return false;
			}

			return wp_mail( $to, $subject, $message, $headers, $attachments );
		}

		public function parse_tag( $replacement, $tag, $tag_inner, $tag_meta )
		{
			return apply_filters( 'motionmill_mail_parse_tag', $replacement, $this->get_tag( $tag_meta ) );
		}
		
		public function print_tag_info()
		{
			$page = MM('Settings')->get_current_page();

			$template = $this->get_template( array( 'id' => $page['_template'] ) );

			$categories = $this->get_tag_categories( array( 'id' => $template['tag_cats'] ) );

			?>

			<div id="tag-info" class="hide-if-js">

				<h3 class="hide-if-js"><?php _e( 'Tags', Motionmill::TEXTDOMAIN ); ?></h3>

				<div class="tag-info">

					<ul class="hide-if-no-js">
						<?php foreach ( $categories as $category ) : ?>
						<li><a href="#category-<?php echo esc_attr( $category['id'] ); ?>"><?php echo $category['title'] ?></a></li>
						<?php endforeach; ?>
					</ul>

					<?php foreach ( $categories as $category ) : 

					$tags = $this->get_tags( array( 'category' => $category['id'] ) );

					?>
					
					<div id="category-<?php echo esc_attr( $category['id'] ); ?>">

						<?php if ( $category['description'] != '' ) : ?>
						<p><?php echo $category['description'] ?></p>
						<?php endif; ?>

						<ul class="tags">
							<?php foreach ( $tags as $tag ) : ?>
							<li class="hide-if-no-js"><a href="#" class="button insert-button mm-tooltip" title="<?php echo esc_attr( $tag['description'] ); ?>" data-code="<?php echo esc_attr( $this->get_tag_code( $tag ) ); ?>"><?php echo $tag['title']; ?></a></li>
							<li class="hide-if-js"><?php echo $tag['title']; ?><input type="text" value="<?php echo esc_attr( $this->get_tag_code( $tag ) ); ?>" readonly="readonly"></li>
							<?php endforeach; ?>
						</ul><!-- .tags -->

						<br class="clear">

					</div>

					<?php endforeach; ?>

				</div><!-- .info -->

			</div><!-- #tag-info -->

			<?php
		}

		public function on_mail_from_name( $value )
		{
			// only overwrite when WordPress defaults
			if ( stripos( $value, 'wordpress' ) === 0 && $this->get_option( 'wp_mail_from_name' ) != '' )
			{
				$value = $this->get_option( 'wp_mail_from_name' );
			}

			return $value;
		}

		public function on_mail_from( $value )
		{
			// only overwrite when WordPress defaults
			if ( stripos( $value, 'wordpress' ) === 0 && $this->get_option( 'wp_mail_from' ) != '' )
			{
				$value = $this->get_option( 'wp_mail_from' );
			}

			return $value;
		}

		public function on_settings_pages( $pages )
		{
			$pages[] = array
			(
				'id'            => 'motionmill_mail',
				'title'         => __( 'Mail', Motionmill::TEXTDOMAIN ),
				'description'   => __( '', Motionmill::TEXTDOMAIN )
			);

			$pages[] = array
			(
				'id'            => 'motionmill_mail_general',
				'title'         => __( 'General options', Motionmill::TEXTDOMAIN ),
				'description'   => __( '', Motionmill::TEXTDOMAIN ),
				'parent_slug'   => 'motionmill_mail',
				'menu_slug'     => 'motionmill_mail',
				'priority'      => 0
			);

			foreach ( $this->templates as $template )
			{
				$page_id = $this->get_template_page_id( $template['id'] );

				$pages[] = array
				(
					'id'          => $page_id,
					'title'       => $template['title'],
					'description' => $template['description'],
					'parent_slug' => 'motionmill_mail',
					'scripts'     => array( array( 'motionmill-mail', plugins_url( 'js/scripts.js', self::FILE ), array( 'motionmill', 'jquery', 'jquery-ui-tabs', 'thickbox' ), '1.0.0', true ) ),
					'styles'      => array( array( 'motionmill-mail', plugins_url( 'css/style.css', self::FILE ), array( 'jquery-ui' ) ) ),
					'_template'   => $template['id']
				);
			}

			return $pages;
		}

		public function on_settings_sections( $sections )
		{
			$sections[] = array
			(
				'id'          => 'motionmill_mail_general',
				'title'       => __( 'General', Motionmill::TEXTDOMAIN ),
				'description' => __( '', Motionmill::TEXTDOMAIN ),
				'page'        => 'motionmill_mail'
			);

			foreach ( $this->templates as $template )
			{
				$page_id = $this->get_template_page_id( $template['id'] );

				$sections[] = array
				(
					'id'          => sprintf( 'template-%s-general', $template['id'] ),
					'title'       => __( '', Motionmill::TEXTDOMAIN ),
					'description' => __( '', Motionmill::TEXTDOMAIN ),
					'page'        => $page_id
				);

				$sections[] = array
				(
					'id'          => sprintf( 'template-%s-tags', $template['id'] ),
					'title'       => __( '', Motionmill::TEXTDOMAIN ),
					'description' => array( &$this, 'print_tag_info' ),
					'page'        => $page_id
				);
			}

			return $sections;
		}

		public function on_settings_fields( $fields )
		{	
			$fields[] = array
			(
				'id'           => 'wp_mail_from_name',
				'title'        => __( 'Sender name', Motionmill::TEXTDOMAIN ),
				'description'  => __( 'Leave empty to use default.', Motionmill::TEXTDOMAIN ),
				'type'         => 'textfield',
				'value'        => 'Motionmill',
				'section'      => 'motionmill_mail_general',
				'page'         => 'motionmill_mail',
				'multilingual' => true
			);

			$fields[] = array
			(
				'id'           => 'wp_mail_from',
				'title'        => __( 'Sender email', Motionmill::TEXTDOMAIN ),
				'description'  => __( 'Leave empty to use default.', Motionmill::TEXTDOMAIN ),
				'type'         => 'textfield',
				'value'        => 'admin@motionmill.com',
				'section'      => 'motionmill_mail_general',
				'page'         => 'motionmill_mail',
				'multilingual' => true
			);

			foreach ( $this->templates as $template )
			{
				$section = sprintf( 'template-%s-general', $template['id'] );
				$page_id = $this->get_template_page_id( $template['id'] );

				if ( ! is_null( $template['to'] ) )
				{
					$fields[] = array
					(
						'id'           => 'to',
						'title'        => __( 'To', Motionmill::TEXTDOMAIN ),
						'description'  => __( 'The recipient(s). Multiple recipients may be specified using a comma-separated string.', Motionmill::TEXTDOMAIN ),
						'type'         => 'textfield',
						'value'        => $template['to'],
						'section'      => $section,
						'page'         => $page_id,
						'multilingual' => true
					);
				}

				if ( ! is_null( $template['subject'] ) )
				{
					$fields[] = array
					(
						'id'          => 'subject',
						'title'       => __( 'Subject', Motionmill::TEXTDOMAIN ),
						'description' => __( 'The subject of the message.', Motionmill::TEXTDOMAIN ),
						'type'        => 'textfield',
						'value'       => $template['subject'],
						'section'     => $section,
						'page'        => $page_id,
						'multilingual' => true
					);
				}

				if ( ! is_null( $template['message'] ) )
				{
					$fields[] = array
					(
						'id'          => 'message',
						'title'       => __( 'Message', Motionmill::TEXTDOMAIN ),
						'description' => __( 'The Message content.', Motionmill::TEXTDOMAIN ),
						'type'        => 'code',
						'value'       => $template['message'],
						'section'     => $section,
						'page'        => $page_id,
						'multilingual' => true
					);
				}

				if ( ! is_null( $template['headers'] ) )
				{
					$fields[] = array
					(
						'id'          => 'headers',
						'title'       => __( 'Headers', Motionmill::TEXTDOMAIN ),
						'description' => __( 'Mail headers to send with the message. Each header line is delimited with a newline.', Motionmill::TEXTDOMAIN ),
						'type'        => 'code',
						'value'       => $template['headers'],
						'section'     => $section,
						'page'        => $page_id,
						'multilingual' => true
					);
				}

				if ( ! is_null( $template['attachments'] ) )
				{
					$fields[] = array
					(
						'id'           => 'attachments',
						'title'        => __( 'Attachments', Motionmill::TEXTDOMAIN ),
						'description'  => __( ' Files to attach. A newline-delimited list of filenames.', Motionmill::TEXTDOMAIN ),
						'type'         => 'code',
						'value'        => $template['attachments'],
						'section'      => $section,
						'page'         => $page_id,
						'multilingual' => true
					);
				}

				if ( ! is_null( $template['enable'] ) )
				{
					$fields[] = array
					(
						'id'           => 'enable',
						'title'        => __( 'Enable', Motionmill::TEXTDOMAIN ),
						'description'  => __( '', Motionmill::TEXTDOMAIN ),
						'type'         => 'checkbox',
						'value'        => (boolean) $template['enable'],
						'section'      => $section,
						'page'         => $page_id,
						'multilingual' => false
					);
				}
			}

			return $fields;
		}
	}
}

// registers plugin
if ( ! function_exists('motionmill_plugins_add_mail') )
{
	function motionmill_plugins_add_mail( $plugins )
	{
		$plugins[] = 'MM_Mail';

		return $plugins;
	}

	add_filter( 'motionmill_plugins', 'motionmill_plugins_add_mail', 10 );
}

?>
