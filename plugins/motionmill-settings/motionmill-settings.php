<?php if ( ! defined('ABSPATH') ) exit; // Exits when accessed directly
/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Settings
 Plugin URI:
 Description: Settings page
 Version: 1.0.0
 Author: Motionmill
 Author URI: http://www.motionmill.com
------------------------------------------------------------------------------------------------------------------------
*/

add_action( 'motionmill_loaded', function(){

if ( ! class_exists('MM_Settings') )
{
	class MM_Settings extends MM_Plugin
	{
		protected $page_slug         = null;
		protected $page_title        = null;
		protected $page_menu_title   = null;
		protected $page_parent       = null;
		protected $page_capabilities = null;
		protected $page_hook         = null;
		protected $fields            = array();
		protected $sections          = array();
		protected $pages             = array();
		protected $current_page      = null;

		public function __construct()
		{
			parent::__construct();
		}

		public function initialize()
		{
			$this->page_slug         = 'motionmill_settings';
			$this->page_title        = __('Motionmill Settings', MM_TEXTDOMAIN);
			$this->page_menu_title   = __('Settings', MM_TEXTDOMAIN);
			$this->page_parent       = $this->motionmill->page_slug;
			$this->page_capabilities = 'manage_options';

			add_action( 'init', array(&$this, 'on_init'), 100 );
			add_action( 'admin_init', array(&$this, 'on_admin_init') );
			add_action( 'admin_menu', array(&$this, 'on_admin_menu') );
			add_action( 'admin_enqueue_scripts', array(&$this, 'on_admin_enqueue_scripts') );
			add_filter( 'motionmill_page_slug', array(&$this, 'on_motionmill_page_slug') );
			
			register_deactivation_hook( MM_FILE, array(&$this, 'on_deactivate') );
		}

		public function get_option($page_id, $name = null, $default = '')
		{
			$page = MM_Helper::get_element_by( 'id='.$page_id, $this->pages );

			if ( $page )
			{
				$options = get_option( $page['option_name'], $this->get_default_options($page['id']) );

				if ( $name == null )
				{
					return $options;
				}

				if ( isset($options[$name]) )
				{
					return $options[$name];
				}
			}

			return $default;
		}

		public function get_default_options( $page_id )
		{
			$options = array();

			foreach ( MM_Helper::get_elements_by( 'page='.$page_id, $this->fields ) as $field )
			{
				$options[ $field['id'] ] = $field['value'];
			}

			return $options;
		}

		public function on_motionmill_page_slug($default)
		{
			return $this->page_slug;
		}

		public function on_init()
		{
			// registers pages
			foreach ( apply_filters( $this->page_slug . '_pages', array() ) as $data )
			{
				if ( ! is_array($data) || empty($data['id']) )
					continue;

				$this->pages[] = array_merge(array
				(
					'id'          => $data['id'],
					'title'       => $data['id'],
					'description' => '',
					'option_name' => stripos( $data['id'] , 'motionmill_' ) !== 0 ? 'motionmill__' . $data['id'] : $data['id'] // makes sure the option name has following format: {page_slug}_{page_id}
				), $data);
			}

			// registers sections
			foreach ( apply_filters( $this->page_slug . '_sections', array() ) as $data )
			{
				if ( ! is_array($data) || empty($data['id']) )
					continue;

				$this->sections[] = array_merge(array
				(
					'id'          => $data['id'],
					'title'       => '',
					'description' => '',
					'page'        => ''
				), $data);
			}

			// registers fields
			foreach ( apply_filters( $this->page_slug . '_fields', array() ) as $data )
			{
				if ( ! is_array($data) || empty($data['id']) )
					continue;

				$this->fields[] = array_merge(array
				(
					'id'          => $data['id'],
					'title'       => '',
					'description' => '',
					'type'        => 'textfield',
					'value'       => '',
					'page'        => '',
					'section'     => ''
				), $data);
			}
		}

		public function on_admin_init()
		{
			// sets current page
			if ( ! empty($_GET['page']) && $_GET['page'] == $this->page_slug )
			{
				if ( ! empty($_GET['sub']) )
				{
					$this->current_page = MM_Helper::get_element_by( 'id='.$_GET['sub'], $this->pages );
				}

				else if ( count($this->pages) > 0 )
				{
					$this->current_page = $this->pages[0];
				}
			}

			// registers a setting for each page
			foreach ( $this->pages as $page )
			{
				register_setting( $page['id'], $page['option_name'], array(&$this, 'on_sanitize_input') );
			}
			
			foreach ( $this->sections as $section )
			{
				$callback = create_function( '$a', 'echo "' . $section['description'] . '";' );

				add_settings_section( $section['id'], $section['title'], $callback, $section['page'] );
			}

			foreach ( $this->fields as $field )
			{
				$page = MM_Helper::get_element_by( 'id='.$field['page'], $this->pages );

				if ( is_array($field['type']) )
				{
					$callback = $field['type'];
				}

				add_settings_field( $field['id'], $field['title'], array(&$this, 'on_print_field'), $field['page'], $field['section'], array_merge($field, array
				(
					'id'        => $field['id'],
					'label_for' => $field['id'],
					'name'      => $page['option_name'] . '[' . $field['id'] . ']',
					'value'     => $this->get_option( $field['page'], $field['id'] )
				)));
			}
		}

		public function on_print_field($field)
		{
			switch ( $field['type'] )
			{
				case 'textfield':

					$options = array_merge(array
					(
						'id'    => '',
						'class' => 'regular-text',
						'name'  => '',
						'value' => '',
						'extra' => ''
					), $field);

					printf( '<input type="text" id="%s" class="%s" name="%s" value="%s" %s />', esc_attr($options['id']), esc_attr($options['class']), esc_attr($options['name']), esc_attr($options['value']), $options['extra'] );
					
					break;

				case 'password':

					$options = array_merge(array
					(
						'id'    => '',
						'class' => 'regular-text',
						'name'  => '',
						'value' => '',
						'extra' => ''
					), $field);

					printf( '<input type="password" id="%s" class="%s" name="%s" value="%s" %s />', esc_attr($options['id']), esc_attr($options['class']), esc_attr($options['name']), esc_attr($options['value']), $options['extra'] );
					
					break;

				case 'colorpicker':

					$options = array_merge(array
					(
						'id'    => '',
						'class' => 'regular-text colorpicker',
						'name'  => '',
						'value' => '',
						'extra' => ''
					), $field);

					printf( '<input type="text" id="%s" class="%s" name="%s" value="%s" %s />', esc_attr($options['id']), esc_attr($options['class']), esc_attr($options['name']), esc_attr($options['value']), $options['extra'] );
					
					break;

				case 'checkbox':

					$options = array_merge(array
					(
						'id'    => '',
						'class' => '',
						'name'  => '',
						'value' => '',
						'extra' => ''
					), $field);

					$options['extra'] .= checked( $options['value'], 1, false );

					printf( '<input type="checkbox" id="%s" class="%s" name="%s" value="1" %s />', esc_attr($options['id']), esc_attr($options['class']), esc_attr($options['name']), $options['extra'] );
					
					break;

				case 'textarea':

					$options = array_merge(array
					(
						'id'    => '',
						'class' => 'large-text',
						'name'  => '',
						'value' => '',
						'cols'  => 50,
						'rows'  => get_option( 'default_post_edit_rows', 10 ),
						'extra' => ''
					), $field);

					printf( '<textarea id="%s" class="%s" name="%s" cols="%s" rows="%s" %s>%s</textarea>', esc_attr($options['id']), esc_attr($options['class']), esc_attr($options['name']), esc_attr($options['cols']), esc_attr($options['rows']), esc_html($options['extra']), esc_textarea($options['value']) );
					
					break;

				case 'editor':

					$options = array_merge(array
					(
						'id' 		  	=> '',
						'class' 	  	=> '',
						'name'       	=> '',
						'value'       	=> '',
						'rows'        	=> get_option( 'default_post_edit_rows', 10 ),
						'wpautop' 		=> true,
						'media_buttons' => true,
						'editor_css' 	=> '',
						'teeny' 		=> false,
						'dfw' 			=> false,
						'tinymce' 		=> true,
						'quicktags' 	=> true,
						'description'   => ''
					), $field);

					wp_editor( $options['value'], $options['id'], array
					(
						'wpautop' 		=> $options['wpautop'],
						'media_buttons' => $options['media_buttons'],
						'textarea_name' => $options['name'],
						'textarea_rows' => $options['rows'],
						'editor_css' 	=> $options['editor_css'],
						'editor_class' 	=> $options['class'],
						'teeny' 		=> $options['teeny'],
						'dfw' 			=> $options['dfw'],
						'tinymce' 		=> $options['tinymce'],
						'quicktags' 	=> $options['quicktags']
					));

					break;

				case 'dropdown':

					$options = array_merge(array
					(
						'id'          => '',
						'class'       => '',
						'name'        => '',
						'options'     => array(),
						'value'       => '',
						'extra'       => '',
						'description' => ''
					), (array) $args);

					printf( '<select id="%s" class="%s" name="%s" %s>', esc_attr($options['id']), esc_attr($options['class']), esc_attr($options['name']), $options['extra'] );

					foreach ( $options['options'] as $key => $value )
					{
						printf( '<option value="%s"%s>%s</option>', esc_attr($key), selected( $key, $options['value'], false ), esc_html($value) );
					}

					print( '</select>' );

					break;
				
				default:

					do_action( $this->page_slug . '_print_field_type_' . $field['type'], $field );
			}

			// description
			if ( $field['description'] != '' )
			{
				printf( '<p class="description">%s</p>', $field['description'] );
			}
		}
		
		public function on_admin_menu()
		{
			$this->page_hook = add_submenu_page( $this->page_parent, $this->page_title, $this->page_menu_title, $this->page_capabilities, $this->page_slug, array(&$this, 'on_print_page') );
		}

		public function on_admin_enqueue_scripts()
		{
			$screen = get_current_screen();

			if ( $screen->id != $this->page_hook )
				return;

			if ( ! $this->current_page )
				return;
			
			// colorpicker
			wp_enqueue_script( 'iris' );

			// general
			wp_enqueue_style( $this->page_slug . '-style', plugins_url('css/style.css', __FILE__), null, '1.0.0', 'all' );
			wp_enqueue_script( $this->page_slug . '-scripts',  plugins_url('js/scripts.js', __FILE__), array('jquery'), '1.0.0', true );
			wp_localize_script( $this->page_slug . '-scripts', 'MM_Settings', array
			(
				'page_hook' => $this->page_hook,
				'page_slug' => $this->page_slug
			));

			do_action( $this->page_slug . '_enqueue_scripts', $this->current_page['id'] );
		}

		public function on_print_page()
		{
			?>

			<div class="wrap">

				<h2><?php echo esc_html( $this->page_title ); ?></h2>

				<?php if ( $this->current_page ) : ?>
				<?php settings_errors( $this->current_page['id'] ); ?>
				<?php endif; ?>

				<?php if ( empty($this->pages) ) : ?>
				<p><?php _e( 'No settings available.', MM_TEXTDOMAIN ); ?></p>
				<?php else : ?>

				<h2 class="nav-tab-wrapper">
					<?php foreach ( $this->pages as $page ) : ?>
					<a href="?page=<?php echo esc_attr($this->page_slug); ?>&sub=<?php echo esc_attr($page['id']); ?>" class="nav-tab<?php echo $this->current_page && $page['id'] == $this->current_page['id'] ? ' nav-tab-active' : ''; ?>"><?php echo esc_html( $page['title'] ); ?></a>
					<?php endforeach; ?>
				</h2>

				<?php if ( ! $this->current_page ) : ?>
				<p><?php _e( 'The requested page could not be found.', MM_TEXTDOMAIN ); ?></p>
				<?php else : ?>

				<form action="options.php" method="post">

					<?php settings_fields( $this->current_page['id'] ); ?>

					<?php echo $this->current_page['description']; ?>
					
					<?php do_settings_sections( $this->current_page['id'] ); ?>

					<?php submit_button(); ?>

				</form>

				<?php endif; ?>
				<?php endif; ?>

			</div><!-- .wrap-->

			<?php
		}

		public function on_sanitize_input($input)
		{
			$page_id = $_POST['option_page'];

			// makes sure the 'settings saved' message is set
			if ( count( get_settings_errors( $page_id ) ) == 0 )
			{
				add_settings_error( $page_id, 'settings_saved', __( 'Settings saved.', MM_TEXTDOMAIN ), 'updated' );
			}

			return apply_filters( $this->page_slug . '_sanitize_input', $input, $page_id );
		}

		public function on_deactivate()
		{
			foreach ( $this->pages as $page )
			{
				unregister_setting( $page['id'], $page['option_name'], array(&$this, 'on_sanitize_input') );
			}
		}
	}

	function motionmill_plugins_add_settings($plugins)
	{
		array_push( $plugins , 'MM_Settings' );

		return $plugins;
	}

	add_filter( 'motionmill_plugins', 'motionmill_plugins_add_settings', 0 );

}

});

?>